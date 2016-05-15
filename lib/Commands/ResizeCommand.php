<?php

namespace Acme\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Album;

class ResizeCommand extends Command
{
    private $spot;

    protected function configure()
    {
        $this
            ->setName('resize')
            ->setDescription('Resize photos');
        
        $dotenv = new \Dotenv\Dotenv(__DIR__."/../..");
        $dotenv->load();
        $config = new \Spot\Config();
        $config->addConnection("mysql", [
            "dbname" => getenv("DB_NAME"),
            "user" => getenv("DB_USER"),
            "password" => getenv("DB_PASSWORD"),
            "host" => getenv("DB_HOST"),
            "driver" => "pdo_mysql",
            "charset" => "utf8"
        ]);
        $this->spot = new \Spot\Locator($config);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $photo_mapper = $this->spot->mapper("App\\Photo");
        $resize_mapper = $this->spot->mapper("App\\ResizedPhoto");

        $resized_photo = $resize_mapper
            ->where(['status' => 'new'])
            ->order(['created_at' => 'ASC'])
            ->first();

        if ($resized_photo === false) {
            $output->writeln('No more images.');
            return;
        }

        $resized_photo->status = 'in_progress';
        $resize_mapper->save($resized_photo);

        $original_photo = $photo_mapper->get($resized_photo->photo_id);

        try {
            $this->resize(
                $original_photo->image,
                $this->name($original_photo->image, $resized_photo->size),
                $resized_photo->size
            );
        }
        catch (\Exception $e){
            $resized_photo->status = 'error';
            $resize_mapper->save($resized_photo);
            $output->writeln($e->getMessage());
            return;
        }

        $resized_photo->src = $this->name($original_photo->image, $resized_photo->size);
        $resized_photo->status = 'complete';
        if(false === $resize_mapper->save($resized_photo)){
            $output->writeln('Error while updating.');
            return;
        }

        $output->writeln('Done.');
    }

    protected function name($image, $size)
    {
        $dot = strrpos($image, ".");
        $filename = substr($image, 0, $dot);
        $type = substr($image, $dot);
        return $filename . "-$size" . $type;
    }

    protected function resize($originalFile, $targetFile, $new_width)
    {
        $originalFile = __DIR__ . "\\..\\.." . $originalFile;
        $targetFile = __DIR__ . "\\..\\.." . $targetFile;
        $info = getimagesize($originalFile);
        if(!$info){
            throw new \Exception('File not found.' . $originalFile);
        }
        $mime = $info['mime'];

        switch ($mime) {
            case 'image/jpeg':
                $image_create_func = 'imagecreatefromjpeg';
                $image_save_func = 'imagejpeg';
                break;

            case 'image/png':
                $image_create_func = 'imagecreatefrompng';
                $image_save_func = 'imagepng';
                break;

            case 'image/gif':
                $image_create_func = 'imagecreatefromgif';
                $image_save_func = 'imagegif';
                break;

            default:
                throw new \Exception('Unknown image type:' . $originalFile);
        }

        list($width_orig, $height_orig) = getimagesize($originalFile);
        $width = $height = $new_width;

        $image = $image_create_func($originalFile);

        $ratio_orig = $width_orig / $height_orig;

        $tmp = imagecreatetruecolor($width, $width);
        $red = imagecolorallocate($image, 0, 0, 0);
        imagefill($image, 0, 0, $red);
        $x = $y = 0;
        if (1 > $ratio_orig) {
            $width = $height * $ratio_orig;
            $y = ($new_width - $width) / 2;
        } else {
            $height = $width / $ratio_orig;
            $x = ($new_width - $height) / 2;
        }
        imagecopyresampled($tmp, $image, $y, $x, 0, 0, $width, $height, $width_orig, $height_orig);

        if (file_exists($targetFile)) {
            unlink($targetFile);
        }
        if (false === $image_save_func($tmp, $targetFile)) {
            throw new \Exception('Could not save file: ' . $targetFile);
        }
    }
}
