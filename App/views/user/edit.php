<?php
$title = "Edit User";
ob_start();
?>

    <div class="container mt-4">
        <div class="card mx-auto shadow border-0" style="max-width: 600px;">
            <!-- Header Card -->
            <div class="card-header bg-primary text-white text-center">
                <h2 class="mb-0">
                    <i class="bi bi-pencil-square"></i> Edit User
                </h2>
            </div>
            <!-- Body Card -->
            <div class="card-body">
                <!-- Thông báo lỗi -->
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger text-center">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form action="?url=user/update&id=<?= htmlspecialchars($user['user_id']) ?>" method="POST" class="mt-3">
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['user_id']) ?>">

                    <!-- First Name -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">First Name</label>
                        <input type="text" class="form-control" name="first_name"
                               value="<?= htmlspecialchars($user['first_name']) ?>" required>
                    </div>
                    <!-- Last Name -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Last Name</label>
                        <input type="text" class="form-control" name="last_name"
                               value="<?= htmlspecialchars($user['last_name']) ?>" required>
                    </div>
                    <!-- Email -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" class="form-control" name="email"
                               value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <!-- Role -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Role</label>
                        <select class="form-select" name="role">
                            <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                            <option value="tutor" <?= $user['role'] === 'tutor' ? 'selected' : '' ?>>Tutor</option>
                            <option value="staff" <?= $user['role'] === 'staff' ? 'selected' : '' ?>>Staff</option>
                        </select>
                    </div>
                    <!-- Submit -->
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-circle"></i> Update User
                    </button>
                </form>
            </div>
        </div>
    </div>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>