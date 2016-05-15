<?php


namespace App;

use Spot\EntityInterface;
use Spot\MapperInterface;
use Spot\EventEmitter;

class User extends \Spot\Entity
{
    protected static $table = "users";
    public static function fields()
    {
        return [
            'id'           => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'role'         => ['type' => 'integer', 'required' => true],
            'name'         => ['type' => 'string', 'required' => true],
            'username'     => ['type' => 'string', 'required' => true],
            'password'     => ['type' => 'string', 'required' => true],
            'phone'        => ['type' => 'string',  'default' => null],
            'email'        => ['type' => 'string',  'default' => null],
            'created_at'   => ['type' => 'datetime',  'value' => new \DateTime()],
            'modified_at'  => ['type' => 'datetime']
        ];
    }
}