<?php
$title = "Select Student for Meeting";
ob_start();
?>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-people"></i> Select a Student for Meeting</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['error']; ?>
                                <?php unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($tutees)): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i> You currently have no students assigned to you.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($tutees as $tutee): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($tutee['first_name'] . ' ' . $tutee['last_name']) ?></td>
                                            <td><?= htmlspecialchars($tutee['email']) ?></td>
                                            <td>
                                                <a href="?url=meeting/create&student_id=<?= $tutee['user_id'] ?>" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-calendar-plus"></i> Schedule Meeting
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                            <a href="?url=home/index" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>