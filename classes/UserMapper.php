<?php
class UserMapper extends Mapper
{
    public function getUsers() {
        $sql = "SELECT *
            from Users u
            join components c on (c.id = t.component_id)";
        $stmt = $this->db->query($sql);
        $results = [];
        while($row = $stmt->fetch()) {
            $results[] = new UserEntity($row);
        }
        return $results;
    }
    /**
     * Get one ticket by its ID
     *
     * @param int $ticket_id The ID of the ticket
     * @return UserEntity  The ticket
     */
    public function getTicketById($ticket_id) {
        $sql = "SELECT t.id, t.title, t.description, c.component
            from tickets t
            join components c on (c.id = t.component_id)
            where t.id = :ticket_id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(["ticket_id" => $ticket_id]);
        if($result) {
            return new UserEntity($stmt->fetch());
        }
    }
    public function save(UserEntity $ticket) {
        $sql = "insert into tickets
            (title, description, component_id) values
            (:title, :description, 
            (select id from components where component = :component))";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "title" => $ticket->getTitle(),
            "description" => $ticket->getDescription(),
            "component" => $ticket->getComponent(),
        ]);
        if(!$result) {
            throw new Exception("could not save record");
        }
    }
}