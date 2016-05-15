<?php

namespace App;

use Spot\Entity;
use Spot\Locator;
use Spot\Mapper;
use Spot\EventEmitter;

class Photo extends \Spot\Entity
{
    protected static $table = "album/images";
    public static function fields()
    {
        return [
            'id'           => ['type' => 'integer',  'primary' => true, 'autoincrement' => true],
            'album'        => ['type' => 'integer',  'required' => true],
            'image'        => ['type' => 'string',   'required' => true],
            'created_at'   => ['type' => 'datetime', 'required' => true, 'value' => new \DateTime()],
        ];
    }

    public static function events(EventEmitter $eventEmitter)
    {
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
        $spot = new \Spot\Locator($config);
        
        $eventEmitter->on('afterInsert', function (Entity $entity, Mapper $mapper) use ($spot){
            $resized_mapper = $spot->mapper("App\\ResizedPhoto");
            $resized_mapper->create([
                'photo_id' => $entity->id,
                'size' => 100
            ]);
            $resized_mapper->create([
                'photo_id' => $entity->id,
                'size' => 400
            ]);
        });
    }
}