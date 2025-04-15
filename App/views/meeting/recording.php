<?php
$title = "Meeting Recording";
ob_start();
?>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-mic-fill"></i> Meeting Recording
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <h5>Meeting Details</h5>
                            <p>
                                <strong>Date & Time:</strong>
                                <?= date('F j, Y', strtotime($meeting['meeting_date'])) ?>
                                at <?= date('g:i A', strtotime($meeting['meeting_date'])) ?>
                            </p>
                            <p>
                                <strong>Participants:</strong>
                                Student: <?= htmlspecialchars($meeting['student_first_name'] . ' ' . $meeting['student_last_name']) ?>,
                                Tutor: <?= htmlspecialchars($meeting['tutor_first_name'] . ' ' . $meeting['tutor_last_name']) ?>
                            </p>
                        </div>

                        <div class="mb-4">
                            <h5>Recording</h5>
                            <p>
                                <strong>Recorded on:</strong>
                                <?= date('F j, Y g:i A', strtotime($recordingInfo['recording_date'])) ?>
                            </p>
                        </div>

                        <div class="audio-player mb-4">
                            <h5>Play Recording</h5>
                            <audio controls class="w-100">
                                <source src="/eTutoring/public/<?= htmlspecialchars($recordingInfo['audio_recording_path']) ?>" type="audio/mp3">
                                Your browser does not support the audio element.
                            </audio>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="?url=meeting/view&id=<?= $meeting['meeting_id'] ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Back to Meeting
                            </a>

                            <a href="/eTutoring/public/<?= htmlspecialchars($recordingInfo['audio_recording_path']) ?>" class="btn btn-primary" download>
                                <i class="bi bi-download"></i> Download Recording
                            </a>
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