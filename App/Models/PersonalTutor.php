<?php
namespace App\Models;

use App\Core\Database;
use PDO;
class PersonalTutor {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection(); 
    }

    public function getTutorByStudent($student_id) {
        $query = "SELECT tutor_id FROM PersonalTutors WHERE student_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$student_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function assignTutor($student_id, $tutor_id, $assigned_by) {
        $query = "INSERT INTO PersonalTutors (student_id, tutor_id, assigned_by) 
                  VALUES (:student_id, :tutor_id, :assigned_by) 
                  ON DUPLICATE KEY UPDATE tutor_id = VALUES(tutor_id), assigned_by = VALUES(assigned_by)";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(":student_id", $student_id, PDO::PARAM_INT);
        $stmt->bindParam(":tutor_id", $tutor_id, PDO::PARAM_INT);
        $stmt->bindParam(":assigned_by", $assigned_by, PDO::PARAM_INT);
        
        return $stmt->execute();
    }
    

    // Cập nhật gia sư nếu đã có sẵn
    public function updateTutor($student_id, $tutor_id, $assigned_by) {
        $query = "UPDATE PersonalTutors SET tutor_id = ?, assigned_by = ? WHERE student_id = ?";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$tutor_id, $assigned_by, $student_id]);
    }

    // Lấy thông tin gia sư của sinh viên
    public function getTutorDetails($student_id) {
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

    public function updateTutorAssignment($studentId, $newTutorId, $assignedBy) {
    $query = "UPDATE PersonalTutors 
              SET tutor_id = :tutor_id, assigned_by = :assigned_by, assigned_at = NOW()
              WHERE student_id = :student_id";

    $stmt = $this->db->prepare($query);
    $stmt->bindParam(":tutor_id", $newTutorId, PDO::PARAM_INT);
    $stmt->bindParam(":assigned_by", $assignedBy, PDO::PARAM_INT);
    $stmt->bindParam(":student_id", $studentId, PDO::PARAM_INT);

    return $stmt->execute();
}
    
    
}
