<?php
$title = "Technology Usage Report";
ob_start();
?>

    <div class="container py-4">
    <div class="row mb-4">
        <div class="col">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="?url=monitoring/index">System Monitoring</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Technology Usage Report</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col d-flex justify-content-between align-items-center">
            <h2><i class="bi bi-laptop"></i> Technology Usage Report</h2>
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
            <form action="?url=monitoring/techUsage" method="GET" class="row g-3">
                <input type="hidden" name="url" value="monitoring/techUsage">

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
                    <canvas id="browserChart" height="250"></canvas>
                    <div class="table-responsive mt-4">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                            <tr>
                                <th>Browser</th>
                                <th class="text-center">Count</th>
                                <th class="text-center">Percentage</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($browserUsage as $browser): ?>
                                <tr>
                                    <td><?= htmlspecialchars($browser['browser']) ?></td>
                                    <td class="text-center"><?= number_format($browser['count']) ?></td>
                                    <td class="text-center"><?= $browser['percentage'] ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Device Usage -->
    <div class="col-md-4 mb-4">
    <div class="card shadow-sm h-100">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-phone"></i> Device Usage</h5>
    </div>
    <div class="card-body">
<?php if (empty($deviceUsage)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> No device data available for the selected period.
    </div>
<?php else: ?>
    <canvas id="deviceChart" height="250"></canvas>
    <div class="table-responsive mt-4">
    <table class="table table-sm table-hover">
    <thead class="table-light">
    <tr>
        <th>Device</th>
        <th class="text-center">Count</th>
        <th class="text-center">Percentage</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($deviceUsage as $device): ?>
        <tr>
        <td><?= htmlspecialchars($device['device']) ?></td>
            <td class="text-center"><?= number_format($device['count']) ?></td>
            <td class="text-center"><?= $device['percentage'] ?>%</td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    </table>
    </div>
<?php endif; ?>
    </div>
    </div>
    </div>

        <!-- OS Usage -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-laptop"></i> OS Usage</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($osUsage)): ?>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No OS data available for the selected period.
                        </div>
                    <?php else: ?>
                        <canvas id="osChart" height="250"></canvas>
                        <div class="table-responsive mt-4">
                            <table class="table table-sm table-hover">
                                <thead class="table-light">
                                <tr>
                                    <th>Operating System</th>
                                    <th class="text-center">Count</th>
                                    <th class="text-center">Percentage</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($osUsage as $os): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($os['os']) ?></td>
                                        <td class="text-center"><?= number_format($os['count']) ?></td>
                                        <td class="text-center"><?= $os['percentage'] ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
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