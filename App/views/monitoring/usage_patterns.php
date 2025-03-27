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

        <!-- Hourly Activity Results -->
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

                    <div class="table-responsive mt-4">
                        <table class="table table-striped table-hover">
                            <thead class="table-primary">
                            <tr>
                                <th>Hour</th>
                                <th class="text-center">Page Views</th>
                                <th class="text-center">Percentage</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            // Generate array for all 24 hours with zero counts for missing hours
                            $hourData = array_fill(0, 24, 0);
                            foreach ($hourlyActivity as $hour) {
                                $hourData[(int)$hour['hour']] = (int)$hour['count'];
                            }

                            for ($i = 0; $i < 24; $i++) {
                                $hourFormatted = date('g:i A', strtotime($i . ':00'));
                                $count = $hourData[$i];
                                $percentage = ($totalActivity > 0) ? round(($count / $totalActivity) * 100, 1) : 0;
                                $isHighlighted = ($i == $peakHour) ? 'class="table-success"' : '';
                                ?>
                                <tr <?= $isHighlighted ?>>
                                    <td><?= $hourFormatted ?></td>
                                    <td class="text-center"><?= number_format($count) ?></td>
                                    <td class="text-center"><?= $percentage ?>%</td>
                                </tr>
                            <?php } ?>
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