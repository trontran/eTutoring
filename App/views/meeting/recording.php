<?php
$title = "Meeting Recording";
ob_start();

// Function to determine MIME type from file path (basic version)
function get_audio_mime_type($filePath) {
    $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    switch ($extension) {
        case 'webm':
            return 'audio/webm'; // Common type for MediaRecorder
        case 'ogg':
            return 'audio/ogg';  // Another possibility
        case 'mp4': // Less common for audio-only from MediaRecorder, but possible
            return 'audio/mp4';
        case 'mp3':
            return 'audio/mpeg'; // Correct MIME type for MP3
        default:
            return 'audio/webm'; // Default fallback
    }
}

// Assuming $recordingInfo contains the database record with 'audio_recording_path'
$audioPath = $recordingInfo['audio_recording_path'] ?? '';
$mimeType = get_audio_mime_type($audioPath);
// If you saved the MIME type in the database (recommended), use that instead:
// $mimeType = $recordingInfo['audio_mime_type'] ?? get_audio_mime_type($audioPath);

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
                            <?= isset($recordingInfo['recording_date']) ? date('F j, Y g:i A', strtotime($recordingInfo['recording_date'])) : 'N/A' ?>
                        </p>
                    </div>

                    <div class="audio-player mb-4">
                        <h5>Play Recording</h5>
                        <?php if (!empty($audioPath)): ?>
                            <audio controls class="w-100">
                                <source src="/eTutoring/public/<?= htmlspecialchars($audioPath) ?>" type="<?= htmlspecialchars($mimeType) ?>">
                                Your browser does not support the audio element. Please download the file to listen.
                            </audio>
                        <?php else: ?>
                            <div class="alert alert-warning">No recording path found for this meeting.</div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="?url=meeting/view&id=<?= $meeting['meeting_id'] ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Meeting
                        </a>

                        <?php if (!empty($audioPath)): ?>
                            <a href="/eTutoring/public/<?= htmlspecialchars($audioPath) ?>" class="btn btn-primary" download>
                                <i class="bi bi-download"></i> Download Recording
                            </a>
                        <?php endif; ?>
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
