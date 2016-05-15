<?php


namespace App;

use Spot\EntityInterface;
use Spot\MapperInterface;
use Spot\EventEmitter;

class AlbumClient extends \Spot\Entity
{
    protected static $table = "album/clients";
    public static function fields()
    {
        return [
            'id'           => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'album'         => ['type' => 'integer', 'required' => true],
            'user'         => ['type' => 'integer', 'required' => true],
            'access'       => ['type' => 'integer', 'required' => true, 'default' => 0],
            'created_at'   => ['type' => 'datetime', 'required' => true, 'value' => new \DateTime()],
        ];
    }
}