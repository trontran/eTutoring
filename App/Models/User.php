<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    private $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    // // Lấy tất cả người dùng
    // public function getAllUsers() {
    //     $stmt = $this->pdo->prepare("SELECT * FROM users");
    //     $stmt->execute();
    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }

    // Lấy danh sách tất cả user
    public function getAllUsers() {
        $query = "SELECT * FROM Users ORDER BY created_at DESC";
        return $this->pdo->query($query)->fetchAll();
    }

    // Lấy 1 người dùng theo id
    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
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

    public function getUserByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
}