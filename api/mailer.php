<?php
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

function send_email(string $to, string $subject, string $body): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['EMAIL_USER'];
        $mail->Password   = $_ENV['EMAIL_PASS'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom($_ENV['EMAIL_USER'], 'HRMS');
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('HRMS mail failed: ' . $mail->ErrorInfo);
        return false;   // don't crash the main action if email fails
    }
}

function generate_code(): string {
    return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

function send_verification_code(string $to, string $code): bool {
    return send_email($to, 'Your HRMS verification code',
        "Your code is $code. It expires in 10 minutes.\n" .
        "If you didn't request this, ignore this email.");
}

function send_notification(string $to, string $subject, string $message): bool {
    return send_email($to, $subject, $message);
}