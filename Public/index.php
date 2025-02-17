<?php

use App\Controllers\LoginController;

session_start();

// Require core, controllers, and models
require_once '../app/core/Controller.php';
require_once '../app/core/Database.php';
require_once '../app/controllers/HomeController.php';
require_once '../app/controllers/UserController.php';
require_once '../app/controllers/LoginController.php';
require_once '../app/models/User.php';

// Lấy URL từ query string, ví dụ: ?url=user/index
$url = isset($_GET['url']) ? $_GET['url'] : '';

// Nếu không có url (hoặc = 'home/index'), ta gọi HomeController
if ($url === '' || $url === 'home/index') {
    $controller = new HomeController();
    $controller->index();
// Hiển thị Home (Dashboard)
} elseif ($url === '' || $url === 'home/index') {
    require_once '../app/controllers/HomeController.php';
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

// Nếu không khớp route nào, báo 404
} else {
    echo "404 Not Found or Route not handled yet.";
}
