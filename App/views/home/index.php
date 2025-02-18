<?php
// Check session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = isset($_SESSION['user']);
$isAdmin = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'staff';
$isStudent = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'student';
$isTutor = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'tutor';
$username = $isLoggedIn ? $_SESSION['user']['first_name'] : 'Guest';

// Ensure $tutor is defined to avoid undefined variable notice
$tutor = $tutor ?? null;

// Fetch assigned tutor if student is logged in
if ($isStudent && isset($_SESSION['user']['user_id'])) {
    require_once '../app/models/PersonalTutor.php';
    $personalTutorModel = new App\Models\PersonalTutor();
    $tutor = $personalTutorModel->getTutorDetails($_SESSION['user']['user_id']);
}

// Fetch tutees if tutor is logged in
$tutees = [];
if ($isTutor && isset($_SESSION['user']['user_id'])) {
    require_once '../app/models/PersonalTutor.php';
    $personalTutorModel = new App\Models\PersonalTutor();
    $tutees = $personalTutorModel->getTutorByStudent($_SESSION['user']['user_id']);
}
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
    <link rel="stylesheet" href="/eTutoring/public/Css/style.css">
</head>
<body>
    <div class="wrapper"> 
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
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

                        <!-- Admin: Manage Users & Assign Tutor -->
                        <?php if ($isAdmin): ?>
                            <li class="nav-item">
                                <a class="nav-link btn btn-warning text-dark ms-2 btn-custom" href="?url=user/index">
                                    <i class="bi bi-people-fill"></i> Manage Users
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn btn-success text-light ms-2 btn-custom" href="?url=tutor/assign">
                                    <i class="bi bi-person-plus"></i> Assign Tutor
                                </a>
                            </li>
                        <?php endif; ?>

                        <!-- Tutor: View My Tutees -->
                        <?php if ($isTutor): ?>
                            <li class="nav-item">
                                <a class="nav-link btn btn-light text-dark ms-2 btn-custom" href="?url=tutor/dashboard">
                                    <i class="bi bi-people"></i> View My Tutees
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- End Navbar -->

        <!-- Hero Section with User Info -->
        <section class="hero-section text-center text-white py-5" style="background: linear-gradient(to right, #6a11cb, #2575fc);">
            <div class="container">
                <h1 class="fw-bold mt-3"><?= $username ?></h1>
                <p class="lead"><?= $isStudent ? 'Student' : ($isTutor ? 'Tutor' : ($isAdmin ? 'Staff' : 'Guest')) ?></p>
            </div>
        </section>
        <!-- End Hero Section -->

        <?php if ($isStudent && $tutor): ?>
            <section class="container my-4">
                <div class="card shadow-lg border-0">
                    <div class="card-header text-white text-center" style="background: linear-gradient(to right, #6a11cb, #2575fc);">
                        <h5>Your Personal Tutor</h5>
                    </div>
                    <div class="card-body text-center bg-light">
                        <i class="bi bi-person-circle text-primary" style="font-size: 3rem;"></i>
                        <h4 class="mt-2"><?= $tutor['first_name'] . " " . $tutor['last_name'] ?></h4>
                        <p class="text-muted"><?= $tutor['email'] ?></p>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Total Students:</strong> <span class="badge bg-primary"><?= $tutor['total_students'] ?? 0 ?></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Total Messages:</strong> <span class="badge bg-success"><?= $tutor['total_messages'] ?? 0 ?></span></p>
                            </div>
                        </div>
                        <a href="mailto:<?= $tutor['email'] ?>" class="btn btn-primary mt-3">
                            <i class="bi bi-envelope"></i> Contact Tutor
                        </a>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($isTutor && !empty($tutees)): ?>
            <section class="container my-4">
                <div class="card shadow-lg border-0">
                    <div class="card-header text-white text-center" style="background: linear-gradient(to right, #6a11cb, #2575fc);">
                        <h5>Your Tutees</h5>
                    </div>
                    <div class="card-body bg-light">
                        <ul class="list-group">
                            <?php foreach ($tutees as $tutee): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= $tutee['first_name'] . " " . $tutee['last_name'] ?>
                                    <a href="mailto:<?= $tutee['email'] ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-envelope"></i> Contact
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Footer -->
        <footer class="footer bg-dark text-white text-center py-3">
            <p>&copy; <?= date('Y') ?> eTutoring System | University XYZ</p>
        </footer>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
