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
        header("Location: ?url=home/index"); // Chuyển hướng về home
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
            $data = [
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'email' => $_POST['email'],
                'password' => password_hash($_POST['password'], PASSWORD_BCRYPT),
                'role' => $_POST['role']
            ];
            $this->userModel->createUser($data);
            header("Location: ?url=user/index");
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

}