<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../vendor/autoload.php'; // Load thư viện PHPMailer từ Composer

class MailHelper {
    public static function sendMail($to, $subject, $body) {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'sandbox.smtp.mailtrap.io'; // Thay bằng Host từ Mailtrap
            $mail->SMTPAuth = true;
            $mail->Username = 'e4b6662025a79f'; // Thay bằng Username
            $mail->Password = '7b6eda3764a596'; // Thay bằng Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 2525;

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

    
}