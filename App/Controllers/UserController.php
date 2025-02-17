<?php

use App\Core\Controller;
use App\Models\User;

class UserController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function index() {
        // Retrieve all user records from the database using the User model
        $users = $this->userModel->getAllUsers();
    
        $data = [
            'title' => 'User Management',
            'users' => $users 
        ];
    
        $this->view('user/index', $data);
    }

    // Display form to add a new user
    public function create() {
        $this->view('user/create', ['title' => 'Add New User']);
    }

    // Handle storing user data
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'email' => $_POST['email'],
                'password' => password_hash($_POST['password'], PASSWORD_DEFAULT), // encrypt password
                'role' => $_POST['role']
            ];

            if ($this->userModel->createUser($data)) {
                header("Location: ?url=user/index");
                exit;
            } else {
                $this->view('user/create', ['error' => 'Failed to create user.']);
            }
        }
    }

     // Hiển thị form chỉnh sửa user
     public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header("Location: ?url=user/index");
            exit;
        }

        $user = $this->userModel->getUserById($id);
        if (!$user) {
            header("Location: ?url=user/index");
            exit;
        }

        $this->view('user/edit', ['title' => 'Edit User', 'user' => $user]);
    }

    // Xử lý cập nhật user
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['user_id'];
            $data = [
                'first_name' => $_POST['first_name'],
                'last_name' => $_POST['last_name'],
                'email' => $_POST['email'],
                'role' => $_POST['role']
            ];

            if ($this->userModel->updateUser($id, $data)) {
                header("Location: ?url=user/index");
                exit;
            } else {
                $this->view('user/edit', ['error' => 'Failed to update user.', 'user' => $data]);
            }
        }
    }

    public function delete() {
        if (isset($_GET['id'])) {
            $id = $_GET['id'];
            if ($this->userModel->deleteUser($id)) {
                header("Location: ?url=user/index");
                exit;
            } else {
                echo "Error: Failed to delete user.";
            }
        } else {
            echo "Error: User ID is missing.";
        }
    }

}