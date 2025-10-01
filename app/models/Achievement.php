<?php
class Achievement {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function getAchievementsByTeacher($teacher_id){
        $this->db->query("SELECT
                              a.id as achievementId,
                              u.name as studentName,
                              a.achievement,
                              a.date
                          FROM achievements as a
                          JOIN students as s ON a.student_id = s.id
                          JOIN users as u ON s.user_id = u.id
                          WHERE a.teacher_id = :teacher_id
                          ORDER BY a.date DESC");
        $this->db->bind(':teacher_id', $teacher_id);
        return $this->db->resultSet();
    }

    public function addAchievement($data){
        $this->db->query('INSERT INTO achievements (student_id, teacher_id, achievement, date) VALUES (:student_id, :teacher_id, :achievement, :date)');
        $this->db->bind(':student_id', $data['student_id']);
        $this->db->bind(':teacher_id', $data['teacher_id']);
        $this->db->bind(':achievement', $data['achievement']);
        $this->db->bind(':date', $data['date']);

        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }

    public function getAchievementById($id){
        $this->db->query("SELECT * FROM achievements WHERE id = :id");
        $this->db->bind(':id', $id);
        $row = $this->db->single();
        return $row;
    }

    public function updateAchievement($data){
        $this->db->query('UPDATE achievements SET student_id = :student_id, achievement = :achievement, date = :date WHERE id = :id');
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':student_id', $data['student_id']);
        $this->db->bind(':achievement', $data['achievement']);
        $this->db->bind(':date', $data['date']);

        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }

    public function deleteAchievement($id){
        $this->db->query('DELETE FROM achievements WHERE id = :id');
        $this->db->bind(':id', $id);
        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }
}
?>