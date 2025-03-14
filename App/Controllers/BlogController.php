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
     * Process and store a new blog - UPDATED for student blogs
     */
    /**
     * Process and store a new blog with optional document upload
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

        // Initialize document variables
        $documentId = null;
        $hasDocument = isset($_FILES['document']) && $_FILES['document']['error'] !== UPLOAD_ERR_NO_FILE;

        // Process document upload if present
        if ($hasDocument) {
            // Handle file upload
            if ($_FILES['document']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = "File upload failed. Please try again.";
                header("Location: ?url=blog/create");
                exit;
            }

            $uploadedFile = $_FILES['document'];

            // Validate file type and size
            $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
            $maxSize = 10 * 1024 * 1024; // 10 MB

            if (!in_array($uploadedFile['type'], $allowedTypes)) {
                $_SESSION['error'] = "Invalid file type. Allowed types are PDF, DOC, DOCX, and TXT.";
                header("Location: ?url=blog/create");
                exit;
            }

            if ($uploadedFile['size'] > $maxSize) {
                $_SESSION['error'] = "File size exceeds the maximum limit of 10 MB.";
                header("Location: ?url=blog/create");
                exit;
            }

            // Create upload directory if it doesn't exist
            $uploadDir = '../uploads/document/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generate unique filename
            $originalName = $uploadedFile['name'];
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $fileName = uniqid('doc_') . '.' . $extension;
            $filePath = $uploadDir . $fileName;

            // Move the uploaded file
            if (!move_uploaded_file($uploadedFile['tmp_name'], $filePath)) {
                $_SESSION['error'] = "Failed to save the uploaded file. Please try again.";
                header("Location: ?url=blog/create");
                exit;
            }
        }

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

                // Save document if uploaded
                if ($hasDocument) {
                    // Create document record in database
                    $documentData = [
                        'uploader_id' => $userId,
                        'student_id' => $userId,
                        'tutor_id' => $tutorId,
                        'file_path' => 'uploads/document/' . $fileName,
                        'file_name' => $originalName,
                        'file_type' => $uploadedFile['type'],
                        'file_size' => $uploadedFile['size']
                    ];

                    $documentModel = new \App\Models\Document();
                    $documentId = $documentModel->createDocument($documentData);

                    // Associate document with blog
                    if ($documentId) {
                        $blogDocumentModel = new \App\Models\BlogDocument();
                        $blogDocumentModel->addDocumentToBlog($blogId, $documentId);
                    }
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

                // Save document if uploaded
                if ($hasDocument) {
                    // Determine the student ID (we'll use the first student if multiple)
                    $studentId = !empty($selectedStudents) ? $selectedStudents[0] : null;

                    // Create document record in database
                    $documentData = [
                        'uploader_id' => $userId,
                        'student_id' => $studentId,
                        'tutor_id' => $userId,
                        'file_path' => 'uploads/document/' . $fileName,
                        'file_name' => $originalName,
                        'file_type' => $uploadedFile['type'],
                        'file_size' => $uploadedFile['size']
                    ];

                    $documentModel = new \App\Models\Document();
                    $documentId = $documentModel->createDocument($documentData);

                    // Associate document with blog
                    if ($documentId) {
                        $blogDocumentModel = new \App\Models\BlogDocument();
                        $blogDocumentModel->addDocumentToBlog($blogId, $documentId);
                    }
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
            'userId' => $userId
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

        // Only the owner (tutor) or staff can edit a blog
        $userId = $_SESSION['user']['user_id'];
        $userRole = $_SESSION['user']['role'];

        if ($userRole !== 'staff' && $blog['tutor_id'] != $userId) {
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

        // Only the owner (tutor) or staff can update a blog
        $userId = $_SESSION['user']['user_id'];
        $userRole = $_SESSION['user']['role'];

        if ($userRole !== 'staff' && $blog['tutor_id'] != $userId) {
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

        if ($userRole !== 'staff' && $blog['tutor_id'] != $userId) {
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
}