<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class ActivityTracker
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Track a page view
     *
     * @param string $url The URL being viewed
     * @param int|null $userId The user ID or null if not logged in
     * @return bool Success status
     */
    public function trackPageView($url, $userId = null)
    {
        $browser = $this->getBrowserInfo();
        $ipAddress = $this->getIpAddress();

        $query = "INSERT INTO PageViews (url, user_id, ip_address, view_datetime, browser, device, operating_system) 
                 VALUES (:url, :user_id, :ip_address, NOW(), :browser, :device, :os)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':url', $url, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $userId, $userId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindParam(':ip_address', $ipAddress, PDO::PARAM_STR);
        $stmt->bindParam(':browser', $browser['browser'], PDO::PARAM_STR);
        $stmt->bindParam(':device', $browser['device'], PDO::PARAM_STR);
        $stmt->bindParam(':os', $browser['os'], PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Track user activity
     *
     * @param int $userId User ID
     * @param string $activityType Type of activity
     * @param string $details Additional details about the activity
     * @return bool Success status
     */
    public function trackUserActivity($userId, $activityType, $details = '')
    {
        $browser = $this->getBrowserInfo();
        $ipAddress = $this->getIpAddress();

        $query = "INSERT INTO UserActivity (user_id, activity_type, activity_datetime, ip_address, browser, details) 
                 VALUES (:user_id, :activity_type, NOW(), :ip_address, :browser, :details)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':activity_type', $activityType, PDO::PARAM_STR);
        $stmt->bindParam(':ip_address', $ipAddress, PDO::PARAM_STR);
        $stmt->bindParam(':browser', $browser['browser'], PDO::PARAM_STR);
        $stmt->bindParam(':details', $details, PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Track system errors
     *
     * @param string $errorType Type of error
     * @param string $errorMessage Error message
     * @param int|null $userId User ID if available
     * @param string|null $url URL where the error occurred
     * @return bool Success status
     */
    public function trackError($errorType, $errorMessage, $userId = null, $url = null)
    {
        $browser = $this->getBrowserInfo();
        $ipAddress = $this->getIpAddress();

        $query = "INSERT INTO SystemErrors (error_type, error_message, error_datetime, user_id, url, ip_address, browser) 
                 VALUES (:error_type, :error_message, NOW(), :user_id, :url, :ip_address, :browser)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':error_type', $errorType, PDO::PARAM_STR);
        $stmt->bindParam(':error_message', $errorMessage, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $userId, $userId ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindParam(':url', $url, PDO::PARAM_STR);
        $stmt->bindParam(':ip_address', $ipAddress, PDO::PARAM_STR);
        $stmt->bindParam(':browser', $browser['browser'], PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Get information about the current browser and device
     *
     * @return array Browser information
     */
    private function getBrowserInfo()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        // Default values
        $browser = 'Unknown';
        $device = 'Unknown';
        $os = 'Unknown';

        // Detect browser
        if (strpos($userAgent, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            $browser = 'Safari';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            $browser = 'Edge';
        } elseif (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident') !== false) {
            $browser = 'Internet Explorer';
        }

        // Detect OS
        if (strpos($userAgent, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (strpos($userAgent, 'Mac') !== false) {
            $os = 'MacOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $os = 'Linux';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $os = 'Android';
        } elseif (strpos($userAgent, 'iOS') !== false || strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
            $os = 'iOS';
        }

        // Detect device type
        if (strpos($userAgent, 'Mobile') !== false || strpos($userAgent, 'Android') !== false || strpos($userAgent, 'iPhone') !== false) {
            $device = 'Mobile';
        } elseif (strpos($userAgent, 'iPad') !== false || strpos($userAgent, 'Tablet') !== false) {
            $device = 'Tablet';
        } else {
            $device = 'Desktop';
        }

        return [
            'browser' => $browser,
            'device' => $device,
            'os' => $os,
            'user_agent' => $userAgent
        ];
    }

    /**
     * Get the client's IP address
     *
     * @return string IP address
     */
    private function getIpAddress()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }

        return $ip;
    }
}