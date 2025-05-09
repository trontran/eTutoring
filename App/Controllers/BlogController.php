<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Blog;
use App\Models\BlogComment;
use App\Models\BlogParticipant;
use App\Models\Notification;
use App\Models\User;

class BlogController extends Controller
{
    private $blogModel;
    private $blogCommentModel;
    private $blogParticipantModel;
    private $userModel;
    private $notificationModel;

    public function __construct()
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Initialize models
        $this->blogModel = new Blog();
        $this->blogCommentModel = new BlogComment();
        $this->blogParticipantModel = new BlogParticipant();
        $this->userModel = new User();
        $this->notificationModel = new Notification();
    }

    /**
     * Display a list of all blogs accessible to the current user
     */
    public function index(): void
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        $userId = $_SESSION['user']['user_id'];
        $userRole = $_SESSION['user']['role'];

        // Get blogs based on role
        if ($userRole === 'student') {
            $blogs = $this->blogModel->getBlogsForStudent($userId);
        } else if ($userRole === 'tutor') {
            $blogs = $this->blogModel->getBlogsByTutor($userId);
        } else if ($userRole === 'staff') {
            // For staff, get all blogs or blogs based on search criteria
            $searchTerm = $_GET['search'] ?? '';
            $blogs = $this->blogModel->getAllBlogs($searchTerm);
        }

        $data = [
            'blogs' => $blogs,
            'userRole' => $userRole
        ];

        $this->view('blog/index', $data);
    }

    /**
     * Display form to create a new blog - UPDATED to allow students
     */
    public function create(): void
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        $userId = $_SESSION['user']['user_id'];
        $userRole = $_SESSION['user']['role'];

        // If student, they can only create for themselves and their tutor
        if ($userRole === 'student') {
            // Get student's tutor
            require_once '../app/models/PersonalTutor.php';
            $personalTutorModel = new \App\Models\PersonalTutor();
            $tutor = $personalTutorModel->getTutorDetails($userId);
            $data = ['tutor' => $tutor];

            $this->view('blog/create_student', $data); // Create a student-specific view
            return;
        }

        // For tutors, keep existing flow
        if ($userRole === 'tutor') {
            $tutees = $this->userModel->getTuteesByTutor($userId);
            $data = ['tutees' => $tutees];
        } else {
            $data = [];
        }

        $this->view('blog/create', $data);
    }

    /**
     * View a specific blog with comments and attached documents
     */
    public function viewDetails(): void
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        $blogId = $_GET['id'] ?? null;

        if (!$blogId) {
            header("Location: ?url=blog/index");
            exit;
        }

        $blog = $this->blogModel->getBlogById($blogId);

        if (!$blog) {
            $_SESSION['error'] = "Blog not found.";
            header("Location: ?url=blog/index");
            exit;
        }

        // Check if user has access to this blog
        $userId = $_SESSION['user']['user_id'];
        $userRole = $_SESSION['user']['role'];

        // Staff have access to all blogs
        if ($userRole !== 'staff') {
            // Tutors can access their own blogs
            if ($userRole === 'tutor' && $blog['tutor_id'] != $userId) {
                $_SESSION['error'] = "You don't have permission to view this blog.";
                header("Location: ?url=blog/index");
                exit;
            }

            // Students can only access blogs they are participants of
            if ($userRole === 'student' && !$this->blogParticipantModel->isParticipant($blogId, $userId)) {
                $_SESSION['error'] = "You don't have permission to view this blog.";
                header("Location: ?url=blog/index");
                exit;
            }
        }

        // Get comments for this blog
        $comments = $this->blogCommentModel->getCommentsByBlogId($blogId);

        // Get participants for this blog
        $participants = $this->blogParticipantModel->getParticipantsByBlogId($blogId);

        // Get attached documents for this blog
        $blogDocumentModel = new \App\Models\BlogDocument();
        $documents = $blogDocumentModel->getDocumentsByBlogId($blogId);

        $data = [
            'blog' => $blog,
            'comments' => $comments,
            'participants' => $participants,
            'documents' => $documents,
            'userRole' => $userRole,
            'userId' => $userId,
            'createdByStudent' => $blog['created_by_student'] ?? null
        ];

        $this->view('blog/view', $data);
    }

    /**
     * Add a comment to a blog
     */
    public function comment()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?url=blog/index");
            exit;
        }

        $blogId = $_POST['blog_id'] ?? null;
        $comment = $_POST['comment'] ?? null;

        if (!$blogId || !$comment) {
            $_SESSION['error'] = "Missing required information.";
            header("Location: ?url=blog/view&id=" . $blogId);
            exit;
        }

        $userId = $_SESSION['user']['user_id'];
        $userRole = $_SESSION['user']['role'];

        // Check if user has access to comment on this blog
        $blog = $this->blogModel->getBlogById($blogId);

        if (!$blog) {
            $_SESSION['error'] = "Blog not found.";
            header("Location: ?url=blog/index");
            exit;
        }

        // Staff can comment on any blog
        if ($userRole !== 'staff') {
            // Tutors can comment on their own blogs
            if ($userRole === 'tutor' && $blog['tutor_id'] != $userId) {
                $_SESSION['error'] = "You don't have permission to comment on this blog.";
                header("Location: ?url=blog/index");
                exit;
            }

            // Students can only comment on blogs they are participants of
            if ($userRole === 'student' && !$this->blogParticipantModel->isParticipant($blogId, $userId)) {
                $_SESSION['error'] = "You don't have permission to comment on this blog.";
                header("Location: ?url=blog/index");
                exit;
            }
        }

        // Save comment
        $commentData = [
            'blog_id' => $blogId,
            'user_id' => $userId,
            'comment' => $comment
        ];

        $commentId = $this->blogCommentModel->createComment($commentData);

        if ($commentId) {
            // Create notifications for the tutor and all participants except the commenter
            $commenterName = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
            $notificationText = "$commenterName commented on blog: " . htmlspecialchars($blog['title']);

            // Notify the tutor if the commenter is not the tutor
            if ($userId != $blog['tutor_id']) {
                $this->notificationModel->createNotification($blog['tutor_id'], $notificationText);
            }

            // Notify all participants except the commenter
            $participants = $this->blogParticipantModel->getParticipantsByBlogId($blogId);
            foreach ($participants as $participant) {
                if ($participant['student_id'] != $userId) {
                    $this->notificationModel->createNotification($participant['student_id'], $notificationText);
                }
            }

            $_SESSION['success'] = "Comment added successfully.";
        } else {
            $_SESSION['error'] = "Failed to add comment. Please try again.";
        }

        header("Location: ?url=blog/view&id=" . $blogId);
        exit;
    }

    /**
     * Edit a blog (for tutor/owner only)
     */
    public function edit(): void
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        $blogId = $_GET['id'] ?? null;

        if (!$blogId) {
            header("Location: ?url=blog/index");
            exit;
        }

        $blog = $this->blogModel->getBlogById($blogId);

        if (!$blog) {
            $_SESSION['error'] = "Blog not found.";
            header("Location: ?url=blog/index");
            exit;
        }

        // Allow tutors who own the blog, students who created the blog, or staff to edit
        $userId = $_SESSION['user']['user_id'];
        $userRole = $_SESSION['user']['role'];

        // Check if the blog was created by a student
        $createdByStudent = isset($blog['created_by_student']) ? $blog['created_by_student'] : null;

        // Allow edit if: staff, or tutor who owns it, or student who created it
        if ($userRole !== 'staff' &&
            !($userRole === 'tutor' && $blog['tutor_id'] == $userId) &&
            !($userRole === 'student' && $createdByStudent == $userId)) {
            $_SESSION['error'] = "You don't have permission to edit this blog.";
            header("Location: ?url=blog/view&id=" . $blogId);
            exit;
        }

        // Get current participants
        $participants = $this->blogParticipantModel->getParticipantsByBlogId($blogId);

        // Get all tutees for selection
        if ($userRole === 'tutor') {
            $tutees = $this->userModel->getTuteesByTutor($userId);
        } else {
            // For staff, get all students
            $tutees = $this->userModel->getAllStudents();
        }

        $data = [
            'blog' => $blog,
            'participants' => $participants,
            'tutees' => $tutees
        ];

        $this->view('blog/edit', $data);
    }

    /**
     * Update a blog
     */
    public function update()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?url=blog/index");
            exit;
        }

        $blogId = $_POST['blog_id'] ?? null;
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $selectedStudents = $_POST['student_ids'] ?? [];

        if (!$blogId || empty($title) || empty($content)) {
            $_SESSION['error'] = "All required fields must be filled out.";
            header("Location: ?url=blog/edit&id=" . $blogId);
            exit;
        }

        $blog = $this->blogModel->getBlogById($blogId);

        if (!$blog) {
            $_SESSION['error'] = "Blog not found.";
            header("Location: ?url=blog/index");
            exit;
        }

        // Allow tutors who own the blog, students who created the blog, or staff to update
        $userId = $_SESSION['user']['user_id'];
        $userRole = $_SESSION['user']['role'];

        // Check if the blog was created by a student
        $createdByStudent = isset($blog['created_by_student']) ? $blog['created_by_student'] : null;

        // Allow update if: staff, or tutor who owns it, or student who created it
        if ($userRole !== 'staff' &&
            !($userRole === 'tutor' && $blog['tutor_id'] == $userId) &&
            !($userRole === 'student' && $createdByStudent == $userId)) {
            $_SESSION['error'] = "You don't have permission to update this blog.";
            header("Location: ?url=blog/view&id=" . $blogId);
            exit;
        }

        // Update blog
        $blogData = [
            'title' => $title,
            'content' => $content
        ];

        $success = $this->blogModel->updateBlog($blogId, $blogData);

        if ($success) {
            // Get current participants for comparison
            $currentParticipants = $this->blogParticipantModel->getParticipantsByBlogId($blogId);
            $currentStudentIds = array_column($currentParticipants, 'student_id');

            // Find new students to add
            $newStudents = array_diff($selectedStudents, $currentStudentIds);

            // Find students to remove
            $removedStudents = array_diff($currentStudentIds, $selectedStudents);

            // Add new participants
            if (!empty($newStudents)) {
                foreach ($newStudents as $studentId) {
                    $this->blogParticipantModel->addParticipant($blogId, $studentId);

                    // Create notification for new students
                    $tutorName = $blog['tutor_first_name'] . ' ' . $blog['tutor_last_name'];
                    $notificationText = "$tutorName has added you to blog: " . htmlspecialchars($title);
                    $this->notificationModel->createNotification($studentId, $notificationText);
                }
            }

            // Remove participants
            if (!empty($removedStudents)) {
                foreach ($removedStudents as $studentId) {
                    $this->blogParticipantModel->removeParticipant($blogId, $studentId);
                }
            }

            $_SESSION['success'] = "Blog updated successfully.";
            header("Location: ?url=blog/view&id=" . $blogId);
        } else {
            $_SESSION['error'] = "Failed to update blog. Please try again.";
            header("Location: ?url=blog/edit&id=" . $blogId);
        }
        exit;
    }

    /**
     * Delete a blog
     */
    public function delete()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        $blogId = $_GET['id'] ?? null;

        if (!$blogId) {
            header("Location: ?url=blog/index");
            exit;
        }

        $blog = $this->blogModel->getBlogById($blogId);

        if (!$blog) {
            $_SESSION['error'] = "Blog not found.";
            header("Location: ?url=blog/index");
            exit;
        }

        // Only the owner (tutor) or staff can delete a blog
        $userId = $_SESSION['user']['user_id'];
        $userRole = $_SESSION['user']['role'];

        $createdByStudent = isset($blog['created_by_student']) ? $blog['created_by_student'] : null;

        // Allow delete if: staff, or tutor who owns it, or student who created it
        if ($userRole !== 'staff' &&
            !($userRole === 'tutor' && $blog['tutor_id'] == $userId) &&
            !($userRole === 'student' && $createdByStudent == $userId)) {
            $_SESSION['error'] = "You don't have permission to delete this blog.";
            header("Location: ?url=blog/view&id=" . $blogId);
            exit;
        }

        // Get participants for notifications
        $participants = $this->blogParticipantModel->getParticipantsByBlogId($blogId);

        // Delete blog and all related data
        $success = $this->blogModel->deleteBlog($blogId);

        if ($success) {
            // Send notifications to all participants
            $tutorName = $blog['tutor_first_name'] . ' ' . $blog['tutor_last_name'];
            $notificationText = "$tutorName has deleted the blog: " . htmlspecialchars($blog['title']);

            foreach ($participants as $participant) {
                $this->notificationModel->createNotification($participant['student_id'], $notificationText);
            }

            $_SESSION['success'] = "Blog deleted successfully.";
            header("Location: ?url=blog/index");
        } else {
            $_SESSION['error'] = "Failed to delete blog. Please try again.";
            header("Location: ?url=blog/view&id=" . $blogId);
        }
        exit;
    }

    /**
     * Process and store a new blog with optional multiple document uploads
     */
    public function store()
    {
        // Check if user is logged in
        if (!isset($_SESSION['user'])) {
            header("Location: ?url=login");
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ?url=blog/create");
            exit;
        }

        $userId = $_SESSION['user']['user_id'];
        $userRole = $_SESSION['user']['role'];
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';

        // Check for file uploads
        $hasDocuments = isset($_FILES['document']) && !empty($_FILES['document']['name'][0]);
        $uploadedDocumentIds = []; // To store IDs of successfully uploaded documents

        // For students, handle differently
        if ($userRole === 'student') {
            $tutorId = $_POST['tutor_id'] ?? null;

            if (!$tutorId || empty($title) || empty($content)) {
                $_SESSION['error'] = "All required fields must be filled out.";
                header("Location: ?url=blog/create");
                exit;
            }

            // Create blog data
            $blogData = [
                'tutor_id' => $tutorId, // Use tutor's ID as owner (keeps model consistent)
                'title' => $title,
                'content' => $content,
                'created_by_student' => $userId // Add a flag if needed
            ];

            // Create the blog
            $blogId = $this->blogModel->createBlog($blogData);

            if ($blogId) {
                // Add the student as a participant
                $this->blogParticipantModel->addParticipant($blogId, $userId);

                // Create notification for the tutor
                $studentName = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
                $notificationText = "$studentName has created a new blog: " . htmlspecialchars($title);
                $this->notificationModel->createNotification($tutorId, $notificationText);

                // Process document uploads if any
                if ($hasDocuments) {
                    $uploadedDocumentIds = $this->processDocumentUploads($userId, $userId, $tutorId, $_FILES['document']);
                }

                // Associate documents with blog if any were uploaded
                if (!empty($uploadedDocumentIds)) {
                    $this->associateDocumentsWithBlog($blogId, $uploadedDocumentIds);
                }

                $_SESSION['success'] = "Blog created successfully.";
                header("Location: ?url=blog/view&id=" . $blogId);
            } else {
                $_SESSION['error'] = "Failed to create blog. Please try again.";
                header("Location: ?url=blog/create");
            }
            exit;
        } else {
            // Original tutor blog creation logic
            $selectedStudents = $_POST['student_ids'] ?? [];

            // Validate input
            if (empty($title) || empty($content) || ($userRole === 'tutor' && empty($selectedStudents))) {
                $_SESSION['error'] = "All required fields must be filled out.";
                header("Location: ?url=blog/create");
                exit;
            }

            // Create blog
            $blogData = [
                'tutor_id' => $userId,
                'title' => $title,
                'content' => $content
            ];

            $blogId = $this->blogModel->createBlog($blogData);

            if ($blogId) {
                // Add selected students as participants
                if (!empty($selectedStudents)) {
                    foreach ($selectedStudents as $studentId) {
                        $this->blogParticipantModel->addParticipant($blogId, $studentId);

                        // Create notification for each student
                        $tutorName = $_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name'];
                        $notificationText = "$tutorName has added you to a new blog: " . htmlspecialchars($title);
                        $this->notificationModel->createNotification($studentId, $notificationText);
                    }
                }

                // Process document uploads if any
                if ($hasDocuments) {
                    // Determine the student ID (we'll use the first student if multiple)
                    $studentId = !empty($selectedStudents) ? $selectedStudents[0] : null;
                    $uploadedDocumentIds = $this->processDocumentUploads($userId, $studentId, $userId, $_FILES['document']);
                }

                // Associate documents with blog if any were uploaded
                if (!empty($uploadedDocumentIds)) {
                    $this->associateDocumentsWithBlog($blogId, $uploadedDocumentIds);
                }

                $_SESSION['success'] = "Blog created successfully.";
                header("Location: ?url=blog/view&id=" . $blogId);
            } else {
                $_SESSION['error'] = "Failed to create blog. Please try again.";
                header("Location: ?url=blog/create");
            }
            exit;
        }
    }

    /**
     * Process multiple document uploads
     *
     * @param int $uploaderId ID of the user uploading the documents
     * @param int $studentId Student ID
     * @param int $tutorId Tutor ID
     * @param array $files Files array from $_FILES
     * @return array Array of document IDs that were successfully uploaded
     */
    private function processDocumentUploads($uploaderId, $studentId, $tutorId, $files)
    {
        $uploadedDocumentIds = [];
        $documentModel = new \App\Models\Document();

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
        $fileCount = count($files['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            // Skip if no file was uploaded in this slot
            if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            // Check for upload errors
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                $errorFiles[] = $files['name'][$i] . " (Upload error)";
                continue;
            }

            $fileName = $files['name'][$i];
            $fileType = $files['type'][$i];
            $fileSize = $files['size'][$i];
            $fileTmpName = $files['tmp_name'][$i];

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

            $documentId = $documentModel->createDocument($documentData);

            if ($documentId) {
                $uploadedDocumentIds[] = $documentId;
                $successCount++;
            } else {
                $errorFiles[] = $fileName . " (Database error)";
            }
        }

        // Set error message if any files failed to upload
        if (!empty($errorFiles)) {
            $_SESSION['error'] = "Failed to upload " . count($errorFiles) . " file(s): " . implode(", ", $errorFiles);
        }

        return $uploadedDocumentIds;
    }

    /**
     * Associate multiple documents with a blog
     *
     * @param int $blogId Blog ID
     * @param array $documentIds Array of document IDs to associate
     */
    private function associateDocumentsWithBlog($blogId, $documentIds)
    {
        $blogDocumentModel = new \App\Models\BlogDocument();
        foreach ($documentIds as $documentId) {
            $blogDocumentModel->addDocumentToBlog($blogId, $documentId);
        }
    }
}