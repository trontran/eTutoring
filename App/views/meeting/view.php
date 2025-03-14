<?php
$title = "Meeting Details";
ob_start();

// Xác định các biến quan trọng
$currentTime = time();
$meetingTime = strtotime($meeting['meeting_date']);
$isPastMeeting = ($meetingTime < $currentTime);
$isCompleted = isset($meeting['is_completed']) && $meeting['is_completed'] == 1;

// QUAN TRỌNG: Định nghĩa đúng biến $isTutor
$userRole = $_SESSION['user']['role'] ?? 'unknown';
$isTutor = ($userRole === 'tutor');

// Get status class and icon
$statusClass = 'bg-warning text-dark';
$statusIcon = 'bi-hourglass-split';

if ($meeting['status'] === 'confirmed') {
    $statusClass = 'bg-success';
    $statusIcon = 'bi-check-circle';
} elseif ($meeting['status'] === 'cancelled') {
    $statusClass = 'bg-danger';
    $statusIcon = 'bi-x-circle';
}

// Debug information if requested
if (isset($_GET['debug'])): ?>
    <div class="alert alert-secondary small mt-2 mb-2">
        <strong>DEBUG INFO:</strong><br>
        User ID: <?= $_SESSION['user']['user_id'] ?><br>
        User Role: <?= $userRole ?><br>
        Is Tutor: <?= ($isTutor ? 'Yes' : 'No') ?><br>
        Meeting Status: <?= $meeting['status'] ?><br>
        Meeting Date: <?= date('Y-m-d H:i:s', $meetingTime) ?><br>
        Current Time: <?= date('Y-m-d H:i:s', $currentTime) ?><br>
        Is Past Meeting: <?= ($isPastMeeting ? 'Yes' : 'No') ?><br>
        Is Completed: <?= ($isCompleted ? 'Yes' : 'No') ?>
    </div>
<?php endif; ?>

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

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">
                                <i class="bi bi-calendar-event"></i> Meeting Details
                            </h4>
                            <span class="badge <?= $statusClass ?> fs-6">
                            <i class="bi <?= $statusIcon ?>"></i>
                            <?= $isCompleted ? 'Completed' : ucfirst($meeting['status']) ?>
                        </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5 class="card-title">
                                    <i class="bi bi-clock"></i> Date & Time
                                </h5>
                                <p class="card-text fs-5">
                                    <?= date('F j, Y', strtotime($meeting['meeting_date'])) ?><br>
                                    <small><?= date('g:i A', strtotime($meeting['meeting_date'])) ?></small>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h5 class="card-title">
                                    <i class="bi bi-geo-alt"></i> Meeting Type
                                </h5>
                                <p class="card-text">
                                    <?php if ($meeting['meeting_type'] === 'virtual'): ?>
                                        <span class="badge bg-info">
                                        <i class="bi bi-camera-video"></i> Virtual Meeting
                                    </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                        <i class="bi bi-person"></i> In-Person Meeting
                                    </span>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>

                        <!-- Virtual Meeting Link (Only for virtual meetings) -->
                        <?php if ($meeting['meeting_type'] === 'virtual'): ?>
                            <div class="mb-4">
                                <h5 class="card-title">
                                    <i class="bi bi-link-45deg"></i> Meeting Link
                                </h5>

                                <?php if (!empty($meeting['meeting_link'])): ?>
                                    <div class="input-group">
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($meeting['meeting_link']) ?>" readonly id="meetingLink">
                                        <button class="btn btn-outline-secondary" type="button" onclick="copyMeetingLink()">
                                            <i class="bi bi-clipboard"></i> Copy
                                        </button>
                                        <a href="<?= htmlspecialchars($meeting['meeting_link']) ?>" target="_blank" class="btn btn-primary">
                                            <i class="bi bi-box-arrow-up-right"></i> Join
                                        </a>
                                    </div>
                                    <script>
                                        function copyMeetingLink() {
                                            var linkInput = document.getElementById("meetingLink");
                                            linkInput.select();
                                            linkInput.setSelectionRange(0, 99999);
                                            document.execCommand("copy");
                                            alert("Meeting link copied to clipboard!");
                                        }
                                    </script>
                                <?php else: ?>
                                <?php if ($meeting['status'] === 'confirmed' && !$isCompleted): ?>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="text-muted mb-0">No meeting link has been added yet.</p>

                                        <div class="d-flex">
                                            <!-- Button to add meeting link manually -->
                                            <button type="button" class="btn btn-sm btn-outline-primary me-2" data-bs-toggle="modal" data-bs-target="#addLinkModal">
                                                <i class="bi bi-plus-circle"></i> Add Link
                                            </button>

                                            <!-- Button to generate meeting link automatically -->
<!--                                            <a href="?url=meeting/generateLink&id=--><?php //= $meeting['meeting_id'] ?><!--" class="btn btn-sm btn-primary">-->
<!--                                                <i class="bi bi-magic"></i> Generate Link-->
<!--                                            </a>-->
                                        </div>
                                    </div>

                                    <!-- Modal for adding meeting link -->
                                    <div class="modal fade" id="addLinkModal" tabindex="-1" aria-labelledby="addLinkModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="?url=meeting/addMeetingLink" method="POST">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="addLinkModalLabel">Add Meeting Link</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <input type="hidden" name="meeting_id" value="<?= $meeting['meeting_id'] ?>">
                                                        <div class="mb-3">
                                                            <label for="meeting_link" class="form-label">Meeting Link</label>
                                                            <input type="url" class="form-control" id="meeting_link" name="meeting_link"
                                                                   placeholder="https://meet.google.com/... or https://zoom.us/..." required>
                                                            <div class="form-text">
                                                                Enter the link for your virtual meeting platform (Google Meet, Zoom, Microsoft Teams, etc.)
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Save Link</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No meeting link was provided.</p>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5 class="card-title">
                                    <i class="bi bi-person"></i> Student
                                </h5>
                                <p class="card-text">
                                    <?= htmlspecialchars($meeting['student_first_name'] . ' ' . $meeting['student_last_name']) ?>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h5 class="card-title">
                                    <i class="bi bi-person-badge"></i> Tutor
                                </h5>
                                <p class="card-text">
                                    <?= htmlspecialchars($meeting['tutor_first_name'] . ' ' . $meeting['tutor_last_name']) ?>
                                </p>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h5 class="card-title">
                                <i class="bi bi-journal-text"></i> Meeting Notes
                            </h5>
                            <div class="card">
                                <div class="card-body bg-light">
                                    <?php if (empty($meeting['meeting_notes'])): ?>
                                        <p class="text-muted fst-italic">No meeting notes provided.</p>
                                    <?php else: ?>
                                        <p class="card-text"><?= nl2br(htmlspecialchars($meeting['meeting_notes'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Meeting Outcomes (If completed) -->
                        <?php if ($isCompleted): ?>
                            <div class="mb-4">
                                <h5 class="card-title">
                                    <i class="bi bi-journal-check"></i> Meeting Outcomes
                                </h5>
                                <div class="card">
                                    <div class="card-body bg-light">
                                        <?php if (empty($meeting['meeting_outcome'])): ?>
                                            <p class="text-muted fst-italic">No meeting outcomes recorded.</p>
                                        <?php else: ?>
                                            <p class="card-text"><?= nl2br(htmlspecialchars($meeting['meeting_outcome'])) ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <h5 class="card-title">
                                <i class="bi bi-info-circle"></i> Additional Information
                            </h5>
                            <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Meeting ID</span>
                                    <span class="badge bg-secondary"><?= $meeting['meeting_id'] ?></span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>Created On</span>
                                    <span><?= date('M d, Y H:i', strtotime($meeting['created_at'])) ?></span>
                                </li>
                                <?php if ($isCompleted): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>Completed On</span>
                                        <span><?= date('M d, Y H:i', strtotime($meeting['completed_at'])) ?></span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <?php if ($meeting['status'] === 'pending' && !$isPastMeeting): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                <?php if ($isTutor): ?>
                                    Please confirm or cancel this meeting request.
                                <?php else: ?>
                                    This meeting is waiting for tutor's confirmation.
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between">
                            <a href="?url=meeting/list" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Meetings
                            </a>

                            <div>
                                <?php if ($meeting['status'] === 'confirmed' && !$isCompleted && $isPastMeeting): ?>
                                    <!-- Option to record meeting outcomes -->
                                    <a href="?url=meeting/recordOutcome&id=<?= $meeting['meeting_id'] ?>" class="btn btn-success">
                                        <i class="bi bi-journal-check"></i> Record Outcomes
                                    </a>
                                <?php elseif ($meeting['status'] === 'pending'): ?>
                                    <?php if ($isTutor): ?>
                                        <!-- Tutors can confirm or cancel -->
                                        <form action="?url=meeting/updateStatus" method="POST" class="d-inline">
                                            <input type="hidden" name="meeting_id" value="<?= $meeting['meeting_id'] ?>">
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" class="btn btn-success me-2">
                                                <i class="bi bi-check-circle"></i> Confirm Meeting
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <!-- Both can cancel -->
                                    <form action="?url=meeting/updateStatus" method="POST" class="d-inline">
                                        <input type="hidden" name="meeting_id" value="<?= $meeting['meeting_id'] ?>">
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to cancel this meeting?')">
                                            <i class="bi bi-x-circle"></i> Cancel Meeting
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>