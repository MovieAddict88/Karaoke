<?php
class ParentM {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function getParents(){
        $this->db->query("SELECT
                              p.id as parentId,
                              u.name as parentName,
                              u.email as parentEmail,
                              s_u.name as studentName
                          FROM parents as p
                          JOIN users as u ON p.user_id = u.id
                          JOIN students as s ON p.student_id = s.id
                          JOIN users as s_u ON s.user_id = s_u.id
                          WHERE u.role = 'parent'");
        $results = $this->db->resultSet();
        return $results;
    }

    public function addParent($data){
        // Add user first
        $this->db->query('INSERT INTO users (name, email, password, role) VALUES(:name, :email, :password, :role)');
        // Bind values
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $data['password']);
        $this->db->bind(':role', 'parent');

        if($this->db->execute()){
            $user_id = $this->db->lastInsertId();
            // Then add parent relationship
            $this->db->query('INSERT INTO parents (user_id, student_id) VALUES(:user_id, :student_id)');
            $this->db->bind(':user_id', $user_id);
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

    public function getParentById($id){
        $this->db->query("SELECT *,
                              parents.id as parentId,
                              users.id as userId
                              FROM parents
                              INNER JOIN users
                              ON parents.user_id = users.id
                              WHERE parents.id = :id
                              ");
        $this->db->bind(':id', $id);

        $row = $this->db->single();

        return $row;
    }


    public function updateParent($data){
        // Update user table
        $this->db->query('UPDATE users SET name = :name, email = :email WHERE id = :user_id');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':user_id', $data['user_id']);

        if($this->db->execute()){
             // Update parent relationship
            $this->db->query('UPDATE parents SET student_id = :student_id WHERE id = :parent_id');
            $this->db->bind(':student_id', $data['student_id']);
            $this->db->bind(':parent_id', $data['parent_id']);
            if($this->db->execute()){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function deleteParent($id){
        $parent = $this->getParentById($id);
        $user_id = $parent->userId;

        // Delete from parents table first
        $this->db->query('DELETE FROM parents WHERE id = :id');
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

    public function getParentUserIdByStudentId($student_id){
        $this->db->query("SELECT user_id FROM parents WHERE student_id = :student_id");
        $this->db->bind(':student_id', $student_id);
        $row = $this->db->single();
        return $row ? $row->user_id : null;
    }
}
?>