<?php

use App\Controllers\BlogController;
use App\Controllers\LoginController;
use App\Controllers\MessageController;
session_start();

// Require core, controllers, and models
require_once '../app/models/Meeting.php';
require_once '../app/core/Controller.php';
require_once '../app/core/Database.php';
require_once '../app/controllers/HomeController.php';
require_once '../app/controllers/UserController.php';
require_once '../app/controllers/LoginController.php';
require_once '../app/controllers/TutorController.php';
require_once '../app/models/User.php';
require_once '../app/models/PersonalTutor.php';
require_once '../app/controllers/MessageController.php';
require_once '../app/models/Message.php';
require_once '../app/models/Notification.php';
require_once '../app/controllers/BlogController.php';
require_once '../app/controllers/DocumentController.php';  // New controller
require_once '../app/models/Document.php';  // New model
require_once '../app/models/DocumentComment.php';  // New model
require_once '../app/models/Blog.php';  // New model
require_once '../app/models/BlogComment.php';  // New model
require_once '../app/models/BlogParticipant.php';  // New model
require_once '../app/models/Dashboard.php';
require_once '../app/controllers/DashboardController.php';
require_once '../app/models/BlogDocument.php';
require_once '../vendor/autoload.php';
require_once '../app/models/EmailQueue.php';
require_once '../app/controllers/EmailQueueController.php';
require_once '../app/controllers/SystemMonitoringController.php';
require_once '../app/models/ActivityTracker.php';
require_once '../app/middleware/ActivityTrackerMiddleware.php';
require_once '../app/core/ErrorHandler.php';
require_once '../app/models/SystemStats.php';
// Initialize error handler
//\App\Core\ErrorHandler::init();
// Track page view
\App\Middleware\ActivityTrackerMiddleware::track();


// Lấy URL từ query string, ví dụ: ?url=user/index
$url = isset($_GET['url']) ? $_GET['url'] : '';

// Nếu không có url (hoặc = 'home/index'), ta gọi HomeController
if ($url === '' || $url === 'home/index') {
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
// Route cho Assign Tutor (Chỉ dành cho staff)
} elseif ($url === 'tutor/assign') {
    $controller = new TutorController();
    $controller->assign();

// Xử lý gán tutor (POST)
} elseif ($url === 'tutor/store') {
    require_once '../app/controllers/TutorController.php';
    $controller = new TutorController();
    $controller->store();
// Route cho Tutor Dashboard
} elseif ($url === 'tutor/dashboard') {
    require_once '../app/controllers/TutorController.php';
    $controller = new TutorController();
    $controller->dashboard();
} elseif ($url === 'user/detail') {
    $controller = new UserController();
    $controller->detail();
// Hiển thị form reallocate tutor
} elseif ($url === 'user/reallocate') {
    $controller = new UserController();
    $controller->reallocate();
// Xử lý lưu reallocation (POST)
} elseif ($url === 'user/storeReallocation') {
    $controller = new UserController();
    $controller->storeReallocation();
} elseif ($url === 'tutor/tutee_list') {
    $controller = new TutorController();
    $controller->tuteeList();
} elseif ($url === 'tutor/dashboard') {
    $controller = new TutorController();
    $controller->dashboard();
} elseif ($url === 'user/profile') {
    $controller = new UserController();
    $controller->profile();
    //test
} elseif ($url === 'message/chat') {
    $controller = new MessageController();
    $controller->chat();
} elseif ($url === 'message/send') {
        $controller = new MessageController();
        $controller->send();
} elseif ($url === 'message/chatList') {
    $controller = new MessageController();
    $controller->chatList();
} elseif ($url === 'message/getMessages') {
    $controller = new MessageController();
    $controller->getMessages();
} elseif ($url === 'message/getUnreadCount') {
    $controller = new MessageController();
    $controller->getUnreadCount();
    // Route for displaying the meeting scheduling form
} elseif ($url === 'meeting/create') {
    require_once '../app/controllers/MeetingController.php';
    $controller = new MeetingController();
    $controller->create();

// Route for processing the meeting scheduling form (POST)
} elseif ($url === 'meeting/store') {
    require_once '../app/controllers/MeetingController.php';
    $controller = new MeetingController();
    $controller->store();

// Route for listing all meetings
} elseif ($url === 'meeting/list') {
    require_once '../app/controllers/MeetingController.php';
    $controller = new MeetingController();
    $controller->list();

// Route for viewing meeting details
} elseif ($url === 'meeting/view') {
    require_once '../app/controllers/MeetingController.php';
    $controller = new MeetingController();
    $controller->viewDetails(); // Thay đổi từ view() thành viewDetails()

// Route for updating meeting status (confirm/cancel)
} elseif ($url === 'meeting/updateStatus') {
    require_once '../app/controllers/MeetingController.php';
    $controller = new MeetingController();
    $controller->updateStatus();
// Route for generating a meeting link
} elseif ($url === 'meeting/generateLink') {
    require_once '../app/controllers/MeetingController.php';
    $controller = new MeetingController();
    $controller->generateLink();

// Route for adding a meeting link manually
} elseif ($url === 'meeting/addMeetingLink') {
    require_once '../app/controllers/MeetingController.php';
    $controller = new MeetingController();
    $controller->addMeetingLink();

// Route for displaying the record outcome form
} elseif ($url === 'meeting/recordOutcome') {
    require_once '../app/controllers/MeetingController.php';
    $controller = new MeetingController();
    $controller->recordOutcome();

// Route for saving meeting outcomes
} elseif ($url === 'meeting/saveOutcome') {
    require_once '../app/controllers/MeetingController.php';
    $controller = new MeetingController();
    $controller->saveOutcome();

// Route for viewing completed meetings
} elseif ($url === 'meeting/completed') {
    require_once '../app/controllers/MeetingController.php';
    $controller = new MeetingController();
    $controller->completed();
// Route for marking a notification as read
} elseif ($url === 'notifications/markAsRead') {
    require_once '../app/controllers/NotificationController.php';
    $controller = new NotificationController();
    $controller->markAsRead();

// Route for marking all notifications as read
} elseif ($url === 'notifications/markAllAsRead') {
    require_once '../app/controllers/NotificationController.php';
    $controller = new NotificationController();
    $controller->markAllAsRead();

// Route for viewing all notifications
} elseif ($url === 'notifications/index') {
    require_once '../app/controllers/NotificationController.php';
    $controller = new NotificationController();
    $controller->index();

    // DOCUMENT ROUTES
} elseif ($url === 'document/upload') {
    $controller = new DocumentController();
    $controller->upload();
} elseif ($url === 'document/store') {
    $controller = new DocumentController();
    $controller->store();
} elseif ($url === 'document/list') {
    $controller = new DocumentController();
    $controller->list();
} elseif ($url === 'document/view') {
$controller = new DocumentController();
$controller->viewDetails();
} elseif ($url === 'document/comment') {
    $controller = new DocumentController();
    $controller->comment();
} elseif ($url === 'document/download') {
    $controller = new DocumentController();
    $controller->download();

// BLOG ROUTES
} elseif ($url === 'blog/index') {
    $controller = new BlogController();
    $controller->index();
} elseif ($url === 'blog/create') {
    $controller = new BlogController();
    $controller->create();
} elseif ($url === 'blog/store') {
    $controller = new BlogController();
    $controller->store();
} elseif ($url === 'blog/view') {
    $controller = new BlogController();
    $controller->viewDetails();
} elseif ($url === 'blog/comment') {
    $controller = new BlogController();
    $controller->comment();
} elseif ($url === 'blog/edit') {
    $controller = new BlogController();
    $controller->edit();
} elseif ($url === 'blog/update') {
    $controller = new BlogController();
    $controller->update();
} elseif ($url === 'blog/delete') {
    $controller = new BlogController();
    $controller->delete();

    // Dashboard routes
} elseif ($url === 'dashboard/index') {
    $controller = new DashboardController();
    $controller->index();
} elseif ($url === 'dashboard/student') {
    $controller = new DashboardController();
    $controller->student();
} elseif ($url === 'dashboard/tutor') {
    $controller = new DashboardController();
    $controller->tutor();
} elseif ($url === 'dashboard/studentsWithoutTutor') {
    $controller = new DashboardController();
    $controller->studentsWithoutTutor();
} elseif ($url === 'dashboard/studentsWithoutInteraction') {
    $controller = new DashboardController();
    $controller->studentsWithoutInteraction();
} elseif ($url === 'dashboard/tutorActivity') {
    $controller = new DashboardController();
    $controller->tutorActivity();
//    //test
//    // Add these routes in your routing section for dashboard
} elseif ($url === 'dashboard/timeBasedActivity') {
    $controller = new DashboardController();
    $controller->timeBasedActivity();
} elseif ($url === 'dashboard/peakUsageTimes') {
    $controller = new DashboardController();
    $controller->peakUsageTimes();
} elseif ($url === 'dashboard/compareTimePeriods') {
    $controller = new DashboardController();
    $controller->compareTimePeriods();
    //email queue
} elseif ($url === 'emailqueue/process') {
    require_once '../app/controllers/EmailQueueController.php';
    $controller = new EmailQueueController();
    $controller->process();
} elseif ($url === 'emailqueue/status') {
    require_once '../app/controllers/EmailQueueController.php';
    $controller = new EmailQueueController();
    $controller->status();
    // System Monitoring Routes
} elseif ($url === 'monitoring/index') {
    require_once '../app/controllers/SystemMonitoringController.php';
    $controller = new SystemMonitoringController();
    $controller->index();
} elseif ($url === 'monitoring/pageViews') {
    require_once '../app/controllers/SystemMonitoringController.php';
    $controller = new SystemMonitoringController();
    $controller->pageViews();
} elseif ($url === 'monitoring/userActivity') {
    require_once '../app/controllers/SystemMonitoringController.php';
    $controller = new SystemMonitoringController();
    $controller->userActivity();
} elseif ($url === 'monitoring/techUsage') {
    require_once '../app/controllers/SystemMonitoringController.php';
    $controller = new SystemMonitoringController();
    $controller->techUsage();
} elseif ($url === 'monitoring/usagePatterns') {
    require_once '../app/controllers/SystemMonitoringController.php';
    $controller = new SystemMonitoringController();
    $controller->usagePatterns();
} elseif ($url === 'monitoring/errors') {
    require_once '../app/controllers/SystemMonitoringController.php';
    $controller = new SystemMonitoringController();
    $controller->errors();

} else {
    echo "404 Not Found or Route not handled yet.";
}
