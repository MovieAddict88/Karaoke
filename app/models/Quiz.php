<?php
class Quiz {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function getQuizzesByTeacher($teacher_id){
        $this->db->query("SELECT * FROM quizzes WHERE teacher_id = :teacher_id ORDER BY date DESC");
        $this->db->bind(':teacher_id', $teacher_id);
        return $this->db->resultSet();
    }

    public function addQuiz($data){
        $this->db->query('INSERT INTO quizzes (teacher_id, title, date) VALUES (:teacher_id, :title, :date)');
        $this->db->bind(':teacher_id', $data['teacher_id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':date', $data['date']);

        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }

    public function getQuizById($id){
        $this->db->query("SELECT * FROM quizzes WHERE id = :id");
        $this->db->bind(':id', $id);
        $row = $this->db->single();
        return $row;
    }

    public function updateQuiz($data){
        $this->db->query('UPDATE quizzes SET title = :title, date = :date WHERE id = :id');
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':date', $data['date']);

        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }

    public function deleteQuiz($id){
        $this->db->query('DELETE FROM quizzes WHERE id = :id');
        $this->db->bind(':id', $id);
        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }
}
?>