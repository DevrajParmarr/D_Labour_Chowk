<?php
// Secure login processing script
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobile_no = isset($_POST['mobile_no']) ? trim($_POST['mobile_no']) : '';
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validate CSRF token
    if (!validateCSRFToken($csrf_token)) {
        $_SESSION['login_error'] = 'Invalid request. Please try again.';
        redirect('login_form.php');
    }

    // Validate input
    if (empty($mobile_no) || empty($password)) {
        $_SESSION['login_error'] = 'Please fill in all required fields.';
        redirect('login_form.php');
    }

    // Validate mobile number format
    if (!preg_match('/^[0-9]{10}$/', $mobile_no)) {
        $_SESSION['login_error'] = 'Please enter a valid 10-digit mobile number.';
        redirect('login_form.php');
    }

    try {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT user_ID, user_name, password, user_type, mobile_no, Verified FROM user WHERE mobile_no = ?");
        $stmt->bind_param('s', $mobile_no);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Check if email is verified
            // if ($user['Verified'] != 1) {
            //     $_SESSION['login_error'] = 'Please verify your email address before logging in.';
            //     redirect('login_form.php');
            // }

            // Verify password (using plain text for existing users)
            if ($password === $user['password']) {
                // Regenerate session ID for security
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['user_ID'];
                $_SESSION['user_name'] = $user['user_name'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['last_activity'] = time();

                // Redirect based on user type
                if ($user['user_type'] === 'User') {
                    redirect('../client_/dashboard.php');
                } elseif ($user['user_type'] === 'Labour') {
                    redirect('../Labour/dashboard.php');
                } else {
                    redirect('../admin/dashboard.php');
                }
            } else {
                $_SESSION['login_error'] = 'Invalid mobile number or password.';
                redirect('login_form.php');
            }
        } else {
            $_SESSION['login_error'] = 'Invalid mobile number or password.';
            redirect('login_form.php');
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log('Login Error: ' . $e->getMessage());
        $_SESSION['login_error'] = 'Login failed. Please try again later.';
        redirect('login_form.php');
    }
} else {
    // Redirect to login form if not POST request
    redirect('login_form.php');
}
?>
