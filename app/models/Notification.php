<?php
class Notification {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function createNotification($user_id, $message){
        $this->db->query("INSERT INTO notifications (user_id, message) VALUES (:user_id, :message)");
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':message', $message);

        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }

    public function getUnreadNotifications($user_id){
        $this->db->query("SELECT * FROM notifications WHERE user_id = :user_id AND is_read = 0 ORDER BY created_at DESC");
        $this->db->bind(':user_id', $user_id);
        return $this->db->resultSet();
    }

    public function markAsRead($notification_id){
        $this->db->query("UPDATE notifications SET is_read = 1 WHERE id = :id");
        $this->db->bind(':id', $notification_id);

        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }
}
?>