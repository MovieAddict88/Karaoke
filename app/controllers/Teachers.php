<?php
class Teachers extends Controller {
    public function __construct(){
        if(!isLoggedIn() || $_SESSION['user_role'] != 'teacher'){
            redirect('users/login');
        }
        $this->attendanceModel = $this->model('Attendance');
        $this->teacherModel = $this->model('Teacher');
        $this->gradeModel = $this->model('Grade');
        $this->studentModel = $this->model('Student');
        $this->quizModel = $this->model('Quiz');
        $this->achievementModel = $this->model('Achievement');
        $this->notificationModel = $this->model('Notification');
        $this->parentModel = $this->model('ParentM');
    }

    public function index(){
        $data = [
            'title' => 'Teacher Dashboard'
        ];
        $this->view('teachers/index', $data);
    }

    public function attendance(){
        $teacher_user_id = $_SESSION['user_id'];
        $teacher = $this->teacherModel->getTeacherByUserId($teacher_user_id);
        $teacher_id = $teacher->id;

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            $date = $_POST['attendance_date'];
            $attendance_data = $_POST['attendance'];

            $data_to_save = [
                'teacher_id' => $teacher_id,
                'date' => $date,
                'attendance' => $attendance_data
            ];

            if($this->attendanceModel->saveAttendance($data_to_save)){
                flash('attendance_message', 'Attendance Saved');
                redirect('teachers/attendance?date=' . $date);
            } else {
                die('Something went wrong');
            }

        } else {
            $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
            $students = $this->attendanceModel->getStudentsByTeacher($teacher_id);
            $attendance = $this->attendanceModel->getAttendance($teacher_id, $date);

            $data = [
                'students' => $students,
                'attendance_date' => $date,
                'attendance' => $attendance
            ];

            $this->view('teachers/attendance', $data);
        }
    }

    public function grades(){
        $teacher_user_id = $_SESSION['user_id'];
        $teacher = $this->teacherModel->getTeacherByUserId($teacher_user_id);
        $teacher_id = $teacher->id;

        $grades = $this->gradeModel->getGradesForTeacher($teacher_id);

        $data = [
            'grades' => $grades
        ];

        $this->view('teachers/grades/index', $data);
    }

    public function add_grade(){
        $teacher_user_id = $_SESSION['user_id'];
        $teacher = $this->teacherModel->getTeacherByUserId($teacher_user_id);
        $teacher_id = $teacher->id;

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            $data = [
                'student_id' => trim($_POST['student_id']),
                'teacher_id' => $teacher_id,
                'subject' => trim($_POST['subject']),
                'grade' => trim($_POST['grade']),
                'date' => trim($_POST['date']),
                'student_id_err' => '',
                'subject_err' => '',
                'grade_err' => '',
                'date_err' => ''
            ];

            if(empty($data['subject'])){
                $data['subject_err'] = 'Please enter subject';
            }
            if(empty($data['grade'])){
                $data['grade_err'] = 'Please enter grade';
            }
            if(empty($data['date'])){
                $data['date_err'] = 'Please enter date';
            }

            if(empty($data['subject_err']) && empty($data['grade_err']) && empty($data['date_err'])){
                if($this->gradeModel->addGrade($data)){
                    // Notify parent
                    $parentUserId = $this->parentModel->getParentUserIdByStudentId($data['student_id']);
                    if($parentUserId){
                        $student = $this->studentModel->getStudentById($data['student_id']);
                        $message = "Your child, " . $student->name . ", received a new grade: " . $data['grade'] . " in " . $data['subject'];
                        $this->notificationModel->createNotification($parentUserId, $message);
                    }
                    flash('grade_message', 'Grade Added');
                    redirect('teachers/grades');
                } else {
                    die('Something went wrong');
                }
            } else {
                $students = $this->studentModel->getStudents();
                $data['students'] = $students;
                $this->view('teachers/grades/add', $data);
            }
        } else {
            $students = $this->studentModel->getStudents();
            $data = [
                'students' => $students,
                'subject' => '',
                'grade' => '',
                'date' => ''
            ];
            $this->view('teachers/grades/add', $data);
        }
    }

    public function edit_grade($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            $data = [
                'id' => $id,
                'student_id' => trim($_POST['student_id']),
                'subject' => trim($_POST['subject']),
                'grade' => trim($_POST['grade']),
                'date' => trim($_POST['date']),
                'student_id_err' => '',
                'subject_err' => '',
                'grade_err' => '',
                'date_err' => ''
            ];

            if(empty($data['subject'])){
                $data['subject_err'] = 'Please enter subject';
            }
            if(empty($data['grade'])){
                $data['grade_err'] = 'Please enter grade';
            }
            if(empty($data['date'])){
                $data['date_err'] = 'Please enter date';
            }

            if(empty($data['subject_err']) && empty($data['grade_err']) && empty($data['date_err'])){
                if($this->gradeModel->updateGrade($data)){
                    flash('grade_message', 'Grade Updated');
                    redirect('teachers/grades');
                } else {
                    die('Something went wrong');
                }
            } else {
                $students = $this->studentModel->getStudents();
                $data['students'] = $students;
                $this->view('teachers/grades/edit', $data);
            }
        } else {
            $grade = $this->gradeModel->getGradeById($id);
            $students = $this->studentModel->getStudents();

            $data = [
                'id' => $id,
                'students' => $students,
                'student_id' => $grade->student_id,
                'subject' => $grade->subject,
                'grade' => $grade->grade,
                'date' => $grade->date
            ];

            $this->view('teachers/grades/edit', $data);
        }
    }

    public function delete_grade($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($this->gradeModel->deleteGrade($id)){
                flash('grade_message', 'Grade Removed');
                redirect('teachers/grades');
            } else {
                die('Something went wrong');
            }
        } else {
            redirect('teachers/grades');
        }
    }

    public function quizzes(){
        $teacher_user_id = $_SESSION['user_id'];
        $teacher = $this->teacherModel->getTeacherByUserId($teacher_user_id);
        $teacher_id = $teacher->id;

        $quizzes = $this->quizModel->getQuizzesByTeacher($teacher_id);

        $data = [
            'quizzes' => $quizzes
        ];

        $this->view('teachers/quizzes/index', $data);
    }

    public function add_quiz(){
        $teacher_user_id = $_SESSION['user_id'];
        $teacher = $this->teacherModel->getTeacherByUserId($teacher_user_id);
        $teacher_id = $teacher->id;

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            $data = [
                'teacher_id' => $teacher_id,
                'title' => trim($_POST['title']),
                'date' => trim($_POST['date']),
                'title_err' => '',
                'date_err' => ''
            ];

            if(empty($data['title'])){
                $data['title_err'] = 'Please enter title';
            }
            if(empty($data['date'])){
                $data['date_err'] = 'Please enter date';
            }

            if(empty($data['title_err']) && empty($data['date_err'])){
                if($this->quizModel->addQuiz($data)){
                    flash('quiz_message', 'Quiz Added');
                    redirect('teachers/quizzes');
                } else {
                    die('Something went wrong');
                }
            } else {
                $this->view('teachers/quizzes/add', $data);
            }
        } else {
            $data = [
                'title' => '',
                'date' => ''
            ];
            $this->view('teachers/quizzes/add', $data);
        }
    }

    public function edit_quiz($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            $data = [
                'id' => $id,
                'title' => trim($_POST['title']),
                'date' => trim($_POST['date']),
                'title_err' => '',
                'date_err' => ''
            ];

            if(empty($data['title'])){
                $data['title_err'] = 'Please enter title';
            }
            if(empty($data['date'])){
                $data['date_err'] = 'Please enter date';
            }

            if(empty($data['title_err']) && empty($data['date_err'])){
                if($this->quizModel->updateQuiz($data)){
                    flash('quiz_message', 'Quiz Updated');
                    redirect('teachers/quizzes');
                } else {
                    die('Something went wrong');
                }
            } else {
                $this->view('teachers/quizzes/edit', $data);
            }
        } else {
            $quiz = $this->quizModel->getQuizById($id);

            $data = [
                'id' => $id,
                'title' => $quiz->title,
                'date' => $quiz->date
            ];

            $this->view('teachers/quizzes/edit', $data);
        }
    }

    public function delete_quiz($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($this->quizModel->deleteQuiz($id)){
                flash('quiz_message', 'Quiz Removed');
                redirect('teachers/quizzes');
            } else {
                die('Something went wrong');
            }
        } else {
            redirect('teachers/quizzes');
        }
    }

    public function achievements(){
        $teacher_user_id = $_SESSION['user_id'];
        $teacher = $this->teacherModel->getTeacherByUserId($teacher_user_id);
        $teacher_id = $teacher->id;

        $achievements = $this->achievementModel->getAchievementsByTeacher($teacher_id);

        $data = [
            'achievements' => $achievements
        ];

        $this->view('teachers/achievements/index', $data);
    }

    public function add_achievement(){
        $teacher_user_id = $_SESSION['user_id'];
        $teacher = $this->teacherModel->getTeacherByUserId($teacher_user_id);
        $teacher_id = $teacher->id;

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            $data = [
                'teacher_id' => $teacher_id,
                'student_id' => trim($_POST['student_id']),
                'achievement' => trim($_POST['achievement']),
                'date' => trim($_POST['date']),
                'student_id_err' => '',
                'achievement_err' => '',
                'date_err' => ''
            ];

            if(empty($data['achievement'])){
                $data['achievement_err'] = 'Please enter achievement';
            }
            if(empty($data['date'])){
                $data['date_err'] = 'Please enter date';
            }

            if(empty($data['achievement_err']) && empty($data['date_err'])){
                if($this->achievementModel->addAchievement($data)){
                    // Notify parent
                    $parentUserId = $this->parentModel->getParentUserIdByStudentId($data['student_id']);
                    if($parentUserId){
                        $student = $this->studentModel->getStudentById($data['student_id']);
                        $message = "Your child, " . $student->name . ", has a new achievement: " . $data['achievement'];
                        $this->notificationModel->createNotification($parentUserId, $message);
                    }
                    flash('achievement_message', 'Achievement Added');
                    redirect('teachers/achievements');
                } else {
                    die('Something went wrong');
                }
            } else {
                $students = $this->studentModel->getStudents();
                $data['students'] = $students;
                $this->view('teachers/achievements/add', $data);
            }
        } else {
            $students = $this->studentModel->getStudents();
            $data = [
                'students' => $students,
                'achievement' => '',
                'date' => ''
            ];
            $this->view('teachers/achievements/add', $data);
        }
    }

    public function edit_achievement($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            $_POST = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            $data = [
                'id' => $id,
                'student_id' => trim($_POST['student_id']),
                'achievement' => trim($_POST['achievement']),
                'date' => trim($_POST['date']),
                'student_id_err' => '',
                'achievement_err' => '',
                'date_err' => ''
            ];

            if(empty($data['achievement'])){
                $data['achievement_err'] = 'Please enter achievement';
            }
            if(empty($data['date'])){
                $data['date_err'] = 'Please enter date';
            }

            if(empty($data['achievement_err']) && empty($data['date_err'])){
                if($this->achievementModel->updateAchievement($data)){
                    flash('achievement_message', 'Achievement Updated');
                    redirect('teachers/achievements');
                } else {
                    die('Something went wrong');
                }
            } else {
                $students = $this->studentModel->getStudents();
                $data['students'] = $students;
                $this->view('teachers/achievements/edit', $data);
            }
        } else {
            $achievement = $this->achievementModel->getAchievementById($id);
            $students = $this->studentModel->getStudents();

            $data = [
                'id' => $id,
                'students' => $students,
                'student_id' => $achievement->student_id,
                'achievement' => $achievement->achievement,
                'date' => $achievement->date
            ];

            $this->view('teachers/achievements/edit', $data);
        }
    }

    public function delete_achievement($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($this->achievementModel->deleteAchievement($id)){
                flash('achievement_message', 'Achievement Removed');
                redirect('teachers/achievements');
            } else {
                die('Something went wrong');
            }
        } else {
            redirect('teachers/achievements');
        }
    }
}
?>