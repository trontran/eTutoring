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

// Ensure $tutor is defined
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
    $tutees = $personalTutorModel->getTutorByStudent($_SESSION['user']['user_id']); // Likely needs to be getTuteesByTutor
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  <title><?= $title ?? 'eTutoring System' ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/eTutoring/public/Css/style.css">
    <link rel="icon" href="/eTutoring/public/images/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="wrapper d-flex flex-column min-vh-100"> <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
            <div class="container">
                <a class="navbar-brand" href="?url=home/index">
                    <i class="bi bi-mortarboard-fill"></i> eTutoring </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link <?= (isset($_GET['url']) && $_GET['url'] == 'home/index') ? 'active' : '' ?>" href="?url=home/index">Home</a>
                        </li>

                         <?php if ($isLoggedIn): ?>
                            <li class="nav-item dropdown">  <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle"></i> <?= htmlspecialchars($username) ?> </a>
                                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <li><a class="dropdown-item" href="?url=user/profile"><i class="bi bi-person-fill"></i> Profile</a></li> <?php if ($isStudent): ?>
                                         <li><a class="dropdown-item" href="?url=student/courses"><i class="bi bi-book"></i> My Courses</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="?url=logout" onclick="return confirm('Are you sure you want to logout?')"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="?url=login"><i class="bi bi-box-arrow-in-right"></i> Login</a> </li>
                            <!-- <li class="nav-item">
                                <a class="nav-link" href="?url=register"><i class="bi bi-person-plus-fill"></i> Register</a> </li> -->

                        <?php endif; ?>


                        <?php if ($isAdmin): ?>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                     <i class="bi bi-gear-fill"></i> Admin
                                </a>
                                 <ul class="dropdown-menu" aria-labelledby="adminDropdown">
                                    <li><a class="dropdown-item" href="?url=user/index"><i class="bi bi-people-fill"></i> Manage Users</a></li>
                                    <li><a class="dropdown-item" href="?url=tutor/assign"><i class="bi bi-person-plus-fill"></i> Assign Tutor</a></li>
                                    <li><a class="dropdown-item" href="?url=course/index"><i class="bi bi-journals"></i> Manage Courses</a></li>
                                </ul>
                            </li>

                        <?php endif; ?>

                        <?php if ($isTutor): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="?url=tutor/dashboard">
                                    <i class="bi bi-people-fill"></i> My Tutees
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
        <section class="hero-section py-5 text-center" style="background: linear-gradient(to right, #0056b3, #007bff);">
                <div class="container">
                    <h1 class="fw-bold text-white">
                        <?php
                        if ($isLoggedIn) {
                            echo "Welcome back, " . htmlspecialchars($username) . "!";
                        } else {
                            echo "Welcome to eTutoring!";
                        }
                        ?>
                    </h1>
                    <?php if ($isStudent): ?>
                        <p class="lead text-white">Ready to learn? Explore your courses and connect with your tutor.</p>
                        <a href="?url=student/courses" class="btn btn-light btn-lg"><i class="bi bi-book"></i> View My Courses</a>
                        <?php if ($tutor): ?>
                            <a href="mailto:<?= htmlspecialchars($tutor['email']) ?>" class="btn btn-outline-light btn-lg"><i class="bi bi-envelope"></i> Contact My Tutor</a>
                        <?php endif; ?>

                    <?php elseif ($isTutor): ?>
                        <p class="lead text-white">Manage your tutees and help them succeed.</p>
                        <a href="?url=tutor/dashboard" class="btn btn-light btn-lg"><i class="bi bi-people"></i> View My Tutees</a>
                    <?php elseif ($isAdmin): ?>
                        <p class="lead text-white">Welcome, Administrator.  Manage users, courses, and tutor assignments.</p>
                        <a href="?url=user/index" class="btn btn-light btn-lg"><i class="bi bi-gear"></i> Go to Admin Panel</a>
                    <?php else: ?>
                        <p class="lead text-white">
                            An online platform connecting students and tutors.
                        </p>
                        <p class="lead text-white">Log in to access your personalized dashboard.</p>
                        <a href="?url=login" class="btn btn-light btn-lg"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                        <?php endif; ?>
                </div>
        </section>
        <main class="container my-4 flex-grow-1"> <?php if ($isStudent && $tutor): ?>
                <section class="mb-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white text-center">
                        <h5 class="mb-0"><i class="bi bi-person-badge-fill"></i> Your Personal Tutor</h5>
                    </div>
                    <div class="card-body text-center">
                        <?php
                        $firstName = $tutor['first_name'] ?? '';
                        $lastName = $tutor['last_name'] ?? '';
                        $initials = '';
                        if (!empty($firstName)) {
                            $initials .= strtoupper(substr($firstName, 0, 1));
                        }
                        if (!empty($lastName)) {
                            $initials .= strtoupper(substr($lastName, 0, 1));
                        }
                        ?>
                        <div class="initials-avatar" style="width: 150px; height: 150px; border-radius: 50%; background-color: #007bff; color: white; font-size: 4rem; display: flex; justify-content: center; align-items: center; margin: 0 auto 1rem;">
                            <?= htmlspecialchars($initials) ?>
                        </div>
                            <h4 class="card-title"><?= htmlspecialchars($tutor['first_name'] . " " . $tutor['last_name']) ?></h4>
                        <p class="card-text text-muted">
                            <i class="bi bi-envelope-fill"></i> <?= htmlspecialchars($tutor['email']) ?>
                        </p>
                        <p class="card-text">
                            <strong>Total Students:</strong> <span class="badge bg-primary"><?= htmlspecialchars($tutor['total_students'] ?? 0) ?></span>
                        </p>

                        <a href="mailto:<?= htmlspecialchars($tutor['email']) ?>" class="btn btn-primary">
                            <i class="bi bi-envelope-fill"></i> Contact Tutor
                        </a>
                    </div>
                </div>
                </section>
            <?php endif; ?>
             <?php if ($isTutor && !empty($tutees)): ?>
                <section class="mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-success text-white text-center"> <h5 class="mb-0"><i class="bi bi-people-fill"></i> My Tutees</h5>  </div>
                        <div class="card-body">
                            <div class="table-responsive"> <table class="table table-hover"> <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Actions</th>  </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tutees as $tutee): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($tutee['first_name'] . " " . $tutee['last_name']) ?></td>
                                                <td><?= htmlspecialchars($tutee['email']) ?></td>
                                                <td>
                                                    <a href="mailto:<?= htmlspecialchars($tutee['email']) ?>" class="btn btn-sm btn-primary"><i class="bi bi-envelope-fill"></i> Contact</a>
                                                    <a href="?url=user/profile&id=<?= htmlspecialchars($tutee['user_id']) ?>" class="btn btn-sm btn-secondary"><i class="bi bi-person-fill"></i> View Profile</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            <?php endif; ?>

            <!-- <section class="mb-4">
                <div class="card shadow">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="bi bi-info-circle-fill"></i> About eTutoring</h5> </div>
                     <div class="card-body">
                        <p>eTutoring is a platform designed to connect students with tutors.  We aim to provide a supportive and engaging learning environment.</p>
                        <p>Our features include:...</p> <ul>
                            <li>Personalized tutor matching</li>
                            <li>Interactive learning tools</li>
                            <li>Progress tracking</li>
                            <li>Secure communication channels</li>
                        </ul>
                    </div>
                </div>
            </section> -->

        </main>
        <footer class="footer bg-dark text-white text-center py-3">
            <p>&copy; <?= date('Y') ?> eTutoring System | University XYZ</p>
        </footer>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

</body>
</html>