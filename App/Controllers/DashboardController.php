<?php

use App\Core\Controller;
use App\Models\Dashboard;
use App\Models\User;

class DashboardController extends Controller
{
    private $dashboardModel;
    private $userModel;

    public function __construct()
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        $this->dashboardModel = new Dashboard();
        $this->userModel = new User();
    }

    /**
     * Display student dashboard
     */
    public function student()
    {
        $userId = $_SESSION['user']['user_id'];
        $userRole = $_SESSION['user']['role'];

        // If a staff member is viewing a student dashboard, they can specify which student
        $viewStudentId = null;
        if ($userRole === 'staff' && isset($_GET['id'])) {
            $viewStudentId = (int)$_GET['id'];
            $student = $this->userModel->getUserById($viewStudentId);

            if (!$student || $student['role'] !== 'student') {
                $_SESSION['error'] = "Invalid student specified.";
                header("Location: ?url=dashboard/index");
                exit;
            }
        } else if ($userRole !== 'student') {
            $_SESSION['error'] = "Access denied.";
            header("Location: ?url=home/index");
            exit;
        } else {
            $viewStudentId = $userId;
        }

        // Get student dashboard data
        $dashboardData = $this->dashboardModel->getStudentDashboard($viewStudentId);

        // Get student information
        $student = $this->userModel->getUserById($viewStudentId);

        $this->view('dashboard/student', [
            'dashboardData' => $dashboardData,
            'student' => $student,
            'isOwnDashboard' => ($userId === $viewStudentId)
        ]);
    }

    /**
     * Display staff dashboard
     */
    public function index()
    {
        $userRole = $_SESSION['user']['role'];

        // Only staff can access the main dashboard
        if ($userRole !== 'staff') {
            // Redirect to student dashboard for students
            if ($userRole === 'student') {
                header("Location: ?url=dashboard/student");
                exit;
            }
            // Redirect to tutor dashboard for tutors
            else if ($userRole === 'tutor') {
                header("Location: ?url=dashboard/tutor");
                exit;
            }
            else {
                $_SESSION['error'] = "Access denied.";
                header("Location: ?url=home/index");
                exit;
            }
        }

        // Get system-wide statistics
        $systemStats = $this->dashboardModel->getSystemStats();

        $this->view('dashboard/index', [
            'systemStats' => $systemStats
        ]);
    }

    /**
     * Display tutor dashboard
     */
    public function tutor()
    {
        $userId = $_SESSION['user']['user_id'];
        $userRole = $_SESSION['user']['role'];

        // If a staff member is viewing a tutor dashboard, they can specify which tutor
        $viewTutorId = null;
        if ($userRole === 'staff' && isset($_GET['id'])) {
            $viewTutorId = (int)$_GET['id'];
            $tutor = $this->userModel->getUserById($viewTutorId);

            if (!$tutor || $tutor['role'] !== 'tutor') {
                $_SESSION['error'] = "Invalid tutor specified.";
                header("Location: ?url=dashboard/index");
                exit;
            }
        } else if ($userRole !== 'tutor') {
            $_SESSION['error'] = "Access denied.";
            header("Location: ?url=home/index");
            exit;
        } else {
            $viewTutorId = $userId;
        }

        // Get tutor's tutees
        $tutees = $this->userModel->getTuteesByTutor($viewTutorId);

        // Get tutor information
        $tutor = $this->userModel->getUserById($viewTutorId);

        // Get activity stats for each tutee
        $tuteeStats = [];
        foreach ($tutees as $tutee) {
            $tuteeStats[$tutee['user_id']] = $this->dashboardModel->getStudentDashboard($tutee['user_id']);
        }

        $this->view('dashboard/tutor', [
            'tutor' => $tutor,
            'tutees' => $tutees,
            'tuteeStats' => $tuteeStats,
            'isOwnDashboard' => ($userId === $viewTutorId)
        ]);
    }

    /**
     * Display report for students without tutors
     */
    public function studentsWithoutTutor()
    {
        $userRole = $_SESSION['user']['role'];

        // Only staff can access this report
        if ($userRole !== 'staff') {
            $_SESSION['error'] = "Access denied.";
            header("Location: ?url=home/index");
            exit;
        }

        $students = $this->dashboardModel->getStudentsWithoutTutor();

        $this->view('dashboard/students_without_tutor', [
            'students' => $students
        ]);
    }

    /**
     * Display report for students with no recent interaction
     */
    public function studentsWithoutInteraction()
    {
        $userRole = $_SESSION['user']['role'];

        // Only staff can access this report
        if ($userRole !== 'staff') {
            $_SESSION['error'] = "Access denied.";
            header("Location: ?url=home/index");
            exit;
        }

        $days = isset($_GET['days']) ? (int)$_GET['days'] : 7;
        if (!in_array($days, [7, 28])) {
            $days = 7; // Default to 7 days if invalid value provided
        }

        $students = $this->dashboardModel->getStudentsWithoutInteraction($days);

        $this->view('dashboard/students_without_interaction', [
            'students' => $students,
            'days' => $days
        ]);
    }

    /**
     * Display tutor activity report
     */
    public function tutorActivity()
    {
        $userRole = $_SESSION['user']['role'];

        // Only staff can access this report
        if ($userRole !== 'staff') {
            $_SESSION['error'] = "Access denied.";
            header("Location: ?url=home/index");
            exit;
        }

        $tutors = $this->dashboardModel->getTutorActivity();

        $this->view('dashboard/tutor_activity', [
            'tutors' => $tutors
        ]);
    }
}