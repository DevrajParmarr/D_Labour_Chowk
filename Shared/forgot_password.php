<?php
/**
 * Password Reset Processing Script
 * Handles password reset requests securely
 */

require_once 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validate CSRF token
    if (!validateCSRFToken($csrf_token)) {
        $_SESSION['forgot_error'] = 'Invalid request. Please try again.';
        redirect('forgot_password_form.php');
    }

    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['forgot_error'] = 'Please enter a valid email address.';
        redirect('forgot_password_form.php');
    }

    try {
        $db = Database::getInstance();

        // Check if email exists
        $stmt = $db->prepare("SELECT user_ID, user_name FROM user WHERE email_id = ? AND Verified = 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Generate reset token
            $reset_token = bin2hex(random_bytes(32));
            $reset_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store reset token
            $update_stmt = $db->prepare("UPDATE user SET reset_token = ?, reset_expiry = ? WHERE user_ID = ?");
            $update_stmt->bind_param('ssi', $reset_token, $reset_expiry, $user['user_ID']);
            $update_stmt->execute();
            $update_stmt->close();

            // Send reset email
            if (sendPasswordResetEmail($email, $reset_token, $user['user_name'])) {
                $_SESSION['forgot_success'] = 'Password reset instructions have been sent to your email address.';
                redirect('forgot_password_form.php?sent=1');
            } else {
                $_SESSION['forgot_error'] = 'Failed to send reset email. Please try again later.';
                redirect('forgot_password_form.php');
            }

        } else {
            // Don't reveal if email exists or not for security
            $_SESSION['forgot_success'] = 'If your email address exists in our system, you will receive password reset instructions.';
            redirect('forgot_password_form.php?sent=1');
        }

        $stmt->close();

    } catch (Exception $e) {
        error_log('Password Reset Error: ' . $e->getMessage());
        $_SESSION['forgot_error'] = 'Password reset failed. Please try again later.';
        redirect('forgot_password_form.php');
    }
} else {
    redirect('forgot_password_form.php');
}

function sendPasswordResetEmail($email, $reset_token, $user_name) {
    require 'PHPMailer/PHPMailer.php';
    require 'PHPMailer/SMTP.php';
    require 'PHPMailer/Exception.php';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'tarunpsgsits07@gmail.com';
        $mail->Password = 'qmkneshljyddbitp';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('tarunpsgsits07@gmail.com', 'D Labour Chowk');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset - D Labour Chowk';

        $reset_link = APP_URL . "/Shared/reset_password.php?token=" . $reset_token;

        $mail->Body = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                <h2 style='color: #2563eb;'>Password Reset Request</h2>
                <p>Hello {$user_name},</p>
                <p>You have requested to reset your password for your D Labour Chowk account.</p>
                <p>Please click the link below to reset your password:</p>
                <p style='margin: 20px 0;'>
                    <a href='{$reset_link}' style='background: #2563eb; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;'>Reset Password</a>
                </p>
                <p><strong>Important:</strong> This link will expire in 1 hour for security reasons.</p>
                <p>If you didn't request this password reset, please ignore this email.</p>
                <p>Best regards,<br>D Labour Chowk Team</p>
            </div>
        ";

        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Password Reset Email Error: ' . $mail->ErrorInfo);
        return false;
    }
}
?>