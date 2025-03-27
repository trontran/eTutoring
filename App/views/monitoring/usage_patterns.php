<?php
$title = "Usage Patterns Report";
ob_start();
?>

    <div class="container py-4">
        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="?url=monitoring/index">System Monitoring</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Usage Patterns Report</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-clock-history"></i> Usage Patterns Report</h2>
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
                <form action="?url=monitoring/usagePatterns" method="GET" class="row g-3">
                    <input type="hidden" name="url" value="monitoring/usagePatterns">

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

        <!-- Hourly Activity Chart -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0"><i class="bi bi-clock"></i> Hourly Activity</h4>
            </div>
            <div class="card-body">
                <?php if (empty($hourlyActivity)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No activity data available for the selected period.
                    </div>
                <?php else: ?>
                    <canvas id="hourlyActivityChart" height="300"></canvas>
                    <div class="mt-4">
                        <h5>Key Insights</h5>
                        <?php
                        // Find peak hour
                        $peakHour = 0;
                        $peakCount = 0;
                        $totalActivity = 0;

                        foreach ($hourlyActivity as $hour) {
                            $totalActivity += $hour['count'];
                            if ($hour['count'] > $peakCount) {
                                $peakCount = $hour['count'];
                                $peakHour = $hour['hour'];
                            }
                        }

                        // Format peak hour for display (24h to 12h)
                        $peakHourFormatted = date('g:i A', strtotime($peakHour . ':00'));
                        $peakHourPercentage = ($totalActivity > 0) ? round(($peakCount / $totalActivity) * 100, 1) : 0;
                        ?>
                        <ul>
                            <li><strong>Peak Hour:</strong> <?= $peakHourFormatted ?> with <?= number_format($peakCount) ?> page views (<?= $peakHourPercentage ?>% of total)</li>
                            <li><strong>Total Activity:</strong> <?= number_format($totalActivity) ?> page views in the selected period</li>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Hourly Activity Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-table"></i> Hourly Activity Data</h4>
            </div>
            <div class="card-body">
                <?php if (empty($hourlyActivity)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No activity data available for the selected period.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="hourlyActivityTable">
                            <thead class="table-light">
                            <tr>
                                <th>Hour</th>
                                <th class="text-center">Count</th>
                                <th class="text-center">Percentage</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            foreach ($hourlyActivity as $hour):
                                $hourFormatted = date('g:i A', strtotime($hour['hour'] . ':00'));
                                $percentage = ($totalActivity > 0) ? round(($hour['count'] / $totalActivity) * 100, 1) : 0;
                                ?>
                                <tr class="<?= ($hour['hour'] == $peakHour) ? 'table-success' : '' ?>">
                                    <td><?= $hourFormatted ?></td>
                                    <td class="text-center"><?= number_format($hour['count']) ?></td>
                                    <td class="text-center"><?= $percentage ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (!empty($hourlyActivity)): ?>
            // Prepare data for hourly activity chart
            const hourLabels = [
                <?php
                for ($i = 0; $i < 24; $i++) {
                    echo "'" . date('g A', strtotime($i . ':00')) . "',";
                }
                ?>
            ];

            const hourData = Array(24).fill(0);

            <?php foreach ($hourlyActivity as $hour): ?>
            hourData[<?= $hour['hour'] ?>] = <?= $hour['count'] ?>;
            <?php endforeach; ?>

            // Create hourly activity chart
            const hourlyCtx = document.getElementById('hourlyActivityChart').getContext('2d');
            new Chart(hourlyCtx, {
                type: 'bar',
                data: {
                    labels: hourLabels,
                    datasets: [{
                        label: 'Page Views',
                        data: hourData,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Number of Page Views'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Hour of Day'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        title: {
                            display: true,
                            text: 'Page Views by Hour of Day'
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