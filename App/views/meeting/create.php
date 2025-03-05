<?php
$title = "Schedule a Meeting";
ob_start();

// Get the current date and time for setting minimum values on form inputs
$now = new DateTime();
$minDate = $now->format('Y-m-d');
?>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><i class="bi bi-calendar-plus"></i> Schedule a Meeting</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?= $_SESSION['error']; ?>
                                <?php unset($_SESSION['error']); ?>
                            </div>
                        <?php endif; ?>

                        <form action="?url=meeting/store" method="POST">
                            <input type="hidden" name="other_party_id" value="<?= $otherParty['user_id']; ?>">

                            <div class="mb-3">
                                <label class="form-label">
                                    <?= $userRole === 'student' ? 'Your Tutor' : 'Student'; ?>
                                </label>
                                <input type="text" class="form-control" value="<?= $otherParty['first_name'] . ' ' . $otherParty['last_name']; ?>" readonly>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="meeting_date" class="form-label">Meeting Date</label>
                                    <input type="date" class="form-control" id="meeting_date" name="meeting_date" min="<?= $minDate; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="meeting_time" class="form-label">Meeting Time</label>
                                    <input type="time" class="form-control" id="meeting_time" name="meeting_time" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="meeting_type" class="form-label">Meeting Type</label>
                                <select class="form-select" id="meeting_type" name="meeting_type" required>
                                    <option value="" selected disabled>Select meeting type</option>
                                    <option value="virtual">Virtual Meeting</option>
                                    <option value="in-person">In-Person Meeting</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="meeting_notes" class="form-label">Meeting Notes (Optional)</label>
                                <textarea class="form-control" id="meeting_notes" name="meeting_notes" rows="3" placeholder="What would you like to discuss in this meeting?"></textarea>
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <?php if ($userRole === 'student'): ?>
                                    Your tutor will need to confirm this meeting request.
                                <?php else: ?>
                                    As a tutor, you'll need to confirm the meeting after it's created.
                                <?php endif; ?>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="?url=meeting/list" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-calendar-check"></i> Schedule Meeting
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Set default time to next hour
        document.addEventListener('DOMContentLoaded', function() {
            const now = new Date();

            // Set default date to today
            const dateInput = document.getElementById('meeting_date');
            dateInput.valueAsDate = now;

            // Set default time to the next full hour
            const timeInput = document.getElementById('meeting_time');
            now.setHours(now.getHours() + 1);
            now.setMinutes(0);
            timeInput.value = now.getHours().toString().padStart(2, '0') + ':00';
        });
    </script>

<?php
$content = ob_get_clean();
include '../app/views/partials/layout.php';
?>