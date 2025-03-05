<?php
$title = "Record Meeting Outcomes";
ob_start();

// Format meeting date
$meetingDate = date('F j, Y \a\t g:i A', strtotime($meeting['meeting_date']));
?>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-journal-check"></i> Record Meeting Outcomes</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['error']; ?>
                                <?php unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <div class="alert alert-info mb-4">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="bi bi-info-circle-fill fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="alert-heading">Meeting Information</h5>
                                    <p class="mb-1"><strong>Date & Time:</strong> <?= $meetingDate ?></p>
                                    <p class="mb-1">
                                        <strong>Meeting Type:</strong>
                                        <?= $meeting['meeting_type'] === 'virtual' ? 'Virtual Meeting' : 'In-Person Meeting' ?>
                                    </p>
                                    <p class="mb-1">
                                        <strong><?= $userRole === 'student' ? 'Tutor' : 'Student' ?>:</strong>
                                        <?php
                                        if ($userRole === 'student') {
                                            echo htmlspecialchars($meeting['tutor_first_name'] . ' ' . $meeting['tutor_last_name']);
                                        } else {
                                            echo htmlspecialchars($meeting['student_first_name'] . ' ' . $meeting['student_last_name']);
                                        }
                                        ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <form action="?url=meeting/saveOutcome" method="POST">
                            <input type="hidden" name="meeting_id" value="<?= $meeting['meeting_id'] ?>">

                            <div class="mb-4">
                                <label for="meeting_outcome" class="form-label fw-bold">Meeting Outcomes</label>
                                <textarea class="form-control" id="meeting_outcome" name="meeting_outcome" rows="8" required
                                          placeholder="Please summarize what was discussed in the meeting, any decisions made, and actions to be taken..."></textarea>
                            </div>

                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                Once you submit this form, the meeting will be marked as completed. This action cannot be undone.
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="?url=meeting/view&id=<?= $meeting['meeting_id'] ?>" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Complete Meeting
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>