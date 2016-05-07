<?php
class AlbumEntity
{
    protected $id;
    protected $user;
    protected $name;
    protected $active;
    protected $created_at;
    protected $modified_at;
    
    public function __construct(array $data) {
        // no id if we're creating
        if(isset($data['id'])) {
            $this->id = $data['id'];
        }
        $this->name = $data['name'];
        $this->active = $data['active'];
        $this->user = $data['user'];
    }
    public function getId() {
        return $this->id;
    }
    public function getUserId(){
        return $this->user;
    }
    public function getName() {
        return $this->name;
    }
    public function isActive() {
        return $this->active;
    }
    public function getCreatedAt() {
        return $this->created_at;
    }
    public function getModifiedAt() {
        return $this->modified_at;
    }
}