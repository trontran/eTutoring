<?php
$title = "Tutor Activity Report";
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
                        <li class="breadcrumb-item active" aria-current="page">Tutor Activity Report</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-people"></i> Tutor Activity Report</h2>
                <a href="?url=dashboard/index" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-bar-chart"></i> Tutor Statistics</h4>
            </div>
            <div class="card-body">
                <?php if (empty($tutors)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No tutors found in the system.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                            <tr>
                                <th>Tutor Name</th>
                                <th>Email</th>
                                <th>Tutees</th>
                                <th>Messages (7d)</th>
                                <th>Meetings (7d)</th>
                                <th>Completed Meetings</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($tutors as $tutor): ?>
                                <tr>
                                    <td><?= htmlspecialchars($tutor['first_name'] . ' ' . $tutor['last_name']) ?></td>
                                    <td><?= htmlspecialchars($tutor['email']) ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?= $tutor['tutee_count'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= $tutor['messages_sent_7days'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success"><?= $tutor['meetings_7days'] ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= $tutor['completed_meetings'] ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="?url=dashboard/tutor&id=<?= $tutor['user_id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-speedometer2"></i> Dashboard
                                            </a>
                                            <a href="?url=message/chat&receiver_id=<?= $tutor['user_id'] ?>" class="btn btn-sm btn-success">
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