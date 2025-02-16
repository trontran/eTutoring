<?php

use App\Core\Controller;
use App\Models\User;

class UserController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function index() {
        $users = $this->userModel->getAllUsers(); // Gọi dữ liệu từ model
    
        $data = [
            'title' => 'User Management',
            'users' => $users // Đảm bảo biến users được truyền vào
        ];
    
        $this->view('user/index', $data);
    }
    

    // Hiển thị form thêm người dùng mới
    public function create() {
        $data = [
            'title' => 'Add New User'
        ];
        $this->view('user/create', $data);
    }

    // Xử lý lưu dữ liệu người dùng mới (POST)
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $data = [
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'password' => $_POST['password'],
                'role' => $_POST['role']
            ];
            $this->userModel->createUser($data);
            header("Location: /eTutoring/public/?url=user/index");

        }
    }

    // Hiển thị form chỉnh sửa thông tin người dùng
    // Lấy id từ query string: ví dụ /user/edit?id=1
    public function edit() {
        $id = $_GET['id'];
        $user = $this->userModel->getUserById($id);
        $data = [
            'title' => 'Edit User',
            'user' => $user
        ];
        $this->view('user/edit', $data);
    }

    // Xử lý cập nhật thông tin người dùng (POST)
    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_GET['id'];
            $data = [
                'username' => $_POST['username'],
                'email' => $_POST['email'],
                'password' => $_POST['password'], // Nếu không muốn thay đổi, để trống
                'role' => $_POST['role']
            ];
            $this->userModel->updateUser($id, $data);
            header("Location: /eTutoring/public/?url=user/index");

        }
    }

    // Xoá người dùng
    public function delete() {
        $id = $_GET['id'];
        $this->userModel->deleteUser($id);
        header("Location: /eTutoring/public/?url=user/index");

    }
}