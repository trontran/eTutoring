<?php
$title = "Completed Meetings";
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
                        <li class="breadcrumb-item"><a href="?url=meeting/list">Meetings</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Completed Meetings</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-calendar-check"></i> Completed Meetings</h2>
                <a href="?url=meeting/list" class="btn btn-primary">
                    <i class="bi bi-arrow-left"></i> Back to Meetings
                </a>
            </div>
        </div>

        <!-- Completed Meetings List -->
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="bi bi-journal-check"></i> Meeting Records</h4>
            </div>
            <div class="card-body">
                <?php if (empty($completedMeetings)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> You don't have any completed meetings yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>
                                    <?= $userRole === 'student' ? 'Tutor' : 'Student' ?>
                                </th>
                                <th>Type</th>
                                <th>Completed</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($completedMeetings as $meeting): ?>
                                <tr>
                                    <td>
                                        <?= date('M d, Y - h:i A', strtotime($meeting['meeting_date'])) ?>
                                    </td>
                                    <td>
                                        <?php
                                        if ($userRole === 'student') {
                                            echo htmlspecialchars($meeting['tutor_first_name'] . ' ' . $meeting['tutor_last_name']);
                                        } else {
                                            echo htmlspecialchars($meeting['student_first_name'] . ' ' . $meeting['student_last_name']);
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($meeting['meeting_type'] === 'virtual'): ?>
                                            <span class="badge bg-info">
                                                <i class="bi bi-camera-video"></i> Virtual
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-person"></i> In-Person
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= date('M d, Y', strtotime($meeting['completed_at'])) ?>
                                    </td>
                                    <td>
                                        <a href="?url=meeting/view&id=<?= $meeting['meeting_id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
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