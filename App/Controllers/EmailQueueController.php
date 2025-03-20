<?php

use App\Core\Controller;
use App\Models\EmailQueue;

class EmailQueueController extends Controller
{
    private $emailQueue;

    public function __construct()
    {
        // Make sure only staff can access this
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'staff') {
            header("Location: ?url=login");
            exit;
        }

        $this->emailQueue = new EmailQueue();
    }

    public function process()
    {
        // Get batch size from query parameter or use default
        $batchSize = isset($_GET['batch']) ? (int)$_GET['batch'] : 20;

        // Process the emails
        $processed = $this->emailQueue->processBatch($batchSize);

        // Get total remaining
        $remaining = $this->emailQueue->countPendingEmails();

        $_SESSION['success'] = "Successfully sent {$processed} emails. {$remaining} emails remaining in queue.";

        // Redirect back to referring page or dashboard
        $referer = $_SERVER['HTTP_REFERER'] ?? "?url=dashboard/index";
        header("Location: {$referer}");
        exit;
    }

    public function status()
    {
        // Show a status page with counts of pending/sent/failed emails
        $counts = $this->emailQueue->getStatusCounts();

        $this->view('emailqueue/status', ['counts' => $counts]);
    }
}