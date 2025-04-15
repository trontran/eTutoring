<?php
include __DIR__ . '/header.php';

// Session and role checks
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user']);
$isAdmin = $isLoggedIn && $_SESSION['user']['role'] === 'staff';
$isStudent = $isLoggedIn && $_SESSION['user']['role'] === 'student';
$isTutor = $isLoggedIn && $_SESSION['user']['role'] === 'tutor';
$username = $isLoggedIn ? $_SESSION['user']['first_name'] : 'Guest';

// If student, load tutor info
$tutor = $tutor ?? null;
if ($isStudent && isset($_SESSION['user']['user_id'])) {
    require_once '../app/models/PersonalTutor.php';
    $personalTutorModel = new App\Models\PersonalTutor();
    $tutor = $personalTutorModel->getTutorDetails($_SESSION['user']['user_id']);
}

// If tutor, load tutees
$tutees = [];
if ($isTutor && isset($_SESSION['user']['user_id'])) {
    require_once '../app/models/User.php';
    $personalTutorModel = new App\Models\User();
    $tutees = $personalTutorModel->getTuteesByTutor($_SESSION['user']['user_id']);
}

?>

    <!-- Hero Section -->
    <section class="hero-section py-5 text-center" style="background: linear-gradient(to right, #0056b3, #007bff);">
        <div class="container">
                <h1 class="fw-bold text-white">
                    <?php if ($isLoggedIn): ?>
                        Welcome back, <?= htmlspecialchars($username) ?>!
                    <?php else: ?>
                        Welcome to eTutoring!
                    <?php endif; ?>
                </h1>

                <?php if ($isLoggedIn && isset($_SESSION['previous_login'])): ?>
                    <p class="text-white opacity-75">
                        <i class="bi bi-clock-history"></i> Last login: <?= date('F j, Y \a\t g:i A', strtotime($_SESSION['previous_login'])); ?>
                    </p>
                <?php endif; ?>



            <?php if ($isStudent): ?>
                <p class="lead text-white">Ready to learn? Explore your courses and connect with your tutor.</p>
            <?php elseif ($isTutor): ?>
                <p class="lead text-white">Manage your tutees and help them succeed.</p>
                <a href="?url=tutor/dashboard" class="btn btn-light btn-lg">
                    <i class="bi bi-people"></i> View My Tutees
                </a>
            <?php elseif ($isAdmin): ?>
                <p class="lead text-white">Welcome, Administrator. Manage users, courses, and tutor assignments.</p>
                <a href="?url=user/index" class="btn btn-light btn-lg">
                    <i class="bi bi-gear"></i> Go to Admin Panel
                </a>
            <?php else: ?>
                <p class="lead text-white">
                    An online platform connecting students and tutors.
                </p>
                <p class="lead text-white">Log in to access your personalized dashboard.</p>
                <a href="?url=login" class="btn btn-light btn-lg">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </section>

    <!-- Section: Key Features or Stats -->
    <section class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center">

                    <i class="bi bi-person-check-fill fs-1 text-primary"></i>
                    <h5 class="card-title mt-3">Personalized Tutoring</h5>
                    <p class="card-text text-muted">
                        Each student gets a dedicated tutor, ensuring personalized guidance tailored to their needs.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center">

                    <i class="bi bi-chat-left-text-fill fs-1 text-primary"></i>
                    <h5 class="card-title mt-3">Seamless Communication</h5>
                    <p class="card-text text-muted">
                        Message your tutor, schedule meetings, and stay updated with instant notifications.
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center">

                    <i class="bi bi-cloud-arrow-up-fill fs-1 text-primary"></i>
                    <h5 class="card-title mt-3">Resource Sharing</h5>
                    <p class="card-text text-muted">
                        Upload and share learning materials easily with your tutor and classmates.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section: Student has a tutor -->
<?php if ($isStudent && $tutor): ?>
    <section class="mb-5">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white text-center">
                <h5 class="mb-0">
                    <i class="bi bi-person-badge-fill"></i> Your Personal Tutor

                </h5>
            </div>
            <div class="card-body text-center">
                <?php
                $firstName = $tutor['first_name'] ?? '';
                $lastName = $tutor['last_name'] ?? '';
                $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                ?>
                <div class="initials-avatar d-inline-flex mb-3"
                     style="width: 120px; height: 120px; border-radius: 50%; background-color: #007bff; color: white; font-size: 3rem; justify-content: center; align-items: center;">
                    <?= htmlspecialchars($initials) ?>
                </div>
                <h4 class="card-title"><?= htmlspecialchars($tutor['first_name'] . " " . $tutor['last_name']) ?></h4>
                <p class="card-text text-muted">
                    <i class="bi bi-envelope-fill"></i> <?= htmlspecialchars($tutor['email']) ?>
                </p>
                <p class="card-text">
                    <strong>Total Students:</strong>
                    <span class="badge bg-primary">
                            <?= htmlspecialchars($tutor['total_students'] ?? 0) ?>
                        </span>
                </p>
                <a href="mailto:<?= htmlspecialchars($tutor['email']) ?>" class="btn btn-primary">
                    <i class="bi bi-envelope-fill"></i> Contact Tutor
                </a>
                <a href="?url=message/chat&receiver_id=<?= $dashboardData['tutor']['tutor_id'] ?>" class="btn btn-sm btn-primary">
                    <i class="bi bi-chat-dots"></i> Message Tutor
                </a>
            </div>
        </div>
    </section>
<?php endif; ?>

<!--    -- Section: Tutor with Tutees -->
<?php //if ($isTutor && !empty($tutees)): ?>
<!--    <section class="mb-5">-->
<!--        <div class="card shadow border-0">-->
<!--            <div class="card-header bg-success text-white text-center">-->
<!--                <h5 class="mb-0">-->
<!--                    <i class="bi bi-people-fill"></i> My Tutees-->
<!--                </h5>-->
<!--            </div>-->
<!--            <div class="card-body">-->
<!--                <div class="table-responsive">-->
<!--                    <table class="table table-hover align-middle">-->
<!--                        <thead class="table-light">-->
<!--                        <tr>-->
<!--                            <th>Name</th>-->
<!--                            <th>Email</th>-->
<!--                            <th>Actions</th>-->
<!--                        </tr>-->
<!--                        </thead>-->
<!--                        <tbody>-->
<!--                        --><?php //foreach ($tutees as $tutee): ?>
<!--                            <tr>-->
<!--                                <td>--><?php //= htmlspecialchars($tutee['first_name'] . " " . $tutee['last_name']) ?><!--</td>-->
<!--                                <td>--><?php //= htmlspecialchars($tutee['email']) ?><!--</td>-->
<!--                                <td>-->
<!--                                    <a href="mailto:--><?php //= htmlspecialchars($tutee['email']) ?><!--"-->
<!--                                       class="btn btn-sm btn-primary">-->
<!--                                        <i class="bi bi-envelope-fill"></i> Contact-->
<!--                                    </a>-->
<!--                                    <a href="?url=user/detail&id=--><?php //= htmlspecialchars($tutee['user_id']) ?><!--"-->
<!--                                       class="btn btn-sm btn-secondary">-->
<!--                                        <i class="bi bi-person-fill"></i> View Profile-->
<!--                                    </a>-->
<!--                                </td>-->
<!--                            </tr>-->
<!--                        --><?php //endforeach; ?>
<!--                        </tbody>-->
<!--                    </table>-->
<!--                </div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </section>-->
<?php //endif; ?>

    <!-- Section: Info / Announcements or Demo Stats -->
    <!-- Section: Student/Staff Testimonials -->
    <section class="bg-light p-4 rounded">
        <h4 class="mb-3"><i class="bi bi-chat-left-quote-fill"></i> What Our Users Say</h4>
        <div class="row g-4">
            <!-- Testimonial 1 -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <p class="card-text">
                            "eTutoring has revolutionized the way I connect with my tutor. Meetings are easy to schedule
                            and resources are always at my fingertips!"
                        </p>
                        <h6 class="card-subtitle text-muted mt-3">- Alice, Student</h6>
                    </div>
                </div>
            </div>
            <!-- Testimonial 2 -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <p class="card-text">
                            "Managing my tutees and providing them feedback has never been simpler. eTutoring keeps
                            everything organized in one place."
                        </p>
                        <h6 class="card-subtitle text-muted mt-3">- Dr. Smith, Tutor</h6>
                    </div>
                </div>
            </div>
            <!-- Testimonial 3 -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body">
                        <p class="card-text">
                            "User-friendly, efficient, and reliable! As an admin, I can easily manage user roles and
                            assignments."
                        </p>
                        <h6 class="card-subtitle text-muted mt-3">- Jason, Staff</h6>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php include __DIR__ . '/footer.php'; ?>