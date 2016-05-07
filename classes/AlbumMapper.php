<?php
//require ("Mapper.php");
class AlbumMapper extends Mapper
{
    public function getAlbums($user_id) {
        $sql = "SELECT id, user,  name, active, created_at
            from album
            where user = $user_id";
        $stmt = $this->db->query($sql);
        $results = [];
        while($row = $stmt->fetch()) {
            $results[] = new AlbumEntity($row);
        }
        return $results;
    }
    /**
     * Get one ticket by its ID
     *
     * @param int $ticket_id The ID of the ticket
     * @return UserEntity  The ticket
     */
    public function getAlbumById($album_id) {
        $sql = "SELECT id
            from 'album/images' a
            where a.id = :album_id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(["album_id" => $album_id]);
        if($result) {
            return new AlbumEntity($stmt->fetch());
        }
    }
    public function save(AlbumEntity $album) {
        $sql = "insert into album
            (user, name, active) values
            (:user, :name, :active)";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "user" => $album->getUserId(),
            "name" => $album->getName(),
            "active" => $album->isActive(),
        ]);
        if(!$result) {
            throw new Exception("could not save record");
        }
    }
}