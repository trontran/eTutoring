<?php

use App\Core\Database;
use App\Core\Controller;
use App\Models\User;
use App\Models\PersonalTutor;


class UserController extends Controller
{


    private $db;
    private $userModel;
    private $personalTutor;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
        $this->userModel = new User();
        $this->personalTutor = new PersonalTutor();
    }

    private function requireStaffRole()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'staff') {
                header("Location: ?url=home/index");
                exit;
            }
        }
    }

    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }


        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'staff') {
            header("Location: ?url=home/index");
            exit;
        }


        $usersPerPage = 10;
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $offset = ($page - 1) * $usersPerPage;


        $users = $this->userModel->getPaginatedUsers($usersPerPage, $offset);
        $totalUsers = $this->userModel->getTotalUserCount();
        $totalPages = ceil($totalUsers / $usersPerPage);

        $isAdmin = $_SESSION['user']['role'] === 'staff';


        $data = [
            'title' => 'User Management',
            'users' => $users,
            'totalPages' => $totalPages,
            'currentPage' => $page,
            'isAdmin' => $isAdmin
        ];

        $this->view('user/index', $data);
    }


    public function create()
    {
        $this->requireStaffRole();
        $this->view('user/create');
    }

    public function store()
    {
        $this->requireStaffRole();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'];


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


    public function edit()
    {
        $this->requireStaffRole();
        $id = $_GET['id'];
        $user = $this->userModel->getUserById($id);
        $data = ['user' => $user];
        $this->view('user/edit', $data);
    }


    public function update()
    {
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

    public function delete()
    {
        $this->requireStaffRole();
        $id = $_GET['id'];
        $this->userModel->deleteUser($id);
        header("Location: ?url=user/index");
    }

    public function detail()
    {
        $this->requireStaffRole();

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

    public function storeReallocation()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }


        if (!isset($_SESSION['user']) || !isset($_SESSION['user']['user_id'])) {
            die("<h3 style='color: red;'> Error: User ID is not set in session.</h3>");
        }

        $this->requireStaffRole();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentId = $_POST['student_id'] ?? null;
            $newTutorId = $_POST['new_tutor_id'] ?? null;
            $assignedBy = $_SESSION['user']['user_id'];

            if (!$studentId || !$newTutorId) {
                header("Location: ?url=user/index&error=missing_data");
                exit;
            }


            $this->personalTutor->updateTutorAssignment($studentId, $newTutorId, $assignedBy);


            $this->personalTutor->reallocateTutor($studentId, $newTutorId, $assignedBy);

            // Set a flash message
            $_SESSION['success'] = "Tutor reassignment was successful!";
            // Redirect to the user index page
            header("Location: ?url=user/index");
            exit;
        }
    }

    public function reallocate()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user']['user_id'])) {
            die("<h3 style='color: red;'> Error: User ID is not set in session.</h3>");
        }

        $this->requireStaffRole();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $studentId = $_POST['student_id'] ?? null;
            $newTutorId = $_POST['new_tutor_id'] ?? null;
            $assignedBy = $_SESSION['user']['user_id'];

            if (!$studentId || !$newTutorId) {
                header("Location: ?url=user/index&error=missing_data");
                exit;
            }

            try {
                $this->personalTutor->reallocateTutor($studentId, $newTutorId, $assignedBy);
                header("Location: ?url=user/index&success=reallocated");
            } catch (\Exception $e) {
                die("<h3 style='color: red;'> Error: " . $e->getMessage() . "</h3>");
            }
            exit;
        }


        $studentId = $_GET['id'] ?? null;
        if (!$studentId) {
            header("Location: ?url=user/index&error=missing_student_id");
            exit;
        }

        $student = $this->userModel->getUserById($studentId);
        $tutors = $this->userModel->getTutors();

        if (!$student) {
            header("Location: ?url=user/index&error=student_not_found");
            exit;
        }

        $data = [
            'student' => $student,
            'tutors' => $tutors
        ];
        $this->view('user/reallocate', $data);
    }

    //test 25/feb function profile
    public function profile()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        require_once '../app/models/User.php';
        $userModel = new User();


        if (isset($_GET['id'])) {
            $userId = (int)$_GET['id'];
            $user = $userModel->getUserById($userId);
        } else {

            $user = $_SESSION['user'];
        }

        if (!$user) {
            die("User not found.");
        }

        $this->view('user/profile', ['user' => $user]);
    }

}
    
    
    
    
