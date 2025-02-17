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



}