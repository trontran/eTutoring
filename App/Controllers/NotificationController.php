<?php

use App\Core\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    private $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new Notification();

        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }
    }

    /**
     * Display all notifications for the current user
     */
    public function index()
    {
        $userId = $_SESSION['user']['user_id'];

        // Get all notifications for the user
        $notifications = $this->notificationModel->getAllNotificationsForUser($userId);

        $data = [
            'notifications' => $notifications
        ];

        $this->view('notifications/index', $data);
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead()
    {
        $notificationId = $_GET['id'] ?? null;
        $returnUrl = $_GET['return'] ?? 'notifications/index';

        if (!$notificationId) {
            $_SESSION['error'] = "Notification ID is required.";
            header("Location: ?url=" . $returnUrl);
            exit;
        }

        // Mark notification as read
        $success = $this->notificationModel->markNotificationAsRead($notificationId);

        if ($success) {
            $_SESSION['success'] = "Notification marked as read.";
        } else {
            $_SESSION['error'] = "Failed to mark notification as read.";
        }

        header("Location: ?url=" . $returnUrl);
        exit;
    }

    /**
     * Mark all notifications for the current user as read
     */
    public function markAllAsRead()
    {
        $userId = $_SESSION['user']['user_id'];
        $returnUrl = $_GET['return'] ?? 'notifications/index';

        // Mark all notifications as read
        $success = $this->notificationModel->markAsRead($userId);

        if ($success) {
            $_SESSION['success'] = "All notifications marked as read.";
        } else {
            $_SESSION['error'] = "Failed to mark notifications as read.";
        }

        header("Location: ?url=" . $returnUrl);
        exit;
    }
}