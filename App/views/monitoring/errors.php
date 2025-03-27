<?php
$title = "System Errors Report";
ob_start();
?>

    <div class="container py-4">
        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="?url=monitoring/index">System Monitoring</a></li>
                        <li class="breadcrumb-item active" aria-current="page">System Errors Report</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-exclamation-triangle"></i> System Errors Report</h2>
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
                <form action="?url=monitoring/errors" method="GET" class="row g-3">
                    <input type="hidden" name="url" value="monitoring/errors">

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

        <!-- System Errors Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0"><i class="bi bi-table"></i> System Errors</h4>
            </div>
            <div class="card-body">
                <?php if (empty($systemErrors)): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> No system errors found for the selected period.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="systemErrorsTable">
                            <thead class="table-light">
                            <tr>
                                <th>Time</th>
                                <th>Error Type</th>
                                <th>User</th>
                                <th>URL</th>
                                <th>Message</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($systemErrors as $error): ?>
                                <tr>
                                    <td><?= date('M d, Y H:i:s', strtotime($error['error_datetime'])) ?></td>
                                    <td>
                                        <span class="badge bg-danger"><?= htmlspecialchars($error['error_type']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($error['user_id']): ?>
                                            <?= htmlspecialchars($error['first_name'] . ' ' . $error['last_name']) ?>
                                        <?php else: ?>
                                            <span class="text-muted">Guest</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($error['url'] ?? 'N/A') ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-secondary"
                                                type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#error<?= $error['error_id'] ?>"
                                                aria-expanded="false"
                                                aria-controls="error<?= $error['error_id'] ?>">
                                            View Details
                                        </button>
                                        <div class="collapse mt-2" id="error<?= $error['error_id'] ?>">
                                            <div class="card card-body">
                                                <pre class="mb-0"><?= htmlspecialchars($error['error_message']) ?></pre>
                                            </div>
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