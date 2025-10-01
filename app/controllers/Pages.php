<?php
  class Pages extends Controller {
    public function __construct(){

    }

    public function index(){
      $data = [
        'title' => 'Student Attendance Manager',
        'description' => 'A modern and professional solution to manage student attendance, grades, communication, and more.'
      ];

      $this->view('pages/index', $data);
    }

    public function about(){
      $data = [
        'title' => 'About Us',
        'description' => 'We provide a comprehensive platform to streamline school management for students, parents, and teachers.'
      ];

      $this->view('pages/about', $data);
    }
  }