<?php

use App\Core\Controller;
use App\Models\User;

class UserController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    private function requireStaffRole() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'staff') {
            header("Location: ?url=home/index"); // Chặn truy cập nếu không phải staff
            exit;
        }
        }
    }

    public function index() {
        // Kiểm tra nếu người dùng không đăng nhập hoặc không phải là staff
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'staff') {
        header("Location: ?url=home/index"); 
        exit;
    }

            $users = $this->userModel->getAllUsers();
            $data = [
                'title' => 'User Management',
                'users' => $users
                    ];
    $this->view('user/index', $data);
    }

    public function create() {
        $this->requireStaffRole();
        $this->view('user/create');
    }
    
    public function store() {
        $this->requireStaffRole();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $password = $_POST['password'];
    
            // Kiểm tra độ mạnh của mật khẩu
            if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password)) {
                $_SESSION['error'] = "Password must be at least 8 characters long and contain at least one uppercase letter.";
                header("Location: ?url=user/create");
                exit;
            }
    
            $data = [
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'email' => $_POST['email'],
                'password' => password_hash($password, PASSWORD_BCRYPT),
                'role' => $_POST['role']
            ];
    
            $this->userModel->createUser($data);
            $_SESSION['success'] = "User created successfully!";
            header("Location: ?url=user/index");
            exit;
        }
    }
    

    public function edit() {
        $this->requireStaffRole();
        $id = $_GET['id'];
        $user = $this->userModel->getUserById($id);
        $data = ['user' => $user];
        $this->view('user/edit', $data);
    }
    
    public function update() {
        $this->requireStaffRole();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_GET['id'];
            $data = [
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'email' => $_POST['email'],
                'role' => $_POST['role']
            ];
            $this->userModel->updateUser($id, $data);
            header("Location: ?url=user/index");
        }
    }

    public function delete() {
        $this->requireStaffRole();
        $id = $_GET['id'];
        $this->userModel->deleteUser($id);
        header("Location: ?url=user/index");
    }

    public function detail() {
        $this->requireStaffRole(); // Chỉ staff mới xem được
    
        if (!isset($_GET['id'])) {
            header("Location: ?url=user/index");
            exit;
        }
    
        $id = $_GET['id'];
        $user = $this->userModel->getUserById($id);
    
        if (!$user) {
            $_SESSION['error'] = "User not found.";
            header("Location: ?url=user/index");
            exit;
        }
    
        $data = ['title' => 'User Details', 'user' => $user];
        $this->view('user/detail', $data);
    }

}