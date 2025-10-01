<?php
class Teacher {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function getTeachers(){
        $this->db->query("SELECT *,
                              teachers.id as teacherId,
                              users.id as userId
                              FROM teachers
                              INNER JOIN users
                              ON teachers.user_id = users.id
                              ");
        $results = $this->db->resultSet();
        return $results;
    }

    public function addTeacher($data){
        // Add user first
        $this->db->query('INSERT INTO users (name, email, password, role) VALUES(:name, :email, :password, :role)');
        // Bind values
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':role', 'teacher');

        if($this->db->execute()){
            $user_id = $this->db->lastInsertId();
            // Then add teacher
            $this->db->query('INSERT INTO teachers (user_id) VALUES(:user_id)');
            $this->db->bind(':user_id', $user_id);

            if($this->db->execute()){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getTeacherById($id){
        $this->db->query("SELECT *,
                              teachers.id as teacherId,
                              users.id as userId
                              FROM teachers
                              INNER JOIN users
                              ON teachers.user_id = users.id
                              WHERE teachers.id = :id
                              ");
        $this->db->bind(':id', $id);

        $row = $this->db->single();

        return $row;
    }

    public function getTeacherByUserId($user_id){
        $this->db->query("SELECT * FROM teachers WHERE user_id = :user_id");
        $this->db->bind(':user_id', $user_id);
        $row = $this->db->single();
        return $row;
    }

    public function updateTeacher($data){
        // Update user table
        $this->db->query('UPDATE users SET name = :name, email = :email WHERE id = :user_id');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':user_id', $data['user_id']);

        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }

    public function deleteTeacher($id){
        $teacher = $this->getTeacherById($id);
        $user_id = $teacher->userId;

        // Delete from teachers table first
        $this->db->query('DELETE FROM teachers WHERE id = :id');
        $this->db->bind(':id', $id);

        if($this->db->execute()){
            // Then delete from users table
            $this->db->query('DELETE FROM users WHERE id = :user_id');
            $this->db->bind(':user_id', $user_id);

            if($this->db->execute()){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
?>