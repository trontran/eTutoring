<?php
$title = "Admin Dashboard";
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
                <h2><i class="bi bi-speedometer2"></i> Administrator Dashboard</h2>
                <p class="text-muted">System overview and reports</p>
            </div>
        </div>

        <!-- Key Statistics Cards -->
        <div class="row mb-4">
            <!-- Messages in last 7 days -->
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-chat-dots-fill text-primary" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-3">Recent Messages</h5>
                        <p class="card-text display-6 fw-bold"><?= number_format($systemStats['recent_messages'] ?? 0) ?></p>
                        <p class="text-muted">In the last 7 days</p>
                    </div>
                </div>
            </div>

            <!-- Average messages per tutor -->
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm text-center h-100">
                    <div class="card-body">
                        <i class="bi bi-person-badge-fill text-success" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-3">Avg. Messages per Tutor</h5>
                        <p class="card-text display-6 fw-bold"><?= number_format($systemStats['avg_tutor_messages'] ?? 0, 1) ?></p>
                        <p class="text-muted">All time average</p>
                    </div>
                </div>
            </div>

            <!-- Students without tutors -->
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm text-center h-100 <?= ($systemStats['students_without_tutor'] ?? 0) > 0 ? 'border-danger' : '' ?>">
                    <div class="card-body">
                        <i class="bi bi-exclamation-triangle-fill text-danger" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-3">Students Without Tutor</h5>
                        <p class="card-text display-6 fw-bold"><?= number_format($systemStats['students_without_tutor'] ?? 0) ?></p>
                        <a href="?url=dashboard/studentsWithoutTutor" class="btn btn-sm btn-outline-danger">View List</a>
                    </div>
                </div>
            </div>

            <!-- Students without interaction -->
            <div class="col-md-3 mb-4">
                <div class="card shadow-sm text-center h-100 <?= ($systemStats['no_interaction_7days'] ?? 0) > 0 ? 'border-warning' : '' ?>">
                    <div class="card-body">
                        <i class="bi bi-clock-history text-warning" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-3">No Recent Interaction</h5>
                        <p class="card-text display-6 fw-bold"><?= number_format($systemStats['no_interaction_7days'] ?? 0) ?></p>
                        <div class="btn-group">
                            <a href="?url=dashboard/studentsWithoutInteraction&days=7" class="btn btn-sm btn-outline-warning">7 Days</a>
                            <a href="?url=dashboard/studentsWithoutInteraction&days=28" class="btn btn-sm btn-outline-danger">28 Days</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports Section -->
        <div class="row mb-4">
            <div class="col">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-file-earmark-text"></i> Available Reports</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="bi bi-people"></i> Tutor Activity</h5>
                                        <p class="card-text">View activity statistics for all tutors including message counts and meeting frequency.</p>
                                        <a href="?url=dashboard/tutorActivity" class="btn btn-primary">View Report</a>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="bi bi-exclamation-triangle"></i> Exception Reports</h5>
                                        <p class="card-text">Students without tutors or with no recent interaction (7 or 28 days).</p>
                                        <div class="d-grid gap-2">
                                            <a href="?url=dashboard/studentsWithoutTutor" class="btn btn-outline-danger">Without Tutor</a>
                                            <div class="btn-group">
                                                <a href="?url=dashboard/studentsWithoutInteraction&days=7" class="btn btn-outline-warning">No Activity (7d)</a>
                                                <a href="?url=dashboard/studentsWithoutInteraction&days=28" class="btn btn-outline-danger">No Activity (28d)</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><i class="bi bi-person-lines-fill"></i> Student Dashboards</h5>
                                        <p class="card-text">View individual student dashboards to analyze their progress and interaction.</p>
                                        <a href="?url=user/index" class="btn btn-primary">Select Student</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Time-Based Reports Section -->
    <div class="row mb-4">
        <div class="col">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0"><i class="bi bi-clock-history"></i> Time-Based Analytics</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-graph-up"></i> Activity Trends</h5>
                                    <p class="card-text">View message and meeting activity trends over time (weekly, monthly, term).</p>
                                    <a href="?url=dashboard/timeBasedActivity" class="btn btn-primary">View Trends</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-clock"></i> Peak Usage Times</h5>
                                    <p class="card-text">Analyze when students and tutors are most active by hour and day of week.</p>
                                    <a href="?url=dashboard/peakUsageTimes" class="btn btn-primary">View Peak Times</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-arrow-left-right"></i> Compare Periods</h5>
                                    <p class="card-text">Compare system activity between two custom time periods.</p>
                                    <a href="?url=dashboard/compareTimePeriods" class="btn btn-primary">Compare Periods</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="bi bi-speedometer2"></i> System Monitoring</h5>
                                    <p class="card-text">View detailed system usage statistics, browser data, and user activity.</p>
                                    <a href="?url=monitoring/index" class="btn btn-primary">View Monitoring</a>
                                </div>
                            </div>
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