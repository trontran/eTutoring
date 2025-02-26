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
            $mail->Host = 'smtp.gmail.com'; // Thay bằng Host từ Mailtrap
            $mail->SMTPAuth = true;
            $mail->Username = 'trontran126@Gmail.com'; // Thay bằng Username
            $mail->Password = 'ybyl edns agkt hrki'; // Thay bằng Password
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

    
}