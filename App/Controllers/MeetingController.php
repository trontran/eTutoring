<?php


use App\Core\Controller;
use App\Models\Meeting;
use App\Models\User;
use App\Models\PersonalTutor;
use App\Models\Notification;

class MeetingController extends Controller
{
    private $meetingModel;
    private $userModel;
    private $personalTutorModel;
    private $notificationModel;

    public function __construct()
    {
        $this->meetingModel = new Meeting();
        $this->userModel = new User();
        $this->personalTutorModel = new PersonalTutor();
        $this->notificationModel = new Notification();

        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
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
        } // If tutor, get specified student or show student selection
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

//        // Check if the time slot is available
//        if (!$this->meetingModel->isTimeSlotAvailable($tutorId, $meetingDateTime)) {
//            $_SESSION['error'] = "The selected time slot is not available. Please choose another time.";
//            header("Location: ?url=meeting/create");
//            exit;
//        }

        // Prepare meeting data
        $meetingData = [
            'student_id' => $studentId,
            'tutor_id' => $tutorId,
            'meeting_date' => $meetingDateTime,
            'meeting_type' => $meetingType,
            'meeting_notes' => $meetingNotes,
            'status' => 'pending'
        ];

        // Create the meeting
        $meetingId = $this->meetingModel->createMeeting($meetingData);

        if ($meetingId) {
            // Create notification for the other party
            $notificationReceiverId = ($userRole === 'student') ? $tutorId : $studentId;
            $senderName = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
            $notificationText = "$senderName has requested a meeting on " . date('F j, Y \a\t g:i A', strtotime($meetingDateTime));

            $this->notificationModel->createNotification($notificationReceiverId, $notificationText);

            $_SESSION['success'] = "Meeting request has been sent successfully.";
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
    public function viewDetails() // Đổi tên từ view() thành viewDetails()
    {
        // Code bên trong vẫn giữ nguyên
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

        $isPastMeeting = strtotime($meeting['meeting_date']) < time();
        $isCompleted = isset($meeting['is_completed']) && $meeting['is_completed'] == 1;
        $data = [
            'meeting' => $meeting,
            'userRole' => $_SESSION['user']['role'],
            'isPastMeeting' => $isPastMeeting,
            'isCompleted' => $isCompleted
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
            // Create notification for the other party
            $notificationReceiverId = ($meeting['student_id'] == $userId) ? $meeting['tutor_id'] : $meeting['student_id'];
            $senderName = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];

            if ($newStatus === 'confirmed') {
                $notificationText = "$senderName has confirmed your meeting scheduled for " . date('F j, Y \a\t g:i A', strtotime($meeting['meeting_date']));
            } else {
                $notificationText = "$senderName has cancelled the meeting scheduled for " . date('F j, Y \a\t g:i A', strtotime($meeting['meeting_date']));
            }

            $this->notificationModel->createNotification($notificationReceiverId, $notificationText);

            $_SESSION['success'] = "Meeting has been " . ($newStatus === 'confirmed' ? 'confirmed' : 'cancelled') . " successfully.";
        } else {
            $_SESSION['error'] = "Failed to update meeting status. Please try again.";
        }

        header("Location: ?url=meeting/view&id=" . $meetingId);
        exit;
    }

    /**
     * Google Calendar integration (placeholder for future implementation)
     *
     * @param int $meetingId Meeting ID
     * @return bool Success status
     */
    private function syncWithGoogleCalendar($meetingId)
    {
        // This is a placeholder for future Google Calendar integration
        // Will be implemented when Google Calendar API is set up

        // Get meeting details
        $meeting = $this->meetingModel->getMeetingById($meetingId);

        if (!$meeting) {
            return false;
        }

        // In the future, this function will:
        // 1. Connect to Google Calendar API
        // 2. Create/update/delete event based on meeting status
        // 3. Store the Google event ID in the database

        return true;
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
        if ($meeting['status'] !== 'confirmed') {
            $_SESSION['error'] = "Only confirmed meetings can be marked as completed.";
            header("Location: ?url=meeting/view&id=" . $meetingId);
            exit;
        }

        $data = [
            'meeting' => $meeting,
            'userRole' => $_SESSION['user']['role']
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
            $notificationText = "$senderName has recorded outcomes for your meeting on " . date('F j, Y \a\t g:i A', strtotime($meeting['meeting_date']));

            $this->notificationModel->createNotification($notificationReceiverId, $notificationText);

            $_SESSION['success'] = "Meeting has been marked as completed and outcomes have been recorded.";
            header("Location: ?url=meeting/view&id=" . $meetingId);
        } else {
            $_SESSION['error'] = "Failed to record meeting outcomes. Please try again.";
            header("Location: ?url=meeting/recordOutcome&id=" . $meetingId);
        }
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
}