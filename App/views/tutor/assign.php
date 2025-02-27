<?php
$title = "Assign Personal Tutor";
ob_start();
?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <h2 class="mb-0"><i class="bi bi-person-plus-fill"></i> Assign Personal Tutor</h2>
        </div>
        <div class="card-body">
            <?php if (!empty($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i> Tutor assigned and email sent successfully!
                </div>
            <?php endif; ?>

            <p class="text-muted">
                Please select one or more students, then choose a tutor, and click "Assign Tutor".
            </p>

            <form action="?url=tutor/store" method="POST" class="mb-4">
                <!-- Select Students -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Select Students</label>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-primary text-center">
                            <tr>
                                <th>Select</th>
                                <th>Student ID</th>
                                <th>Name</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr class="text-center">
                                    <td>
                                        <input type="checkbox" name="student_ids[]" value="<?= htmlspecialchars($student['user_id']) ?>">
                                    </td>
                                    <td><?= htmlspecialchars($student['user_id']) ?></td>
                                    <td><?= htmlspecialchars($student['first_name'] . " " . $student['last_name']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Select Tutor -->
                <div class="mb-3">
                    <label for="tutor" class="form-label fw-bold">Select Tutor</label>
                    <select class="form-select" name="tutor_id" id="tutor" required>
                        <option value="">-- Select Tutor --</option>
                        <?php foreach ($tutors as $tutor): ?>
                            <option value="<?= htmlspecialchars($tutor['user_id']) ?>">
                                <?= htmlspecialchars($tutor['first_name'] . " " . $tutor['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="d-grid gap-2">
                    <!-- Assign Tutor -->
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Assign Tutor
                    </button>
                    <!-- Back to Home -->
                    <a href="?url=home/index" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Home
                    </a>
                </div>
            </form>
        </div>
    </div>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>