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
                                    <!-- Code hiển thị link meeting ... -->
                                <?php else: ?>
                                    <!-- Code xử lý khi không có link ... -->
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Recorder Section - Đã di chuyển ra ngoài, hiển thị cho tất cả cuộc họp -->
                        <?php
                        // Khởi tạo meeting model nếu chưa có
                        if (!isset($meetingModel)) {
                            require_once '../app/models/Meeting.php';
                            $meetingModel = new \App\Models\Meeting();
                        }
                        ?>

                        <div class="mb-4">
                            <h5 class="card-title">
                                <i class="bi bi-mic-fill"></i> Audio Recording
                            </h5>

                            <?php
                            // Check if there's an existing recording
                            $recordingInfo = $meetingModel->getRecordingInfo($meeting['meeting_id']);
                            $hasRecording = !empty($recordingInfo) && !empty($recordingInfo['audio_recording_path']);
                            ?>

                            <?php if ($hasRecording): ?>
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle"></i> This meeting has been recorded on
                                    <?= date('F j, Y g:i A', strtotime($recordingInfo['recording_date'])) ?>
                                </div>

                                <a href="?url=meeting/viewRecording&id=<?= $meeting['meeting_id'] ?>" class="btn btn-primary">
                                    <i class="bi bi-play-circle"></i> View Recording
                                </a>
                            <?php else: ?>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="alert alert-warning mb-3">
                                            <i class="bi bi-exclamation-triangle"></i>
                                            <strong>Privacy Notice:</strong> By recording, you confirm all participants have been notified and have consented to being recorded.
                                        </div>

                                        <div class="text-center mb-3">
                                            <button id="startRecording" class="btn btn-danger">
                                                <i class="bi bi-record-circle"></i> Start Recording
                                            </button>
                                            <button id="stopRecording" class="btn btn-secondary" disabled>
                                                <i class="bi bi-stop-circle"></i> Stop Recording
                                            </button>
                                        </div>

                                        <div id="recordingStatus" class="text-center mb-3 d-none">
                                            <span class="badge bg-danger">Recording in progress</span>
                                            <div id="recordingTimer" class="mt-2">00:00</div>
                                        </div>

                                        <div id="audioPlayer" class="d-none">
                                            <audio id="recordedAudio" controls class="w-100 mb-3"></audio>
                                            <div class="d-grid">
                                                <button id="saveRecording" class="btn btn-success">
                                                    <i class="bi bi-save"></i> Save Recording
                                                </button>
                                            </div>
                                        </div>

                                        <div id="uploadStatus" class="mt-3 d-none"></div>
                                    </div>
                                </div>

                                <script>
                                    // Place inside the <script> tag at the end of view.php
                                    document.addEventListener('DOMContentLoaded', function() {
                                        // --- Variable Declarations ---
                                        let mediaRecorder;
                                        let audioChunks = [];
                                        let recordingStartTime;
                                        let timerInterval;
                                        let chosenMimeType = ''; // Variable to store the selected MIME type

                                        // --- Get DOM Elements ---
                                        const startButton = document.getElementById('startRecording');
                                        const stopButton = document.getElementById('stopRecording');
                                        const recordingStatus = document.getElementById('recordingStatus');
                                        const recordingTimer = document.getElementById('recordingTimer');
                                        const audioPlayer = document.getElementById('audioPlayer'); // Container for player AND save button
                                        const recordedAudio = document.getElementById('recordedAudio'); // The <audio> tag
                                        const saveButton = document.getElementById('saveRecording');
                                        const uploadStatus = document.getElementById('uploadStatus');

                                        // --- Event Listener: Start Recording ---
                                        startButton.addEventListener('click', async function() {
                                            try {
                                                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });

                                                // Check for supported MIME types (prefer webm)
                                                const typesToCheck = [
                                                    'audio/webm;codecs=opus',
                                                    'audio/webm',
                                                    'audio/ogg;codecs=opus',
                                                    'audio/ogg;codecs=vorbis',
                                                    'audio/ogg'
                                                    // You can add others like 'audio/mp4' if needed
                                                ];
                                                chosenMimeType = ''; // Reset
                                                for (const type of typesToCheck) {
                                                    if (MediaRecorder.isTypeSupported(type)) {
                                                        chosenMimeType = type;
                                                        console.log('Using MIME type:', chosenMimeType);
                                                        break;
                                                    }
                                                }
                                                if (!chosenMimeType) {
                                                    console.warn('Preferred MIME types not supported, using browser default.');
                                                }

                                                // Initialize MediaRecorder with options (if a preferred type was found)
                                                const options = chosenMimeType ? { mimeType: chosenMimeType } : {};
                                                mediaRecorder = new MediaRecorder(stream, options);
                                                audioChunks = []; // Reset chunks for new recording

                                                // Event Listener: Collect audio data
                                                mediaRecorder.addEventListener('dataavailable', event => {
                                                    if (event.data.size > 0) { // Ensure chunk is not empty
                                                        audioChunks.push(event.data);
                                                    }
                                                });

                                                // Event Listener: Handle stop recording
                                                mediaRecorder.addEventListener('stop', () => {
                                                    // Get the actual MIME type used, fallback to the chosen one or webm
                                                    const actualMimeType = mediaRecorder.mimeType || chosenMimeType || 'audio/webm';
                                                    console.log('Actual MIME type on stop:', actualMimeType);

                                                    // Check if any audio data was actually recorded
                                                    if (audioChunks.length === 0) {
                                                        console.error("No audio data was recorded.");
                                                        alert("Recording failed, no audio data captured.");
                                                        // Reset button states
                                                        startButton.disabled = false;
                                                        stopButton.disabled = true;
                                                        if(recordingStatus) recordingStatus.classList.add('d-none');
                                                        return; // Stop processing if no data
                                                    }

                                                    // Create the Blob object
                                                    const audioBlob = new Blob(audioChunks, { type: actualMimeType });
                                                    const audioUrl = URL.createObjectURL(audioBlob); // Create temporary URL

                                                    console.log('Audio Blob created:', audioBlob);
                                                    console.log('Object URL created:', audioUrl);

                                                    // Update the audio player for preview
                                                    if(recordedAudio) { // Check if the audio element exists
                                                        recordedAudio.src = audioUrl; // Assign the new source

                                                        // *** IMPORTANT STEP: Call load() to refresh the source ***
                                                        recordedAudio.load();

                                                        // Ensure the audio tag itself is visible (if previously hidden)
                                                        recordedAudio.style.display = ''; // Or 'block', 'inline-block' depending on layout

                                                        // (Optional) Try to play automatically after loading, but controls are generally better
                                                        // recordedAudio.play().catch(e => console.error("Autoplay error:", e));

                                                    } else {
                                                        console.error("Could not find the audio element with id='recordedAudio'");
                                                    }

                                                    // Show the container div (which includes the save button)
                                                    if(audioPlayer) { // Check if the container element exists
                                                        audioPlayer.classList.remove('d-none');
                                                    } else {
                                                        console.error("Could not find the div with id='audioPlayer'");
                                                    }

                                                    // Stop the timer
                                                    clearInterval(timerInterval);
                                                });

                                                // Event Listener: Handle errors during recording
                                                mediaRecorder.onerror = (event) => {
                                                    console.error('MediaRecorder error:', event.error);
                                                    alert('An error occurred during recording: ' + event.error.name);
                                                    // Reset button states
                                                    startButton.disabled = false;
                                                    stopButton.disabled = true;
                                                    if(recordingStatus) recordingStatus.classList.add('d-none');
                                                    clearInterval(timerInterval); // Stop timer on error
                                                };

                                                // Start recording
                                                mediaRecorder.start();
                                                recordingStartTime = Date.now();
                                                startRecordingTimer(); // Start the visual timer

                                                // Update UI state
                                                startButton.disabled = true;
                                                stopButton.disabled = false;
                                                if(recordingStatus) recordingStatus.classList.remove('d-none');
                                                if(audioPlayer) audioPlayer.classList.add('d-none'); // Hide player container initially
                                                if(uploadStatus) uploadStatus.classList.add('d-none');


                                            } catch (err) {
                                                // Handle errors getting user media or starting recorder
                                                alert('Error accessing microphone or starting recording: ' + err.message);
                                                console.error('getUserMedia or new MediaRecorder error:', err);
                                                // Reset button states
                                                startButton.disabled = false;
                                                stopButton.disabled = true;
                                            }
                                        });

                                        // --- Event Listener: Stop Recording Button ---
                                        stopButton.addEventListener('click', function() {
                                            if (mediaRecorder && mediaRecorder.state === 'recording') { // Only stop if currently recording
                                                mediaRecorder.stop();
                                                // Stop media tracks after the 'stop' event has processed the Blob
                                                // mediaRecorder.stream.getTracks().forEach(track => track.stop()); // Can be moved to 'stop' listener if needed
                                            } else {
                                                console.warn("MediaRecorder is not in recording state.");
                                            }
                                            // Update button states immediately
                                            startButton.disabled = false;
                                            stopButton.disabled = true;
                                            if(recordingStatus) recordingStatus.classList.add('d-none'); // Hide "Recording in progress"
                                        });

                                        // --- Event Listener: Save Recording Button ---
                                        saveButton.addEventListener('click', function() {
                                            // Check if there's a valid Blob source to save
                                            if (!recordedAudio || !recordedAudio.src || !recordedAudio.src.startsWith('blob:')) {
                                                alert('There is no recording to save.');
                                                console.error('No valid Blob source found on recordedAudio.');
                                                return;
                                            }
                                            // Also check if audioChunks has data, as src might exist but data is empty
                                            if (audioChunks.length === 0) {
                                                alert('Recorded data is empty, cannot save.');
                                                return;
                                            }

                                            // Determine MIME type and extension for saving
                                            const actualMimeType = mediaRecorder.mimeType || chosenMimeType || 'audio/webm';
                                            const fileExtension = actualMimeType.includes('ogg') ? 'ogg' : 'webm'; // Determine file extension

                                            // Recreate Blob from original chunks to ensure latest data
                                            const audioBlobToSave = new Blob(audioChunks, { type: actualMimeType });

                                            // Prepare FormData for upload
                                            const formData = new FormData();
                                            formData.append('audio_data', audioBlobToSave, `recording.${fileExtension}`); // Use correct extension
                                            formData.append('meeting_id', '<?= $meeting['meeting_id'] ?>');
                                            formData.append('mime_type', actualMimeType); // Send the actual MIME type

                                            // Update UI for upload status
                                            if(uploadStatus) {
                                                uploadStatus.innerHTML = '<div class="alert alert-info">Uploading recording...</div>';
                                                uploadStatus.classList.remove('d-none');
                                            }

                                            // Send data to server
                                            fetch('?url=meeting/uploadRecording', {
                                                method: 'POST',
                                                body: formData
                                            })
                                                .then(response => {
                                                    if (!response.ok) {
                                                        // Throw an error for bad HTTP status codes (e.g., 500)
                                                        throw new Error(`HTTP error: ${response.status}`);
                                                    }
                                                    return response.json(); // Parse JSON response
                                                })
                                                .then(data => {
                                                    // Handle server response
                                                    if (data.status === 'success') {
                                                        if(uploadStatus) uploadStatus.innerHTML = '<div class="alert alert-success">Recording saved successfully! Reloading page...</div>';
                                                        // Revoke the old Object URL before reloading
                                                        if (recordedAudio && recordedAudio.src.startsWith('blob:')) {
                                                            URL.revokeObjectURL(recordedAudio.src);
                                                        }
                                                        // Reload the page after a short delay
                                                        setTimeout(() => {
                                                            window.location.reload();
                                                        }, 2000);
                                                    } else {
                                                        // Show specific error from server
                                                        if(uploadStatus) uploadStatus.innerHTML = `<div class="alert alert-danger">Error saving: ${data.message}</div>`;
                                                    }
                                                })
                                                .catch(error => {
                                                    // Handle fetch errors (network issues, server errors)
                                                    console.error('Error fetching during save:', error);
                                                    if(uploadStatus) uploadStatus.innerHTML = `<div class="alert alert-danger">Upload failed. Error: ${error.message}. Please try again.</div>`;
                                                });
                                        });

                                        // --- Function: Start Recording Timer ---
                                        function startRecordingTimer() {
                                            // (Keep the timer function as is)
                                            timerInterval = setInterval(() => {
                                                const elapsedTime = Date.now() - recordingStartTime;
                                                const minutes = Math.floor(elapsedTime / 60000);
                                                const seconds = Math.floor((elapsedTime % 60000) / 1000);
                                                if(recordingTimer) recordingTimer.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                                            }, 1000);
                                        }
                                    });

                                </script>
                            <?php endif; ?>
                        </div>

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