<?php
require_once 'config.php';

// Handle signup request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $mobile = sanitizeInput($_POST['mobile'] ?? '');
    $password = $_POST['password'] ?? '';
    $usertype = sanitizeInput($_POST['usertype'] ?? 'User');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Validate CSRF token
    if (!validateCSRFToken($csrf_token)) {
        $_SESSION['signup_error'] = 'Invalid request. Please try again.';
        redirect('signup_form.php');
    }
    
    // Validate input
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters long.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    
    if (empty($mobile)) {
        $errors[] = 'Mobile number is required.';
    } elseif (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $errors[] = 'Mobile number must be 10 digits.';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }
    
    if (!in_array($usertype, ['User', 'Labour'])) {
        $errors[] = 'Invalid user type.';
    }
    
    if (!empty($errors)) {
        $_SESSION['signup_error'] = implode('<br>', $errors);
        redirect('signup_form.php');
    }
    
    try {
        $db = Database::getInstance();
        
        // Check if email or mobile already exists
        $stmt = $db->prepare("SELECT user_ID FROM user WHERE email_id = ? OR mobile_no = ?");
        $stmt->bind_param('ss', $email, $mobile);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $_SESSION['signup_error'] = 'Email or mobile number already exists.';
            redirect('signup_form.php');
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => BCRYPT_COST]);
        
        // Insert new user
        $stmt = $db->prepare("INSERT INTO user (user_name, email_id, mobile_no, password, user_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', $username, $email, $mobile, $hashed_password, $usertype);
        
        if ($stmt->execute()) {
            $_SESSION['signup_success'] = 'Account created successfully! Please login.';
            redirect('login_form.php');
        } else {
            $_SESSION['signup_error'] = 'Registration failed. Please try again.';
            redirect('signup_form.php');
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        error_log('Signup Error: ' . $e->getMessage());
        $_SESSION['signup_error'] = 'Registration failed. Please try again later.';
        redirect('signup_form.php');
    }
} else {
    // Redirect to signup form if not POST request
    redirect('signup_form.php');
}
?>

