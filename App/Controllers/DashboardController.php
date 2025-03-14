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
    public function student(): void
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
    public function index(): void
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
    public function tutor(): void
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
    public function studentsWithoutTutor(): void
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
    public function studentsWithoutInteraction(): void
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
    public function tutorActivity(): void
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

    //test new function here

    /**
     * Display time-based activity report
     */
    public function timeBasedActivity(): void
    {
        $userRole = $_SESSION['user']['role'];

        // Only staff can access this report
        if ($userRole !== 'staff') {
            $_SESSION['error'] = "Access denied.";
            header("Location: ?url=home/index");
            exit;
        }

        // Get period parameter (weekly/monthly/term)
        $period = isset($_GET['period']) ? $_GET['period'] : 'weekly';
        if (!in_array($period, ['weekly', 'monthly', 'term'])) {
            $period = 'weekly'; // Default to weekly if invalid value provided
        }

        // Get date range parameters
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

        // Get activity data
        $activityData = $this->dashboardModel->getTimeBasedActivity($period, $startDate, $endDate);

        $this->view('dashboard/time_based_activity', [
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'activityData' => $activityData
        ]);
    }

    /**
     * Display peak usage times report
     */
    public function peakUsageTimes(): void
    {
        $userRole = $_SESSION['user']['role'];

        // Only staff can access this report
        if ($userRole !== 'staff') {
            $_SESSION['error'] = "Access denied.";
            header("Location: ?url=home/index");
            exit;
        }

        // Get date range parameters
        $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
        $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

        // Get peak usage data
        $peakData = $this->dashboardModel->getPeakUsageTimes($startDate, $endDate);

        $this->view('dashboard/peak_usage_times', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'peakData' => $peakData
        ]);
    }

    /**
     * Display comparison report between time periods
     */
    public function compareTimePeriods(): void
    {
        $userRole = $_SESSION['user']['role'];

        // Only staff can access this report
        if ($userRole !== 'staff') {
            $_SESSION['error'] = "Access denied.";
            header("Location: ?url=home/index");
            exit;
        }

        // Default to comparing the last two months if no dates provided
        $period1Start = isset($_GET['period1_start']) ? $_GET['period1_start'] : date('Y-m-d', strtotime('-60 days'));
        $period1End = isset($_GET['period1_end']) ? $_GET['period1_end'] : date('Y-m-d', strtotime('-31 days'));
        $period2Start = isset($_GET['period2_start']) ? $_GET['period2_start'] : date('Y-m-d', strtotime('-30 days'));
        $period2End = isset($_GET['period2_end']) ? $_GET['period2_end'] : date('Y-m-d');

        // Get comparison data
        $comparisonData = $this->dashboardModel->getComparisonData($period1Start, $period1End, $period2Start, $period2End);

        $this->view('dashboard/compare_time_periods', [
            'period1Start' => $period1Start,
            'period1End' => $period1End,
            'period2Start' => $period2Start,
            'period2End' => $period2End,
            'comparisonData' => $comparisonData
        ]);
    }
}