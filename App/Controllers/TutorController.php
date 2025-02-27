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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'tutor') {
            header("Location: ?url=login");
            exit;
        }
    
        $tutorId = $_SESSION['user']['user_id'];
        $filter = $_GET['filter'] ?? "";  // Lấy giá trị tìm kiếm
        $sortBy = $_GET['sort_by'] ?? "assigned_at";  // Giá trị sắp xếp mặc định
    
        $tutees = $this->personalTutorModel->getTuteesByTutor($tutorId, $filter, $sortBy);
    
        $this->view('tutor/dashboard', [
            'tutees' => $tutees,
            'filter' => $filter,
            'sortBy' => $sortBy
        ]);
    }

    public function getTuteesByTutor($tutorId, $filter = "", $sortBy = "assigned_at") {
    $validSortColumns = ['first_name', 'email', 'assigned_at']; // Chỉ cho phép các giá trị hợp lệ
    if (!in_array($sortBy, $validSortColumns)) {
        $sortBy = "assigned_at"; // Mặc định nếu giá trị không hợp lệ
    }

    $sql = "SELECT u.user_id, u.first_name, u.last_name, u.email, pt.assigned_at 
            FROM PersonalTutors pt
            JOIN Users u ON pt.student_id = u.user_id
            WHERE pt.tutor_id = :tutorId";

    if (!empty($filter)) {
        $sql .= " AND (u.first_name LIKE :filter OR u.last_name LIKE :filter OR u.email LIKE :filter)";
    }

    $sql .= " ORDER BY $sortBy ASC";  // Sắp xếp theo tiêu chí được chọn

    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':tutorId', $tutorId, PDO::PARAM_INT);
    
    if (!empty($filter)) {
        $filter = "%$filter%";
        $stmt->bindParam(':filter', $filter, PDO::PARAM_STR);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}
