<?php
// Kiểm tra session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra xem người dùng có đăng nhập không
$isLoggedIn = isset($_SESSION['user']);
$isAdmin = $isLoggedIn && $_SESSION['user']['role'] === 'staff';
$isStudent = $isLoggedIn && $_SESSION['user']['role'] === 'student';
$isTutor = $isLoggedIn && $_SESSION['user']['role'] === 'tutor';
$username = $isLoggedIn ? $_SESSION['user']['first_name'] : 'Guest';

// Import model Notification & User
require_once '../app/models/Notification.php';
require_once '../app/models/User.php';

use App\Models\Notification;
use App\Models\User;

// Khởi tạo đối tượng model
$notificationModel = new Notification();
$userModel = new User();

$unreadMessages = 0;
$receiverId = null;

// Kiểm tra session user_id trước khi truy cập
$userId = $_SESSION['user']['user_id'] ?? null;

if ($isLoggedIn && !empty($userId) && is_numeric($userId)) {
    // Cập nhật thông báo đã đọc trước khi lấy số lượng tin nhắn chưa đọc
    if (isset($_GET['url']) && $_GET['url'] === 'message/chat' && isset($_GET['receiver_id'])) {
        $receiverId = $_GET['receiver_id'];
        $notificationModel->markAsRead($userId, $receiverId); // Cập nhật trạng thái đọc
    }

    // Cập nhật số lượng tin nhắn chưa đọc
    $unreadMessages = count($notificationModel->getUnreadNotifications($userId));

    if ($isStudent) {
        // Lấy tutor ID của student
        $tutor = $userModel->getTutorId($userId);
        if ($tutor) {
            $receiverId = $tutor['tutor_id'];
        }
    } elseif ($isTutor) {
        // Lấy danh sách tutees của tutor
        $students = $userModel->getTuteesByTutor($userId);
        if (!empty($students) && isset($students[0]['user_id'])) {
            $receiverId = $students[0]['user_id'];
        }
    }
}

// Xác định trang hiện tại
$currentPage = $_GET['url'] ?? 'home/index';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'eTutoring System' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/eTutoring/public/Css/style.css">
    <link rel="stylesheet" href="/eTutoring/public/Css/header.css">
    <link rel="icon" href="/eTutoring/public/images/favicon.ico" type="image/x-icon">
</head>
<body>
<div class="wrapper d-flex flex-column min-vh-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <!-- Logo & Brand -->
            <a class="navbar-brand" href="?url=home/index">
                <i class="bi bi-mortarboard-fill"></i> eTutoring
            </a>

            <!-- Navigation Menu -->
            <div class="navbar-nav ms-auto">
                <!-- Home -->
                <a class="nav-link <?= $currentPage === 'home/index' ? 'active' : '' ?>" href="?url=home/index">
                    <i class="bi bi-house-door-fill"></i> Home
                </a>

                <?php if ($isLoggedIn): ?>
                    <!-- Messages -->
                    <a class="nav-link <?= strpos($currentPage, 'message') === 0 ? 'active' : '' ?>" href="?url=message/chatList">
                        <i class="bi bi-chat-dots-fill"></i> Messages
                        <?php if ($unreadMessages > 0): ?>
                            <span class="badge bg-danger rounded-pill"><?= $unreadMessages ?></span>
                        <?php endif; ?>
                    </a>

                    <!-- My Tutees (for Tutors) -->
                    <?php if ($isTutor): ?>
                        <a class="nav-link <?= $currentPage === 'tutor/dashboard' ? 'active' : '' ?>" href="?url=tutor/dashboard">
                            <i class="bi bi-people-fill"></i> My Tutees
                        </a>
                    <?php endif; ?>

                    <!-- User Dropdown -->
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <?= htmlspecialchars($username) ?> <i class="bi bi-person-circle ms-1"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li><a class="dropdown-item" href="?url=user/profile"><i class="bi bi-person-fill me-2"></i> Profile</a></li>
                            <?php if ($isStudent): ?>
                                <li><a class="dropdown-item" href="?url=student/courses"><i class="bi bi-book-fill me-2"></i> My Courses</a></li>
                            <?php endif; ?>
                            <?php if ($isAdmin): ?>
                                <li><a class="dropdown-item" href="?url=user/index"><i class="bi bi-people-fill me-2"></i> Manage Users</a></li>
                                <li><a class="dropdown-item" href="?url=tutor/assign"><i class="bi bi-person-plus-fill me-2"></i> Assign Tutor</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="?url=logout"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a class="nav-link <?= $currentPage === 'login' ? 'active' : '' ?>" href="?url=login">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>