<<<<<<< HEAD
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

=======
 use PHPMailer\PHPMailer\PHPMailer;
 use PHPMailer\PHPMailer\SMTP;
 use PHPMailer\PHPMailer\Exception;

print_r($_POST);

$connec = new mysqli("localhost", "root", "", "d_labour", 3306);

if ($connec->connect_error) {
    die("Connection failed: " . $connec->connect_error);
}

$name = $_POST['username']; // Use square brackets
$email=$_POST['email'];
$mobile=$_POST['mobile'];
$password = $_POST['password'];
$usertype = $_POST['usertype'];
$vcode = bin2hex(random_bytes(16));

$sql = "INSERT INTO user (user_name, email_id, mobile_no , password, user_type,`Verification Code`,Verified) VALUES ('$name','$email' , '$mobile' ,  '$password', '$usertype','$vcode','0')";

if (mysqli_query($connec, $sql) && sendmail($_POST['email'] , $vcode)) {
    // echo "Successful Insertion";
    if($usertype == "Labour"){
        $redirectUrl="http://localhost/D_Labour_Chowk/Labour/postL.php";
        echo "<script>alert('successfully sign up as Labour');
        window.location.href = '$redirectUrl';</script>";

    }else if($usertype == "User"){
        $redirectUrl = 'sign_up.html';
        echo "<script>alert('Please check you email to login as a verified user');
        window.location.href = '$redirectUrl';</script>";
        
    }
   }
 else {

//     // DUPLICATE ENTERY ERROR 
    $redirectUrl = "sign_up.html";

    echo "<script>alert('Duplicate Entry check the inputs');
window.location.href = '$redirectUrl';</script>";

 }

function sendmail($email,$vcode){
    require ("PHPMailer/PHPMailer.php");
    require ("PHPMailer/SMTP.php");
    require ("PHPMailer/Exception.php");
    
    $mail = new PHPMailer(true);

    try {
        //Server settings

        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'tarunpsgsits07@gmail.com';                     //SMTP username
        $mail->Password   = 'qmkneshljyddbitp';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                          //465  587         //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    
        //Recipients
        $mail->setFrom('tarunpsgsits07@gmail.com', 'Tarun Parmar');
        $mail->addAddress($email);     //Add a recipient
        
       
        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Verify Your E-mail Address';
        $mail->Body    = " Welcome to D Labor Chowk 
        Thank you for registering with us. We're excited to have you on board.<br>
        To ensure the security of you account, we require email verification. <br>Please click the link below
        to confirm you email address. <br>
        Click On => <a href='http://localhost/D_Labour_Chowk/Shared/verify.php?email=". urlencode($email) . "&vcode=" . $vcode . "'>Verify</a>
        ";

       
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->send();
       return true;
     
       

    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        echo "THis error is in catch";
        return false;
    }
}

// header("Location: sign_up.html");
?>


>>>>>>> 39578cd55d61ac8c691bf23cfd350dd7248f990a

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
                $redirectUrl = "http://localhost/D_Labour_Chowk/Labour/postL.php";
                echo "<script>alert('Successfully signed up as Labour. Please verify your email.'); window.location.href = '$redirectUrl';</script>";
            } else if ($usertype == "User") {
                $redirectUrl = 'sign_up.html';
                echo "<script>alert('Please check your email to login as a verified user.'); window.location.href = '$redirectUrl';</script>";
            }
        } else {
            $redirectUrl = "sign_up.html";
            echo "<script>alert('Registration failed or duplicate entry. Please check your inputs.'); window.location.href = '$redirectUrl';</script>";
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
