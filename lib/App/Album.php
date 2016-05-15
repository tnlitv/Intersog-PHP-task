<?php


namespace App;

class Album extends \Spot\Entity
{
    protected static $table = "album";
    public static function fields()
    {
        return [
            'id'           => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'user'         => ['type' => 'integer', 'required' => true],
            'name'         => ['type' => 'string', 'required' => true],
            'active'       => ['type' => 'smallint', 'required' => true, 'default' => 1],
            'created_at'   => ['type' => 'datetime', 'required' => true, 'value' => new \DateTime()],
            'modified_at'  => ['type' => 'datetime']
        ];
    }
}