<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("HTTP/1.1 403 Forbidden");
    echo "Access denied";
    exit;
}

$filename = isset($_GET['file']) ? basename($_GET['file']) : '';
$filePath = __DIR__ . '/' . $filename;

// Basic validation
if (empty($filename) || !file_exists($filePath) || !is_file($filePath)) {
    header("HTTP/1.1 404 Not Found");
    echo "File not found";
    exit;
}

// Get meeting ID from filename (assuming format: meeting_ID_datetime.mp3)
preg_match('/meeting_(\d+)_/', $filename, $matches);
$meetingId = $matches[1] ?? 0;

if (!$meetingId) {
    header("HTTP/1.1 403 Forbidden");
    echo "Access denied";
    exit;
}

// Connect to database
require_once '../../app/core/Database.php';
$db = \App\Core\Database::getInstance()->getConnection();

// Check if user has permission to access this recording
$userId = $_SESSION['user']['user_id'];
$query = "SELECT * FROM Meetings WHERE meeting_id = :meeting_id AND (student_id = :user_id OR tutor_id = :user_id)";
$stmt = $db->prepare($query);
$stmt->bindParam(':meeting_id', $meetingId, \PDO::PARAM_INT);
$stmt->bindParam(':user_id', $userId, \PDO::PARAM_INT);
$stmt->execute();

if (!$stmt->fetch()) {
    header("HTTP/1.1 403 Forbidden");
    echo "Access denied";
    exit;
}

// Set appropriate headers for file download
header('Content-Description: File Transfer');
header('Content-Type: audio/mp3');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

// Clear output buffer
ob_clean();
flush();

// Output file
readfile($filePath);
exit;