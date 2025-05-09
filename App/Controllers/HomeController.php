<?php

use App\Core\Controller;
use App\Models\User;
use App\Models\PersonalTutor;

class HomeController extends Controller
{
    private $userModel;
    private $personalTutorModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->personalTutorModel = new PersonalTutor();
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $tutor = null;

        // If the user is a student, get their tutor information
        if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'student') {
            $student_id = $_SESSION['user']['user_id'];
            $tutor = $this->personalTutorModel->getTutorDetails($student_id);
        }

        // Render view tutor's data (nếu có)
        $this->view('home/index', [
            'title' => 'Home Page - eTutoring System',
            'tutor' => $tutor
        ]);
    }

    
}
