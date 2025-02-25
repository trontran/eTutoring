<?php
$title = "User Details";
ob_start();
?>

    <h2 class="text-center mb-4">User Details</h2>
    <div class="card shadow-lg p-4">
        <div class="card-body">
            <h4><i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h4>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Role:</strong> <?= ucfirst(htmlspecialchars($user['role'])) ?></p>
            <a href="?url=user/index" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Users
            </a>
        </div>
    </div>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>