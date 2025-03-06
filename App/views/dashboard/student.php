<?php
$title = $isOwnDashboard ? "My Dashboard" : "Student Dashboard: " . $student['first_name'] . " " . $student['last_name'];
ob_start();

// Helper function to format date
function formatDate($date) {
    if (!$date) return 'Never';
    return date('M d, Y', strtotime($date));
}

// Helper function to calculate days since a date
function daysSince($date) {
    if (!$date) return 'N/A';
    $date = new DateTime($date);
    $now = new DateTime();
    $diff = $date->diff($now);
    return $diff->days;
}

// Get interaction color based on last interaction
function getInteractionColor($lastInteraction) {
    if (!$lastInteraction) return 'danger';
    $days = daysSince($lastInteraction);
    if ($days > 28) return 'danger';
    if ($days > 7) return 'warning';
    return 'success';
}
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
                <h2>
                    <i class="bi bi-speedometer2"></i>
                    <?= $isOwnDashboard ? "My Dashboard" : "Student Dashboard" ?>
                </h2>

                <?php if (!$isOwnDashboard && $_SESSION['user']['role'] === 'staff'): ?>
                    <a href="?url=dashboard/index" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Main Dashboard
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Student Info Card -->
        <div class="row mb-4">
            <div class="col">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-person-circle"></i> Student Information
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2 text-center">
                                <?php
                                $initials = strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1));
                                ?>
                                <div class="avatar d-inline-flex mb-3"
                                     style="width: 100px; height: 100px; border-radius: 50%; background-color: #007bff; color: #fff; font-size: 2.5rem; justify-content: center; align-items: center;">
                                    <?= $initials ?>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <h4><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></h4>
                                <p><i class="bi bi-envelope"></i> <?= htmlspecialchars($student['email']) ?></p>
                                <p>
                                    <span class="badge bg-primary">Student</span>
                                    <?php if (!$dashboardData['tutor']): ?>
                                        <span class="badge bg-danger">No Tutor Assigned</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-5">
                                <?php if ($dashboardData['tutor']): ?>
                                    <h5>Personal Tutor</h5>
                                    <p>
                                        <strong><?= htmlspecialchars($dashboardData['tutor']['first_name'] . ' ' . $dashboardData['tutor']['last_name']) ?></strong><br>
                                        <i class="bi bi-envelope"></i> <?= htmlspecialchars($dashboardData['tutor']['email']) ?>
                                    </p>
                                    <a href="?url=message/chat&receiver_id=<?= $dashboardData['tutor']['tutor_id'] ?>" class="btn btn-sm btn-primary">
                                        <i class="bi bi-chat-dots"></i> Message Tutor
                                    </a>
                                <?php else: ?>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle"></i> No personal tutor has been assigned yet.
                                        <?php if ($_SESSION['user']['role'] === 'staff'): ?>
                                            <a href="?url=tutor/assign" class="btn btn-sm btn-primary ms-2">
                                                <i class="bi bi-person-plus"></i> Assign Tutor
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Interaction Summary Card -->
        <div class="row mb-4">
            <div class="col">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-activity"></i> Interaction Summary
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <!-- Last Interaction -->
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">Last Interaction</h5>
                                        <p class="fs-4 fw-bold text-<?= getInteractionColor($dashboardData['last_interaction']) ?>">
                                            <?= formatDate($dashboardData['last_interaction']) ?>
                                        </p>
                                        <?php if ($dashboardData['last_interaction']): ?>
                                            <p class="text-muted">
                                                <?= daysSince($dashboardData['last_interaction']) ?> days ago
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Messages -->
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">Total Messages</h5>
                                        <p class="fs-4 fw-bold"><?= $dashboardData['messages']['total_messages'] ?? 0 ?></p>
                                        <p class="text-muted">
                                            <span class="badge bg-primary"><?= $dashboardData['messages']['messages_last_7_days'] ?? 0 ?></span> in the last 7 days
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Meetings -->
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <h5 class="card-title">Meetings</h5>
                                        <p class="fs-4 fw-bold"><?= $dashboardData['meetings']['total_meetings'] ?? 0 ?></p>
                                        <p class="text-muted">
                                            <span class="badge bg-success"><?= $dashboardData['meetings']['completed_meetings'] ?? 0 ?></span> completed
                                            <span class="badge bg-info"><?= $dashboardData['meetings']['upcoming_meetings'] ?? 0 ?></span> upcoming
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Activity Cards -->
        <div class="row mb-4">
            <!-- Message Activity -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-chat-dots"></i> Message Activity
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-center mb-3">
                            <div class="p-2 text-center">
                                <h6>Total</h6>
                                <p class="fs-4 fw-bold"><?= $dashboardData['messages']['total_messages'] ?? 0 ?></p>
                            </div>
                            <div class="p-2 text-center">
                                <h6>Sent</h6>
                                <p class="fs-4 fw-bold"><?= $dashboardData['messages']['sent_messages'] ?? 0 ?></p>
                            </div>
                            <div class="p-2 text-center">
                                <h6>Received</h6>
                                <p class="fs-4 fw-bold"><?= $dashboardData['messages']['received_messages'] ?? 0 ?></p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0">
                                <strong>Last message:</strong>
                                <?= $dashboardData['messages']['last_message_date'] ? date('M d, Y', strtotime($dashboardData['messages']['last_message_date'])) : 'Never' ?>
                            </p>
                            <a href="?url=message/chatList" class="btn btn-sm btn-primary">
                                <i class="bi bi-chat-dots"></i> Messages
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Meeting Activity -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-calendar-event"></i> Meeting Activity
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-center mb-3">
                            <div class="p-2 text-center">
                                <h6>Total</h6>
                                <p class="fs-4 fw-bold"><?= $dashboardData['meetings']['total_meetings'] ?? 0 ?></p>
                            </div>
                            <div class="p-2 text-center">
                                <h6>Completed</h6>
                                <p class="fs-4 fw-bold"><?= $dashboardData['meetings']['completed_meetings'] ?? 0 ?></p>
                            </div>
                            <div class="p-2 text-center">
                                <h6>Upcoming</h6>
                                <p class="fs-4 fw-bold"><?= $dashboardData['meetings']['upcoming_meetings'] ?? 0 ?></p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <?php if ($dashboardData['meetings']['next_meeting_date']): ?>
                                <p class="mb-0">
                                    <strong>Next meeting:</strong>
                                    <?= date('M d, Y', strtotime($dashboardData['meetings']['next_meeting_date'])) ?>
                                </p>
                            <?php else: ?>
                                <p class="mb-0">No upcoming meetings scheduled.</p>
                            <?php endif; ?>
                            <a href="?url=meeting/list" class="btn btn-sm btn-success">
                                <i class="bi bi-calendar"></i> Meetings
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Document Activity -->
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-file-earmark-text"></i> Document Activity
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-center mb-3">
                            <div class="p-2 text-center">
                                <h6>Total Docs</h6>
                                <p class="fs-4 fw-bold"><?= $dashboardData['documents']['total_documents'] ?? 0 ?></p>
                            </div>
                            <div class="p-2 text-center">
                                <h6>Recent (30 days)</h6>
                                <p class="fs-4 fw-bold"><?= $dashboardData['documents']['recent_documents'] ?? 0 ?></p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0">
                                <strong>Last document:</strong>
                                <?= $dashboardData['documents']['last_document_date'] ? date('M d, Y', strtotime($dashboardData['documents']['last_document_date'])) : 'Never' ?>
                            </p>
                            <a href="?url=document/list" class="btn btn-sm btn-secondary">
                                <i class="bi bi-file-earmark"></i> Documents
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Blog Activity -->
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="bi bi-journal-richtext"></i> Blog Activity
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-center mb-3">
                            <div class="p-2 text-center">
                                <h6>Blogs</h6>
                                <p class="fs-4 fw-bold"><?= $dashboardData['blogs']['total_blogs'] ?? 0 ?></p>
                            </div>
                            <div class="p-2 text-center">
                                <h6>Comments</h6>
                                <p class="fs-4 fw-bold"><?= $dashboardData['blogs']['total_comments'] ?? 0 ?></p>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <p class="mb-0">
                                <strong>Participation rate:</strong>
                                <span class="badge <?= ($dashboardData['blogs']['total_comments'] > 0) ? 'bg-success' : 'bg-warning' ?>">
                                <?= ($dashboardData['blogs']['total_blogs'] > 0)
                                    ? round(($dashboardData['blogs']['total_comments'] / $dashboardData['blogs']['total_blogs']) * 100) . '%'
                                    : '0%' ?>
                            </span>
                            </p>
                            <a href="?url=blog/index" class="btn btn-sm btn-info">
                                <i class="bi bi-journal-text"></i> Blogs
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