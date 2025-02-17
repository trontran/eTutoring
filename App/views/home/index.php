<?php
// Kiểm tra session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user']);
$isAdmin = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'staff';
$username = $isLoggedIn ? $_SESSION['user']['first_name'] : 'Guest';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'eTutoring System' ?></title>

    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <!-- Link đến file style.css -->
    <link rel="stylesheet" href="/eTutoring/public/Css/style.css">
</head>
<body>
    <div class="wrapper"> <!-- Bọc toàn bộ trang -->
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container">
                <a class="navbar-brand" href="?url=home/index">
                    <i class="bi bi-mortarboard"></i> eTutoring
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item"><a class="nav-link active" href="?url=home/index">Home</a></li>
                        <?php if ($isLoggedIn): ?>
                            <li class="nav-item">
                                <a class="nav-link text-light" href="?url=logout" onclick="return confirm('Are you sure you want to logout?')">
                                    <i class="bi bi-box-arrow-right"></i> Logout
                                </a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link text-light" href="?url=login">Login</a>
                            </li>
                        <?php endif; ?>
                        <?php if ($isAdmin): ?>
                            <li class="nav-item">
                                <a class="nav-link btn btn-warning text-dark ms-2 btn-custom" href="?url=user/index">
                                    <i class="bi bi-people-fill"></i> Manage Users
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- End Navbar -->

        <div class="content"> <!-- Bọc nội dung chính -->
            <!-- Hero Section -->
            <section class="hero-section text-center">
                <div class="container">
                    <h1>Welcome, <?= $username ?>!</h1>
                    <p>Your digital space for seamless eTutoring experience.</p>
                    <div>
                        <?php if (!$isLoggedIn): ?>
                            <a href="?url=login" class="btn btn-primary btn-lg btn-custom">
                                <i class="bi bi-box-arrow-in-right"></i> Get Started
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            <!-- End Hero Section -->

            <!-- Features Section -->
            <section class="py-5">
                <div class="container">
                    <div class="row text-center">
                        <div class="col-md-4 feature-box">
                            <i class="bi bi-person-badge-fill feature-icon"></i>
                            <h3>Personal Tutor</h3>
                            <p>Each student is assigned a personal tutor for guidance.</p>
                        </div>
                        <div class="col-md-4 feature-box">
                            <i class="bi bi-chat-dots-fill feature-icon"></i>
                            <h3>Seamless Communication</h3>
                            <p>Interact with your tutors and schedule meetings effortlessly.</p>
                        </div>
                        <div class="col-md-4 feature-box">
                            <i class="bi bi-journal-text feature-icon"></i>
                            <h3>Resource Sharing</h3>
                            <p>Upload and share learning materials easily.</p>
                        </div>
                    </div>
                </div>
            </section>
            <!-- End Features Section -->
        </div>

        <!-- Footer -->
        <footer class="footer">
            <p>&copy; 2025 eTutoring System | University XYZ</p>
        </footer>
    </div> <!-- Kết thúc wrapper -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
