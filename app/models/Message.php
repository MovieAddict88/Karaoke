<?php
class Message {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function getConversations($user_id){
        // This query finds all unique users that the given user has communicated with
        $this->db->query("SELECT DISTINCT
                              u.id,
                              u.name,
                              u.role
                          FROM users u
                          JOIN messages m ON u.id = m.sender_id OR u.id = m.receiver_id
                          WHERE (m.sender_id = :user_id OR m.receiver_id = :user_id) AND u.id != :user_id");
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    public function getMessages($user1_id, $user2_id){
        $this->db->query("SELECT * FROM messages
                          WHERE (sender_id = :user1_id AND receiver_id = :user2_id)
                          OR (sender_id = :user2_id AND receiver_id = :user1_id)
                          ORDER BY timestamp ASC");
        $this->db->bind(':user1_id', $user1_id);
        $this->db->bind(':user2_id', $user2_id);
        return $this->db->resultSet();
    }

    public function sendMessage($data){
        $this->db->query("INSERT INTO messages (sender_id, receiver_id, message) VALUES (:sender_id, :receiver_id, :message)");
        $this->db->bind(':sender_id', $data['sender_id']);
        $this->db->bind(':receiver_id', $data['receiver_id']);
        $this->db->bind(':message', $data['message']);

        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }
}
?>