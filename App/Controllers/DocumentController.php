<?php

use App\Core\Controller;
use App\Models\Document;
use App\Models\DocumentComment;
use App\Models\Notification;

class DocumentController extends Controller
{
    private $documentModel;
    private $documentCommentModel;
    private $notificationModel;

    public function __construct()
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Initialize models
        $this->documentModel = new Document();
        $this->documentCommentModel = new DocumentComment();
        $this->notificationModel = new Notification();
    }

    /**
     * Display document upload form
     */
    public function upload()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        $userId = $_SESSION['user']['user_id'];
        $userRole = $_SESSION['user']['role'];

        // Prepare data for the view based on user role
        $data = [];

        // If tutor, show a list of tutees to select for upload
        if ($userRole === 'tutor') {
            // Get all tutees for this tutor
            require_once '../app/models/User.php';
            $userModel = new \App\Models\User();
            $tutees = $userModel->getTuteesByTutor($userId);
            $data['tutees'] = $tutees;
        }
        // If student, get tutor info
        else if ($userRole === 'student') {
            require_once '../app/models/PersonalTutor.php';
            $personalTutorModel = new \App\Models\PersonalTutor();
            $tutor = $personalTutorModel->getTutorDetails($userId);
            $data['tutor'] = $tutor;
        }

        $this->view('document/upload', $data);
    }

    /**
     * Process document upload
     */
    public function store()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?url=document/upload");
            exit;
        }

        $uploaderId = $_SESSION['user']['user_id'];
        $userRole = $_SESSION['user']['role'];

        // Get the other party ID (student/tutor)
        if ($userRole === 'student') {
            $studentId = $uploaderId;
            $tutorId = $_POST['tutor_id'] ?? null;
        } else { // tutor
            $tutorId = $uploaderId;
            $studentId = $_POST['student_id'] ?? null;
        }

        // Validate input
        if (!$studentId || !$tutorId) {
            $_SESSION['error'] = "Missing required information.";
            header("Location: ?url=document/upload");
            exit;
        }

        // Check if any files were uploaded
        if (!isset($_FILES['document']) || empty($_FILES['document']['name'][0])) {
            $_SESSION['error'] = "No files were uploaded. Please select at least one file.";
            header("Location: ?url=document/upload");
            exit;
        }

        // Create upload directory if it doesn't exist
        $uploadDir = '../uploads/document/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Define allowed file types and maximum size
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
        $maxSize = 10 * 1024 * 1024; // 10 MB

        // Track success and errors
        $successCount = 0;
        $errorFiles = [];

        // Process each uploaded file
        $fileCount = count($_FILES['document']['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            // Skip if no file was uploaded in this slot
            if ($_FILES['document']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            // Check for upload errors
            if ($_FILES['document']['error'][$i] !== UPLOAD_ERR_OK) {
                $errorFiles[] = $_FILES['document']['name'][$i] . " (Upload error)";
                continue;
            }

            $fileName = $_FILES['document']['name'][$i];
            $fileType = $_FILES['document']['type'][$i];
            $fileSize = $_FILES['document']['size'][$i];
            $fileTmpName = $_FILES['document']['tmp_name'][$i];

            // Validate file type
            if (!in_array($fileType, $allowedTypes)) {
                $errorFiles[] = $fileName . " (Invalid file type)";
                continue;
            }

            // Validate file size
            if ($fileSize > $maxSize) {
                $errorFiles[] = $fileName . " (Exceeds size limit of 10MB)";
                continue;
            }

            // Generate unique filename
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $uniqueFileName = uniqid('doc_') . '.' . $extension;
            $filePath = $uploadDir . $uniqueFileName;

            // Move the uploaded file
            if (!move_uploaded_file($fileTmpName, $filePath)) {
                $errorFiles[] = $fileName . " (Failed to save file)";
                continue;
            }

            // Save document info to database
            $documentData = [
                'uploader_id' => $uploaderId,
                'student_id' => $studentId,
                'tutor_id' => $tutorId,
                'file_path' => 'uploads/document/' . $uniqueFileName,
                'file_name' => $fileName,
                'file_type' => $fileType,
                'file_size' => $fileSize
            ];

            $documentId = $this->documentModel->createDocument($documentData);

            if ($documentId) {
                $successCount++;

                // Create notification for the other party
                $notificationReceiverId = ($userRole === 'student') ? $tutorId : $studentId;
                $senderName = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];

                $notificationText = "$senderName has uploaded a new document: " . htmlspecialchars($fileName);
                $this->notificationModel->createNotification($notificationReceiverId, $notificationText);
            } else {
                $errorFiles[] = $fileName . " (Database error)";
            }
        }

        // Set appropriate session messages
        if ($successCount > 0) {
            $_SESSION['success'] = "$successCount document(s) uploaded successfully.";
        }

        if (!empty($errorFiles)) {
            $_SESSION['error'] = "Failed to upload " . count($errorFiles) . " file(s): " . implode(", ", $errorFiles);
        }

        header("Location: ?url=document/list");
        exit;
    }
    /**
     * List document for the current user
     */
    public function list(): void
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        $userId = $_SESSION['user']['user_id'];
        $userRole = $_SESSION['user']['role'];
//        // Debug session info
//        echo "User ID: $userId, Role: $userRole<br>";
        // Get document based on role
        if ($userRole === 'student') {
            $documents = $this->documentModel->getDocumentsByStudentId($userId);
        } else if ($userRole === 'tutor') {
            $documents = $this->documentModel->getDocumentsByTutorId($userId);
        } else if ($userRole === 'staff') {
            // For staff, get all document or document based on search criteria
            $searchTerm = $_GET['search'] ?? '';
            $documents = $this->documentModel->getAllDocuments($searchTerm);
        }
//        // Debug documents
//        echo "<pre>";
//        print_r($documents);
//        echo "</pre>";
        $data = [
            'documents' => $documents,
            'userRole' => $userRole
        ];
//        echo '<pre>';
//        print_r($documents);
//        echo '</pre>';
//        die();
        $this->view('document/list', $data);
    }

    /**
     * View a specific document with comments
     */
    public function viewDetails()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        $documentId = $_GET['id'] ?? null;

        if (!$documentId) {
            header("Location: ?url=document/list");
            exit;
        }

        $document = $this->documentModel->getDocumentById($documentId);

        if (!$document) {
            $_SESSION['error'] = "Document not found.";
            header("Location: ?url=document/list");
            exit;
        }

        // Check if user has access to this document
        $userId = $_SESSION['user']['user_id'];
        $userRole = $_SESSION['user']['role'];

        if ($userRole !== 'staff' &&
            $document['student_id'] != $userId &&
            $document['tutor_id'] != $userId &&
            $document['uploader_id'] != $userId) {
            $_SESSION['error'] = "You don't have permission to view this document.";
            header("Location: ?url=document/list");
            exit;
        }

        // Get comments for this document
        $comments = $this->documentCommentModel->getCommentsByDocumentId($documentId);
        // Debug comments
        error_log('Comments count: ' . count($comments));
        error_log('Comments data: ' . print_r($comments, true));
        $data = [
            'document' => $document,  // Make sure this is named 'document'
            'comments' => $comments,  // Make sure this is named 'comments'
            'userRole' => $userRole,
            'userId' => $userId
        ];

        $this->view('document/view', $data);
    }

    /**
     * Add a comment to a document
     */
    public function comment()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?url=document/list");
            exit;
        }

        $documentId = $_POST['document_id'] ?? null;
        $commentText = $_POST['comment_text'] ?? null;

        if (!$documentId || !$commentText) {
            $_SESSION['error'] = "Missing required information.";
            header("Location: ?url=document/view&id=" . $documentId);
            exit;
        }

        $commenterId = $_SESSION['user']['user_id'];

        // Save comment
        $commentData = [
            'document_id' => $documentId,
            'commenter_id' => $commenterId,
            'comment_text' => $commentText
        ];

        $commentId = $this->documentCommentModel->createComment($commentData);

        if ($commentId) {
            // Get document details for notification
            $document = $this->documentModel->getDocumentById($documentId);

            // Determine who should receive the notification
            $notificationReceiverId = null;

            if ($commenterId == $document['student_id']) {
                // Student commented, notify tutor
                $notificationReceiverId = $document['tutor_id'];
            } else if ($commenterId == $document['tutor_id']) {
                // Tutor commented, notify student
                $notificationReceiverId = $document['student_id'];
            } else if ($commenterId != $document['uploader_id']) {
                // Someone else commented, notify uploader
                $notificationReceiverId = $document['uploader_id'];
            }

            if ($notificationReceiverId) {
                $commenterName = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
                $documentName = $document['file_name'];

                $notificationText = "$commenterName commented on document: " . htmlspecialchars($documentName);
                $this->notificationModel->createNotification($notificationReceiverId, $notificationText);
            }

            $_SESSION['success'] = "Comment added successfully.";
        } else {
            $_SESSION['error'] = "Failed to add comment. Please try again.";
        }

        header("Location: ?url=document/view&id=" . $documentId);
        exit;
    }

    /**
     * Download a document
     */
    public function download()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        $documentId = $_GET['id'] ?? null;

        if (!$documentId) {
            header("Location: ?url=document/list");
            exit;
        }

        $document = $this->documentModel->getDocumentById($documentId);

        if (!$document) {
            $_SESSION['error'] = "Document not found.";
            header("Location: ?url=document/list");
            exit;
        }

        // Check if user has access to this document
        $userId = $_SESSION['user']['user_id'];
        $userRole = $_SESSION['user']['role'];

        if ($userRole !== 'staff' &&
            $document['student_id'] != $userId &&
            $document['tutor_id'] != $userId &&
            $document['uploader_id'] != $userId) {
            $_SESSION['error'] = "You don't have permission to download this document.";
            header("Location: ?url=document/list");
            exit;
        }

        $filePath = "../" . $document['file_path'];

        if (!file_exists($filePath)) {
            $_SESSION['error'] = "File not found on the server.";
            header("Location: ?url=document/view&id=" . $documentId);
            exit;
        }

        // Set appropriate headers
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $document['file_type']);
        header('Content-Disposition: attachment; filename="' . $document['file_name'] . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));

        // Clear output buffer
        ob_clean();
        flush();

        // Read file and send to output
        readfile($filePath);
        exit;
    }
}