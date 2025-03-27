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
                            <div class="table-responsive">
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
                            <div class="table-responsive">
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
                            <div class="table-responsive">
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

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>