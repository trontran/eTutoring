<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;

//class LoginController extends Controller
//{
//    private $userModel;
//
//    public function __construct()
//    {
//        $this->userModel = new User();
//    }
//
//    // display form login
//    public function index()
//    {
//        $this->view('auth/login');
//    }
//
//    // login process
//    public function process()
//    {
//        if (session_status() === PHP_SESSION_NONE) {
//            session_start();
//        }
//
//        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//            $email = $_POST['email'];
//            $password = $_POST['password'];
//
//            $user = $this->userModel->getUserByEmail($email);
//
//            if ($user && password_verify($password, $user['password_hash'])) {
//                $_SESSION['user'] = [
//                    'id' => $user['user_id'],
//                    'first_name' => $user['first_name'],
//                    'last_name' => $user['last_name'],
//                    'email' => $user['email'],
//                    'role' => $user['role']
//                ];
//
//                // Chuyển hướng đến dashboard hoặc home
//                header("Location: /eTutoring/public/?url=user/index");
//                exit;
//            } else {
//                $this->view('auth/login', ['error' => 'Invalid email or password']);
//            }
//        } else {
//            header("Location: ?url=login");
//            exit;
//        }
//    }
//
//    public function logout()
//    {
//        session_start();
//
//        // Xóa tất cả dữ liệu trong session
//        session_unset();
//
//        // Hủy bỏ session
//        session_destroy();
//
//        // Chuyển hướng về trang chủ
//        header("Location: ?url=home/index");
//        exit;
//    }
//}

class LoginController extends Controller
{
    private $userModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->userModel = new User();
    }

    //Show the login form
    public function index()
    {
        $this->view('auth/login');
    }


//    public function process()
//    {
//        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//            $email = $_POST['email'];
//            $password = $_POST['password'];
//
//            $user = $this->userModel->getUserByEmail($email);
//
//            if ($user && password_verify($password, $user['password_hash'])) {
//                // Lưu session với key đúng
//                $_SESSION['user'] = [
//                    'user_id' => $user['user_id'], // Đổi 'id' thành 'user_id'
//                    'first_name' => $user['first_name'],
//                    'last_name' => $user['last_name'],
//                    'email' => $user['email'],
//                    'role' => $user['role']
//                ];
//                // Chuyển hướng đến dashboard
//                header("Location: /eTutoring/public/?url=user/index");
//                exit;
//            } else {
//                $this->view('auth/login', ['error' => 'Invalid email or password']);
//            }
//        } else {
//            header("Location: ?url=login");
//            exit;
//        }
//    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }


        $_SESSION = [];

        if (isset($_SESSION['user']) && isset($_SESSION['user']['user_id'])) {
            $activityTracker = new \App\Models\ActivityTracker();
            $activityTracker->trackUserActivity($_SESSION['user']['user_id'], 'logout');
        }

        session_destroy();


        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }


        header("Location: ?url=home/index");
        exit;
    }

    //test new function for sprint 6
    public function process(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            $user = $this->userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Get previous login time before updating it
                $previousLogin = $this->userModel->getPreviousLoginTime($user['user_id']);

                // Update login timestamps
                $this->userModel->updateLoginTimestamps($user['user_id']);

                // Store previous login time in session for display
                $_SESSION['previous_login'] = $previousLogin;

                // Original login processing continues...
                $_SESSION['user'] = [
                    'user_id' => $user['user_id'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];
                // Track login activity
                $activityTracker = new \App\Models\ActivityTracker();
                $activityTracker->trackUserActivity($user['user_id'], 'login');
                header("Location: ?url=home/index");
                exit;
            }

            $this->view('auth/login', ['error' => 'Invalid email or password']);
        } else {
            header("Location: ?url=login");
            exit;
        }
    }
}