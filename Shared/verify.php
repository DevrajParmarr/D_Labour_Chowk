<?php
/**
 * Email Verification Script
 * Secure email verification with proper validation and database security
 */

require_once 'config.php';
session_start();

if (isset($_GET['email']) && isset($_GET['vcode'])) {
    $email = trim($_GET['email']);
    $verification_code = trim($_GET['vcode']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['verify_error'] = 'Invalid email address.';
        redirect('login_form.php');
    }

    // Validate verification code format (should be 32 characters for bin2hex)
    if (!preg_match('/^[a-f0-9]{32}$/', $verification_code)) {
        $_SESSION['verify_error'] = 'Invalid verification code.';
        redirect('login_form.php');
    }

    try {
        $db = Database::getInstance();

        // Check if user exists with this email and verification code
        $stmt = $db->prepare("SELECT user_ID, Verified FROM user WHERE email_id = ? AND `Verification Code` = ?");
        $stmt->bind_param('ss', $email, $verification_code);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if ($user['Verified'] == 0) {
                // Update verification status
                $update_stmt = $db->prepare("UPDATE user SET Verified = 1 WHERE user_ID = ?");
                $update_stmt->bind_param('i', $user['user_ID']);

                if ($update_stmt->execute()) {
                    $_SESSION['verify_success'] = 'Email verified successfully! You can now log in.';
                    $update_stmt->close();
                } else {
                    $_SESSION['verify_error'] = 'Verification failed. Please try again.';
                }
            } else {
                $_SESSION['verify_success'] = 'Email already verified. You can log in now.';
            }

            $stmt->close();
        } else {
            $_SESSION['verify_error'] = 'Invalid verification link or email already verified.';
        }

        redirect('login_form.php');

    } catch (Exception $e) {
        error_log('Verification Error: ' . $e->getMessage());
        $_SESSION['verify_error'] = 'Verification failed. Please try again later.';
        redirect('login_form.php');
    }
} else {
    $_SESSION['verify_error'] = 'Invalid verification request.';
    redirect('login_form.php');
}
?>