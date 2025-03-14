<?php
$title = "Compare Time Periods";
ob_start();

// Helper function to format date for display
function formatDisplayDate($date) {
    return date('M d, Y', strtotime($date));
}

// Helper function to get appropriate badge class based on value change
function getChangeBadgeClass($change) {
    if ($change > 0) {
        return 'bg-success';
    } elseif ($change < 0) {
        return 'bg-danger';
    } else {
        return 'bg-secondary';
    }
}

// Helper function to format percent change
function formatPercentChange($change) {
    $prefix = $change > 0 ? '+' : '';
    return $prefix . number_format($change, 1) . '%';
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
            <div class="col">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="?url=dashboard/index">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Compare Time Periods</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-calendar3"></i> Compare Time Periods</h2>
                <a href="?url=dashboard/index" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-funnel"></i> Time Period Selection</h4>
            </div>
            <div class="card-body">
                <form action="?url=dashboard/compareTimePeriods" method="GET" class="row g-3">
                    <input type="hidden" name="url" value="dashboard/compareTimePeriods">

                    <div class="col-12">
                        <h5>First Period</h5>
                    </div>

                    <div class="col-md-3">
                        <label for="period1_start" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="period1_start" name="period1_start" value="<?= $period1Start ?>">
                    </div>

                    <div class="col-md-3">
                        <label for="period1_end" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="period1_end" name="period1_end" value="<?= $period1End ?>">
                    </div>

                    <div class="col-12 mt-3">
                        <h5>Second Period</h5>
                    </div>

                    <div class="col-md-3">
                        <label for="period2_start" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="period2_start" name="period2_start" value="<?= $period2Start ?>">
                    </div>

                    <div class="col-md-3">
                        <label for="period2_end" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="period2_end" name="period2_end" value="<?= $period2End ?>">
                    </div>

                    <div class="col-md-6 align-self-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Compare Periods
                        </button>
                    </div>

                    <!-- Quick Period Selectors -->
                    <div class="col-12 mt-3">
                        <div class="btn-group w-100">
                            <a href="?url=dashboard/compareTimePeriods&period1_start=<?= date('Y-m-d', strtotime('-60 days')) ?>&period1_end=<?= date('Y-m-d', strtotime('-31 days')) ?>&period2_start=<?= date('Y-m-d', strtotime('-30 days')) ?>&period2_end=<?= date('Y-m-d') ?>" class="btn btn-outline-primary">
                                Last Month vs Previous Month
                            </a>
                            <a href="?url=dashboard/compareTimePeriods&period1_start=<?= date('Y-m-d', strtotime('-14 days')) ?>&period1_end=<?= date('Y-m-d', strtotime('-8 days')) ?>&period2_start=<?= date('Y-m-d', strtotime('-7 days')) ?>&period2_end=<?= date('Y-m-d') ?>" class="btn btn-outline-primary">
                                Last Week vs Previous Week
                            </a>
                            <a href="?url=dashboard/compareTimePeriods&period1_start=<?= date('Y-m-d', strtotime('-90 days')) ?>&period1_end=<?= date('Y-m-d', strtotime('-31 days')) ?>&period2_start=<?= date('Y-m-d', strtotime('-30 days')) ?>&period2_end=<?= date('Y-m-d') ?>" class="btn btn-outline-primary">
                                Last Month vs Last Quarter
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Comparison Results -->
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">
                    <i class="bi bi-bar-chart-steps"></i>
                    Comparison Results
                </h4>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5 class="mb-0 text-primary">Period 1: <?= formatDisplayDate($period1Start) ?> to <?= formatDisplayDate($period1End) ?></h5>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="mb-0 text-success">Period 2: <?= formatDisplayDate($period2Start) ?> to <?= formatDisplayDate($period2End) ?></h5>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-primary">
                                        <tr>
                                            <th>Metric</th>
                                            <th class="text-center">Period 1</th>
                                            <th class="text-center">Period 2</th>
                                            <th class="text-center">Change</th>
                                            <th class="text-center">% Change</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($comparisonData['changes'] as $metric => $change): ?>
                                            <tr>
                                                <td>
                                                    <strong>
                                                        <?php
                                                        switch ($metric) {
                                                            case 'message_count':
                                                                echo 'Messages';
                                                                break;
                                                            case 'meeting_count':
                                                                echo 'Total Meetings';
                                                                break;
                                                            case 'completed_meeting_count':
                                                                echo 'Completed Meetings';
                                                                break;
                                                            case 'document_count':
                                                                echo 'Documents Uploaded';
                                                                break;
                                                            case 'blog_comment_count':
                                                                echo 'Blog Comments';
                                                                break;
                                                            default:
                                                                echo ucfirst(str_replace('_', ' ', $metric));
                                                        }
                                                        ?>
                                                    </strong>
                                                </td>
                                                <td class="text-center"><?= number_format($change['period1']) ?></td>
                                                <td class="text-center"><?= number_format($change['period2']) ?></td>
                                                <td class="text-center">
                                                    <span class="badge <?= getChangeBadgeClass($change['difference']) ?>">
                                                        <?= $change['difference'] > 0 ? '+' : '' ?><?= number_format($change['difference']) ?>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge <?= getChangeBadgeClass($change['percent_change']) ?>">
                                                        <?= formatPercentChange($change['percent_change']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <canvas id="comparisonChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Prepare data for comparison chart
            const metricLabels = [];
            const period1Data = [];
            const period2Data = [];

            <?php
            $labels = [];
            $period1Values = [];
            $period2Values = [];

            foreach ($comparisonData['changes'] as $metric => $change) {
                // Get readable metric name
                $metricLabel = '';
                switch ($metric) {
                    case 'message_count':
                        $metricLabel = 'Messages';
                        break;
                    case 'meeting_count':
                        $metricLabel = 'Total Meetings';
                        break;
                    case 'completed_meeting_count':
                        $metricLabel = 'Completed Meetings';
                        break;
                    case 'document_count':
                        $metricLabel = 'Documents Uploaded';
                        break;
                    case 'blog_comment_count':
                        $metricLabel = 'Blog Comments';
                        break;
                    default:
                        $metricLabel = ucfirst(str_replace('_', ' ', $metric));
                }

                $labels[] = $metricLabel;
                $period1Values[] = $change['period1'];
                $period2Values[] = $change['period2'];
            }
            ?>

            // Set chart data
            const chartData = {
                labels: <?= json_encode($labels) ?>,
                datasets: [
                    {
                        label: 'Period 1 (<?= formatDisplayDate($period1Start) ?> to <?= formatDisplayDate($period1End) ?>)',
                        data: <?= json_encode($period1Values) ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Period 2 (<?= formatDisplayDate($period2Start) ?> to <?= formatDisplayDate($period2End) ?>)',
                        data: <?= json_encode($period2Values) ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }
                ]
            };

            // Create comparison chart
            const comparisonChart = new Chart(
                document.getElementById('comparisonChart'),
                {
                    type: 'bar',
                    data: chartData,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'Comparison Between Time Periods'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Count'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Metrics'
                                }
                            }
                        }
                    }
                }
            );
        });
    </script>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>