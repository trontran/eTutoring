<?php

use App\Core\Controller;
use App\Models\SystemStats;
use App\Models\ActivityTracker;

class SystemMonitoringController extends Controller
{
    private $systemStats;
    private $activityTracker;

    public function __construct()
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Staff-only access check
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'staff') {
            header("Location: ?url=login");
            exit;
        }

        $this->systemStats = new SystemStats();
        $this->activityTracker = new ActivityTracker();
    }

    /**
     * Main dashboard for system monitoring
     */
    public function index()
    {
        // Default to the last 30 days
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-30 days'));

        // Handle date range filtering
        if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
            $startDate = $_GET['start_date'];
        }

        if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
            $endDate = $_GET['end_date'];
        }

        // Get summary data for dashboard
        $mostViewedPages = $this->systemStats->getMostViewedPages($startDate, $endDate, 5);
        $mostActiveUsers = $this->systemStats->getMostActiveUsers($startDate, $endDate, 5);
        $browserUsage = $this->systemStats->getBrowserUsage($startDate, $endDate);
        $deviceUsage = $this->systemStats->getDeviceUsage($startDate, $endDate);
        $osUsage = $this->systemStats->getOSUsage($startDate, $endDate);

        // Prepare data for the view
        $data = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'mostViewedPages' => $mostViewedPages,
            'mostActiveUsers' => $mostActiveUsers,
            'browserUsage' => $browserUsage,
            'deviceUsage' => $deviceUsage,
            'osUsage' => $osUsage
        ];

        $this->view('monitoring/dashboard', $data);
    }

    /**
     * Page views report
     */
    public function pageViews()
    {
        // Default to the last 30 days
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-30 days'));

        // Handle date range filtering
        if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
            $startDate = $_GET['start_date'];
        }

        if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
            $endDate = $_GET['end_date'];
        }

        // Get detailed page view data
        $mostViewedPages = $this->systemStats->getMostViewedPages($startDate, $endDate, 50);

        // Prepare data for the view
        $data = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'mostViewedPages' => $mostViewedPages
        ];

        $this->view('monitoring/page_views', $data);
    }

    /**
     * User activity report
     */
    public function userActivity()
    {
        // Default to the last 30 days
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-30 days'));

        // Handle date range filtering
        if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
            $startDate = $_GET['start_date'];
        }

        if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
            $endDate = $_GET['end_date'];
        }

        // Get detailed user activity data
        $mostActiveUsers = $this->systemStats->getMostActiveUsers($startDate, $endDate, 50);

        // Prepare data for the view
        $data = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'mostActiveUsers' => $mostActiveUsers
        ];

        $this->view('monitoring/user_activity', $data);
    }

    /**
     * Technology usage report (browsers, devices, OS)
     */
    public function techUsage()
    {
        // Default to the last 30 days
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-30 days'));

        // Handle date range filtering
        if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
            $startDate = $_GET['start_date'];
        }

        if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
            $endDate = $_GET['end_date'];
        }

        // Get technology usage data
        $browserUsage = $this->systemStats->getBrowserUsage($startDate, $endDate);
        $deviceUsage = $this->systemStats->getDeviceUsage($startDate, $endDate);
        $osUsage = $this->systemStats->getOSUsage($startDate, $endDate);

        // Prepare data for the view
        $data = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'browserUsage' => $browserUsage,
            'deviceUsage' => $deviceUsage,
            'osUsage' => $osUsage
        ];

        $this->view('monitoring/tech_usage', $data);
    }

    /**
     * Usage patterns report (hourly activity)
     */
    public function usagePatterns()
    {
        // Default to the last 30 days
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-30 days'));

        // Handle date range filtering
        if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
            $startDate = $_GET['start_date'];
        }

        if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
            $endDate = $_GET['end_date'];
        }

        // Get hourly activity data
        $hourlyActivity = $this->systemStats->getHourlyActivity($startDate, $endDate);

        // Prepare data for the view
        $data = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'hourlyActivity' => $hourlyActivity
        ];

        $this->view('monitoring/usage_patterns', $data);
    }

    /**
     * System errors report
     */
    public function errors()
    {
        // Default to the last 30 days
        $endDate = date('Y-m-d');
        $startDate = date('Y-m-d', strtotime('-30 days'));

        // Handle date range filtering
        if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
            $startDate = $_GET['start_date'];
        }

        if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
            $endDate = $_GET['end_date'];
        }

        // Get system errors data
        $systemErrors = $this->systemStats->getSystemErrors($startDate, $endDate, 100);

        // Prepare data for the view
        $data = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'systemErrors' => $systemErrors
        ];

        $this->view('monitoring/errors', $data);
    }
}