<?php


namespace App;

use Spot\EntityInterface;
use Spot\MapperInterface;

class ResizedPhoto extends \Spot\Entity
{
    protected static $table = "resized_photos";
    public static function fields()
    {
        return [
            'id'           => ['type' => 'integer',  'primary' => true, 'autoincrement' => true],
            'photo_id'     => ['type' => 'integer',  'required' => true, 'unique' => 'id_size'],
            'size'         => ['type' => 'integer',  'required' => true, 'unique' => 'id_size'],
            'src'          => ['type' => 'string',  'default' => NULL],
            'status'       => ['type' => 'smallint', 'required' => true, 'value' => 'new'],
            'created_at'   => ['type' => 'datetime', 'required' => true, 'value' => new \DateTime()],
            'comment'      => ['type' => 'string']
        ];
    }
    public static function relations(MapperInterface $mapper, EntityInterface $entity)
    {
        return [
            'photo' => $mapper->belongsTo($entity, 'App\Photo', 'photo_id')
        ];
    }
}