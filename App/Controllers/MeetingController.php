<?php

use App\Core\Controller;
use App\Models\Meeting;
use App\Models\User;
use App\Models\PersonalTutor;
use App\Models\Notification;
use JetBrains\PhpStorm\NoReturn;

require_once __DIR__ . '/../Helpers/MailHelper.php';

class MeetingController extends Controller
{
    private $meetingModel;
    private $userModel;
    private $personalTutorModel;
    private $notificationModel;

    public function __construct()
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Initialize models
        $this->meetingModel = new Meeting();
        $this->userModel = new User();
        $this->personalTutorModel = new PersonalTutor();
        $this->notificationModel = new Notification();
    }

    /**
     * Display the meeting creation form
     */
    public function create()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        $userId = $_SESSION['user']['user_id'];
        $userRole = $_SESSION['user']['role'];
        $otherParty = null;

        // If student, get their tutor
        if ($userRole === 'student') {
            $tutor = $this->personalTutorModel->getTutorDetails($userId);
            $otherParty = $tutor;
            $otherPartyRole = 'tutor';
        }
        // If tutor, get specified student or show student selection
        elseif ($userRole === 'tutor') {
            $studentId = $_GET['student_id'] ?? null;

            if ($studentId) {
                $student = $this->userModel->getUserById($studentId);
                $otherParty = $student;
                $otherPartyRole = 'student';
            } else {
                // Get all tutees for this tutor
                $tutees = $this->userModel->getTuteesByTutor($userId);
                $this->view('meeting/select_student', ['tutees' => $tutees]);
                return;
            }
        } else {
            // Staff cannot schedule meetings
            $_SESSION['error'] = "Only students and tutors can schedule meetings.";
            header("Location: ?url=home/index");
            exit;
        }

        // Check if we have the necessary parties for the meeting
        if (!$otherParty) {
            $_SESSION['error'] = "Cannot schedule a meeting at this time. Please ensure you have a tutor assigned.";
            header("Location: ?url=home/index");
            exit;
        }

        // Pass data to the view
        $data = [
            'userRole' => $userRole,
            'otherParty' => $otherParty,
            'otherPartyRole' => $otherPartyRole
        ];

        $this->view('meeting/create', $data);
    }

    /**
     * Process and store the meeting data
     */
    public function store()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?url=meeting/create");
            exit;
        }

        $userId = $_SESSION['user']['user_id'];
        $userRole = $_SESSION['user']['role'];

        // Get form data
        $otherPartyId = $_POST['other_party_id'] ?? null;
        $meetingDate = $_POST['meeting_date'] ?? null;
        $meetingTime = $_POST['meeting_time'] ?? null;
        $meetingType = $_POST['meeting_type'] ?? null;
        $meetingNotes = $_POST['meeting_notes'] ?? null;

        // Validate input
        if (!$otherPartyId || !$meetingDate || !$meetingTime || !$meetingType) {
            $_SESSION['error'] = "All required fields must be filled out.";
            header("Location: ?url=meeting/create");
            exit;
        }

        // Combine date and time
        $meetingDateTime = $meetingDate . ' ' . $meetingTime;

        // Set student and tutor IDs based on user role
        $studentId = ($userRole === 'student') ? $userId : $otherPartyId;
        $tutorId = ($userRole === 'tutor') ? $userId : $otherPartyId;

        // Check if the time slot is available
        if (!$this->meetingModel->isTimeSlotAvailable($tutorId, $meetingDateTime)) {
            $_SESSION['error'] = "The selected time slot is not available. Please choose another time.";
            header("Location: ?url=meeting/create");
            exit;
        }

        // Prepare meeting data
        $meetingData = [
            'student_id' => $studentId,
            'tutor_id' => $tutorId,
            'meeting_date' => $meetingDateTime,
            'meeting_type' => $meetingType,
            'meeting_notes' => $meetingNotes,
            'status' => ($userRole === 'tutor') ? 'confirmed' : 'pending'
        ];

        // Create the meeting
        $meetingId = $this->meetingModel->createMeeting($meetingData);

        if ($meetingId) {
            // If meeting is already confirmed (created by tutor), try to schedule reminders
            if ($meetingData['status'] === 'confirmed') {
                try {
                    $this->scheduleReminders(
                        $meetingId,
                        $studentId,
                        $tutorId,
                        $meetingDateTime
                    );
                } catch (\Exception $e) {
                    // Log error but continue processing
                    error_log("Error scheduling reminders: " . $e->getMessage());
                }
            }

            // Create notification for the other party
            $notificationReceiverId = ($userRole === 'student') ? $tutorId : $studentId;
            $senderName = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];

            if ($userRole === 'student') {
                $notificationText = "$senderName has requested a meeting on " . date('F j, Y \a\t g:i A', strtotime($meetingDateTime));
            } else {
                $notificationText = "$senderName has scheduled a meeting with you on " . date('F j, Y \a\t g:i A', strtotime($meetingDateTime));
            }

            $this->notificationModel->createNotification($notificationReceiverId, $notificationText);

            // Send email notification
            $meeting = $this->meetingModel->getMeetingById($meetingId);
            $this->sendMeetingEmail($meeting, $notificationReceiverId, 'created');

            $_SESSION['success'] = "Meeting has been " . ($userRole === 'tutor' ? "scheduled" : "requested") . " successfully.";
            header("Location: ?url=meeting/list");
        } else {
            $_SESSION['error'] = "Failed to create meeting. Please try again.";
            header("Location: ?url=meeting/create");
        }
        exit;
    }

    /**
     * Display a list of meetings for the user
     */
    public function list()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        $userId = $_SESSION['user']['user_id'];
        $userRole = $_SESSION['user']['role'];

        // Get upcoming meetings
        $upcomingMeetings = $this->meetingModel->getUpcomingMeetings($userId, $userRole);

        // Get past meetings
        $pastMeetings = [];
        if ($userRole === 'student') {
            $allMeetings = $this->meetingModel->getMeetingsByStudentId($userId);
        } else { // tutor
            $allMeetings = $this->meetingModel->getMeetingsByTutorId($userId);
        }

        // Filter past meetings
        foreach ($allMeetings as $meeting) {
            if (strtotime($meeting['meeting_date']) < time()) {
                $pastMeetings[] = $meeting;
            }
        }

        // Pass data to the view
        $data = [
            'userRole' => $userRole,
            'upcomingMeetings' => $upcomingMeetings,
            'pastMeetings' => $pastMeetings
        ];

        $this->view('meeting/list', $data);
    }

    /**
     * Show details of a specific meeting
     */
    /**
     * Show details of a specific meeting
     */
    public function viewDetails()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        $meetingId = $_GET['id'] ?? null;

        if (!$meetingId) {
            header("Location: ?url=meeting/list");
            exit;
        }

        $meeting = $this->meetingModel->getMeetingById($meetingId);

        if (!$meeting) {
            $_SESSION['error'] = "Meeting not found.";
            header("Location: ?url=meeting/list");
            exit;
        }

        // Check if user is part of this meeting
        $userId = $_SESSION['user']['user_id'];
        if ($meeting['student_id'] != $userId && $meeting['tutor_id'] != $userId) {
            $_SESSION['error'] = "You don't have permission to view this meeting.";
            header("Location: ?url=meeting/list");
            exit;
        }

        $data = [
            'meeting' => $meeting,
            'userRole' => $_SESSION['user']['role']
        ];

        $this->view('meeting/view', $data);
    }

    /**
     * Update meeting status (confirm or cancel)
     */
    public function updateStatus()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?url=meeting/list");
            exit;
        }

        $meetingId = $_POST['meeting_id'] ?? null;
        $newStatus = $_POST['status'] ?? null;

        if (!$meetingId || !in_array($newStatus, ['confirmed', 'cancelled'])) {
            $_SESSION['error'] = "Invalid request.";
            header("Location: ?url=meeting/list");
            exit;
        }

        $meeting = $this->meetingModel->getMeetingById($meetingId);

        if (!$meeting) {
            $_SESSION['error'] = "Meeting not found.";
            header("Location: ?url=meeting/list");
            exit;
        }

        // Check if user is part of this meeting
        $userId = $_SESSION['user']['user_id'];
        if ($meeting['student_id'] != $userId && $meeting['tutor_id'] != $userId) {
            $_SESSION['error'] = "You don't have permission to update this meeting.";
            header("Location: ?url=meeting/list");
            exit;
        }

        // Only tutors can confirm meetings
        if ($newStatus === 'confirmed' && $_SESSION['user']['role'] !== 'tutor') {
            $_SESSION['error'] = "Only tutors can confirm meetings.";
            header("Location: ?url=meeting/view&id=" . $meetingId);
            exit;
        }

        // Update meeting status
        if ($this->meetingModel->updateMeetingStatus($meetingId, $newStatus)) {
            // If meeting is being confirmed, try to schedule reminders
            if ($newStatus === 'confirmed') {
                try {
                    $this->updateReminders($meetingId);
                } catch (\Exception $e) {
                    // Log error but continue processing
                    error_log("Error updating reminders: " . $e->getMessage());
                }
            }

            // Create notification for the other party
            $notificationReceiverId = ($meeting['student_id'] == $userId) ? $meeting['tutor_id'] : $meeting['student_id'];
            $senderName = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];

            if ($newStatus === 'confirmed') {
                $notificationText = "$senderName has confirmed your meeting scheduled for " . date('F j, Y \a\t g:i A', strtotime($meeting['meeting_date']));
            } else {
                $notificationText = "$senderName has cancelled the meeting scheduled for " . date('F j, Y \a\t g:i A', strtotime($meeting['meeting_date']));
            }

            $this->notificationModel->createNotification($notificationReceiverId, $notificationText);

            // Send email notification
            $updatedMeeting = $this->meetingModel->getMeetingById($meetingId);
            $this->sendMeetingEmail($updatedMeeting, $notificationReceiverId, $newStatus);

            $_SESSION['success'] = "Meeting has been " . ($newStatus === 'confirmed' ? 'confirmed' : 'cancelled') . " successfully.";
        } else {
            $_SESSION['error'] = "Failed to update meeting status. Please try again.";
        }

        header("Location: ?url=meeting/view&id=" . $meetingId);
        exit;
    }

    /**
     * Process and schedule reminders for a meeting
     *
     * @param int $meetingId Meeting ID
     * @param int $studentId Student ID
     * @param int $tutorId Tutor ID
     * @param string $meetingDate Meeting date and time
     */
    private function scheduleReminders($meetingId, $studentId, $tutorId, $meetingDate)
    {
        try {
            require_once '../app/models/MeetingReminder.php';
            $reminderModel = new \App\Models\MeetingReminder();

            // Schedule reminders for the meeting
            $reminderModel->scheduleReminders($meetingId, $studentId, $tutorId, $meetingDate);
            return true;
        } catch (\Exception $e) {
            // Log error and continue processing
            error_log("Error scheduling reminders: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update or reschedule reminders when a meeting is updated
     *
     * @param int $meetingId Meeting ID
     */
    private function updateReminders($meetingId)
    {
        try {
            // Get meeting details
            $meeting = $this->meetingModel->getMeetingById($meetingId);

            if (!$meeting) {
                return false;
            }

            require_once '../app/models/MeetingReminder.php';
            $reminderModel = new \App\Models\MeetingReminder();

            // Delete existing reminders for this meeting
            $reminderModel->deleteRemindersByMeetingId($meetingId);

            // If meeting is confirmed, schedule new reminders
            if ($meeting['status'] === 'confirmed') {
                $this->scheduleReminders(
                    $meetingId,
                    $meeting['student_id'],
                    $meeting['tutor_id'],
                    $meeting['meeting_date']
                );
            }

            return true;
        } catch (\Exception $e) {
            // Log error and continue processing
            error_log("Error updating reminders: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Show form to record meeting outcomes
     */
    public function recordOutcome()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        $meetingId = $_GET['id'] ?? null;

        if (!$meetingId) {
            header("Location: ?url=meeting/list");
            exit;
        }

        $meeting = $this->meetingModel->getMeetingById($meetingId);

        if (!$meeting) {
            $_SESSION['error'] = "Meeting not found.";
            header("Location: ?url=meeting/list");
            exit;
        }

        // Check if user is part of this meeting
        $userId = $_SESSION['user']['user_id'];
        if ($meeting['student_id'] != $userId && $meeting['tutor_id'] != $userId) {
            $_SESSION['error'] = "You don't have permission to update this meeting.";
            header("Location: ?url=meeting/list");
            exit;
        }

        // Check if meeting can be marked as completed
        // Allow for both students and tutors, but meeting must be confirmed and past
        if ($meeting['status'] !== 'confirmed') {
            $_SESSION['error'] = "Only confirmed meetings can be marked as completed.";
            header("Location: ?url=meeting/view&id=" . $meetingId);
            exit;
        }

        // Check if meeting is in the past
        $isPastMeeting = strtotime($meeting['meeting_date']) < time();
        if (!$isPastMeeting) {
            $_SESSION['error'] = "You can only record outcomes after the meeting has taken place.";
            header("Location: ?url=meeting/view&id=" . $meetingId);
            exit;
        }

        // Check if already completed
        $isCompleted = isset($meeting['is_completed']) && $meeting['is_completed'] == 1;
        if ($isCompleted) {
            $_SESSION['error'] = "This meeting has already been marked as completed.";
            header("Location: ?url=meeting/view&id=" . $meetingId);
            exit;
        }

        $data = [
            'meeting' => $meeting,
            'userRole' => $_SESSION['user']['role'],
            'userId' => $userId
        ];

        $this->view('meeting/record_outcome', $data);
    }

    /**
     * Save meeting outcomes
     */
    public function saveOutcome()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?url=meeting/list");
            exit;
        }

        $meetingId = $_POST['meeting_id'] ?? null;
        $meetingOutcome = $_POST['meeting_outcome'] ?? null;

        if (!$meetingId || !$meetingOutcome) {
            $_SESSION['error'] = "Meeting outcome cannot be empty.";
            header("Location: ?url=meeting/recordOutcome&id=" . $meetingId);
            exit;
        }

        $meeting = $this->meetingModel->getMeetingById($meetingId);

        if (!$meeting) {
            $_SESSION['error'] = "Meeting not found.";
            header("Location: ?url=meeting/list");
            exit;
        }

        // Check if user is part of this meeting
        $userId = $_SESSION['user']['user_id'];
        if ($meeting['student_id'] != $userId && $meeting['tutor_id'] != $userId) {
            $_SESSION['error'] = "You don't have permission to update this meeting.";
            header("Location: ?url=meeting/list");
            exit;
        }

        // Save meeting outcome
        if ($this->meetingModel->completeMeeting($meetingId, $meetingOutcome)) {
            // Create notification for the other party
            $notificationReceiverId = ($meeting['student_id'] == $userId) ? $meeting['tutor_id'] : $meeting['student_id'];
            $senderName = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
            $senderRole = $_SESSION['user']['role'];

            // Create role-specific notification text
            if ($senderRole === 'student') {
                $notificationText = "Student $senderName has recorded outcomes for your meeting on " . date('F j, Y \a\t g:i A', strtotime($meeting['meeting_date']));
            } else {
                $notificationText = "Tutor $senderName has recorded outcomes for your meeting on " . date('F j, Y \a\t g:i A', strtotime($meeting['meeting_date']));
            }

            $this->notificationModel->createNotification($notificationReceiverId, $notificationText);

            // Send email notification with meeting outcome
            $updatedMeeting = $this->meetingModel->getMeetingById($meetingId);
            $additionalInfo = "The following outcomes were recorded:<br><blockquote>" . nl2br(htmlspecialchars($meetingOutcome)) . "</blockquote>";
            $this->sendMeetingEmail($updatedMeeting, $notificationReceiverId, 'completed', $additionalInfo);

            $_SESSION['success'] = "Meeting has been marked as completed and outcomes have been recorded.";
            header("Location: ?url=meeting/view&id=" . $meetingId);
        } else {
            $_SESSION['error'] = "Failed to record meeting outcomes. Please try again.";
            header("Location: ?url=meeting/recordOutcome&id=" . $meetingId);
        }
        exit;
    }

    /**
     * Add meeting link for virtual meetings
     */
    public function addMeetingLink()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?url=meeting/list");
            exit;
        }

        $meetingId = $_POST['meeting_id'] ?? null;
        $meetingLink = $_POST['meeting_link'] ?? null;

        if (!$meetingId || !$meetingLink) {
            $_SESSION['error'] = "Missing required information.";
            header("Location: ?url=meeting/view&id=" . $meetingId);
            exit;
        }

        $meeting = $this->meetingModel->getMeetingById($meetingId);

        if (!$meeting) {
            $_SESSION['error'] = "Meeting not found.";
            header("Location: ?url=meeting/list");
            exit;
        }

        // Check if user is part of this meeting
        $userId = $_SESSION['user']['user_id'];
        if ($meeting['student_id'] != $userId && $meeting['tutor_id'] != $userId) {
            $_SESSION['error'] = "You don't have permission to update this meeting.";
            header("Location: ?url=meeting/list");
            exit;
        }

        // Update meeting link
        if ($this->meetingModel->updateMeetingLink($meetingId, $meetingLink)) {
            // Notify the other party
            $notificationReceiverId = ($meeting['student_id'] == $userId) ? $meeting['tutor_id'] : $meeting['student_id'];
            $senderName = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];

            $notificationText = "$senderName has added a link to your meeting scheduled for " . date('F j, Y \a\t g:i A', strtotime($meeting['meeting_date']));
            $this->notificationModel->createNotification($notificationReceiverId, $notificationText);

            // Send email notification with the meeting link
            $updatedMeeting = $this->meetingModel->getMeetingById($meetingId);
            $additionalInfo = "A meeting link has been added. You can join the meeting using this link:<br><a href='{$meetingLink}'>{$meetingLink}</a>";
            $action = $meeting['status'] === 'confirmed' ? 'confirmed' : 'created';
            $this->sendMeetingEmail($updatedMeeting, $notificationReceiverId, $action, $additionalInfo);

            $_SESSION['success'] = "Meeting link has been added successfully.";
        } else {
            $_SESSION['error'] = "Failed to add meeting link. Please try again.";
        }

        header("Location: ?url=meeting/view&id=" . $meetingId);
        exit;
    }

    /**
     * Generate a meeting link automatically
     */
    public function generateLink()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        $meetingId = $_GET['id'] ?? null;

        if (!$meetingId) {
            $_SESSION['error'] = "Meeting ID is required.";
            header("Location: ?url=meeting/list");
            exit;
        }

        $meeting = $this->meetingModel->getMeetingById($meetingId);

        if (!$meeting) {
            $_SESSION['error'] = "Meeting not found.";
            header("Location: ?url=meeting/list");
            exit;
        }

        // Check if user is part of this meeting
        $userId = $_SESSION['user']['user_id'];
        if ($meeting['student_id'] != $userId && $meeting['tutor_id'] != $userId) {
            $_SESSION['error'] = "You don't have permission to update this meeting.";
            header("Location: ?url=meeting/list");
            exit;
        }

        // Only allow generating link for virtual meetings
        if ($meeting['meeting_type'] !== 'virtual') {
            $_SESSION['error'] = "Can only generate links for virtual meetings.";
            header("Location: ?url=meeting/view&id=" . $meetingId);
            exit;
        }

        // Generate meeting link
        $meetingLink = $this->meetingModel->generateMeetingLink($meetingId);

        // Update meeting with the generated link
        if ($this->meetingModel->updateMeetingLink($meetingId, $meetingLink)) {
            $_SESSION['success'] = "Meeting link has been generated successfully.";
        } else {
            $_SESSION['error'] = "Failed to generate meeting link. Please try again.";
        }

        header("Location: ?url=meeting/view&id=" . $meetingId);
        exit;
    }

    /**
     * View completed meetings
     */
    public function completed()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        $userId = $_SESSION['user']['user_id'];
        $userRole = $_SESSION['user']['role'];

        // Get completed meetings
        $completedMeetings = $this->meetingModel->getCompletedMeetings($userId, $userRole);

        $data = [
            'userRole' => $userRole,
            'completedMeetings' => $completedMeetings
        ];

        $this->view('meeting/completed', $data);
    }

    /**
     * Send email notification about a meeting
     *
     * @param array $meeting Meeting data
     * @param int $receiverId User ID of the recipient
     * @param string $action The action performed (created, confirmed, cancelled, completed)
     * @param string $additionalInfo Optional additional information for the email
     */
    private function sendMeetingEmail($meeting, $receiverId, $action, $additionalInfo = '')
    {
        // Get recipient details
        $recipient = $this->userModel->getUserById($receiverId);

        if (!$recipient) {
            return false;
        }

        // Get sender details (current user)
        $sender = [
            'name' => $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'],
            'role' => $_SESSION['user']['role']
        ];

        // Format meeting date and time
        $meetingDateTime = date('F j, Y \a\t g:i A', strtotime($meeting['meeting_date']));

        // Set email subject based on action
        switch ($action) {
            case 'created':
                $subject = "New Meeting Request - eTutoring System";
                break;
            case 'confirmed':
                $subject = "Meeting Confirmed - eTutoring System";
                break;
            case 'cancelled':
                $subject = "Meeting Cancelled - eTutoring System";
                break;
            case 'completed':
                $subject = "Meeting Completed - eTutoring System";
                break;
            default:
                $subject = "Meeting Update - eTutoring System";
        }

        // Build email body
        $body = "<p>Dear {$recipient['first_name']},</p>";

        // Add specific message based on action
        switch ($action) {
            case 'created':
                if ($sender['role'] === 'student') {
                    $body .= "<p>Your student {$sender['name']} has requested a new meeting with you.</p>";
                } else {
                    $body .= "<p>Your tutor {$sender['name']} has scheduled a new meeting with you.</p>";
                }
                break;
            case 'confirmed':
                $body .= "<p>A meeting has been confirmed by {$sender['name']}.</p>";
                break;
            case 'cancelled':
                $body .= "<p>A meeting has been cancelled by {$sender['name']}.</p>";
                break;
            case 'completed':
                $body .= "<p>A meeting has been marked as completed by {$sender['name']}.</p>";
                break;
        }

        // Add meeting details
        $body .= "
    <p><strong>Meeting Details:</strong></p>
    <ul>
        <li><strong>Date & Time:</strong> {$meetingDateTime}</li>
        <li><strong>Meeting Type:</strong> " . ucfirst($meeting['meeting_type']) . " Meeting</li>
    </ul>";

        // Add additional information if provided
        if (!empty($additionalInfo)) {
            $body .= "<p>{$additionalInfo}</p>";
        }

        // Add meeting notes if available
        if (!empty($meeting['meeting_notes'])) {
            $body .= "<p><strong>Meeting Notes:</strong><br>" . nl2br(htmlspecialchars($meeting['meeting_notes'])) . "</p>";
        }

        // Add virtual meeting link if applicable
        if ($meeting['meeting_type'] === 'virtual' && !empty($meeting['meeting_link'])) {
            $body .= "<p><strong>Meeting Link:</strong> <a href='{$meeting['meeting_link']}'>{$meeting['meeting_link']}</a></p>";
        }

        // Add closing
        $body .= "
    <p>You can view the full meeting details and updates in your eTutoring dashboard.</p>
    <p>Best regards,</p>
    <p><strong>eTutoring Team</strong></p>
    <hr>
    <p style='font-size:12px; color:gray;'>This is an automated message, please do not reply to this email.</p>";

        // Send the email
        return MailHelper::sendMail($recipient['email'], $subject, $body);
    }

    //this is for testing recording purposes

    /**
     * Handle audio recording upload
     */
    public function uploadRecording()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
            exit;
        }

        // Check if request has file
        if (!isset($_FILES['audio_data']) || $_FILES['audio_data']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['status' => 'error', 'message' => 'No audio file uploaded']);
            exit;
        }

        $meetingId = $_POST['meeting_id'] ?? null;

        if (!$meetingId) {
            echo json_encode(['status' => 'error', 'message' => 'Meeting ID is required']);
            exit;
        }

        $meeting = $this->meetingModel->getMeetingById($meetingId);

        if (!$meeting) {
            echo json_encode(['status' => 'error', 'message' => 'Meeting not found']);
            exit;
        }

        // Check if user is part of this meeting
        $userId = $_SESSION['user']['user_id'];
        if ($meeting['student_id'] != $userId && $meeting['tutor_id'] != $userId) {
            echo json_encode(['status' => 'error', 'message' => 'You do not have permission']);
            exit;
        }

        // Create unique filename
        $fileName = 'meeting_' . $meetingId . '_' . date('Ymd_His') . '.mp3';
        $uploadDir = __DIR__ . '/../../public/recordings/';
        $uploadPath = $uploadDir . $fileName;

        // Move uploaded file
        if (move_uploaded_file($_FILES['audio_data']['tmp_name'], $uploadPath)) {
            // Save recording info in database
            $recordingPath = 'recordings/' . $fileName;
            if ($this->meetingModel->saveRecordingInfo($meetingId, $recordingPath)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Recording saved successfully',
                    'file_path' => $recordingPath
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to save recording info']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to save recording file']);
        }
        exit;
    }

    /**
     * View recording of a meeting
     */
    public function viewRecording()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        $meetingId = $_GET['id'] ?? null;

        if (!$meetingId) {
            $_SESSION['error'] = "Meeting ID is required.";
            header("Location: ?url=meeting/list");
            exit;
        }

        $meeting = $this->meetingModel->getMeetingById($meetingId);

        if (!$meeting) {
            $_SESSION['error'] = "Meeting not found.";
            header("Location: ?url=meeting/list");
            exit;
        }

        // Check if user is part of this meeting
        $userId = $_SESSION['user']['user_id'];
        if ($meeting['student_id'] != $userId && $meeting['tutor_id'] != $userId) {
            $_SESSION['error'] = "You don't have permission to view this recording.";
            header("Location: ?url=meeting/list");
            exit;
        }

        // Get recording info
        $recordingInfo = $this->meetingModel->getRecordingInfo($meetingId);

        if (!$recordingInfo || !$recordingInfo['audio_recording_path']) {
            $_SESSION['error'] = "No recording available for this meeting.";
            header("Location: ?url=meeting/view&id=" . $meetingId);
            exit;
        }

        // Pass data to the view
        $data = [
            'meeting' => $meeting,
            'recordingInfo' => $recordingInfo
        ];

        $this->view('meeting/recording', $data);
    }

}