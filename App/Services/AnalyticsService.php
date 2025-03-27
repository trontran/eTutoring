<?php
namespace App\Services;

use App\Core\Database;
use PDO;

class AnalyticsService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function recordPageView($userId, $controller, $action) {
        $url = $_SERVER['REQUEST_URI'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        $sessionId = session_id();

        $query = "INSERT INTO PageViews 
                 (user_id, page_url, controller, action, view_timestamp, session_id, user_agent, ip_address) 
                 VALUES (:user_id, :page_url, :controller, :action, NOW(), :session_id, :user_agent, :ip_address)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':page_url', $url, PDO::PARAM_STR);
        $stmt->bindParam(':controller', $controller, PDO::PARAM_STR);
        $stmt->bindParam(':action', $action, PDO::PARAM_STR);
        $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_STR);
        $stmt->bindParam(':user_agent', $userAgent, PDO::PARAM_STR);
        $stmt->bindParam(':ip_address', $ipAddress, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function recordUserAction($userId, $actionType, $actionDetails, $controller = null, $action = null) {
        $query = "INSERT INTO UserActions 
                 (user_id, action_type, action_details, action_timestamp, controller, action) 
                 VALUES (:user_id, :action_type, :action_details, NOW(), :controller, :action)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':action_type', $actionType, PDO::PARAM_STR);
        $stmt->bindParam(':action_details', $actionDetails, PDO::PARAM_STR);
        $stmt->bindParam(':controller', $controller, PDO::PARAM_STR);
        $stmt->bindParam(':action', $action, PDO::PARAM_STR);

        return $stmt->execute();
    }
}