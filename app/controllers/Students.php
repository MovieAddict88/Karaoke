<?php
class Students extends Controller {
    public function __construct(){
        if(!isLoggedIn() || $_SESSION['user_role'] != 'student'){
            redirect('users/login');
        }
        $this->studentModel = $this->model('Student');
        $this->gradeModel = $this->model('Grade');
        $this->attendanceModel = $this->model('Attendance');
    }

    public function index(){
        $student_user_id = $_SESSION['user_id'];

        // Get student table id from the user_id in the session
        $student = $this->studentModel->getStudentByUserId($student_user_id);
        $student_id = $student->id;

        // Get student data (grades, attendance, etc.)
        $grades = $this->gradeModel->getGradesForStudent($student_id);
        $attendance = $this->attendanceModel->getAttendanceForStudent($student_id);

        $data = [
            'title' => 'Your Dashboard',
            'grades' => $grades,
            'attendance' => $attendance
        ];

        $this->view('students/index', $data);
    }
}
?>