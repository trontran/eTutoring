
<?php
include __DIR__ . '/header.php';

// Xử lý session và logic của trang
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
    require_once '../app/models/User.php';
    $personalTutorModel = new App\Models\User();
    $tutees = $personalTutorModel->getTuteesByTutor($_SESSION['user']['user_id']);
}
?>

<!-- Nội dung trang index (Main Content) -->
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
            <?php if ($tutor): ?>
                <!-- Nút liên hệ tutor nếu cần -->
            <?php endif; ?>
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

<main class="container my-4 flex-grow-1">
    <?php if ($isStudent && $tutor): ?>
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

<!--    --><?php //if ($isTutor && !empty($tutees)): ?>
<!--        <section class="mb-4">-->
<!--            <div class="card shadow">-->
<!--                <div class="card-header bg-success text-white text-center">-->
<!--                    <h5 class="mb-0"><i class="bi bi-people-fill"></i> My Tutees</h5>-->
<!--                </div>-->
<!--                <div class="card-body">-->
<!--                    <div class="table-responsive">-->
<!--                        <table class="table table-hover">-->
<!--                            <thead>-->
<!--                                <tr>-->
<!--                                    <th>Name</th>-->
<!--                                    <th>Email</th>-->
<!--                                    <th>Actions</th>-->
<!--                                </tr>-->
<!--                            </thead>-->
<!--                            <tbody>-->
<!--                                --><?php //foreach ($tutees as $tutee): ?>
<!--                                    <tr>-->
<!--                                        <td>--><?php //= htmlspecialchars($tutee['first_name'] . " " . $tutee['last_name']) ?><!--</td>-->
<!--                                        <td>--><?php //= htmlspecialchars($tutee['email']) ?><!--</td>-->
<!--                                        <td>-->
<!--                                            <a href="mailto:--><?php //= htmlspecialchars($tutee['email']) ?><!--" class="btn btn-sm btn-primary">-->
<!--                                                <i class="bi bi-envelope-fill"></i> Contact-->
<!--                                            </a>-->
<!--                                            <a href="?url=user/profile&id=--><?php //= htmlspecialchars($tutee['user_id']) ?><!--" class="btn btn-sm btn-secondary">-->
<!--                                                <i class="bi bi-person-fill"></i> View Profile-->
<!--                                            </a>-->
<!--                                        </td>-->
<!--                                    </tr>-->
<!--                                --><?php //endforeach; ?>
<!--                            </tbody>-->
<!--                        </table>-->
<!--                    </div>-->
<!--                </div>-->
<!--            </div>-->
<!--        </section>-->
<!--    --><?php //endif; ?>
</main>

<?php include __DIR__ . '/footer.php'; ?>
