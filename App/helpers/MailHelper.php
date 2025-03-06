<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php'; // Load thư viện PHPMailer từ Composer

class MailHelper {
    public static function sendMail($to, $subject, $body): bool
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'trontran126@Gmail.com';
            $mail->Password = 'ybyl edns agkt hrki';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('noreply@etutoring.com', 'eTutoring System');
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            return $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
            return false;
        }
    }

//    /**
//     * Send a meeting reminder email
//     *
//     * @param string $to Recipient email
//     * @param string $recipientName Recipient name
//     * @param array $meetingData Meeting data
//     * @return bool True if email was sent successfully
//     */
//    public static function sendMeetingReminder($to, $recipientName, $meetingData): bool
//    {
//        $subject = "Reminder: Upcoming Meeting - eTutoring System";
//
//        $meetingTime = date('F j, Y \a\t g:i A', strtotime($meetingData['meeting_date']));
//        $meetingType = ucfirst($meetingData['meeting_type']);
//        $otherPartyName = $meetingData['other_party_name'];
//
//        $body = "
//        <html>
//        <head>
//            <style>
//                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
//                .container { width: 100%; max-width: 600px; margin: 0 auto; }
//                .header { background-color: #3f51b5; color: white; padding: 20px; text-align: center; }
//                .content { padding: 20px; }
//                .footer { background-color: #f5f5f5; padding: 10px; text-align: center; font-size: 12px; }
//            </style>
//        </head>
//        <body>
//            <div class='container'>
//                <div class='header'>
//                    <h2>Meeting Reminder</h2>
//                </div>
//                <div class='content'>
//                    <p>Dear $recipientName,</p>
//
//                    <p>This is a friendly reminder about your upcoming meeting:</p>
//
//                    <p><strong>Date & Time:</strong> $meetingTime</p>
//                    <p><strong>Meeting Type:</strong> $meetingType Meeting</p>
//                    <p><strong>With:</strong> $otherPartyName</p>
//
//                    <p>Please ensure you are prepared and available for this meeting. If you need to reschedule, please do so as soon as possible through the eTutoring system.</p>
//
//                    <p>You can view the full details of this meeting by logging into your eTutoring account.</p>
//
//                    <p>Best regards,<br>
//                    eTutoring System</p>
//                </div>
//                <div class='footer'>
//                    <p>This is an automated message from the eTutoring System. Please do not reply to this email.</p>
//                </div>
//            </div>
//        </body>
//        </html>
//        ";
//
//        return self::sendMail($to, $subject, $body);
//    }
}