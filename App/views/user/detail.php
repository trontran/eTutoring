<?php
$title = "User Details";
ob_start();
?>

    <div class="container mt-4">
        <div class="card shadow border-0 mx-auto" style="max-width: 600px;">
            <!-- Header -->
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0">
                    <i class="bi bi-person-circle"></i> User Details
                </h4>
            </div>
            <!-- Body -->
            <div class="card-body">
                <div class="d-flex align-items-center mb-3">
                    <?php
                    // Create avatar (base on first letter, if needed)
                    $firstName = $user['first_name'] ?? '';
                    $lastName = $user['last_name'] ?? '';
                    $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                    ?>
                    <div class="avatar bg-primary text-white d-inline-flex justify-content-center align-items-center me-3"
                         style="width: 60px; height: 60px; border-radius: 50%; font-size: 1.5rem;">
                        <?= htmlspecialchars($initials) ?>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0">
                            <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>
                        </h5>
                        <small class="text-muted">
                            <i class="bi bi-envelope-fill"></i> <?= htmlspecialchars($user['email']) ?>
                        </small>
                    </div>
                </div>

                <hr>

                <p class="mb-1"><strong>Role:</strong>
                    <span class="badge bg-success">
                    <?= ucfirst(htmlspecialchars($user['role'])) ?>
                </span>
                </p>

                <!-- "Back to user" button -->
                <div class="mt-4 text-end">
                    <a href="?url=user/index" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Users
                    </a>
                </div>
            </div>
        </div>
    </div>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>