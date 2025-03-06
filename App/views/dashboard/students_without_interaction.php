<?php
$title = "Students Without Interaction";
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
                        <li class="breadcrumb-item active" aria-current="page">Students Without Interaction (<?= $days ?> Days)</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col d-flex justify-content-between align-items-center">
                <h2>
                    <i class="bi bi-clock-history"></i>
                    Students Without Interaction
                    <span class="badge bg-<?= $days > 7 ? 'danger' : 'warning' ?>"><?= $days ?> Days</span>
                </h2>
                <div>
                    <div class="btn-group me-2">
                        <a href="?url=dashboard/studentsWithoutInteraction&days=7" class="btn btn-<?= $days == 7 ? 'warning' : 'outline-warning' ?>">
                            7 Days
                        </a>
                        <a href="?url=dashboard/studentsWithoutInteraction&days=28" class="btn btn-<?= $days == 28 ? 'danger' : 'outline-danger' ?>">
                            28 Days
                        </a>
                    </div>

                    <a href="?url=dashboard/index" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header bg-<?= $days > 7 ? 'danger' : 'warning' ?> text-white">
                <h4 class="mb-0"><i class="bi bi-people"></i> Students with No Activity</h4>
            </div>
            <div class="card-body">
                <?php if (empty($students)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Good news! All students have had interaction within the last <?= $days ?> days.
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        Found <strong><?= count($students) ?></strong> students with no interaction in the last <?= $days ?> days.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                            <tr>
                                <th>Student Name</th>
                                <th>Personal Tutor</th>
                                <th>Last Message</th>
                                <th>Last Meeting</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?>
                                        <br><small class="text-muted"><?= htmlspecialchars($student['email']) ?></small>
                                    </td>
                                    <td>
                                        <?php if (isset($student['tutor_first_name'])): ?>
                                            <?= htmlspecialchars($student['tutor_first_name'] . ' ' . $student['tutor_last_name']) ?>
                                        <?php else: ?>
                                            <span class="badge bg-danger">No Tutor</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($student['last_message_date']): ?>
                                            <?= date('M d, Y', strtotime($student['last_message_date'])) ?>
                                            <br><small class="text-muted">
                                                <?= round((time() - strtotime($student['last_message_date'])) / (60 * 60 * 24)) ?> days ago
                                            </small>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($student['last_meeting_date']): ?>
                                            <?= date('M d, Y', strtotime($student['last_meeting_date'])) ?>
                                            <br><small class="text-muted">
                                                <?= round((time() - strtotime($student['last_meeting_date'])) / (60 * 60 * 24)) ?> days ago
                                            </small>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="?url=dashboard/student&id=<?= $student['user_id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-speedometer2"></i> Dashboard
                                            </a>
                                            <a href="?url=message/chat&receiver_id=<?= $student['user_id'] ?>" class="btn btn-sm btn-success">
                                                <i class="bi bi-chat-dots"></i> Message
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>