<?php
require_once __DIR__ . '/../app/helpers/MailHelper.php';

$testEmail = 'test@example.com'; // Email giả
$subject = 'Test Mailtrap SMTP';
$body = '<h3>This is a test email from Mailtrap SMTP</h3>';

$result = MailHelper::sendMail($testEmail, $subject, $body);
echo $result ? "✅ Email sent successfully!" : "❌ Email failed!";