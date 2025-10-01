<?php
class Attendance {
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    public function getStudentsByTeacher($teacher_id){
        // This is a simplified query. In a real app, you'd link students to teachers via classes/subjects.
        // For now, we assume a teacher can see all students.
        $this->db->query("SELECT u.id, u.name FROM users u JOIN students s ON u.id = s.user_id WHERE u.role = 'student'");
        return $this->db->resultSet();
    }

    public function getAttendance($teacher_id, $date){
        $this->db->query("SELECT a.student_id, a.status FROM attendance a WHERE a.teacher_id = :teacher_id AND a.date = :date");
        $this->db->bind(':teacher_id', $teacher_id);
        $this->db->bind(':date', $date);
        $results = $this->db->resultSet();

        $attendance_data = [];
        foreach($results as $row){
            $attendance_data[$row->student_id] = $row->status;
        }
        return $attendance_data;
    }

    public function saveAttendance($data){
        // First, delete existing records for this date to avoid duplicates
        $this->db->query("DELETE FROM attendance WHERE teacher_id = :teacher_id AND date = :date");
        $this->db->bind(':teacher_id', $data['teacher_id']);
        $this->db->bind(':date', $data['date']);
        $this->db->execute();

        // Now, insert the new attendance records
        $this->db->query("INSERT INTO attendance (student_id, teacher_id, status, date) VALUES (:student_id, :teacher_id, :status, :date)");

        foreach($data['attendance'] as $student_id => $status){
            $this->db->bind(':student_id', $student_id);
            $this->db->bind(':teacher_id', $data['teacher_id']);
            $this->db->bind(':status', $status);
            $this->db->bind(':date', $data['date']);
            $this->db->execute();
        }

        return true;
    }

    public function getAttendanceForStudent($student_id){
        $this->db->query("SELECT * FROM attendance WHERE student_id = :student_id ORDER BY date DESC");
        $this->db->bind(':student_id', $student_id);
        return $this->db->resultSet();
    }
}
?>