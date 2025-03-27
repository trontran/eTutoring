<?php
$title = "Page Views Report";

ob_start();
?>

    <div class="container py-4">
        <div class="row mb-4">
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="?url=monitoring/index">System Monitoring</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Page Views Report</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-file-earmark-text"></i> Page Views Report</h2>
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
                <form action="?url=monitoring/pageViews" method="GET" class="row g-3">
                    <input type="hidden" name="url" value="monitoring/pageViews">

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

        <!-- Page Views Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-table"></i> Page Views</h4>
            </div>
            <div class="card-body">
                <?php if (empty($mostViewedPages)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> No page view data available for the selected period.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover" id="pageViewsTable">
                            <thead class="table-light">
                            <tr>
                                <th>Rank</th>
                                <th>Page URL</th>
                                <th class="text-center">Views</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $rank = 1;
                            foreach ($mostViewedPages as $page):
                                ?>
                                <tr>
                                    <td><?= $rank++ ?></td>
                                    <td><?= htmlspecialchars($page['url']) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary"><?= number_format($page['view_count']) ?></span>
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