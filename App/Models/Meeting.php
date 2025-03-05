<?php


namespace App\Models;

use App\Core\Database;
use PDO;

class Meeting
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Create a new meeting
     *
     * @param array $data Meeting data
     * @return int|bool The meeting ID if successful, false otherwise
     */
    public function createMeeting($data)
    {
        $query = "INSERT INTO Meetings (student_id, tutor_id, meeting_date, meeting_type, meeting_notes, status) 
                  VALUES (:student_id, :tutor_id, :meeting_date, :meeting_type, :meeting_notes, :status)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':student_id', $data['student_id'], PDO::PARAM_INT);
        $stmt->bindParam(':tutor_id', $data['tutor_id'], PDO::PARAM_INT);
        $stmt->bindParam(':meeting_date', $data['meeting_date'], PDO::PARAM_STR);
        $stmt->bindParam(':meeting_type', $data['meeting_type'], PDO::PARAM_STR);
        $stmt->bindParam(':meeting_notes', $data['meeting_notes'], PDO::PARAM_STR);
        $stmt->bindParam(':status', $data['status'], PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Get meeting by ID
     *
     * @param int $meetingId Meeting ID
     * @return array|false Meeting data or false if not found
     */
    public function getMeetingById($meetingId)
    {
        $query = "SELECT m.*, 
                s.first_name as student_first_name, s.last_name as student_last_name, 
                t.first_name as tutor_first_name, t.last_name as tutor_last_name,
                m.is_completed, m.completed_at, m.meeting_outcome
              FROM Meetings m
              JOIN Users s ON m.student_id = s.user_id
              JOIN Users t ON m.tutor_id = t.user_id
              WHERE m.meeting_id = :meeting_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':meeting_id', $meetingId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get meetings for a student
     *
     * @param int $studentId Student ID
     * @param string $status (optional) Filter by status
     * @return array Meetings
     */
    public function getMeetingsByStudentId($studentId, $status = null)
    {
        $query = "SELECT m.*, 
                    t.first_name as tutor_first_name, t.last_name as tutor_last_name 
                  FROM Meetings m
                  JOIN Users t ON m.tutor_id = t.user_id
                  WHERE m.student_id = :student_id";

        if ($status) {
            $query .= " AND m.status = :status";
        }

        $query .= " ORDER BY m.meeting_date ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);

        if ($status) {
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get meetings for a tutor
     *
     * @param int $tutorId Tutor ID
     * @param string $status (optional) Filter by status
     * @return array Meetings
     */
    public function getMeetingsByTutorId($tutorId, $status = null)
    {
        $query = "SELECT m.*, 
                    s.first_name as student_first_name, s.last_name as student_last_name 
                  FROM Meetings m
                  JOIN Users s ON m.student_id = s.user_id
                  WHERE m.tutor_id = :tutor_id";

        if ($status) {
            $query .= " AND m.status = :status";
        }

        $query .= " ORDER BY m.meeting_date ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tutor_id', $tutorId, PDO::PARAM_INT);

        if ($status) {
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        }

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update meeting status
     *
     * @param int $meetingId Meeting ID
     * @param string $status New status (pending, confirmed, cancelled)
     * @return bool True if successful, false otherwise
     */
    public function updateMeetingStatus($meetingId, $status)
    {
        $query = "UPDATE Meetings SET status = :status WHERE meeting_id = :meeting_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':meeting_id', $meetingId, PDO::PARAM_INT);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Update Google Calendar event ID for a meeting
     *
     * @param int $meetingId Meeting ID
     * @param string $googleEventId Google Calendar event ID
     * @return bool True if successful, false otherwise
     */
    public function updateGoogleEventId($meetingId, $googleEventId)
    {
        $query = "UPDATE Meetings SET google_event_id = :google_event_id WHERE meeting_id = :meeting_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':meeting_id', $meetingId, PDO::PARAM_INT);
        $stmt->bindParam(':google_event_id', $googleEventId, PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Check if a meeting time slot is available
     *
     * @param int $tutorId Tutor ID
     * @param string $meetingDate Date and time of the meeting
     * @return bool True if available, false if conflict exists
     */
    public function isTimeSlotAvailable($tutorId, $meetingDate)
    {
        // Convert meeting date to DateTime object
        $meetingDateTime = new \DateTime($meetingDate);

        // Add 1 hour to get end time (assuming meetings are 1 hour long)
        $endDateTime = clone $meetingDateTime;
        $endDateTime->modify('+1 hour');

        // Format dates for comparison
        $meetingStart = $meetingDateTime->format('Y-m-d H:i:s');
        $meetingEnd = $endDateTime->format('Y-m-d H:i:s');

        // Check for conflicts
        $query = "SELECT COUNT(*) FROM Meetings
                  WHERE tutor_id = :tutor_id
                  AND status != 'cancelled'
                  AND (
                      (meeting_date <= :meeting_start AND DATE_ADD(meeting_date, INTERVAL 1 HOUR) > :meeting_start)
                      OR
                      (meeting_date < :meeting_end AND DATE_ADD(meeting_date, INTERVAL 1 HOUR) >= :meeting_end)
                      OR
                      (meeting_date >= :meeting_start AND DATE_ADD(meeting_date, INTERVAL 1 HOUR) <= :meeting_end)
                  )";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tutor_id', $tutorId, PDO::PARAM_INT);
        $stmt->bindParam(':meeting_start', $meetingStart, PDO::PARAM_STR);
        $stmt->bindParam(':meeting_end', $meetingEnd, PDO::PARAM_STR);
        $stmt->execute();

        $count = $stmt->fetchColumn();

        return $count == 0;
    }

    /**
     * Get upcoming meetings for a user (either student or tutor)
     *
     * @param int $userId User ID
     * @param string $role Role (student or tutor)
     * @return array Upcoming meetings
     */
    public function getUpcomingMeetings($userId, $role)
    {
        $idColumn = ($role === 'student') ? 'student_id' : 'tutor_id';
        $joinColumn = ($role === 'student') ? 'tutor_id' : 'student_id';
        $namePrefix = ($role === 'student') ? 'tutor' : 'student';

        $query = "SELECT m.*, 
                    u.first_name as {$namePrefix}_first_name, 
                    u.last_name as {$namePrefix}_last_name 
                  FROM Meetings m
                  JOIN Users u ON m.{$joinColumn} = u.user_id
                  WHERE m.{$idColumn} = :user_id
                  AND m.meeting_date >= NOW()
                  AND m.status != 'cancelled'
                  ORDER BY m.meeting_date ASC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * Update meeting link for virtual meetings
     *
     * @param int $meetingId Meeting ID
     * @param string $meetingLink Meeting link URL
     * @return bool True if successful, false otherwise
     */
    public function updateMeetingLink($meetingId, $meetingLink)
    {
        $query = "UPDATE Meetings SET meeting_link = :meeting_link WHERE meeting_id = :meeting_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':meeting_id', $meetingId, PDO::PARAM_INT);
        $stmt->bindParam(':meeting_link', $meetingLink, PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Mark meeting as completed and record outcomes
     *
     * @param int $meetingId Meeting ID
     * @param string $meetingOutcome Summary of meeting outcomes
     * @return bool True if successful, false otherwise
     */
    public function completeMeeting($meetingId, $meetingOutcome)
    {
        $now = date('Y-m-d H:i:s');
        $query = "UPDATE Meetings 
              SET meeting_outcome = :meeting_outcome, 
                  is_completed = 1, 
                  completed_at = :completed_at 
              WHERE meeting_id = :meeting_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':meeting_id', $meetingId, PDO::PARAM_INT);
        $stmt->bindParam(':meeting_outcome', $meetingOutcome, PDO::PARAM_STR);
        $stmt->bindParam(':completed_at', $now, PDO::PARAM_STR);

        return $stmt->execute();
    }

    /**
     * Get completed meetings for a user
     *
     * @param int $userId User ID
     * @param string $role Role (student or tutor)
     * @return array Completed meetings
     */
    public function getCompletedMeetings($userId, $role)
    {
        $idColumn = ($role === 'student') ? 'student_id' : 'tutor_id';
        $joinColumn = ($role === 'student') ? 'tutor_id' : 'student_id';
        $namePrefix = ($role === 'student') ? 'tutor' : 'student';

        $query = "SELECT m.*, 
                u.first_name as {$namePrefix}_first_name, 
                u.last_name as {$namePrefix}_last_name 
              FROM Meetings m
              JOIN Users u ON m.{$joinColumn} = u.user_id
              WHERE m.{$idColumn} = :user_id
              AND m.is_completed = 1
              ORDER BY m.completed_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Generate a custom meeting link for virtual meetings
     * This could be replaced with actual video conferencing integration
     *
     * @param int $meetingId Meeting ID
     * @return string Meeting link URL
     */
    public function generateMeetingLink($meetingId)
    {
        // This is a placeholder - in a real application, you might integrate with
        // Zoom, Google Meet, or another video conferencing platform
        $baseUrl = "https://meet.example.com/";
        $uniqueCode = md5('etutoring-' . $meetingId . '-' . time());
        return $baseUrl . substr($uniqueCode, 0, 10);
    }
}