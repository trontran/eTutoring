<?php
$title = "My Meetings";
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
            <div class="col d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-calendar3"></i> My Meetings</h2>
                <a href="?url=meeting/create" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Schedule New Meeting
                </a>
            </div>
        </div>

        <!-- Upcoming Meetings -->
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-calendar-check"></i> Upcoming Meetings</h4>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingMeetings)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> You have no upcoming meetings scheduled.
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
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($upcomingMeetings as $meeting): ?>
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
                                        <?php if ($meeting['status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php elseif ($meeting['status'] === 'confirmed'): ?>
                                            <span class="badge bg-success">Confirmed</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Cancelled</span>
                                        <?php endif; ?>
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

        <!-- Past Meetings -->
        <div class="card shadow">
            <div class="card-header bg-secondary text-white">
                <h4 class="mb-0"><i class="bi bi-calendar-x"></i> Past Meetings</h4>
            </div>
            <div class="card-body">
                <?php if (empty($pastMeetings)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> You have no past meetings.
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
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($pastMeetings as $meeting): ?>
                                <tr class="table-light">
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
                                        <?php if ($meeting['status'] === 'pending'): ?>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        <?php elseif ($meeting['status'] === 'confirmed'): ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="?url=meeting/view&id=<?= $meeting['meeting_id'] ?>" class="btn btn-sm btn-secondary">
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