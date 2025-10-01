<?php
class Grade {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function getGradesForTeacher($teacher_id){
        $this->db->query("SELECT
                              g.id as gradeId,
                              u.name as studentName,
                              g.subject,
                              g.grade,
                              g.date
                          FROM grades as g
                          JOIN students as s ON g.student_id = s.id
                          JOIN users as u ON s.user_id = u.id
                          WHERE g.teacher_id = :teacher_id
                          ORDER BY g.date DESC");
        $this->db->bind(':teacher_id', $teacher_id);
        return $this->db->resultSet();
    }

    public function addGrade($data){
        $this->db->query('INSERT INTO grades (student_id, teacher_id, subject, grade, date) VALUES (:student_id, :teacher_id, :subject, :grade, :date)');
        $this->db->bind(':student_id', $data['student_id']);
        $this->db->bind(':teacher_id', $data['teacher_id']);
        $this->db->bind(':subject', $data['subject']);
        $this->db->bind(':grade', $data['grade']);
        $this->db->bind(':date', $data['date']);

        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }

    public function getGradeById($id){
        $this->db->query("SELECT * FROM grades WHERE id = :id");
        $this->db->bind(':id', $id);
        $row = $this->db->single();
        return $row;
    }

    public function updateGrade($data){
        $this->db->query('UPDATE grades SET student_id = :student_id, subject = :subject, grade = :grade, date = :date WHERE id = :id');
        $this->db->bind(':id', $data['id']);
        $this->db->bind(':student_id', $data['student_id']);
        $this->db->bind(':subject', $data['subject']);
        $this->db->bind(':grade', $data['grade']);
        $this->db->bind(':date', $data['date']);

        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }

    public function deleteGrade($id){
        $this->db->query('DELETE FROM grades WHERE id = :id');
        $this->db->bind(':id', $id);
        if($this->db->execute()){
            return true;
        } else {
            return false;
        }
    }

    public function getGradesForStudent($student_id){
        $this->db->query("SELECT * FROM grades WHERE student_id = :student_id ORDER BY date DESC");
        $this->db->bind(':student_id', $student_id);
        return $this->db->resultSet();
    }
}
?>