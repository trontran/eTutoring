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

        // Chỉ cho phép staff thực hiện chức năng này
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'staff') {
            header("Location: ?url=home/index");
            exit;
        }

        // Lấy danh sách sinh viên chưa có gia sư
        $students = $this->userModel->getStudentsWithoutTutor();
        
        // Lấy danh sách tất cả gia sư
        $tutors = $this->userModel->getAllTutors();

        $this->view('tutor/assign', ['students' => $students, 'tutors' => $tutors]);
    }

    // Xử lý việc gán gia sư
    public function store()
    {
        session_start();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_id'], $_POST['tutor_id'])) {
            $student_id = $_POST['student_id'];
            $tutor_id = $_POST['tutor_id'];
            $assigned_by = $_SESSION['user']['user_id'];

            // Kiểm tra nếu sinh viên đã có gia sư chưa
            $existingTutor = $this->personalTutorModel->getTutorByStudent($student_id);

            if ($existingTutor) {
                // Nếu đã có gia sư, cập nhật lại gia sư mới
                $this->personalTutorModel->updateTutor($student_id, $tutor_id, $assigned_by);
            } else {
                // Nếu chưa có, thêm mới
                $this->personalTutorModel->assignTutor($student_id, $tutor_id, $assigned_by);
            }

            header("Location: ?url=tutor/assign&success=1");
            exit;
        }
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
