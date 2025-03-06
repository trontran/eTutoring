<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Dashboard
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get student dashboard data
     *
     * @param int $studentId Student ID
     * @return array Dashboard data
     */
    public function getStudentDashboard($studentId)
    {
        $data = [];

        // Get tutor information
        $tutorQuery = "SELECT pt.tutor_id, u.first_name, u.last_name, u.email
                      FROM PersonalTutors pt
                      JOIN Users u ON pt.tutor_id = u.user_id
                      WHERE pt.student_id = :student_id
                      ORDER BY pt.assigned_at DESC
                      LIMIT 1";

        $stmt = $this->db->prepare($tutorQuery);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        $data['tutor'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get message statistics
        $messageQuery = "SELECT 
                            COUNT(*) as total_messages,
                            SUM(CASE WHEN m.sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as messages_last_7_days,
                            SUM(CASE WHEN m.sender_id = :student_id THEN 1 ELSE 0 END) as sent_messages,
                            SUM(CASE WHEN m.receiver_id = :student_id THEN 1 ELSE 0 END) as received_messages,
                            MAX(m.sent_at) as last_message_date
                         FROM Messages m
                         WHERE (m.sender_id = :student_id AND m.receiver_id = :tutor_id)
                            OR (m.sender_id = :tutor_id AND m.receiver_id = :student_id)";

        if ($data['tutor']) {
            $stmt = $this->db->prepare($messageQuery);
            $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmt->bindParam(':tutor_id', $data['tutor']['tutor_id'], PDO::PARAM_INT);
            $stmt->execute();
            $data['messages'] = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $data['messages'] = [
                'total_messages' => 0,
                'messages_last_7_days' => 0,
                'sent_messages' => 0,
                'received_messages' => 0,
                'last_message_date' => null
            ];
        }

        // Get meeting statistics
        $meetingQuery = "SELECT 
                            COUNT(*) as total_meetings,
                            SUM(CASE WHEN m.meeting_date >= NOW() THEN 1 ELSE 0 END) as upcoming_meetings,
                            SUM(CASE WHEN m.is_completed = 1 THEN 1 ELSE 0 END) as completed_meetings,
                            MAX(CASE WHEN m.is_completed = 1 THEN m.completed_at ELSE NULL END) as last_completed_meeting,
                            MIN(CASE WHEN m.meeting_date >= NOW() THEN m.meeting_date ELSE NULL END) as next_meeting_date
                         FROM Meetings m
                         WHERE m.student_id = :student_id";

        $stmt = $this->db->prepare($meetingQuery);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        $data['meetings'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get document statistics
        $documentQuery = "SELECT 
                            COUNT(*) as total_documents,
                            SUM(CASE WHEN d.uploaded_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as recent_documents,
                            MAX(d.uploaded_at) as last_document_date
                          FROM Documents d
                          WHERE d.student_id = :student_id";

        $stmt = $this->db->prepare($documentQuery);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        $data['documents'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get blog statistics
        $blogQuery = "SELECT 
                        COUNT(DISTINCT b.blog_id) as total_blogs,
                        COUNT(DISTINCT bc.comment_id) as total_comments
                      FROM BlogParticipants bp
                      JOIN Blogs b ON bp.blog_id = b.blog_id
                      LEFT JOIN BlogComments bc ON b.blog_id = bc.blog_id AND bc.user_id = :student_id
                      WHERE bp.student_id = :student_id";

        $stmt = $this->db->prepare($blogQuery);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        $data['blogs'] = $stmt->fetch(PDO::FETCH_ASSOC);

        // Calculate last interaction date (most recent of any type)
        $lastInteractionQuery = "SELECT MAX(last_date) as last_interaction FROM (
                                  SELECT MAX(sent_at) as last_date FROM Messages 
                                  WHERE sender_id = :student_id OR receiver_id = :student_id
                                  UNION
                                  SELECT MAX(meeting_date) FROM Meetings 
                                  WHERE student_id = :student_id AND is_completed = 1
                                  UNION
                                  SELECT MAX(created_at) FROM BlogComments 
                                  WHERE user_id = :student_id
                                  UNION
                                  SELECT MAX(commented_at) FROM DocumentComments 
                                  WHERE commenter_id = :student_id
                                ) as interactions";

        $stmt = $this->db->prepare($lastInteractionQuery);
        $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        $lastInteraction = $stmt->fetch(PDO::FETCH_ASSOC);
        $data['last_interaction'] = $lastInteraction['last_interaction'];

        return $data;
    }

    /**
     * Get system-wide statistics for staff
     *
     * @return array Statistics data
     */
    public function getSystemStats()
    {
        $data = [];

        // Recent message count (last 7 days)
        $recentMessagesQuery = "SELECT COUNT(*) as message_count 
                               FROM Messages 
                               WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $stmt = $this->db->prepare($recentMessagesQuery);
        $stmt->execute();
        $data['recent_messages'] = $stmt->fetch(PDO::FETCH_ASSOC)['message_count'];

        // Average messages per tutor
        $avgTutorMessagesQuery = "SELECT 
                                    AVG(message_count) as avg_messages
                                  FROM (
                                    SELECT 
                                      u.user_id,
                                      COUNT(m.message_id) as message_count
                                    FROM Users u
                                    LEFT JOIN Messages m ON (m.sender_id = u.user_id OR m.receiver_id = u.user_id)
                                    WHERE u.role = 'tutor'
                                    GROUP BY u.user_id
                                  ) as tutor_messages";
        $stmt = $this->db->prepare($avgTutorMessagesQuery);
        $stmt->execute();
        $data['avg_tutor_messages'] = $stmt->fetch(PDO::FETCH_ASSOC)['avg_messages'] ?? 0;

        // Students without tutors
        $studentsWithoutTutorQuery = "SELECT COUNT(*) as count
                                     FROM Users u
                                     LEFT JOIN PersonalTutors pt ON u.user_id = pt.student_id
                                     WHERE u.role = 'student' AND pt.tutor_id IS NULL";
        $stmt = $this->db->prepare($studentsWithoutTutorQuery);
        $stmt->execute();
        $data['students_without_tutor'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Students without recent interaction (7 days)
        $noInteraction7DaysQuery = "SELECT COUNT(*) as count
                                   FROM Users u
                                   WHERE u.role = 'student'
                                   AND u.user_id NOT IN (
                                       SELECT DISTINCT sender_id FROM Messages 
                                       WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                                       UNION
                                       SELECT DISTINCT receiver_id FROM Messages 
                                       WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                                       UNION
                                       SELECT DISTINCT student_id FROM Meetings 
                                       WHERE meeting_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                                   )";
        $stmt = $this->db->prepare($noInteraction7DaysQuery);
        $stmt->execute();
        $data['no_interaction_7days'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Students without recent interaction (28 days)
        $noInteraction28DaysQuery = "SELECT COUNT(*) as count
                                    FROM Users u
                                    WHERE u.role = 'student'
                                    AND u.user_id NOT IN (
                                        SELECT DISTINCT sender_id FROM Messages 
                                        WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 28 DAY)
                                        UNION
                                        SELECT DISTINCT receiver_id FROM Messages 
                                        WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 28 DAY)
                                        UNION
                                        SELECT DISTINCT student_id FROM Meetings 
                                        WHERE meeting_date >= DATE_SUB(NOW(), INTERVAL 28 DAY)
                                    )";
        $stmt = $this->db->prepare($noInteraction28DaysQuery);
        $stmt->execute();
        $data['no_interaction_28days'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        return $data;
    }

    /**
     * Get list of students without tutors
     *
     * @return array Students without tutors
     */
    public function getStudentsWithoutTutor()
    {
        $query = "SELECT u.user_id, u.first_name, u.last_name, u.email, u.created_at
                 FROM Users u
                 LEFT JOIN PersonalTutors pt ON u.user_id = pt.student_id
                 WHERE u.role = 'student' AND pt.tutor_id IS NULL
                 ORDER BY u.created_at DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get list of students with no interaction for a specified number of days
     *
     * @param int $days Number of days without interaction
     * @return array Students without recent interaction
     */
    public function getStudentsWithoutInteraction($days = 7)
    {
        $query = "SELECT u.user_id, u.first_name, u.last_name, u.email,
                        t.first_name as tutor_first_name, t.last_name as tutor_last_name,
                        (SELECT MAX(sent_at) FROM Messages 
                         WHERE sender_id = u.user_id OR receiver_id = u.user_id) as last_message_date,
                        (SELECT MAX(meeting_date) FROM Meetings 
                         WHERE student_id = u.user_id) as last_meeting_date
                  FROM Users u
                  LEFT JOIN PersonalTutors pt ON u.user_id = pt.student_id
                  LEFT JOIN Users t ON pt.tutor_id = t.user_id
                  WHERE u.role = 'student'
                  AND u.user_id NOT IN (
                      SELECT DISTINCT sender_id FROM Messages 
                      WHERE sent_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                      UNION
                      SELECT DISTINCT receiver_id FROM Messages 
                      WHERE sent_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                      UNION
                      SELECT DISTINCT student_id FROM Meetings 
                      WHERE meeting_date >= DATE_SUB(NOW(), INTERVAL :days DAY)
                  )
                  ORDER BY last_message_date DESC, last_meeting_date DESC";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get tutor activity summary
     *
     * @return array Tutor activity data
     */
    public function getTutorActivity()
    {
        $query = "SELECT 
                    u.user_id, u.first_name, u.last_name, u.email,
                    (SELECT COUNT(*) FROM PersonalTutors WHERE tutor_id = u.user_id) as tutee_count,
                    (SELECT COUNT(*) FROM Messages WHERE sender_id = u.user_id AND sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as messages_sent_7days,
                    (SELECT COUNT(*) FROM Meetings WHERE tutor_id = u.user_id AND meeting_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)) as meetings_7days,
                    (SELECT COUNT(*) FROM Meetings WHERE tutor_id = u.user_id AND is_completed = 1) as completed_meetings
                  FROM Users u
                  WHERE u.role = 'tutor'
                  ORDER BY tutee_count DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}