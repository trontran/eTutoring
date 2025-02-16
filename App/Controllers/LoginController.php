<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

class LoginController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    // Hiển thị form đăng nhập
    public function index() {
        $this->view('auth/login');
    }

    // Xử lý đăng nhập
    public function process() {
        session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $user = $this->userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user'] = [
                    'id' => $user['user_id'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];

                // Chuyển hướng đến dashboard hoặc home
                header("Location: /eTutoring/public/?url=user/index");
                exit;
            } else {
                $this->view('auth/login', ['error' => 'Invalid email or password']);
            }
        } else {
            header("Location: ?url=login");
            exit;
        }
    }

    public function logout() {
        session_start();
        
        // Xóa tất cả dữ liệu trong session
        session_unset();
        
        // Hủy bỏ session
        session_destroy();
    
        // Chuyển hướng về trang chủ
        header("Location: ?url=home/index");
        exit;
    }
}
