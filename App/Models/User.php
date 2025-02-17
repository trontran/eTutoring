<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // Lấy danh sách tất cả user
    public function getAllUsers() {
        $query = "SELECT * FROM Users ORDER BY created_at DESC";
        return $this->pdo->query($query)->fetchAll();
    }

    public function createUser($data) {
        $query = "INSERT INTO Users (first_name, last_name, email, password_hash, role) 
                  VALUES (:first_name, :last_name, :email, :password_hash, :role)";

        $stmt = $this->pdo->prepare($query);

        $stmt->bindParam(":first_name", $data['first_name']);
        $stmt->bindParam(":last_name", $data['last_name']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":password_hash", $data['password']);
        $stmt->bindParam(":role", $data['role']);

        return $stmt->execute();
    }

    // Lấy thông tin user theo ID
    public function getUserById($id) {
        $query = "SELECT * FROM Users WHERE user_id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    // Cập nhật thông tin user
    public function updateUser($id, $data) {
        $query = "UPDATE Users SET first_name = :first_name, last_name = :last_name, 
                  email = :email, role = :role WHERE user_id = :id";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(":first_name", $data['first_name']);
        $stmt->bindParam(":last_name", $data['last_name']);
        $stmt->bindParam(":email", $data['email']);
        $stmt->bindParam(":role", $data['role']);
        $stmt->bindParam(":id", $id);

        return $stmt->execute();
    }

    public function deleteUser($id) {
        $query = "DELETE FROM Users WHERE user_id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }

    public function getUserByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
}