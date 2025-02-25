<?php

namespace App\Models;

use App\Core\Database;
use MailHelper;
use PDO;

require_once __DIR__ . '/../Helpers/MailHelper.php';

class PersonalTutor
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getTutorByStudent($student_id)
    {
        $query = "SELECT tutor_id FROM PersonalTutors WHERE student_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$student_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function assignTutor($student_id, $tutor_id, $assigned_by): bool
    {
        $query = "INSERT INTO PersonalTutors (student_id, tutor_id, assigned_by) 
                  VALUES (:student_id, :tutor_id, :assigned_by) 
                  ON DUPLICATE KEY UPDATE tutor_id = VALUES(tutor_id), assigned_by = VALUES(assigned_by)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":student_id", $student_id, PDO::PARAM_INT);
        $stmt->bindParam(":tutor_id", $tutor_id, PDO::PARAM_INT);
        $stmt->bindParam(":assigned_by", $assigned_by, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Lấy thông tin Student và Tutor để gửi email
            $student = $this->db->query("SELECT * FROM Users WHERE user_id = $student_id")->fetch(PDO::FETCH_ASSOC);
            $tutor = $this->db->query("SELECT * FROM Users WHERE user_id = $tutor_id")->fetch(PDO::FETCH_ASSOC);

            // Gửi email cho Student
            $studentSubject = "Your New Tutor Assignment - eTutoring System";
            $studentBody = "
            <p>Dear {$student['first_name']},</p>
            <p>We are pleased to inform you that you have been assigned a new personal tutor to support your learning journey.</p>
            
            <p><strong>Tutor Details:</strong></p>
            <ul>
                <li><strong>Name:</strong> {$tutor['first_name']} {$tutor['last_name']}</li>
                <li><strong>Email:</strong> {$tutor['email']}</li>
            </ul>
        
            <p>Your tutor will assist you with academic guidance and support. Feel free to reach out to them if you need any help.</p>
        
            <p>Best regards,</p>
            <p><strong>eTutoring Team</strong></p>
            <hr>
            <p style='font-size:12px; color:gray;'>This is an automated message, please do not reply to this email.</p>
               ";

            MailHelper::sendMail($student['email'], $studentSubject, $studentBody);

            // Gửi email cho Tutor
            $tutorSubject = "New Student Assigned - eTutoring System";
            $tutorBody = "
            <p>Dear {$tutor['first_name']},</p>
            <p>We are pleased to inform you that you have been assigned a new student in the eTutoring system.</p>
            
            <p><strong>Student Details:</strong></p>
            <ul>
                <li><strong>Name:</strong> {$student['first_name']} {$student['last_name']}</li>
                <li><strong>Email:</strong> {$student['email']}</li>
            </ul>
        
            <p>Please reach out to your student soon to introduce yourself and discuss their learning needs.</p>
        
            <p>Best regards,</p>
            <p><strong>eTutoring Team</strong></p>
            <hr>
            <p style='font-size:12px; color:gray;'>This is an automated message, please do not reply to this email.</p>
                ";

            MailHelper::sendMail($tutor['email'], $tutorSubject, $tutorBody);

            return true;
        }

        return false;
    }
    public function updateTutor($student_id, $tutor_id, $assigned_by): bool
    {
        $query = "UPDATE PersonalTutors SET tutor_id = ?, assigned_by = ? WHERE student_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$tutor_id, $assigned_by, $student_id]);
    }

    // Lấy thông tin gia sư của sinh viên
    public function getTutorDetails($student_id)
    {
        $query = "SELECT u.user_id, u.first_name, u.last_name, u.email,
                         (SELECT COUNT(*) FROM PersonalTutors WHERE tutor_id = u.user_id) AS total_students,
                         (SELECT COUNT(*) FROM Messages WHERE sender_id = u.user_id) AS total_messages
                  FROM Users u
                  JOIN PersonalTutors pt ON u.user_id = pt.tutor_id
                  WHERE pt.student_id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$student_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateTutorAssignment($studentId, $newTutorId, $assignedBy): bool
    {
        $query = "UPDATE PersonalTutors 
                    SET tutor_id = :tutor_id, assigned_by = :assigned_by, assigned_at = NOW()
                    WHERE student_id = :student_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":tutor_id", $newTutorId, PDO::PARAM_INT);
        $stmt->bindParam(":assigned_by", $assignedBy, PDO::PARAM_INT);
        $stmt->bindParam(":student_id", $studentId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function getCurrentTutor($studentId)
    {
        $sql = "SELECT * FROM PersonalTutors WHERE student_id = :student_id ORDER BY assigned_at DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Retrieves a list of tutees assigned to a specific tutor with optional filtering and sorting.
     *
     * @param int $tutorId The ID of the tutor whose tutees are being retrieved.
     * @param string $filter Optional search term to filter tutees by their first name, last name, or email.
     * @param string $sortBy Optional sorting column; valid values are 'first_name', 'email', or 'assigned_at'.
     *
     * @return array Returns an array of tutees with their details, including user ID, first name, last name, email, and assigned date.
     */
    public function getTuteesByTutor(int $tutorId, string $filter = "", string $sortBy = "assigned_at"): array
    {
        $validSortColumns = ['first_name', 'email', 'assigned_at']; // Chỉ cho phép các giá trị hợp lệ
        if (!in_array($sortBy, $validSortColumns)) {
            $sortBy = "assigned_at"; // Mặc định nếu giá trị không hợp lệ
        }

        $sql = "SELECT u.user_id, u.first_name, u.last_name, u.email, pt.assigned_at 
                    FROM PersonalTutors pt
                    JOIN Users u ON pt.student_id = u.user_id
                    WHERE pt.tutor_id = :tutorId";

        if (!empty($filter)) {
            $sql .= " AND (u.first_name LIKE :filter OR u.last_name LIKE :filter OR u.email LIKE :filter)";
        }

        $sql .= " ORDER BY $sortBy ASC";  // Sắp xếp theo tiêu chí được chọn

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tutorId', $tutorId, PDO::PARAM_INT);

        if (!empty($filter)) {
            $filter = "%$filter%";
            $stmt->bindParam(':filter', $filter, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function reallocateTutor(int $studentId, int $newTutorId, int $assignedBy): bool
    {
        // take information old tutor (if had)
        $oldTutorQuery = "SELECT tutor_id FROM PersonalTutors WHERE student_id = :student_id";
        $stmt = $this->db->prepare($oldTutorQuery);
        $stmt->bindParam(":student_id", $studentId, \PDO::PARAM_INT);
        $stmt->execute();
        $oldTutorId = $stmt->fetchColumn();
        $oldTutor = null;

        if (!empty($oldTutorId)) {
            $oldTutor = (new User())->getUserById($oldTutorId);
        }

        // update database
        $updateQuery = "UPDATE PersonalTutors SET tutor_id = :new_tutor_id, assigned_by = :assigned_by WHERE student_id = :student_id";
        $stmt = $this->db->prepare($updateQuery);
        $stmt->bindParam(":student_id", $studentId, \PDO::PARAM_INT);
        $stmt->bindParam(":new_tutor_id", $newTutorId, \PDO::PARAM_INT);
        $stmt->bindParam(":assigned_by", $assignedBy, \PDO::PARAM_INT);
        $stmt->execute();

        // take information
        $student = (new User())->getUserById($studentId);
        $newTutor = (new User())->getUserById($newTutorId);

        // Check email valid
        if (empty($student['email']) || empty($newTutor['email']) || ($oldTutor && empty($oldTutor['email']))) {
            throw new \RuntimeException("❌ Error: One of the email addresses is empty.");
        }

        // Gửi email cho Student
        $studentSubject = "Notification: Your Tutor Has Been Reassigned";
        $studentBody = "Dear {$student['first_name']},<br><br>";
        $studentBody .= "We wish to inform you that your personal tutor assignment has been updated. ";
        if ($oldTutor) {
            $studentBody .= "Previously, your tutor was <b>{$oldTutor['first_name']} {$oldTutor['last_name']}</b>. ";
        }
        $studentBody .= "Your new tutor is <b>{$newTutor['first_name']} {$newTutor['last_name']}</b> ";
        $studentBody .= "(Email: <a href='mailto:{$newTutor['email']}'>{$newTutor['email']}</a>).<br><br>";
        $studentBody .= "We trust that this change will enhance your learning experience. Should you have any questions or require further assistance, ";
        $studentBody .= "please feel free to contact our support team or reach out directly to your new tutor using the email above.<br><br>";
        $studentBody .= "Best regards,<br>eTutoring System Team";

        if (!MailHelper::sendMail($student['email'], $studentSubject, $studentBody)) {
            die(" Failed to send email to student: {$student['email']}");
        }
        echo "✅ Email sent successfully to Student!";


        // Send email for Old Tutor (if had)
        if ($oldTutor) {
            $oldTutorSubject = "Notification: Student Reassignment";
            $oldTutorBody = "Dear {$oldTutor['first_name']},<br><br>";
            $oldTutorBody .= "We wish to notify you that your student, <b>{$student['first_name']} {$student['last_name']}</b>, ";
            $oldTutorBody .= "has been reassigned to a new tutor.<br><br>";
            $oldTutorBody .= "Thank you for your continued support. If you have any questions regarding this change, please do not hesitate to contact us.<br><br>";
            $oldTutorBody .= "Best regards,<br>eTutoring System Team";

            if (!MailHelper::sendMail($oldTutor['email'], $oldTutorSubject, $oldTutorBody)) {
                die("Failed to send email to old tutor: {$oldTutor['email']}");
            }
            echo " Email sent successfully to Old Tutor!";
        }


        // Send email for New Tutor
        $newTutorSubject = "Notification: New Student Assignment";
        $newTutorBody = "Dear {$newTutor['first_name']},<br><br>";
        $newTutorBody .= "We are pleased to inform you that you have been assigned a new student: <b>{$student['first_name']} {$student['last_name']}</b>.<br><br>";
        $newTutorBody .= "For your reference, you may contact the student via email if necessary. We trust in your expertise and commitment to support your student's academic journey.<br><br>";
        $newTutorBody .= "If you require further assistance, please contact our support team.<br><br>";
        $newTutorBody .= "Best regards,<br>eTutoring System Team";

        if (!MailHelper::sendMail($newTutor['email'], $newTutorSubject, $newTutorBody)) {
            die(" Failed to send email to new tutor: {$newTutor['email']}");
        }
        echo " Email sent successfully to New Tutor!";

        return true;
    }

}
