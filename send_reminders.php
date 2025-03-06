<?php
// Define root path
define('ROOT_PATH', __DIR__);

// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once ROOT_PATH . '/app/core/Database.php';
require_once ROOT_PATH . '/app/models/MeetingReminder.php';
require_once ROOT_PATH . '/app/Helpers/MailHelper.php';

// Check for vendor/autoload.php
if (file_exists(ROOT_PATH . '/vendor/autoload.php')) {
    require_once ROOT_PATH . '/vendor/autoload.php';
}

use App\Models\MeetingReminder;

// Display header
echo "=================================================\n";
echo "eTutoring System - Meeting Reminder Service\n";
echo "=================================================\n\n";

try {
    // Initialize meeting reminder model
    $reminderModel = new MeetingReminder();
    echo "✓ Database connection successful\n";

    // Get all pending reminders
    $pendingReminders = $reminderModel->getPendingReminders();

    // If there are pending reminders, send emails
    if (!empty($pendingReminders)) {
        echo "✓ Found " . count($pendingReminders) . " pending reminders to send.\n\n";

        foreach ($pendingReminders as $reminder) {
            // Format meeting date
            $meetingDate = date('F j, Y \a\t g:i A', strtotime($reminder['meeting_date']));

            // Create email subject based on reminder type
            if ($reminder['reminder_type'] == 'day_before') {
                $subject = "Reminder: Meeting Tomorrow - eTutoring System";
                $timeText = "tomorrow";
            } else {
                $subject = "Reminder: Meeting in 1 Hour - eTutoring System";
                $timeText = "in 1 hour";
            }

            // Create email body
            $body = "
            <p>Hello {$reminder['first_name']},</p>
            
            <p>This is a reminder about your scheduled meeting $timeText:</p>
            
            <p><strong>Meeting Details:</strong></p>
            <ul>
                <li><strong>Date & Time:</strong> {$meetingDate}</li>
                <li><strong>Meeting Type:</strong> " . ucfirst($reminder['meeting_type']) . " Meeting</li>
            </ul>
            
            <p>Please ensure you are prepared and available for this meeting.</p>
            
            <p>Best regards,</p>
            <p><strong>eTutoring Team</strong></p>
            <hr>
            <p style='font-size:12px; color:gray;'>This is an automated message, please do not reply to this email.</p>
            ";

            // Send email
            echo "Sending reminder to: {$reminder['email']} ({$reminder['first_name']} {$reminder['last_name']})... ";
            $success = MailHelper::sendMail($reminder['email'], $subject, $body);

            if ($success) {
                echo "✓ Success!\n";
                // Mark reminder as sent
                $reminderModel->markAsSent($reminder['reminder_id']);
            } else {
                echo "✗ Failed!\n";
            }
        }
    } else {
        echo "✓ No pending reminders found.\n";
    }

    echo "\nReminder process completed.\n";

} catch (\PDOException $e) {
    // Handle database connection errors
    echo "Database connection error: " . $e->getMessage() . "\n";
} catch (\Exception $e) {
    // Handle other errors
    echo "Error: " . $e->getMessage() . "\n";
}

// Quick test - Uncomment the line below for quick testing
// echo "\nQUICK TEST: Run this SQL query to test reminders immediately:\n";
// echo "UPDATE MeetingReminders SET reminder_time = NOW() - INTERVAL 5 MINUTE WHERE is_sent = 0;\n";