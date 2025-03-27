<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class SystemStats
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get most viewed pages in a date range
     *
     * @param string $startDate Start date (format: 'Y-m-d')
     * @param string $endDate End date (format: 'Y-m-d')
     * @param int $limit Maximum number of results
     * @return array Most viewed pages with counts
     */
    public function getMostViewedPages($startDate = null, $endDate = null, $limit = 10)
    {
        $query = "SELECT url, COUNT(*) as view_count 
                 FROM PageViews 
                 WHERE 1=1";

        $params = [];

        if ($startDate) {
            $query .= " AND view_datetime >= :start_date";
            $params[':start_date'] = $startDate . ' 00:00:00';
        }

        if ($endDate) {
            $query .= " AND view_datetime <= :end_date";
            $params[':end_date'] = $endDate . ' 23:59:59';
        }

        $query .= " GROUP BY url ORDER BY view_count DESC LIMIT :limit";

        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get most active users
     *
     * @param string $startDate Start date (format: 'Y-m-d')
     * @param string $endDate End date (format: 'Y-m-d')
     * @param int $limit Maximum number of results
     * @return array Most active users with activity counts
     */
    public function getMostActiveUsers($startDate = null, $endDate = null, $limit = 10)
    {
        $query = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.role,
                 COUNT(DISTINCT pv.view_id) as page_views,
                 COUNT(DISTINCT ua.activity_id) as activities,
                 (COUNT(DISTINCT pv.view_id) + COUNT(DISTINCT ua.activity_id)) as total_activity
                 FROM Users u
                 LEFT JOIN PageViews pv ON u.user_id = pv.user_id
                 LEFT JOIN UserActivity ua ON u.user_id = ua.user_id
                 WHERE 1=1";

        $params = [];

        if ($startDate) {
            $query .= " AND (pv.view_datetime >= :start_date1 OR ua.activity_datetime >= :start_date2)";
            $params[':start_date1'] = $startDate . ' 00:00:00';
            $params[':start_date2'] = $startDate . ' 00:00:00';
        }

        if ($endDate) {
            $query .= " AND (pv.view_datetime <= :end_date1 OR ua.activity_datetime <= :end_date2)";
            $params[':end_date1'] = $endDate . ' 23:59:59';
            $params[':end_date2'] = $endDate . ' 23:59:59';
        }

        $query .= " GROUP BY u.user_id ORDER BY total_activity DESC LIMIT :limit";

        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get browser usage statistics
     *
     * @param string $startDate Start date (format: 'Y-m-d')
     * @param string $endDate End date (format: 'Y-m-d')
     * @return array Browser usage counts and percentages
     */
    public function getBrowserUsage($startDate = null, $endDate = null)
    {
        $query = "SELECT browser, COUNT(*) as count,
                 ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM PageViews), 2) as percentage
                 FROM PageViews
                 WHERE 1=1";

        $params = [];

        if ($startDate) {
            $query .= " AND view_datetime >= :start_date";
            $params[':start_date'] = $startDate . ' 00:00:00';
        }

        if ($endDate) {
            $query .= " AND view_datetime <= :end_date";
            $params[':end_date'] = $endDate . ' 23:59:59';
        }

        $query .= " GROUP BY browser ORDER BY count DESC";

        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get device usage statistics
     *
     * @param string $startDate Start date (format: 'Y-m-d')
     * @param string $endDate End date (format: 'Y-m-d')
     * @return array Device usage counts and percentages
     */
    public function getDeviceUsage($startDate = null, $endDate = null)
    {
        $query = "SELECT device, COUNT(*) as count,
                 ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM PageViews), 2) as percentage
                 FROM PageViews
                 WHERE 1=1";

        $params = [];

        if ($startDate) {
            $query .= " AND view_datetime >= :start_date";
            $params[':start_date'] = $startDate . ' 00:00:00';
        }

        if ($endDate) {
            $query .= " AND view_datetime <= :end_date";
            $params[':end_date'] = $endDate . ' 23:59:59';
        }

        $query .= " GROUP BY device ORDER BY count DESC";

        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get operating system usage statistics
     *
     * @param string $startDate Start date (format: 'Y-m-d')
     * @param string $endDate End date (format: 'Y-m-d')
     * @return array OS usage counts and percentages
     */
    public function getOSUsage($startDate = null, $endDate = null)
    {
        $query = "SELECT operating_system as os, COUNT(*) as count,
                 ROUND(COUNT(*) * 100.0 / (SELECT COUNT(*) FROM PageViews), 2) as percentage
                 FROM PageViews
                 WHERE 1=1";

        $params = [];

        if ($startDate) {
            $query .= " AND view_datetime >= :start_date";
            $params[':start_date'] = $startDate . ' 00:00:00';
        }

        if ($endDate) {
            $query .= " AND view_datetime <= :end_date";
            $params[':end_date'] = $endDate . ' 23:59:59';
        }

        $query .= " GROUP BY operating_system ORDER BY count DESC";

        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get hourly activity distribution
     *
     * @param string $startDate Start date (format: 'Y-m-d')
     * @param string $endDate End date (format: 'Y-m-d')
     * @return array Hourly activity counts
     */
    public function getHourlyActivity($startDate = null, $endDate = null)
    {
        $query = "SELECT HOUR(view_datetime) as hour, COUNT(*) as count
                 FROM PageViews
                 WHERE 1=1";

        $params = [];

        if ($startDate) {
            $query .= " AND view_datetime >= :start_date";
            $params[':start_date'] = $startDate . ' 00:00:00';
        }

        if ($endDate) {
            $query .= " AND view_datetime <= :end_date";
            $params[':end_date'] = $endDate . ' 23:59:59';
        }

        $query .= " GROUP BY hour ORDER BY hour ASC";

        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get system errors report
     *
     * @param string $startDate Start date (format: 'Y-m-d')
     * @param string $endDate End date (format: 'Y-m-d')
     * @param int $limit Maximum number of results
     * @return array System errors with details
     */
    public function getSystemErrors($startDate = null, $endDate = null, $limit = 50)
    {
        $query = "SELECT se.*, u.first_name, u.last_name, u.email
                 FROM SystemErrors se
                 LEFT JOIN Users u ON se.user_id = u.user_id
                 WHERE 1=1";

        $params = [];

        if ($startDate) {
            $query .= " AND se.error_datetime >= :start_date";
            $params[':start_date'] = $startDate . ' 00:00:00';
        }

        if ($endDate) {
            $query .= " AND se.error_datetime <= :end_date";
            $params[':end_date'] = $endDate . ' 23:59:59';
        }

        $query .= " ORDER BY se.error_datetime DESC LIMIT :limit";

        $stmt = $this->db->prepare($query);

        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}