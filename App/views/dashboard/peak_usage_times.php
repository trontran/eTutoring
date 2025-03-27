<?php
$title = "Peak Usage Times Report";
ob_start();

// Helper function to format date for display
function formatDisplayDate($date) {
    return date('M d, Y', strtotime($date));
}

// Helper function to convert weekday number to name
function getDayName($dayNum) {
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    return $days[$dayNum] ?? 'Unknown';
}

// Helper function to format hour for display
function formatHour($hour) {
    return date('g:i A', strtotime($hour . ':00'));
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
                        <li class="breadcrumb-item active" aria-current="page">Peak Usage Times</li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col d-flex justify-content-between align-items-center">
                <h2><i class="bi bi-clock"></i> Peak Usage Times Report</h2>
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
                <form action="?url=dashboard/peakUsageTimes" method="GET" class="row g-3">
                    <input type="hidden" name="url" value="dashboard/peakUsageTimes">

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
                    Peak Usage Times (<?= formatDisplayDate($startDate) ?> to <?= formatDisplayDate($endDate) ?>)
                </h4>
            </div>
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Messages by Hour of Day</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="messageHoursChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Meetings by Hour of Day</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="meetingHoursChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Messages by Day of Week</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="messageDaysChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Meetings by Day of Week</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="meetingDaysChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">Peak Usage Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card border-primary mb-3">
                                            <div class="card-header bg-primary text-white">
                                                <h5 class="mb-0">Message Peak Times</h5>
                                            </div>
                                            <div class="card-body">
                                                <?php
                                                // Find peak hour for messages
                                                $peakMessageHour = null;
                                                $peakMessageCount = 0;
                                                if (!empty($peakData['message_hours'])) {
                                                    foreach ($peakData['message_hours'] as $hourData) {
                                                        if ($hourData['message_count'] > $peakMessageCount) {
                                                            $peakMessageHour = $hourData['hour_of_day'];
                                                            $peakMessageCount = $hourData['message_count'];
                                                        }
                                                    }
                                                }

                                                // Find peak day for messages
                                                $peakMessageDay = null;
                                                $peakMessageDayCount = 0;
                                                if (!empty($peakData['message_days'])) {
                                                    foreach ($peakData['message_days'] as $dayData) {
                                                        if ($dayData['message_count'] > $peakMessageDayCount) {
                                                            $peakMessageDay = $dayData['day_of_week'];
                                                            $peakMessageDayCount = $dayData['message_count'];
                                                        }
                                                    }
                                                }
                                                ?>

                                                <p><strong>Peak Hour:</strong>
                                                    <?= $peakMessageHour !== null ? formatHour($peakMessageHour) : 'N/A' ?>
                                                    <?= $peakMessageCount > 0 ? "({$peakMessageCount} messages)" : '' ?>
                                                </p>
                                                <p><strong>Peak Day:</strong>
                                                    <?= $peakMessageDay !== null ? getDayName($peakMessageDay) : 'N/A' ?>
                                                    <?= $peakMessageDayCount > 0 ? "({$peakMessageDayCount} messages)" : '' ?>
                                                </p>
                                                <p class="text-muted">
                                                    These times represent when most messages are sent in the system.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="card border-success mb-3">
                                            <div class="card-header bg-success text-white">
                                                <h5 class="mb-0">Meeting Peak Times</h5>
                                            </div>
                                            <div class="card-body">
                                                <?php
                                                // Find peak hour for meetings
                                                $peakMeetingHour = null;
                                                $peakMeetingCount = 0;
                                                if (!empty($peakData['meeting_hours'])) {
                                                    foreach ($peakData['meeting_hours'] as $hourData) {
                                                        if ($hourData['meeting_count'] > $peakMeetingCount) {
                                                            $peakMeetingHour = $hourData['hour_of_day'];
                                                            $peakMeetingCount = $hourData['meeting_count'];
                                                        }
                                                    }
                                                }

                                                // Find peak day for meetings
                                                $peakMeetingDay = null;
                                                $peakMeetingDayCount = 0;
                                                if (!empty($peakData['meeting_days'])) {
                                                    foreach ($peakData['meeting_days'] as $dayData) {
                                                        if ($dayData['meeting_count'] > $peakMeetingDayCount) {
                                                            $peakMeetingDay = $dayData['day_of_week'];
                                                            $peakMeetingDayCount = $dayData['meeting_count'];
                                                        }
                                                    }
                                                }
                                                ?>

                                                <p><strong>Peak Hour:</strong>
                                                    <?= $peakMeetingHour !== null ? formatHour($peakMeetingHour) : 'N/A' ?>
                                                    <?= $peakMeetingCount > 0 ? "({$peakMeetingCount} meetings)" : '' ?>
                                                </p>
                                                <p><strong>Peak Day:</strong>
                                                    <?= $peakMeetingDay !== null ? getDayName($peakMeetingDay) : 'N/A' ?>
                                                    <?= $peakMeetingDayCount > 0 ? "({$peakMeetingDayCount} meetings)" : '' ?>
                                                </p>
                                                <p class="text-muted">
                                                    These times represent when most meetings are scheduled in the system.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
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
            // Convert PHP arrays to JavaScript
            <?php
             //Prepare data for hour charts
            $hours = range(0, 23);
            $messageHourData = array_fill(0, 24, 0);
            $meetingHourData = array_fill(0, 24, 0);

            if (!empty($peakData['message_hours'])) {
                foreach ($peakData['message_hours'] as $hourData) {
                    $hour = (int)$hourData['hour_of_day'];
                    $messageHourData[$hour] = (int)$hourData['message_count'];
                }
            }

            if (!empty($peakData['meeting_hours'])) {
                foreach ($peakData['meeting_hours'] as $hourData) {
                    $hour = (int)$hourData['hour_of_day'];
                    $meetingHourData[$hour] = (int)$hourData['meeting_count'];
                }
            }

            // Prepare formatted hour labels
            $hourLabels = [];
            foreach ($hours as $hour) {
                $hourLabels[] = date('g A', strtotime($hour . ':00'));
            }

            // Prepare data for day of week charts
            $days = range(0, 6);
            $messageDayData = array_fill(0, 7, 0);
            $meetingDayData = array_fill(0, 7, 0);

            if (!empty($peakData['message_days'])) {
                foreach ($peakData['message_days'] as $dayData) {
                    $day = (int)$dayData['day_of_week'];
                    $messageDayData[$day] = (int)$dayData['message_count'];
                }
            }

            if (!empty($peakData['meeting_days'])) {
                foreach ($peakData['meeting_days'] as $dayData) {
                    $day = (int)$dayData['day_of_week'];
                    $meetingDayData[$day] = (int)$dayData['meeting_count'];
                }
            }

            // Prepare day labels
            $dayLabels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            ?>

            const hourLabels = <?= json_encode($hourLabels) ?>;
            const messageHourData = <?= json_encode($messageHourData) ?>;
            const meetingHourData = <?= json_encode($meetingHourData) ?>;

            const dayLabels = <?= json_encode($dayLabels) ?>;
            const messageDayData = <?= json_encode($messageDayData) ?>;
            const meetingDayData = <?= json_encode($meetingDayData) ?>;

            // Create the message hours chart
            const messageHoursCtx = document.getElementById('messageHoursChart').getContext('2d');
            new Chart(messageHoursCtx, {
                type: 'bar',
                data: {
                    labels: hourLabels,
                    datasets: [{
                        label: 'Number of Messages',
                        data: messageHourData,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgb(54, 162, 235)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Message Distribution by Hour of Day'
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
                                text: 'Hour of Day'
                            }
                        }
                    }
                }
            });

            // Create the meeting hours chart
            const meetingHoursCtx = document.getElementById('meetingHoursChart').getContext('2d');
            new Chart(meetingHoursCtx, {
                type: 'bar',
                data: {
                    labels: hourLabels,
                    datasets: [{
                        label: 'Number of Meetings',
                        data: meetingHourData,
                        backgroundColor: 'rgba(255, 159, 64, 0.5)',
                        borderColor: 'rgb(255, 159, 64)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Meeting Distribution by Hour of Day'
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
                                text: 'Hour of Day'
                            }
                        }
                    }
                }
            });

            // Create the message days chart
            const messageDaysCtx = document.getElementById('messageDaysChart').getContext('2d');
            new Chart(messageDaysCtx, {
                type: 'bar',
                data: {
                    labels: dayLabels,
                    datasets: [{
                        label: 'Number of Messages',
                        data: messageDayData,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgb(75, 192, 192)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Message Distribution by Day of Week'
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
                                text: 'Day of Week'
                            }
                        }
                    }
                }
            });

            // Create the meeting days chart
            const meetingDaysCtx = document.getElementById('meetingDaysChart').getContext('2d');
            new Chart(meetingDaysCtx, {
                type: 'bar',
                data: {
                    labels: dayLabels,
                    datasets: [{
                        label: 'Number of Meetings',
                        data: meetingDayData,
                        backgroundColor: 'rgba(153, 102, 255, 0.5)',
                        borderColor: 'rgb(153, 102, 255)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Meeting Distribution by Day of Week'
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
                                text: 'Day of Week'
                            }
                        }
                    }
                }
            });
        });
    </script>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>