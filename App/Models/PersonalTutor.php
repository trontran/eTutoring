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

    public function assignTutor($student_id, $tutor_id, $assigned_by)
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

    // Cập nhật gia sư nếu đã có sẵn
    public function updateTutor($student_id, $tutor_id, $assigned_by)
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

    public function updateTutorAssignment($studentId, $newTutorId, $assignedBy)
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
    public function getTuteesByTutor($tutorId, $filter = "", $sortBy = "assigned_at")
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
    // test##

    /**
     * Reallocates a tutor for a student by updating the database and notifying relevant parties.
     *
     * @param int $studentId The ID of the student whose tutor is being reassigned.
     * @param int $newTutorId The ID of the new tutor to be assigned to the student.
     * @param int $assignedBy The ID of the user who is performing the reassignment.
     *
     * @return bool Returns true if the reassignment and notifications were successfully completed.
     */
    public function reallocateTutor($studentId, $newTutorId, $assignedBy)
    {
        // Lấy thông tin old tutor (nếu có)
        $oldTutorQuery = "SELECT tutor_id FROM PersonalTutors WHERE student_id = :student_id";
        $stmt = $this->db->prepare($oldTutorQuery);
        $stmt->bindParam(":student_id", $studentId, \PDO::PARAM_INT);
        $stmt->execute();
        $oldTutorId = $stmt->fetchColumn();
        $oldTutor = null;

        if (!empty($oldTutorId)) {
            $oldTutor = (new User())->getUserById($oldTutorId);
        }

        // Cập nhật database
        $updateQuery = "UPDATE PersonalTutors SET tutor_id = :new_tutor_id, assigned_by = :assigned_by WHERE student_id = :student_id";
        $stmt = $this->db->prepare($updateQuery);
        $stmt->bindParam(":student_id", $studentId, \PDO::PARAM_INT);
        $stmt->bindParam(":new_tutor_id", $newTutorId, \PDO::PARAM_INT);
        $stmt->bindParam(":assigned_by", $assignedBy, \PDO::PARAM_INT);
        $stmt->execute();

//        // Lấy thông tin người dùng
//        $student = (new User())->getUserById($studentId);
//        $newTutor = (new User())->getUserById($newTutorId);
//
//        // Kiểm tra email hợp lệ
//        if (empty($student['email']) || empty($newTutor['email']) || ($oldTutor && empty($oldTutor['email']))) {
//            throw new \RuntimeException("❌ Error: One of the email addresses is empty.");
//        }
//
//        // Gửi email cho student
//        $studentSubject = "Tutor Reassignment Notification";
//        $studentBody = "Dear {$student['first_name']},<br><br>Your tutor has been changed ";
//        if ($oldTutor) {
//            $studentBody .= "from <b>{$oldTutor['first_name']} {$oldTutor['last_name']}</b> ";
//        }
//        $studentBody .= "to <b>{$newTutor['first_name']} {$newTutor['last_name']}</b>.";
//
//        MailHelper::sendMail($student['email'], $studentSubject, $studentBody);
//        if (!MailHelper::sendMail($student['email'], $studentSubject, $studentBody)) {
//            die("❌ Failed to send email to student: {$student['email']}");
//        }
//
//        echo "✅ Email sent successfully to Student!";
//        // Gửi email cho old tutor (nếu có)
//        if ($oldTutor) {
//            $oldTutorSubject = "Student Reassignment Notification";
//            $oldTutorBody = "Dear {$oldTutor['first_name']},<br><br>Your student <b>{$student['first_name']} {$student['last_name']}</b> has been reassigned.";
//
//            MailHelper::sendMail($oldTutor['email'], $oldTutorSubject, $oldTutorBody);
//            if (!MailHelper::sendMail($student['email'], $studentSubject, $studentBody)) {
//                die("❌ Failed to send email to student: {$student['email']}");
//            }
//
//            echo "✅ Email sent successfully to Student!";
//        }
//
//        // Gửi email cho new tutor
//        $newTutorSubject = "New Student Assigned";
//        $newTutorBody = "Dear {$newTutor['first_name']},<br><br>You have been assigned a new student: <b>{$student['first_name']} {$student['last_name']}</b>.";
//
//        MailHelper::sendMail($newTutor['email'], $newTutorSubject, $newTutorBody);
//        if (!MailHelper::sendMail($student['email'], $studentSubject, $studentBody)) {
//            die("❌ Failed to send email to student: {$student['email']}");
//        }
//
//        echo "✅ Email sent successfully to Student!";

        return true;
    }

}
