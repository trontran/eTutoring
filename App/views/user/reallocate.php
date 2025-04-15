<?php
$title = "Reallocate Tutor";
ob_start();
?>

    <div class="container mt-4">
        <div class="card mx-auto shadow border-0" style="max-width: 600px;">
            <!-- Header -->
            <div class="card-header bg-primary text-white text-center">
                <h4 class="mb-0">
                    <i class="bi bi-arrow-repeat"></i>
                    <?php if (!empty($student)): ?>
                        Reallocate Tutor for <?= htmlspecialchars($student['first_name'] . " " . $student['last_name']) ?>
                    <?php else: ?>
                        <span class="text-warning">Error: Student Not Found</span>
                    <?php endif; ?>
                </h4>
            </div>

            <div class="card-body">
                <?php if (empty($student)): ?>
                    <!-- Display error if student is not exist -->
                    <div class="alert alert-danger text-center">
                        Student not found.
                        <a href="?url=user/index" class="ms-2">
                            <i class="bi bi-arrow-left"></i> Go back
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Form reallocate -->
                    <form action="?url=user/storeReallocation" method="POST" class="mt-2">
                        <input type="hidden" name="student_id" value="<?= htmlspecialchars($student['user_id']) ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Select New Tutor</label>
                            <select class="form-select" name="new_tutor_id" required>
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

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle"></i> Reallocate Tutor
                        </button>
                    </form>
                <?php endif; ?>

                <!-- "Back to User Management" button -->
                <a href="?url=user/index" class="btn btn-secondary w-100 mt-3">
                    <i class="bi bi-arrow-left"></i> Back to User Management
                </a>
            </div>
        </div>
    </div>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>