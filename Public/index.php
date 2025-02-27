<?php

use App\Controllers\LoginController;
use App\Controllers\MessageController;

session_start();

// Require core, controllers, and models
require_once '../app/core/Controller.php';
require_once '../app/core/Database.php';
require_once '../app/controllers/HomeController.php';
require_once '../app/controllers/UserController.php';
require_once '../app/controllers/LoginController.php';
require_once '../app/controllers/TutorController.php';
require_once '../app/models/User.php';
require_once '../app/models/PersonalTutor.php';
require_once '../app/controllers/MessageController.php';
require_once '../app/models/Message.php';
require_once '../app/models/Notification.php';

// Lấy URL từ query string, ví dụ: ?url=user/index
$url = isset($_GET['url']) ? $_GET['url'] : '';

// Nếu không có url (hoặc = 'home/index'), ta gọi HomeController
if ($url === '' || $url === 'home/index') {
    $controller = new HomeController();
    $controller->index();

// Route cho Login
} elseif ($url === 'login') {
    $controller = new LoginController();
    $controller->index();

// Xử lý Login (POST)
} elseif ($url === 'login/process') {
    $controller = new LoginController();
    $controller->process();

// Xử lý Logout
} elseif ($url === 'logout') {
    $controller = new LoginController();
    $controller->logout();

// Route cho Register (tạm thời chưa triển khai)
} elseif ($url === 'register') {
    echo "Register page not implemented yet.";

// Danh sách user
} elseif ($url === 'user/index') {
    $controller = new UserController();
    $controller->index();

// Hiển thị form thêm user
} elseif ($url === 'user/create') {
    $controller = new UserController();
    $controller->create();

// Xử lý lưu user (POST)
} elseif ($url === 'user/store') {
    $controller = new UserController();
    $controller->store();

// Hiển thị form edit user
} elseif ($url === 'user/edit') {
    $controller = new UserController();
    $controller->edit();

// Xử lý update user (POST)
} elseif ($url === 'user/update') {
    $controller = new UserController();
    $controller->update();

// Xoá user
} elseif ($url === 'user/delete') {
    $controller = new UserController();
    $controller->delete();
// Route cho Assign Tutor (Chỉ dành cho staff)
} elseif ($url === 'tutor/assign') {
    $controller = new TutorController();
    $controller->assign();

// Xử lý gán tutor (POST)
} elseif ($url === 'tutor/store') {
    require_once '../app/controllers/TutorController.php';
    $controller = new TutorController();
    $controller->store();
// Route cho Tutor Dashboard
} elseif ($url === 'tutor/dashboard') {
    require_once '../app/controllers/TutorController.php';
    $controller = new TutorController();
    $controller->dashboard();
} elseif ($url === 'user/detail') {
    $controller = new UserController();
    $controller->detail();
// Hiển thị form reallocate tutor
} elseif ($url === 'user/reallocate') {
    $controller = new UserController();
    $controller->reallocate();
// Xử lý lưu reallocation (POST)
} elseif ($url === 'user/storeReallocation') {
    $controller = new UserController();
    $controller->storeReallocation();
} elseif ($url === 'tutor/tutee_list') {
    $controller = new TutorController();
    $controller->tuteeList();
} elseif ($url === 'tutor/dashboard') {
    $controller = new TutorController();
    $controller->dashboard();
} elseif ($url === 'user/profile') {
    $controller = new UserController();
    $controller->profile();
    //test
} elseif ($url === 'message/chat') {
    $controller = new MessageController();
    $controller->chat();
} elseif ($url === 'message/send') {
        $controller = new MessageController();
        $controller->send();
} elseif ($url === 'message/chatList') {
    $controller = new MessageController();
    $controller->chatList();
} else {
    echo "404 Not Found or Route not handled yet.";
}
