<?php
$title = "Students Without Tutor";
ob_start();
?>

    <div class="container py-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $_SESSION['success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="?url=dashboard/index">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Students Without Tutor</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-exclamation-triangle"></i> Students Without Tutor</h2>
                <a href="?url=dashboard/index" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0"><i class="bi bi-people"></i> Student List</h4>
            </div>
            <div class="card-body">
                <?php if (empty($students)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Good news! All students have been assigned a tutor.
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Found <strong><?= count($students) ?></strong> students without an assigned tutor.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Student Name</th>
                                <th>Email</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?= $student['user_id'] ?></td>
                                    <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                                    <td><?= htmlspecialchars($student['email']) ?></td>
                                    <td><?= date('M d, Y', strtotime($student['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="?url=dashboard/student&id=<?= $student['user_id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-speedometer2"></i> Dashboard
                                            </a>
                                            <a href="?url=user/reallocate&id=<?= $student['user_id'] ?>" class="btn btn-sm btn-success">
                                                <i class="bi bi-person-plus"></i> Assign Tutor
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <a href="?url=tutor/assign" class="btn btn-primary">
                            <i class="bi bi-person-plus"></i> Bulk Assign Tutors
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>