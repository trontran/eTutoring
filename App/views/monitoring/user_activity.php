<?php
$title = "User Activity Report";
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
ob_start();
?>

    <div class="container py-4">
        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="?url=monitoring/index">System Monitoring</a></li>
                        <li class="breadcrumb-item active" aria-current="page">User Activity Report</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-people"></i> User Activity Report</h2>
                <a href="?url=monitoring/index" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Date Filter Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-funnel"></i> Filter Data</h4>
            </div>
            <div class="card-body">
                <form action="?url=monitoring/userActivity" method="GET" class="row g-3">
                    <input type="hidden" name="url" value="monitoring/userActivity">

                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $startDate ?>">
                    </div>

                    <div class="col-md-4">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $endDate ?>">
                    </div>

                    <div class="col-md-4 align-self-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Update Report
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- User Activity Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="bi bi-table"></i> User Activity</h4>
            </div>
            <div class="card-body">
                <?php if (empty($mostActiveUsers)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No user activity data available for the selected period.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="userActivityTable">
                            <thead class="table-light">
                            <tr>
                                <th>Rank</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th class="text-center">Page Views</th>
                                <th class="text-center">Activities</th>
                                <th class="text-center">Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $rank = 1;
                            foreach ($mostActiveUsers as $user):
                                ?>
                                <tr>
                                    <td><?= $rank++ ?></td>
                                    <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?= ucfirst(htmlspecialchars($user['role'])) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?= number_format($user['page_views']) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success"><?= number_format($user['activities']) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-dark"><?= number_format($user['total_activity']) ?></span>
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