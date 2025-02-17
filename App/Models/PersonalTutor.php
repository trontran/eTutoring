<?php
namespace App\Models;

use App\Core\Database;

class PersonalTutor {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Lấy gia sư của sinh viên
    public function getTutorByStudent($student_id) {
        $query = "SELECT tutor_id FROM PersonalTutors WHERE student_id = ?";
        return $this->db->fetch($query, [$student_id]);
    }

    // Gán gia sư cho sinh viên mới
    public function assignTutor($student_id, $tutor_id, $assigned_by) {
        $query = "INSERT INTO PersonalTutors (student_id, tutor_id, assigned_by) VALUES (?, ?, ?)";
        return $this->db->execute($query, [$student_id, $tutor_id, $assigned_by]);
    }

    // Cập nhật gia sư nếu đã có sẵn
    public function updateTutor($student_id, $tutor_id, $assigned_by) {
        $query = "UPDATE PersonalTutors SET tutor_id = ?, assigned_by = ? WHERE student_id = ?";
        return $this->db->execute($query, [$tutor_id, $assigned_by, $student_id]);
    }

    // Lấy thông tin gia sư cho sinh viên
    public function getTutorDetails($student_id) {
        $query = "SELECT u.user_id, u.first_name, u.last_name, 
                         IFNULL((SELECT COUNT(*) FROM PersonalTutors WHERE tutor_id = u.user_id), 0) AS total_students,
                         IFNULL((SELECT COUNT(*) FROM Messages WHERE sender_id = u.user_id), 0) AS total_messages
                  FROM Users u 
                  JOIN PersonalTutors pt ON u.user_id = pt.tutor_id
                  WHERE pt.student_id = ?";
        
        return $this->db->fetch($query, [$student_id]);
    }
}
