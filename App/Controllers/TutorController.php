<?php

use App\Core\Controller;
use App\Models\User;
use App\Models\PersonalTutor;

class TutorController extends Controller
{
    private $userModel;
    private $personalTutorModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->personalTutorModel = new PersonalTutor();
    }

    // Hiển thị danh sách sinh viên và gia sư
    public function assign()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'staff') {
            header("Location: ?url=home/index");
            exit;
        }

        
        $students = $this->userModel->getStudentsWithoutTutor();
        
        
        $tutors = $this->userModel->getAllTutors();

        $this->view('tutor/assign', ['students' => $students, 'tutors' => $tutors]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_ids'], $_POST['tutor_id'])) {
            $tutor_id = $_POST['tutor_id'];
            $student_ids = $_POST['student_ids']; // array of student_id
            $assigned_by = $_SESSION['user']['user_id']; //(staff)
    
            $personalTutorModel = new PersonalTutor();
    
            // Duyệt qua từng student_id để gán tutor
            foreach ($student_ids as $student_id) {
                $personalTutorModel->assignTutor($student_id, $tutor_id, $assigned_by);
            }
    
            // Redirect về trang assign với thông báo thành công
            header("Location: ?url=tutor/assign&success=1");
            exit();
        }
    
        // Nếu không có dữ liệu hợp lệ, báo lỗi
        header("Location: ?url=tutor/assign&error=1");
        exit();
    }
    

    public function dashboard() {
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'tutor') {
            header("Location: ?url=login");
            exit;
        }

        $tutor_id = $_SESSION['user']['id'];
        $tutees = $this->userModel->getTuteesByTutor($tutor_id);

        $this->view('tutor/dashboard', [
            'title' => 'My Tutees - Dashboard',
            'tutees' => $tutees
        ]);
    }
}
