<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }


    public function getAllUsers() {
        $query = "SELECT * FROM Users ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function createUser($data): bool
    {
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



    public function getUserById($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function updateUser($id, $data): bool
    {
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


    public function deleteUser($id): bool
    {
        $query = "DELETE FROM Users WHERE user_id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([":id" => $id]);
    }


    public function getUserByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM Users WHERE email = :email LIMIT 1");
        $stmt->execute([":email" => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function getTutorByStudentId($studentId) {
        $query = "SELECT u.user_id AS tutor_id, u.first_name, u.last_name, 
                         t.total_students, t.total_messages 
                  FROM Users u
                  INNER JOIN PersonalTutors p ON u.user_id = p.tutor_id
                  LEFT JOIN tutordashboard t ON u.user_id = t.tutor_id
                  WHERE p.student_id = :student_id";

        $stmt = $this->db->prepare($query);
        $stmt->execute([":student_id" => $studentId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function getStudentsWithoutTutor(): array
    {
        $query = "SELECT u.user_id, u.first_name, u.last_name 
                  FROM Users u
                  LEFT JOIN PersonalTutors pt ON u.user_id = pt.student_id
                  WHERE u.role = 'student' AND pt.tutor_id IS NULL";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getAllTutors(): array
    {
        $query = "SELECT user_id, first_name, last_name FROM Users WHERE role = 'tutor'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTuteesByTutor($tutor_id): array
    {
        $query = "SELECT u.user_id, u.first_name, u.last_name, u.email
                  FROM Users u
                  INNER JOIN PersonalTutors pt ON u.user_id = pt.student_id
                  WHERE pt.tutor_id = :tutor_id";
    
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':tutor_id', $tutor_id, PDO::PARAM_INT);
        $stmt->execute();
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTutors(): array
    {
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

    public function getTutorId($studentId) {
        $stmt = $this->db->prepare("SELECT tutor_id FROM PersonalTutors WHERE student_id = :studentId");
        $stmt->bindParam(':studentId', $studentId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllStudents(): array
    {
        $query = "SELECT user_id, first_name, last_name, email FROM Users WHERE role = 'student'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    //test new function for sprint 6

    /**
     * Update the user's login timestamps
     * @param int $userId User ID
     * @return bool Success status
     */
    public function updateLoginTimestamps(int $userId): bool
    {
        $currentTime = date('Y-m-d H:i:s');

        // First get the current last_login value
        $query = "SELECT last_login FROM Users WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $lastLogin = $stmt->fetchColumn();

        // Now update both timestamps
        $query = "UPDATE Users SET 
              previous_login = :last_login,
              last_login = :current_time 
              WHERE user_id = :user_id";

        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':last_login', $lastLogin, PDO::PARAM_STR);
        $stmt->bindParam(':current_time', $currentTime, PDO::PARAM_STR);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Get the previous login time for a user
     * @param int $userId User ID
     * @return string|null Previous login timestamp or null if first login
     */
    public function getPreviousLoginTime(int $userId): ?string
    {
        $query = "SELECT previous_login FROM Users WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

}
