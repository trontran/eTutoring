<?php
$title = "Reallocate Tutor";
ob_start();
?>

    <h2 class="text-center">
        <?php if (!empty($student)): ?>
            Reallocate Tutor for <?= htmlspecialchars($student['first_name'] . " " . $student['last_name']) ?>
        <?php else: ?>
            <span class="text-danger">Error: Student Not Found</span>
        <?php endif; ?>
    </h2>

<?php if (empty($student)): ?>
    <div class="alert alert-danger text-center">
        Student not found. <a href="?url=user/index">Go back</a>
    </div>
<?php else: ?>
    <form action="?url=user/storeReallocation" method="POST" class="w-50 mx-auto mt-4">
        <input type="hidden" name="student_id" value="<?= htmlspecialchars($student['user_id']) ?>">

        <div class="mb-3">
            <label class="form-label">Select New Tutor</label>
            <select class="form-control" name="new_tutor_id" required>
                <option value="">-- Select Tutor --</option>
                <?php if (!empty($tutors)): ?>
                    <?php foreach ($tutors as $tutor): ?>
                        <option value="<?= htmlspecialchars($tutor['user_id']) ?>">
                            <?= htmlspecialchars($tutor['first_name'] . " " . $tutor['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="" disabled>No tutors available</option>
                <?php endif; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary w-100">Reallocate Tutor</button>
    </form>
<?php endif; ?>

    <a href="?url=user/index" class="btn btn-secondary w-100 mt-3">
        <i class="bi bi-arrow-left"></i> Back to User Management
    </a>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>