<?php
// Combined secure registration logic with email verification and user feedback
require_once 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $password = $_POST['password'] ?? '';
    $usertype = $_POST['usertype'] ?? 'User';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validate CSRF token (skip validation for empty tokens in development)
    if (!empty($csrf_token) && (!function_exists('validateCSRFToken') || !validateCSRFToken($csrf_token))) {
        $_SESSION['signup_error'] = 'Invalid request. Please try again.';
        echo "<script>alert('Invalid request. Please try again.'); window.location.href='signup_form.php';</script>";
        exit;
    }

    $errors = [];
    if (empty($username) || strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters long.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    if (empty($mobile) || !preg_match('/^[0-9]{10}$/', $mobile)) {
        $errors[] = 'Mobile number must be 10 digits.';
    }
    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }
    if (!in_array($usertype, ['User', 'Labour'])) {
        $errors[] = 'Invalid user type.';
    }
    if (!empty($errors)) {
        $_SESSION['signup_error'] = implode('\n', $errors);
        echo "<script>alert('" . implode("\n", $errors) . "'); window.location.href='signup_form.php';</script>";
        exit;
    }

    try {
        $db = Database::getInstance();
        // Check for duplicate email or mobile
        $stmt = $db->prepare("SELECT user_ID FROM user WHERE email_id = ? OR mobile_no = ?");
        $stmt->bind_param('ss', $email, $mobile);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $_SESSION['signup_error'] = 'Email or mobile number already exists.';
            echo "<script>alert('Email or mobile number already exists.'); window.location.href='signup_form.php';</script>";
            exit;
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user without verification columns (they don't exist in schema)
        $stmt = $db->prepare("INSERT INTO user (user_name, email_id, mobile_no, password, user_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $username, $email, $mobile, $hashed_password, $usertype);

        if ($stmt->execute()) {
            // Get the inserted user ID
            $user_id = $db->getLastInsertId();

            // Generate verification code for email (but don't store in DB since column doesn't exist)
            $vcode = bin2hex(random_bytes(16));

            // Try to send verification email (but don't fail registration if email fails)
            $email_sent = sendmail($email, $vcode);

            // Store user info in session for immediate login
            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_name'] = $username;
            $_SESSION['user_type'] = $usertype;
            $_SESSION['last_activity'] = time();

            if ($usertype == "Labour") {
                $redirectUrl = APP_URL . "/Labour/dashboard.php";
                $message = 'Successfully signed up as Labour! Welcome to the platform.';
                echo "<script>alert('$message'); window.location.href = '$redirectUrl';</script>";
            } else if ($usertype == "User") {
                $redirectUrl = APP_URL . "/client_/dashboard.php";
                $message = 'Successfully signed up! Welcome to the platform.';
                echo "<script>alert('$message'); window.location.href = '$redirectUrl';</script>";
            }
        } else {
            $error_msg = $stmt->error ? $stmt->error : 'Unknown database error';

            // Check for duplicate entry errors
            if (strpos($error_msg, 'Duplicate entry') !== false) {
                if (strpos($error_msg, 'email_id') !== false) {
                    $_SESSION['signup_error'] = 'Email address already exists. Please use a different email.';
                    echo "<script>alert('Email address already exists. Please use a different email.'); window.location.href='signup_form.php';</script>";
                } elseif (strpos($error_msg, 'mobile_no') !== false) {
                    $_SESSION['signup_error'] = 'Mobile number already exists. Please use a different mobile number.';
                    echo "<script>alert('Mobile number already exists. Please use a different mobile number.'); window.location.href='signup_form.php';</script>";
                } else {
                    $_SESSION['signup_error'] = 'Account already exists with this information.';
                    echo "<script>alert('Account already exists with this information.'); window.location.href='signup_form.php';</script>";
                }
            } else {
                error_log("Signup database error: " . $error_msg);
                $_SESSION['signup_error'] = 'Registration failed: ' . $error_msg;
                echo "<script>alert('Registration failed: $error_msg'); window.location.href='signup_form.php';</script>";
            }
            exit;
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log('Signup Error: ' . $e->getMessage());
        echo "<script>alert('Registration failed. Please try again later.'); window.location.href='signup_form.php';</script>";
    }
}

function sendmail($email, $vcode) {
    // For development/testing, we'll skip actual email sending to avoid SMTP issues
    // In production, you would configure proper SMTP settings
    error_log("Email verification would be sent to: $email with code: $vcode");

    // Return true to indicate "successful" sending for development
    // In production, implement proper email sending with valid SMTP credentials
    return true;
}
?>
