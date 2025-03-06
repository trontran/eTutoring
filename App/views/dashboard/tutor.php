<?php
$title = $isOwnDashboard ? "My Tutor Dashboard" : "Tutor Dashboard: " . $tutor['first_name'] . " " . $tutor['last_name'];
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
                    <?= $isOwnDashboard ? "My Tutor Dashboard" : "Tutor Dashboard" ?>
                </h2>

                <?php if (!$isOwnDashboard && $_SESSION['user']['role'] === 'staff'): ?>
                    <a href="?url=dashboard/index" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Main Dashboard
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tutor Info Card -->
        <div class="row mb-4">
            <div class="col">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-person-badge-fill"></i> Tutor Information
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2 text-center">
                                <?php
                                $initials = strtoupper(substr($tutor['first_name'], 0, 1) . substr($tutor['last_name'], 0, 1));
                                ?>
                                <div class="avatar d-inline-flex mb-3"
                                     style="width: 100px; height: 100px; border-radius: 50%; background-color: #28a745; color: #fff; font-size: 2.5rem; justify-content: center; align-items: center;">
                                    <?= $initials ?>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <h4><?= htmlspecialchars($tutor['first_name'] . ' ' . $tutor['last_name']) ?></h4>
                                <p><i class="bi bi-envelope"></i> <?= htmlspecialchars($tutor['email']) ?></p>
                                <p>
                                    <span class="badge bg-success">Tutor</span>
                                </p>
                            </div>
                            <div class="col-md-5">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="bi bi-people-fill"></i> Tutee Summary</h5>
                                        <div class="d-flex justify-content-around">
                                            <div class="text-center">
                                                <h2 class="mb-0"><?= count($tutees) ?></h2>
                                                <p class="mb-0">Total Tutees</p>
                                            </div>
                                            <?php
                                            // Count active tutees (interaction in last 7 days)
                                            $activeTutees = 0;
                                            foreach ($tuteeStats as $stats) {
                                                if ($stats['last_interaction'] && daysSince($stats['last_interaction']) <= 7) {
                                                    $activeTutees++;
                                                }
                                            }
                                            ?>
                                            <div class="text-center">
                                                <h2 class="mb-0"><?= $activeTutees ?></h2>
                                                <p class="mb-0">Active (7d)</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Summary -->
        <div class="row mb-4">
            <div class="col">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-bar-chart-fill"></i> Activity Summary
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Messages Sent -->
                            <div class="col-md-3 mb-3">
                                <div class="card text-center h-100">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="bi bi-chat-dots text-primary"></i> Messages</h5>
                                        <?php
                                        // Calculate messages sent in last 7 days
                                        $recentMessages = 0;
                                        foreach ($tutees as $tutee) {
                                            if (isset($tuteeStats[$tutee['user_id']]['messages']['messages_last_7_days'])) {
                                                $recentMessages += $tuteeStats[$tutee['user_id']]['messages']['messages_last_7_days'];
                                            }
                                        }
                                        ?>
                                        <h3><?= $recentMessages ?></h3>
                                        <p class="text-muted">Last 7 days</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Meetings -->
                            <div class="col-md-3 mb-3">
                                <div class="card text-center h-100">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="bi bi-calendar-check text-success"></i> Meetings</h5>
                                        <?php
                                        // Calculate upcoming meetings
                                        $upcomingMeetings = 0;
                                        foreach ($tutees as $tutee) {
                                            if (isset($tuteeStats[$tutee['user_id']]['meetings']['upcoming_meetings'])) {
                                                $upcomingMeetings += $tuteeStats[$tutee['user_id']]['meetings']['upcoming_meetings'];
                                            }
                                        }
                                        ?>
                                        <h3><?= $upcomingMeetings ?></h3>
                                        <p class="text-muted">Upcoming</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Completed Meetings -->
                            <div class="col-md-3 mb-3">
                                <div class="card text-center h-100">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="bi bi-check-circle text-info"></i> Completed</h5>
                                        <?php
                                        // Calculate completed meetings
                                        $completedMeetings = 0;
                                        foreach ($tutees as $tutee) {
                                            if (isset($tuteeStats[$tutee['user_id']]['meetings']['completed_meetings'])) {
                                                $completedMeetings += $tuteeStats[$tutee['user_id']]['meetings']['completed_meetings'];
                                            }
                                        }
                                        ?>
                                        <h3><?= $completedMeetings ?></h3>
                                        <p class="text-muted">Meetings</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Documents Shared -->
                            <div class="col-md-3 mb-3">
                                <div class="card text-center h-100">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="bi bi-file-earmark-text text-secondary"></i> Documents</h5>
                                        <?php
                                        // Calculate recent documents
                                        $recentDocuments = 0;
                                        foreach ($tutees as $tutee) {
                                            if (isset($tuteeStats[$tutee['user_id']]['documents']['recent_documents'])) {
                                                $recentDocuments += $tuteeStats[$tutee['user_id']]['documents']['recent_documents'];
                                            }
                                        }
                                        ?>
                                        <h3><?= $recentDocuments ?></h3>
                                        <p class="text-muted">Last 30 days</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tutee List -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">
                    <i class="bi bi-people-fill"></i> My Tutees
                    <span class="badge bg-light text-primary"><?= count($tutees) ?></span>
                </h4>
            </div>
            <div class="card-body">
                <?php if (empty($tutees)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> You don't have any tutees assigned yet.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>Last Interaction</th>
                                <th>Messages</th>
                                <th>Meetings</th>
                                <th>Activity</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($tutees as $tutee): ?>
                                <?php
                                $stats = $tuteeStats[$tutee['user_id']] ?? [];
                                $lastInteraction = $stats['last_interaction'] ?? null;
                                $interactionColor = getInteractionColor($lastInteraction);
                                ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="me-2">
                                                <?php
                                                $initials = strtoupper(substr($tutee['first_name'], 0, 1) . substr($tutee['last_name'], 0, 1));
                                                ?>
                                                <div style="width: 40px; height: 40px; border-radius: 50%; background-color: #007bff; color: #fff; font-size: 1rem; display: flex; justify-content: center; align-items: center;">
                                                    <?= $initials ?>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?= htmlspecialchars($tutee['first_name'] . ' ' . $tutee['last_name']) ?></div>
                                                <div class="small text-muted"><?= htmlspecialchars($tutee['email']) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $interactionColor ?>">
                                            <?= $lastInteraction ? formatDate($lastInteraction) : 'Never' ?>
                                        </span>
                                        <?php if ($lastInteraction): ?>
                                            <div class="small text-muted"><?= daysSince($lastInteraction) ?> days ago</div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div><?= $stats['messages']['total_messages'] ?? 0 ?> total</div>
                                        <div class="small text-muted"><?= $stats['messages']['messages_last_7_days'] ?? 0 ?> in 7 days</div>
                                    </td>
                                    <td>
                                        <div><?= $stats['meetings']['total_meetings'] ?? 0 ?> total</div>
                                        <div class="small text-muted">
                                            <span class="text-success"><?= $stats['meetings']['upcoming_meetings'] ?? 0 ?> upcoming</span> |
                                            <span class="text-primary"><?= $stats['meetings']['completed_meetings'] ?? 0 ?> completed</span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        // Calculate activity level based on interactions
                                        $activityLevel = 'Low';
                                        $activityColor = 'danger';

                                        if ($lastInteraction) {
                                            $daysSinceInteraction = daysSince($lastInteraction);
                                            $recentMessages = $stats['messages']['messages_last_7_days'] ?? 0;

                                            if ($daysSinceInteraction <= 7 && $recentMessages >= 3) {
                                                $activityLevel = 'High';
                                                $activityColor = 'success';
                                            } else if ($daysSinceInteraction <= 14) {
                                                $activityLevel = 'Medium';
                                                $activityColor = 'warning';
                                            }
                                        }
                                        ?>
                                        <span class="badge bg-<?= $activityColor ?>"><?= $activityLevel ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="?url=dashboard/student&id=<?= $tutee['user_id'] ?>" class="btn btn-sm btn-primary">
                                                <i class="bi bi-speedometer2"></i> Dashboard
                                            </a>
                                            <a href="?url=message/chat&receiver_id=<?= $tutee['user_id'] ?>" class="btn btn-sm btn-success">
                                                <i class="bi bi-chat-dots"></i> Message
                                            </a>
                                            <a href="?url=meeting/create&student_id=<?= $tutee['user_id'] ?>" class="btn btn-sm btn-info">
                                                <i class="bi bi-calendar-plus"></i> Schedule
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