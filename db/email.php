<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

function sendEmail($to_email, $to_name, $subject, $message) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'luqman.ahmad.cs@gmail.com';
        $mail->Password   = 'wwrl mahg mjkt zynb';
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('luqman.ahmad.cs@gmail.com', 'MediCare Hospital');
        $mail->addAddress($to_email, $to_name);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = "
        <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
            <div style='background:linear-gradient(135deg,#0d6efd,#0a58ca);padding:25px;text-align:center;border-radius:10px 10px 0 0;'>
                <h1 style='color:white;margin:0;font-size:24px;'>🏥 MediCare</h1>
                <p style='color:#AACCFF;margin:5px 0 0;'>Online Hospital Management System</p>
            </div>
            <div style='background:#f8f9fa;padding:30px;border-radius:0 0 10px 10px;'>
                <p style='font-size:16px;color:#333;'>Dear <strong>$to_name</strong>,</p>
                $message
                <hr style='border:1px solid #e0e0e0;margin:20px 0;'>
                <p style='font-size:12px;color:#888;text-align:center;'>
                    This is an automated email from MediCare Hospital System.<br>
                    Government Degree College Hayatabad — FYP Project
                </p>
            </div>
        </div>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>