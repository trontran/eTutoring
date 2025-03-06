<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class MeetingReminder
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function scheduleReminders($meetingId, $studentId, $tutorId, $meetingDate)
    {
        // Schedule reminder for 24 hours before meeting
        $reminderTime = date('Y-m-d H:i:s', strtotime($meetingDate . ' -24 hours'));
        $this->createReminder($meetingId, $studentId, 'day_before', $reminderTime);
        $this->createReminder($meetingId, $tutorId, 'day_before', $reminderTime);

        // Schedule reminder for 1 hour before meeting
        $reminderTime = date('Y-m-d H:i:s', strtotime($meetingDate . ' -1 hour'));
        $this->createReminder($meetingId, $studentId, 'hour_before', $reminderTime);
        $this->createReminder($meetingId, $tutorId, 'hour_before', $reminderTime);
    }

    public function createReminder($meetingId, $userId, $reminderType, $reminderTime)
    {
        $query = "INSERT INTO MeetingReminders (meeting_id, user_id, reminder_type, reminder_time) 
                  VALUES (:meeting_id, :user_id, :reminder_type, :reminder_time)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':meeting_id', $meetingId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':reminder_type', $reminderType, PDO::PARAM_STR);
        $stmt->bindParam(':reminder_time', $reminderTime, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function deleteRemindersByMeetingId($meetingId)
    {
        $query = "DELETE FROM MeetingReminders WHERE meeting_id = :meeting_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':meeting_id', $meetingId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function getPendingReminders()
    {
        $now = date('Y-m-d H:i:s');
        $query = "SELECT mr.*, m.meeting_date, m.meeting_type, u.email, u.first_name, u.last_name 
                  FROM MeetingReminders mr
                  JOIN Meetings m ON mr.meeting_id = m.meeting_id
                  JOIN Users u ON mr.user_id = u.user_id
                  WHERE mr.reminder_time <= :now AND mr.is_sent = 0";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':now', $now, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsSent($reminderId)
    {
        $query = "UPDATE MeetingReminders SET is_sent = 1 WHERE reminder_id = :reminder_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':reminder_id', $reminderId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}