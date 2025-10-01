<?php
class Student {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function getStudents(){
        $this->db->query("SELECT *,
                              students.id as studentId,
                              users.id as userId
                              FROM students
                              INNER JOIN users
                              ON students.user_id = users.id
                              ");
        $results = $this->db->resultSet();
        return $results;
    }

    public function addStudent($data){
        // Add user first
        $this->db->query('INSERT INTO users (name, email, password, role) VALUES(:name, :email, :password, :role)');
        // Bind values
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':role', 'student');

        if($this->db->execute()){
            $user_id = $this->db->lastInsertId();
            // Then add student
            $this->db->query('INSERT INTO students (user_id, qr_code) VALUES(:user_id, :qr_code)');
            $this->db->bind(':user_id', $user_id);
            $this->db->bind(':qr_code', $data['qr_code']);

            if($this->db->execute()){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getStudentById($id){
        $this->db->query("SELECT *,
                              students.id as studentId,
                              users.id as userId
                              FROM students
                              INNER JOIN users
                              ON students.user_id = users.id
                              WHERE students.id = :id
                              ");
        $this->db->bind(':id', $id);

        $row = $this->db->single();

        return $row;
    }

    public function updateStudent($data){
        // Update user table
        $this->db->query('UPDATE users SET name = :name, email = :email WHERE id = :user_id');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':user_id', $data['user_id']);

        if($this->db->execute()){
            // Update students table
            $this->db->query('UPDATE students SET qr_code = :qr_code WHERE id = :student_id');
            $this->db->bind(':qr_code', $data['qr_code']);
            $this->db->bind(':student_id', $data['student_id']);

            if($this->db->execute()){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function deleteStudent($id){
        $student = $this->getStudentById($id);
        $user_id = $student->userId;

        // Delete from students table first
        $this->db->query('DELETE FROM students WHERE id = :id');
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

    public function getStudentByUserId($user_id){
        $this->db->query("SELECT * FROM students WHERE user_id = :user_id");
        $this->db->bind(':user_id', $user_id);
        $row = $this->db->single();
        return $row;
    }
}
?>