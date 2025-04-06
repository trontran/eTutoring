<?php

use App\Core\Controller;
use App\Models\EmailQueue;
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
        //test function email queue status , this is main function of the system.
//    public function store() {
//        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_ids'], $_POST['tutor_id'])) {
//            $tutor_id = $_POST['tutor_id'];
//            $student_ids = $_POST['student_ids']; // array of student_id
//            $assigned_by = $_SESSION['user']['user_id']; //(staff)
//
//            $personalTutorModel = new PersonalTutor();
//
//
//            foreach ($student_ids as $student_id) {
//                $personalTutorModel->assignTutor($student_id, $tutor_id, $assigned_by);
//            }
//
//
//            header("Location: ?url=tutor/assign&success=1");
//            exit();
//        }
//
//
//        header("Location: ?url=tutor/assign&error=1");
//        exit();
//    }



    public function dashboard() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    
        if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'tutor') {
            header("Location: ?url=login");
            exit;
        }
    
        $tutorId = $_SESSION['user']['user_id'];
        $filter = $_GET['filter'] ?? "";
        $sortBy = $_GET['sort_by'] ?? "assigned_at";
    
        $tutees = $this->personalTutorModel->getTuteesByTutor($tutorId, $filter, $sortBy);
    
        $this->view('tutor/dashboard', [
            'tutees' => $tutees,
            'filter' => $filter,
            'sortBy' => $sortBy
        ]);
    }

    public function getTuteesByTutor($tutorId, $filter = "", $sortBy = "assigned_at") {
    $validSortColumns = ['first_name', 'email', 'assigned_at'];
    if (!in_array($sortBy, $validSortColumns)) {
        $sortBy = "assigned_at";
    }

    $sql = "SELECT u.user_id, u.first_name, u.last_name, u.email, pt.assigned_at 
            FROM PersonalTutors pt
            JOIN Users u ON pt.student_id = u.user_id
            WHERE pt.tutor_id = :tutorId";

    if (!empty($filter)) {
        $sql .= " AND (u.first_name LIKE :filter OR u.last_name LIKE :filter OR u.email LIKE :filter)";
    }

    $sql .= " ORDER BY $sortBy ASC";

    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':tutorId', $tutorId, PDO::PARAM_INT);
    
    if (!empty($filter)) {
        $filter = "%$filter%";
        $stmt->bindParam(':filter', $filter, PDO::PARAM_STR);
    }

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    //test function email queue status

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['student_ids'], $_POST['tutor_id'])) {
            $tutor_id = $_POST['tutor_id'];
            $student_ids = $_POST['student_ids']; // array of student_id
            $assigned_by = $_SESSION['user']['user_id']; // (staff)

            $personalTutorModel = new PersonalTutor();
            $emailQueue = new EmailQueue();
            $userModel = new User();

            // Get tutor information once
            $tutor = $userModel->getUserById($tutor_id);
            $studentsAssigned = 0;

            // Do all database operations first without sending emails
            foreach ($student_ids as $student_id) {
                if ($personalTutorModel->assignTutorWithoutEmail($student_id, $tutor_id, $assigned_by)) {
                    $studentsAssigned++;

                    // Get student info
                    $student = $userModel->getUserById($student_id);

                    // Queue student email
                    $studentSubject = "Your New Tutor Assignment - eTutoring System";
                    $studentBody = "
                <p>Dear {$student['first_name']},</p>
                <p>We are pleased to inform you that you have been assigned a new personal tutor to support your learning journey.</p>
                
                <p><strong>Tutor Details:</strong></p>
                <ul>
                    <li><strong>Name:</strong> {$tutor['first_name']} {$tutor['last_name']}</li>
                    <li><strong>Email:</strong> {$tutor['email']}</li>
                </ul>
            
                <p>Your tutor will assist you with academic guidance and support. Feel free to reach out to them if you need any help.</p>
            
                <p>Best regards,</p>
                <p><strong>eTutoring Team</strong></p>
                <hr>
                <p style='font-size:12px; color:gray;'>This is an automated message, please do not reply to this email.</p>";

                    $emailQueue->addToQueue($student['email'], $studentSubject, $studentBody);
                }
            }

            // Queue a single summary email to tutor if any students were assigned
            if ($studentsAssigned > 0) {
                // Create a list of all students for the tutor email
                $studentList = "";
                foreach ($student_ids as $student_id) {
                    $student = $userModel->getUserById($student_id);
                    $studentList .= "<li><strong>Name:</strong> {$student['first_name']} {$student['last_name']} - <strong>Email:</strong> {$student['email']}</li>\n";
                }

                $tutorSubject = "New Students Assigned - eTutoring System";
                $tutorBody = "
            <p>Dear {$tutor['first_name']},</p>
            <p>We are pleased to inform you that you have been assigned {$studentsAssigned} new students in the eTutoring system.</p>
            
            <p><strong>Student Details:</strong></p>
            <ul>
                $studentList
            </ul>
        
            <p>Please reach out to your students soon to introduce yourself and discuss their learning needs.</p>
        
            <p>Best regards,</p>
            <p><strong>eTutoring Team</strong></p>
            <hr>
            <p style='font-size:12px; color:gray;'>This is an automated message, please do not reply to this email.</p>";

                $emailQueue->addToQueue($tutor['email'], $tutorSubject, $tutorBody);
            }

            // Store the count of emails in session for the success page
            $_SESSION['emails_queued'] = $studentsAssigned + ($studentsAssigned > 0 ? 1 : 0); // Students + tutor
            $_SESSION['success'] = "{$studentsAssigned} students have been assigned to the tutor. Emails have been queued for sending.";

            header("Location: ?url=tutor/assign&success=1");
            exit();
        }

        header("Location: ?url=tutor/assign&error=1");
        exit();
    }

}
