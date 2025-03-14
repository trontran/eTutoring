<?php
$title = "Time-Based Activity Report";
ob_start();

// Helper function to format date for display
function formatDisplayDate($date) {
    return date('M d, Y', strtotime($date));
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
                        <li class="breadcrumb-item active" aria-current="page">Time-Based Activity Report</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-bar-chart-line"></i> Time-Based Activity Report</h2>
                <a href="?url=dashboard/index" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Filter Form -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="bi bi-funnel"></i> Filter Options</h4>
            </div>
            <div class="card-body">
                <form action="?url=dashboard/timeBasedActivity" method="GET" class="row g-3">
                    <input type="hidden" name="url" value="dashboard/timeBasedActivity">

                    <div class="col-md-3">
                        <label for="period" class="form-label">Time Period</label>
                        <select class="form-select" id="period" name="period">
                            <option value="weekly" <?= $period === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                            <option value="monthly" <?= $period === 'monthly' ? 'selected' : '' ?>>Monthly</option>
                            <option value="term" <?= $period === 'term' ? 'selected' : '' ?>>Term</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" value="<?= $startDate ?>">
                    </div>

                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $endDate ?>">
                    </div>

                    <div class="col-md-3 align-self-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Generate Report
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Report Results -->
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">
                    <i class="bi bi-graph-up"></i>
                    Activity Summary (<?= formatDisplayDate($startDate) ?> to <?= formatDisplayDate($endDate) ?>)
                </h4>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Message Activity Over Time</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="messageChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Meeting Activity Over Time</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="meetingChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Activity Data Table</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead class="table-primary">
                                        <tr>
                                            <th>Time Period</th>
                                            <th>Message Count</th>
                                            <th>Meeting Count</th>
                                            <th>Completed Meetings</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        // Create a combined array with both message and meeting data
                                        $combinedData = [];

                                        // Process message data
                                        foreach ($activityData['messages'] as $message) {
                                            $period = $message['time_period'];
                                            if (!isset($combinedData[$period])) {
                                                $combinedData[$period] = [
                                                    'period' => $period,
                                                    'message_count' => 0,
                                                    'meeting_count' => 0,
                                                    'completed_count' => 0
                                                ];
                                            }
                                            $combinedData[$period]['message_count'] = $message['message_count'];
                                        }

                                        // Process meeting data
                                        foreach ($activityData['meetings'] as $meeting) {
                                            $period = $meeting['time_period'];
                                            if (!isset($combinedData[$period])) {
                                                $combinedData[$period] = [
                                                    'period' => $period,
                                                    'message_count' => 0,
                                                    'meeting_count' => 0,
                                                    'completed_count' => 0
                                                ];
                                            }
                                            $combinedData[$period]['meeting_count'] = $meeting['meeting_count'];
                                            $combinedData[$period]['completed_count'] = $meeting['completed_count'];
                                        }

                                        // Sort by period
                                        ksort($combinedData);

                                        // Display table rows
                                        foreach ($combinedData as $data):
                                            $displayPeriod = $data['period'];
                                            // Format display for monthly if needed
                                            if ($period === 'monthly' && strpos($displayPeriod, '-') !== false) {
                                                list($year, $month) = explode('-', $displayPeriod);
                                                $timestamp = mktime(0, 0, 0, $month, 1, $year);
                                                $displayPeriod = date('F Y', $timestamp);
                                            }
                                            ?>
                                            <tr>
                                                <td><?= htmlspecialchars($displayPeriod) ?></td>
                                                <td><?= number_format($data['message_count']) ?></td>
                                                <td><?= number_format($data['meeting_count']) ?></td>
                                                <td><?= number_format($data['completed_count']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Prepare data for message chart
            const messageData = {
                labels: [],
                datasets: [{
                    label: 'Number of Messages',
                    data: [],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.1
                }]
            };

            // Prepare data for meeting chart
            const meetingData = {
                labels: [],
                datasets: [
                    {
                        label: 'Total Meetings',
                        data: [],
                        backgroundColor: 'rgba(255, 159, 64, 0.2)',
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 2,
                        tension: 0.1
                    },
                    {
                        label: 'Completed Meetings',
                        data: [],
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 2,
                        tension: 0.1
                    }
                ]
            };

            <?php
            // Prepare JavaScript arrays for charts
            $periodLabels = [];
            $messageCounts = [];

            // Process message data for charts
            foreach ($activityData['messages'] as $message) {
                $displayPeriod = $message['time_period'];
                // Format display for monthly if needed
                if ($period === 'monthly' && strpos($displayPeriod, '-') !== false) {
                    list($year, $month) = explode('-', $displayPeriod);
                    $timestamp = mktime(0, 0, 0, $month, 1, $year);
                    $displayPeriod = date('F Y', $timestamp);
                }
                $periodLabels[] = $displayPeriod;
                $messageCounts[] = $message['message_count'];
            }

            $meetingLabels = [];
            $meetingCounts = [];
            $completedCounts = [];

            // Process meeting data for charts
            foreach ($activityData['meetings'] as $meeting) {
                $displayPeriod = $meeting['time_period'];
                // Format display for monthly if needed
                if ($period === 'monthly' && strpos($displayPeriod, '-') !== false) {
                    list($year, $month) = explode('-', $displayPeriod);
                    $timestamp = mktime(0, 0, 0, $month, 1, $year);
                    $displayPeriod = date('F Y', $timestamp);
                }
                $meetingLabels[] = $displayPeriod;
                $meetingCounts[] = $meeting['meeting_count'];
                $completedCounts[] = $meeting['completed_count'];
            }
            ?>

            // Populate message chart data
            messageData.labels = <?= json_encode($periodLabels) ?>;
            messageData.datasets[0].data = <?= json_encode($messageCounts) ?>;

            // Populate meeting chart data
            meetingData.labels = <?= json_encode($meetingLabels) ?>;
            meetingData.datasets[0].data = <?= json_encode($meetingCounts) ?>;
            meetingData.datasets[1].data = <?= json_encode($completedCounts) ?>;

            // Create charts
            const messageChart = new Chart(
                document.getElementById('messageChart'),
                {
                    type: 'line',
                    data: messageData,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'Message Count Over Time'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Number of Messages'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: '<?= $activityData['period_type'] ?>'
                                }
                            }
                        }
                    }
                }
            );

            const meetingChart = new Chart(
                document.getElementById('meetingChart'),
                {
                    type: 'bar',
                    data: meetingData,
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'Meeting Activity Over Time'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Number of Meetings'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: '<?= $activityData['period_type'] ?>'
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