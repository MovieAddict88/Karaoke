<?php
  class Admin extends Controller {
    public function __construct(){
      if(!isLoggedIn() || $_SESSION['user_role'] != 'admin'){
        redirect('users/login');
      }
      $this->studentModel = $this->model('Student');
      $this->teacherModel = $this->model('Teacher');
      $this->parentModel = $this->model('ParentM');
    }

    public function index(){
      $data = [
        'title' => 'Admin Dashboard'
      ];

      $this->view('admin/index', $data);
    }

    public function students(){
        // Get students
        $students = $this->studentModel->getStudents();

        $data = [
            'students' => $students
        ];

        $this->view('admin/students/index', $data);
    }

    public function add_student(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // Sanitize POST array
            $_POST = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            $data = [
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'qr_code' => trim($_POST['qr_code']),
                'name_err' => '',
                'email_err' => '',
                'password_err' => ''
            ];

            // Validate data
            if(empty($data['name'])){
                $data['name_err'] = 'Please enter name';
            }
            if(empty($data['email'])){
                $data['email_err'] = 'Please enter email';
            }
            if(empty($data['password'])){
                $data['password_err'] = 'Please enter password';
            }

            // Make sure no errors
            if(empty($data['name_err']) && empty($data['email_err']) && empty($data['password_err'])){
                // Validated
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

                if($this->studentModel->addStudent($data)){
                    flash('student_message', 'Student Added');
                    redirect('admin/students');
                } else {
                    die('Something went wrong');
                }
            } else {
                // Load view with errors
                $this->view('admin/students/add', $data);
            }

        } else {
            $data = [
                'name' => '',
                'email' => '',
                'password' => '',
                'qr_code' => ''
            ];

            $this->view('admin/students/add', $data);
        }
    }

    public function edit_student($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // Sanitize POST array
            $_POST = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            $data = [
                'student_id' => $id,
                'user_id' => $_POST['user_id'],
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'qr_code' => trim($_POST['qr_code']),
                'name_err' => '',
                'email_err' => ''
            ];

            // Validate data
            if(empty($data['name'])){
                $data['name_err'] = 'Please enter name';
            }
            if(empty($data['email'])){
                $data['email_err'] = 'Please enter email';
            }

            // Make sure no errors
            if(empty($data['name_err']) && empty($data['email_err'])){
                // Validated
                if($this->studentModel->updateStudent($data)){
                    flash('student_message', 'Student Updated');
                    redirect('admin/students');
                } else {
                    die('Something went wrong');
                }
            } else {
                // Load view with errors
                $this->view('admin/students/edit', $data);
            }

        } else {
            // Get existing student from model
            $student = $this->studentModel->getStudentById($id);

            $data = [
                'student_id' => $id,
                'user_id' => $student->userId,
                'name' => $student->name,
                'email' => $student->email,
                'qr_code' => $student->qr_code
            ];

            $this->view('admin/students/edit', $data);
        }
    }

    public function delete_student($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($this->studentModel->deleteStudent($id)){
                flash('student_message', 'Student Removed');
                redirect('admin/students');
            } else {
                die('Something went wrong');
            }
        } else {
            redirect('admin/students');
        }
    }

    public function teachers(){
        // Get teachers
        $teachers = $this->teacherModel->getTeachers();

        $data = [
            'teachers' => $teachers
        ];

        $this->view('admin/teachers/index', $data);
    }

    public function add_teacher(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // Sanitize POST array
            $_POST = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            $data = [
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'name_err' => '',
                'email_err' => '',
                'password_err' => ''
            ];

            // Validate data
            if(empty($data['name'])){
                $data['name_err'] = 'Please enter name';
            }
            if(empty($data['email'])){
                $data['email_err'] = 'Please enter email';
            }
            if(empty($data['password'])){
                $data['password_err'] = 'Please enter password';
            }

            // Make sure no errors
            if(empty($data['name_err']) && empty($data['email_err']) && empty($data['password_err'])){
                // Validated
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

                if($this->teacherModel->addTeacher($data)){
                    flash('teacher_message', 'Teacher Added');
                    redirect('admin/teachers');
                } else {
                    die('Something went wrong');
                }
            } else {
                // Load view with errors
                $this->view('admin/teachers/add', $data);
            }

        } else {
            $data = [
                'name' => '',
                'email' => '',
                'password' => ''
            ];

            $this->view('admin/teachers/add', $data);
        }
    }

    public function edit_teacher($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // Sanitize POST array
            $_POST = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            $data = [
                'teacher_id' => $id,
                'user_id' => $_POST['user_id'],
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'name_err' => '',
                'email_err' => ''
            ];

            // Validate data
            if(empty($data['name'])){
                $data['name_err'] = 'Please enter name';
            }
            if(empty($data['email'])){
                $data['email_err'] = 'Please enter email';
            }

            // Make sure no errors
            if(empty($data['name_err']) && empty($data['email_err'])){
                // Validated
                if($this->teacherModel->updateTeacher($data)){
                    flash('teacher_message', 'Teacher Updated');
                    redirect('admin/teachers');
                } else {
                    die('Something went wrong');
                }
            } else {
                // Load view with errors
                $this->view('admin/teachers/edit', $data);
            }

        } else {
            // Get existing teacher from model
            $teacher = $this->teacherModel->getTeacherById($id);

            $data = [
                'teacher_id' => $id,
                'user_id' => $teacher->userId,
                'name' => $teacher->name,
                'email' => $teacher->email
            ];

            $this->view('admin/teachers/edit', $data);
        }
    }

    public function delete_teacher($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($this->teacherModel->deleteTeacher($id)){
                flash('teacher_message', 'Teacher Removed');
                redirect('admin/teachers');
            } else {
                die('Something went wrong');
            }
        } else {
            redirect('admin/teachers');
        }
    }

    public function parents(){
        // Get parents
        $parents = $this->parentModel->getParents();

        $data = [
            'parents' => $parents
        ];

        $this->view('admin/parents/index', $data);
    }

    public function add_parent(){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // Sanitize POST array
            $_POST = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            $data = [
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'student_id' => trim($_POST['student_id']),
                'name_err' => '',
                'email_err' => '',
                'password_err' => ''
            ];

            // Validate data
            if(empty($data['name'])){
                $data['name_err'] = 'Please enter name';
            }
            if(empty($data['email'])){
                $data['email_err'] = 'Please enter email';
            }
            if(empty($data['password'])){
                $data['password_err'] = 'Please enter password';
            }

            // Make sure no errors
            if(empty($data['name_err']) && empty($data['email_err']) && empty($data['password_err'])){
                // Validated
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

                if($this->parentModel->addParent($data)){
                    flash('parent_message', 'Parent Added');
                    redirect('admin/parents');
                } else {
                    die('Something went wrong');
                }
            } else {
                // Load view with errors
                $this->view('admin/parents/add', $data);
            }

        } else {
            // Get students for select list
            $students = $this->studentModel->getStudents();

            $data = [
                'name' => '',
                'email' => '',
                'password' => '',
                'students' => $students
            ];

            $this->view('admin/parents/add', $data);
        }
    }

    public function edit_parent($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            // Sanitize POST array
            $_POST = filter_input_array(INPUT_POST, FILTER_DEFAULT);

            $data = [
                'parent_id' => $id,
                'user_id' => $_POST['user_id'],
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'student_id' => trim($_POST['student_id']),
                'name_err' => '',
                'email_err' => ''
            ];

            // Validate data
            if(empty($data['name'])){
                $data['name_err'] = 'Please enter name';
            }
            if(empty($data['email'])){
                $data['email_err'] = 'Please enter email';
            }

            // Make sure no errors
            if(empty($data['name_err']) && empty($data['email_err'])){
                // Validated
                if($this->parentModel->updateParent($data)){
                    flash('parent_message', 'Parent Updated');
                    redirect('admin/parents');
                } else {
                    die('Something went wrong');
                }
            } else {
                // Load view with errors
                $this->view('admin/parents/edit', $data);
            }

        } else {
            // Get existing parent from model
            $parent = $this->parentModel->getParentById($id);
            // Get students for select list
            $students = $this->studentModel->getStudents();

            $data = [
                'parent_id' => $id,
                'user_id' => $parent->userId,
                'name' => $parent->name,
                'email' => $parent->email,
                'student_id' => $parent->student_id,
                'students' => $students
            ];

            $this->view('admin/parents/edit', $data);
        }
    }

    public function delete_parent($id){
        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if($this->parentModel->deleteParent($id)){
                flash('parent_message', 'Parent Removed');
                redirect('admin/parents');
            } else {
                die('Something went wrong');
            }
        } else {
            redirect('admin/parents');
        }
    }
  }