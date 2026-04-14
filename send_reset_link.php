<?php
session_start();
require 'vendor/autoload.php'; // Composer autoload for PHPMailer
require 'db.php';

// Clean expired tokens
$conn->query("DELETE FROM password_resets WHERE expires_at < NOW()");


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];

    // 1. Check if the email exists in the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result(); // Needed for num_rows

    if ($stmt->num_rows > 0) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime('+1 day'));

        // 2. Insert token into password_resets table
        $insert = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $insert->bind_param("sss", $email, $token, $expires);
        $insert->execute();

        $resetLink = "http://localhost/mindhaven/reset_password.php?token=$token";

        // 3. Send password reset email using Gmail SMTP
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = '2022899734@student.uitm.edu.my';       // Your Gmail address
            $mail->Password   = 'srajilwivzzayflh';                     // 16-char Gmail App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port       = 587;

            $mail->setFrom('2022899734@student.uitm.edu.my', 'MindHaven Support');
            $mail->addAddress($email); // Send to user
            $mail->Subject = 'Reset Your Password - MindHaven';
            $mail->Body    = "We received a password reset request for your MindHaven account.\n\nClick the link below to reset your password:\n\n$resetLink\n\nIf you didn't request this, please ignore this email.";

            $mail->send();

            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'A password reset link has been sent to your email.'
            ];
        } catch (Exception $e) {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Failed to send email. Error: ' . $mail->ErrorInfo
            ];
        }
    } else {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Email address not found in our records.'
        ];
    }

    header("Location: forgot_password.php");
    exit;
}
?>
