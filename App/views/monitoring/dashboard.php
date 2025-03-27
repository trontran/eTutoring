<?php
$title = "System Monitoring Dashboard";
ob_start();
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col d-flex justify-content-between align-items-center">
            <h2><i class="bi bi-speedometer2"></i> System Monitoring Dashboard</h2>
            <a href="?url=dashboard/index" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Main Dashboard
            </a>
        </div>
    </div>

    <!-- Date Filter Form -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="bi bi-funnel"></i> Filter Data</h4>
        </div>
        <div class="card-body">
            <form action="?url=monitoring/index" method="GET" class="row g-3">
                <input type="hidden" name="url" value="monitoring/index">

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

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <h4 class="mb-0"><i class="bi bi-graph-up"></i> Quick Stats</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-4">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">Total Page Views</h6>
                                    <h1 class="display-4">
                                        <?php
                                        $totalViews = 0;
                                        foreach ($mostViewedPages as $page) {
                                            $totalViews += $page['view_count'];
                                        }
                                        echo number_format($totalViews);
                                        ?>
                                    </h1>
                                    <p class="mb-0">
                                        <a href="?url=monitoring/pageViews&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> View Details
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 text-center mb-4">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">Active Users</h6>
                                    <h1 class="display-4">
                                        <?= count($mostActiveUsers) ?>
                                    </h1>
                                    <p class="mb-0">
                                        <a href="?url=monitoring/userActivity&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-people"></i> View Details
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 text-center mb-4">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">Top Browser</h6>
                                    <h1 class="display-4">
                                        <?php
                                        echo !empty($browserUsage) ? $browserUsage[0]['browser'] : 'N/A';
                                        ?>
                                    </h1>
                                    <p class="mb-0">
                                        <a href="?url=monitoring/techUsage&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-laptop"></i> View Details
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 text-center mb-4">
                            <div class="card bg-light h-100">
                                <div class="card-body">
                                    <h6 class="text-muted">Top Device</h6>
                                    <h1 class="display-4">
                                        <?php
                                        echo !empty($deviceUsage) ? $deviceUsage[0]['device'] : 'N/A';
                                        ?>
                                    </h1>
                                    <p class="mb-0">
                                        <a href="?url=monitoring/techUsage&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-phone"></i> View Details
                                        </a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Most Viewed Pages -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Most Viewed Pages</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($mostViewedPages)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No page view data available for the selected period.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                <tr>
                                    <th>Page URL</th>
                                    <th class="text-center">Views</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($mostViewedPages as $page): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($page['url']) ?></td>
                                        <td class="text-center">
                                            <span class="badge bg-primary"><?= number_format($page['view_count']) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 text-end">
                            <a href="?url=monitoring/pageViews&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="btn btn-sm btn-primary">
                                <i class="bi bi-arrow-right"></i> View All
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Most Active Users -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-people"></i> Most Active Users</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($mostActiveUsers)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No user activity data available for the selected period.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th class="text-center">Page Views</th>
                                    <th class="text-center">Activities</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($mostActiveUsers as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                        <td>
                                            <span class="badge bg-secondary"><?= ucfirst(htmlspecialchars($user['role'])) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary"><?= number_format($user['page_views']) ?></span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success"><?= number_format($user['activities']) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 text-end">
                            <a href="?url=monitoring/userActivity&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="btn btn-sm btn-success">
                                <i class="bi bi-arrow-right"></i> View All
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <!-- Browser Usage -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-globe"></i> Browser Usage</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($browserUsage)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No browser data available for the selected period.
                        </div>
                    <?php else: ?>
                        <canvas id="browserChart" height="200"></canvas>
                        <div class="mt-3 text-end">
                            <a href="?url=monitoring/techUsage&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="btn btn-sm btn-info">
                                <i class="bi bi-arrow-right"></i> View Details
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Device Usage -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-phone"></i> Device Usage</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($deviceUsage)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No device data available for the selected period.
                        </div>
                    <?php else: ?>
                        <canvas id="deviceChart" height="200"></canvas>
                        <div class="mt-3 text-end">
                            <a href="?url=monitoring/techUsage&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="btn btn-sm btn-info">
                                <i class="bi bi-arrow-right"></i> View Details
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- OS Usage -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-laptop"></i> OS Usage</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($osUsage)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No OS data available for the selected period.
                        </div>
                    <?php else: ?>
                        <canvas id="osChart" height="200"></canvas>
                        <div class="mt-3 text-end">
                            <a href="?url=monitoring/techUsage&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>" class="btn btn-sm btn-info">
                                <i class="bi bi-arrow-right"></i> View Details
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Browser Chart
        <?php if (!empty($browserUsage)): ?>
        const browserCtx = document.getElementById('browserChart').getContext('2d');
        new Chart(browserCtx, {
            type: 'pie',
            data: {
                labels: [
                    <?php foreach ($browserUsage as $browser): ?>
                    '<?= $browser['browser'] ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    data: [
                        <?php foreach ($browserUsage as $browser): ?>
                        <?= $browser['count'] ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(201, 203, 207, 0.8)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 15
                        }
                    }
                }
            }
        });
        <?php endif; ?>

        // Device Chart
        <?php if (!empty($deviceUsage)): ?>
        const deviceCtx = document.getElementById('deviceChart').getContext('2d');
        new Chart(deviceCtx, {
            type: 'pie',
            data: {
                labels: [
                    <?php foreach ($deviceUsage as $device): ?>
                    '<?= $device['device'] ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    data: [
                        <?php foreach ($deviceUsage as $device): ?>
                        <?= $device['count'] ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 15
                        }
                    }
                }
            }
        });
        <?php endif; ?>

        // OS Chart
        <?php if (!empty($osUsage)): ?>
        const osCtx = document.getElementById('osChart').getContext('2d');
        new Chart(osCtx, {
            type: 'pie',
            data: {
                labels: [
                    <?php foreach ($osUsage as $os): ?>
                    '<?= $os['os'] ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    data: [
                        <?php foreach ($osUsage as $os): ?>
                        <?= $os['count'] ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                        'rgba(201, 203, 207, 0.8)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 15
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    });
</script>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>