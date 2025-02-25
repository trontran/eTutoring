<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Lấy danh sách tất cả user
    public function getAllUsers() {
        $query = "SELECT * FROM Users ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Tạo user mới
    public function createUser($data) {
        $query = "INSERT INTO Users (first_name, last_name, email, password_hash, role) 
                  VALUES (:first_name, :last_name, :email, :password_hash, :role)";
        $stmt = $this->db->prepare($query);

        return $stmt->execute([
            ":first_name" => $data['first_name'],
            ":last_name" => $data['last_name'],
            ":email" => $data['email'],
            ":password_hash" => $data['password'],
            ":role" => $data['role']
        ]);
    }

    // Lấy thông tin user theo ID

    public function getUserById($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Cập nhật thông tin user
    public function updateUser($id, $data) {
        $query = "UPDATE Users SET first_name = :first_name, last_name = :last_name, 
                  email = :email, role = :role WHERE user_id = :id";
        $stmt = $this->db->prepare($query);

        return $stmt->execute([
            ":first_name" => $data['first_name'],
            ":last_name" => $data['last_name'],
            ":email" => $data['email'],
            ":role" => $data['role'],
            ":id" => $id
        ]);
    }

    // Xóa user
    public function deleteUser($id) {
        $query = "DELETE FROM Users WHERE user_id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([":id" => $id]);
    }

    // Lấy thông tin user theo email
    public function getUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE email = :email LIMIT 1");
        $stmt->execute([":email" => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Lấy thông tin gia sư của sinh viên
    public function getTutorByStudentId($studentId) {
        $query = "SELECT u.user_id AS tutor_id, u.first_name, u.last_name, 
                         t.total_students, t.total_messages 
                  FROM Users u
                  INNER JOIN PersonalTutors p ON u.user_id = p.tutor_id
                  LEFT JOIN tutordashboard t ON u.user_id = t.tutor_id
                  WHERE p.student_id = :student_id";

        $stmt = $this->db->prepare($query);
        $stmt->execute([":student_id" => $studentId]);

        return $stmt->fetch(PDO::FETCH_ASSOC); // Trả về thông tin tutor
    }

    // Lấy danh sách sinh viên chưa có gia sư
    public function getStudentsWithoutTutor() {
        $query = "SELECT u.user_id, u.first_name, u.last_name 
                  FROM Users u
                  LEFT JOIN PersonalTutors pt ON u.user_id = pt.student_id
                  WHERE u.role = 'student' AND pt.tutor_id IS NULL";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Lấy danh sách tất cả gia sư
    public function getAllTutors() {
        $query = "SELECT user_id, first_name, last_name FROM Users WHERE role = 'tutor'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTuteesByTutor($tutor_id) {
        $query = "SELECT u.user_id, u.first_name, u.last_name, u.email
                  FROM Users u
                  INNER JOIN PersonalTutors pt ON u.user_id = pt.student_id
                  WHERE pt.tutor_id = :tutor_id";
    
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tutor_id', $tutor_id, PDO::PARAM_INT);
        $stmt->execute();
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTutors() {
        $query = "SELECT user_id, first_name, last_name, email FROM Users WHERE role = 'tutor'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

    public function emailExists($email) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ? true : false;
    }



    //test

    public function getTuteesByTutorSorted($tutorId, $sort = 'assigned_at', $order = 'DESC') {
        $validSortColumns = ['first_name', 'last_name', 'email', 'assigned_at'];
        $sort = in_array($sort, $validSortColumns) ? $sort : 'assigned_at';
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT u.user_id, u.first_name, u.last_name, u.email, pt.assigned_at 
                FROM PersonalTutors pt
                JOIN Users u ON pt.student_id = u.user_id
                WHERE pt.tutor_id = :tutorId
                ORDER BY $sort $order";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(":tutorId", $tutorId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getPaginatedUsers($limit, $offset): array
    {
        $query = "SELECT * FROM Users ORDER BY user_id ASC LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalUserCount() {
        $query = "SELECT COUNT(*) FROM Users";
        return $this->db->query($query)->fetchColumn();
    }
}
