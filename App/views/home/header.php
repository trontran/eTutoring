<?php

// Kiểm tra session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Xác định vai trò của người dùng
$isLoggedIn = isset($_SESSION['user']);
$isAdmin = $isLoggedIn && $_SESSION['user']['role'] === 'staff';
$isStudent = $isLoggedIn && $_SESSION['user']['role'] === 'student';
$isTutor = $isLoggedIn && $_SESSION['user']['role'] === 'tutor';
$username = $isLoggedIn ? $_SESSION['user']['first_name'] : 'Guest';

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
    <link rel="icon" href="/eTutoring/public/images/favicon.ico" type="image/x-icon">
</head>
<body>
<div class="wrapper d-flex flex-column min-vh-100">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="?url=home/index">
                <i class="bi bi-mortarboard-fill"></i> eTutoring
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= ($_GET['url'] ?? '') === 'home/index' ? 'active' : '' ?>" href="?url=home/index">
                            <i class="bi bi-house-door-fill"></i> Home
                        </a>
                    </li>

                    <?php if ($isLoggedIn): ?>
                        <!-- User Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($username) ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="?url=user/profile"><i class="bi bi-person-fill"></i> Profile</a></li>
                                <?php if ($isStudent): ?>
                                    <li><a class="dropdown-item" href="?url=student/courses"><i class="bi bi-book"></i> My Courses</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="?url=logout" onclick="return confirm('Are you sure you want to logout?')">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['url'] ?? '') === 'login' ? 'active' : '' ?>" href="?url=login">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </li>
                    <?php endif; ?>

                    <!-- Admin Dropdown -->
                    <?php if ($isAdmin): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-gear-fill"></i> Admin
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="?url=user/index"><i class="bi bi-people-fill"></i> Manage Users</a></li>
                                <li><a class="dropdown-item" href="?url=tutor/assign"><i class="bi bi-person-plus-fill"></i> Assign Tutor</a></li>
                            </ul>
                        </li>
                    <?php endif; ?>

                    <!-- Tutor Navigation -->
                    <?php if ($isTutor): ?>
                        <li class="nav-item">
                            <a class="nav-link <?= ($_GET['url'] ?? '') === 'tutor/dashboard' ? 'active' : '' ?>" href="?url=tutor/dashboard">
                                <i class="bi bi-people-fill"></i> My Tutees
                            </a>
                        </li>
                    <?php endif; ?>

                </ul>
            </div>
        </div>
    </nav>