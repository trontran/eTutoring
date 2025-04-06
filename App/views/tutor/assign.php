<?php
$title = "Assign Personal Tutor";
ob_start();
?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <h2 class="mb-0"><i class="bi bi-person-plus-fill"></i> Allocate Personal Tutor</h2>
        </div>

        <div class="card-body">
            <?php if (!empty($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i> Tutor Allocate successfully!
                </div>
            <?php endif; ?>
            <?php if (!empty($_GET['success'])): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i> Tutor allocated and emails queued successfully!

                    <?php if (isset($_SESSION['emails_queued']) && $_SESSION['emails_queued'] > 0): ?>
                        <div class="mt-3">
                            <p><?= $_SESSION['emails_queued'] ?> emails are queued. You can send them now or later.</p>
                            <a href="?url=emailqueue/process" class="btn btn-primary">
                                <i class="bi bi-envelope"></i> Send Emails Now (<?= $_SESSION['emails_queued'] ?>)
                            </a>
                            <a href="?url=emailqueue/status" class="btn btn-outline-secondary">
                                <i class="bi bi-info-circle"></i> View Email Queue Status
                            </a>
                        </div>
                        <?php unset($_SESSION['emails_queued']); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <p class="text-muted">
                Please select a tutor, then choose one or more students, and click "Allocate Tutor".
            </p>

            <form action="?url=tutor/store" method="POST" class="mb-4">
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

                <!-- Buttons -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Allocate Tutor
                    </button>
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