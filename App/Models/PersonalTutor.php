<?php

namespace App\Models;
use App\Models\User;
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

            $student = $this->db->query("SELECT * FROM Users WHERE user_id = $student_id")->fetch(PDO::FETCH_ASSOC);
            $tutor = $this->db->query("SELECT * FROM Users WHERE user_id = $tutor_id")->fetch(PDO::FETCH_ASSOC);


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

        $sql .= " ORDER BY $sortBy ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':tutorId', $tutorId, PDO::PARAM_INT);

        if (!empty($filter)) {
            $filter = "%$filter%";
            $stmt->bindParam(':filter', $filter, PDO::PARAM_STR);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOldTutorByStudentId(int $studentId): ?array
    {
        $query = "SELECT u.user_id, u.first_name, u.last_name, u.email
              FROM PersonalTutors pt
              JOIN Users u ON pt.tutor_id = u.user_id
              WHERE pt.student_id = :student_id
              ORDER BY pt.assigned_at DESC
              LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":student_id", $studentId, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function reallocateTutor(int $studentId, int $newTutorId, int $assignedBy): bool
    {
        try {

            $this->db->beginTransaction();

            //  Retrieve the current (old) tutor_id from the PersonalTutors table (if exists)
            $sqlOld = "SELECT tutor_id FROM PersonalTutors 
                   WHERE student_id = :student_id 
                   ORDER BY assigned_at DESC 
                   LIMIT 1";
            $stmtOld = $this->db->prepare($sqlOld);
            $stmtOld->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmtOld->execute();
            $oldTutorId = $stmtOld->fetchColumn();

            // 3. If an old tutor exists, insert the record into TutorHistory to store the assignment history
            if ($oldTutorId) {
                $insertHistory = "INSERT INTO TutorHistory (student_id, tutor_id, assigned_by, assigned_at) 
                              VALUES (:student_id, :tutor_id, :assigned_by, NOW())";
                $stmtHistory = $this->db->prepare($insertHistory);
                $stmtHistory->bindParam(':student_id', $studentId, PDO::PARAM_INT);
                $stmtHistory->bindParam(':tutor_id', $oldTutorId, PDO::PARAM_INT);
                $stmtHistory->bindParam(':assigned_by', $assignedBy, PDO::PARAM_INT);
                $stmtHistory->execute();
            }

            // 4. Update the student's current tutor in the PersonalTutors table with the new tutor
            $updateQuery = "UPDATE PersonalTutors 
                        SET tutor_id = :new_tutor_id, assigned_by = :assigned_by, assigned_at = NOW() 
                        WHERE student_id = :student_id";
            $stmtUpdate = $this->db->prepare($updateQuery);
            $stmtUpdate->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':new_tutor_id', $newTutorId, PDO::PARAM_INT);
            $stmtUpdate->bindParam(':assigned_by', $assignedBy, PDO::PARAM_INT);
            $stmtUpdate->execute();

            // 5. Commit the transaction to ensure the TutorHistory record is saved
            $this->db->commit();

        } catch (\Exception $e) {
            // Roll back the transaction in case of any error
            $this->db->rollBack();
            throw $e;
        }
        $userModel = new User();

        $oldTutorIdFromHistory = null;
        if ($oldTutorId) {
            // Retrieve the second most recent tutor_id (skipping the latest record)
            $sqlHistory = "SELECT tutor_id 
                       FROM TutorHistory 
                       WHERE student_id = :student_id 
                       ORDER BY assigned_at DESC 
                       LIMIT 1 OFFSET 1";
            $stmtH = $this->db->prepare($sqlHistory);
            $stmtH->bindParam(':student_id', $studentId, PDO::PARAM_INT);
            $stmtH->execute();
            $oldTutorIdFromHistory = $stmtH->fetchColumn();
        }

        // 7. Retrieve the details for the student, the new tutor, and the old tutor (if exists)
        $student  = $userModel->getUserById($studentId);
        $newTutor = $userModel->getUserById($newTutorId);
        $oldTutor = null;
        if ($oldTutorIdFromHistory) {
            $oldTutor = $userModel->getUserById($oldTutorIdFromHistory);
        }

        // Log the final old tutor information before sending emails
        error_log("Final Old Tutor ID for Email: " . ($oldTutorIdFromHistory ?: "NULL"));
        if ($oldTutor) {
            error_log("Final Old Tutor Email: " . $oldTutor['email']);
        }

        // Send email to the student
        $studentSubject = "Notification: Your Tutor Has Been Reassigned";
        $studentBody = "Dear {$student['first_name']},<br><br>";
        if ($oldTutor) {
            $studentBody .= "Previously, your tutor was <b>{$oldTutor['first_name']} {$oldTutor['last_name']}</b>. ";
        }
        $studentBody .= "Your new tutor is <b>{$newTutor['first_name']} {$newTutor['last_name']}</b> ";
        $studentBody .= "(Email: <a href='mailto:{$newTutor['email']}'>{$newTutor['email']}</a>).<br><br>";
        MailHelper::sendMail($student['email'], $studentSubject, $studentBody);

        // Send email to the old tutor (if exists)
        if ($oldTutor && !empty($oldTutor['email'])) {
            $oldTutorSubject = "Notification: Student Reassignment";
            $oldTutorBody = "Dear {$oldTutor['first_name']},<br><br>";
            $oldTutorBody .= "Your student, <b>{$student['first_name']} {$student['last_name']}</b>, ";
            $oldTutorBody .= "has been reassigned to another tutor.<br><br>";
            MailHelper::sendMail($oldTutor['email'], $oldTutorSubject, $oldTutorBody);
        }

        // Send email to the new tutor
        $newTutorSubject = "Notification: New Student Assignment";
        $newTutorBody = "Dear {$newTutor['first_name']},<br><br>";
        $newTutorBody .= "You have been assigned a new student: <b>{$student['first_name']} {$student['last_name']}</b>.<br><br>";
        MailHelper::sendMail($newTutor['email'], $newTutorSubject, $newTutorBody);

        return true;
    }

    //test queue email

    /**
     * Assign a tutor to a student without sending emails
     *
     * @param int $student_id Student ID
     * @param int $tutor_id Tutor ID
     * @param int $assigned_by User ID of who assigned the tutor
     * @return bool True if successful, false otherwise
     */
    public function assignTutorWithoutEmail($student_id, $tutor_id, $assigned_by): bool
    {
        $query = "INSERT INTO PersonalTutors (student_id, tutor_id, assigned_by) 
              VALUES (:student_id, :tutor_id, :assigned_by) 
              ON DUPLICATE KEY UPDATE tutor_id = VALUES(tutor_id), assigned_by = VALUES(assigned_by)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":student_id", $student_id, PDO::PARAM_INT);
        $stmt->bindParam(":tutor_id", $tutor_id, PDO::PARAM_INT);
        $stmt->bindParam(":assigned_by", $assigned_by, PDO::PARAM_INT);

        return $stmt->execute();
    }

}
