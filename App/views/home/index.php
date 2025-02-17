<?php
// Kiểm tra session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user']);
$isAdmin = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'staff';
$isStudent = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'student';
$isTutor = isset($_SESSION['user']) && $_SESSION['user']['role'] === 'tutor';
$username = $isLoggedIn ? $_SESSION['user']['first_name'] : 'Guest';

// Kiểm tra nếu HomeController đã truyền tutor data
$tutor = $tutor ?? null;
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
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
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
                          <li class="nav-item">
                              <a class="nav-link btn btn-success text-light ms-2 btn-custom" href="?url=tutor/assign">
                                  <i class="bi bi-person-plus"></i> Assign Tutor
                              </a>
                          </li>
                        <?php endif; ?>
                        <?php if ($isTutor): ?>
                          <li class="nav-item">
                              <a class="nav-link btn btn-primary text-light ms-2 btn-custom" href="?url=tutor/dashboard">
                                  <i class="bi bi-people"></i> View My Tutees
                              </a>
                          </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- End Navbar -->

        <div class="content"> 
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
                        <?php elseif ($isTutor): ?>
                            <a href="?url=tutor/dashboard" class="btn btn-primary btn-lg btn-custom">
                                <i class="bi bi-people"></i> View My Tutees
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            <!-- End Hero Section -->

            <!-- Tutor Info Section (Only for Students) -->
            <?php if ($isStudent && $tutor): ?>
            <section class="tutor-section py-5">
                <div class="container">
                    <div class="card shadow-lg">
                        <div class="card-header bg-success text-white text-center">
                            <h5>Your Personal Tutor</h5>
                        </div>
                        <div class="card-body text-center">
                            <p><strong>Name:</strong> <?= $tutor['first_name'] . " " . $tutor['last_name'] ?></p>
                            <p><strong>Total Students:</strong> <?= $tutor['total_students'] ?? 0 ?></p>
                            <p><strong>Total Messages:</strong> <?= $tutor['total_messages'] ?? 0 ?></p>
                        </div>
                    </div>
                </div>
            </section>
            <?php elseif ($isStudent): ?>
                <div class="container mt-4">
                    <div class="alert alert-warning text-center">
                        You do not have a personal tutor assigned yet.
                    </div>
                </div>
            <?php endif; ?>
            <!-- End Tutor Section -->
        </div>

        <!-- Footer -->
        <footer class="footer bg-dark text-white text-center py-3">
            <p>&copy; 2025 eTutoring System | University XYZ</p>
        </footer>
    </div> 

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
