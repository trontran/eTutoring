<?php

namespace App\Middleware;

use App\Models\ActivityTracker;

class ActivityTrackerMiddleware
{
    /**
     * Track page view and activities
     */
    public static function track()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Get current URL
        $url = isset($_GET['url']) ? $_GET['url'] : 'home/index';

        // Get user ID if logged in
        $userId = isset($_SESSION['user']) ? $_SESSION['user']['user_id'] : null;

        // Create ActivityTracker instance
        $activityTracker = new ActivityTracker();

        // Track page view
        $activityTracker->trackPageView($url, $userId);
    }
}