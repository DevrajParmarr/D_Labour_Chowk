<?php
// Combined secure registration logic with email verification and user feedback
require_once 'config.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $password = $_POST['password'] ?? '';
    $usertype = $_POST['usertype'] ?? 'User';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validate CSRF token if present
    if (function_exists('validateCSRFToken') && !$csrf_token || (function_exists('validateCSRFToken') && !validateCSRFToken($csrf_token))) {
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
        $vcode = bin2hex(random_bytes(16));
        $stmt = $db->prepare("INSERT INTO user (user_name, email_id, mobile_no, password, user_type, `Verification Code`, Verified) VALUES (?, ?, ?, ?, ?, ?, '0')");
        $stmt->bind_param('ssssss', $username, $email, $mobile, $hashed_password, $usertype, $vcode);
        if ($stmt->execute() && sendmail($email, $vcode)) {
            if ($usertype == "Labour") {
                $redirectUrl = "http://localhost/D_Labour_Chowk/Labour/dashboard.php";
                echo "<script>alert('Successfully signed up as Labour! Please verify your email to access all features.'); window.location.href = '$redirectUrl';</script>";
            } else if ($usertype == "User") {
                $redirectUrl = "http://localhost/D_Labour_Chowk/client_/dashboard.php";
                echo "<script>alert('Successfully signed up! Please check your email to verify your account.'); window.location.href = '$redirectUrl';</script>";
            }
        } else {
            $error_msg = $stmt->error ? $stmt->error : 'Unknown database error';
            error_log("Signup database error: " . $error_msg);
            $redirectUrl = "signup_form.php";
            echo "<script>alert('Registration failed. Please try again.'); window.location.href = '$redirectUrl';</script>";
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log('Signup Error: ' . $e->getMessage());
        echo "<script>alert('Registration failed. Please try again later.'); window.location.href='signup_form.php';</script>";
    }
}

function sendmail($email, $vcode) {
    require ("PHPMailer/PHPMailer.php");
    require ("PHPMailer/SMTP.php");
    require ("PHPMailer/Exception.php");
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'tarunpsgsits07@gmail.com';
        $mail->Password   = 'qmkneshljyddbitp';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->setFrom('tarunpsgsits07@gmail.com', 'Tarun Parmar');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your E-mail Address';
        $mail->Body    = " Welcome to D Labor Chowk <br>Thank you for registering with us. We're excited to have you on board.<br>To ensure the security of your account, we require email verification. <br>Please click the link below to confirm your email address. <br>Click On => <a href='http://localhost/D_Labour_Chowk/Shared/verify.php?email=". urlencode($email) . "&vcode=" . $vcode . "'>Verify</a>";
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}
?>
