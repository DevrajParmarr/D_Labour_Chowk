<!DOCTYPE html>
<html lang="en">

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="login.css">
    

</head>

<body>
    <div class="d-flex justify-content-center align-items-center vh-100">
        <form action="login.php" method="post" class="w-50  bg-info p-4">
            <h5 class="text-center">Login to D_Labour_Chowk</h5>

            <input required class="form-control mt-4" type="text" placeholder="Enter Mobile Number" name="mobile_no" id="contact">

            <div class="text-center mt-3">
                <div class="input-group mt-2">
                    <input required class="form-control" type="password" placeholder="Password" id="password"
                        name="password">
                    <p class="password-error"></p>
                    <button type="button" class="btn btn-outline-secondary" type="button" onclick="toggle()"
                        id="showbtn " style="background-color: aliceblue;">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>

                <button class="btn btn-warning mt-3 w-25" id="login">Login</button>
                

                <div class="popup" id="popup">
                    <img src="tick.webp" alt="GreenTick">
                    <pre>LogIn Success!</pre>
                </div>
                <div class="popup" id="erpopup">
                   <img src="red cross.png" alt="img">
                   <pre>Check Details</pre>
                </div>

            </div>

            <div class="text-end mt-3">
                <span class="text-start ">If you are new user </span>
                <a class="text-danger " href="sign_up.html">Sign Up here</a>
            </div>


    </div>

    </form>
    </div>


    <!-- JAVA SCRIPT -->
    <script>
        function toggle() {
            if (state === 'show') {
                state = 'hide';
                pass2Obj.type = 'password';
                show.innerHTML = '<i class="bi bi-eye"></i>';
            } else if (state === 'hide') {
                state = 'show';
                pass2Obj.type = 'text';
                show.innerHTML = '<i class="bi bi-eye-slash"></i>';
            }
        }
        function openPopup() {
            console.log("function called");
            let Timeout;
             var element = document.getElementById("popup");
              if (element) { // Check if the element exists
                 element.classList.add("openPopup"); // Add the class
                 Timeout = setTimeout(closePopup, 3000);
             } else {
                console.error("Element not found!");
         }
        }
function closePopup() {
    popup.classList.remove("openPopup");
}
function wrong_credential(){
    let Timeout;
    var wrongpass = document.getElementById("password");
    var wrongcont = document.getElementById("contact");
    wrongpass.classList.add("htmlerror");
    wrongcont.classList.add("htmlerror");
    Timeout = setTimeout(close ,3000);
}
function close(){
    var wrongpass = document.getElementById("password");
    var wrongcont = document.getElementById("contact");
    
    wrongpass.classList.remove("htmlerror");
    wrongcont.classList.remove("htmlerror");
}
function eropenPopup(){
    let erpop = document.getElementById("erpopup");
    erpop.classList.add("eropenPopup");
    timeout = setTimeout(close1,3000);
}
function close1(){
    let erpop = document.getElementById("erpopup");
    erpop.classList.remove("eropenPopup");
}

    </script>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</body>

</html>

<!-- PHP CODE STARTS -->

<<<<<<< HEAD
require_once 'config.php';

// Handle login request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobile_no = sanitizeInput($_POST['mobile_no'] ?? '');
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
    
    try {
        $db = Database::getInstance();
        
        // Use prepared statement to prevent SQL injection
        $stmt = $db->prepare("SELECT user_ID, user_name, password, user_type, mobile_no FROM user WHERE mobile_no = ?");
        $stmt->bind_param('s', $mobile_no);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password (assuming we'll implement proper hashing later)
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                // Login successful
                $_SESSION['login_status'] = true;
                $_SESSION['user_id'] = $user['user_ID'];
                $_SESSION['user_name'] = $user['user_name'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['last_activity'] = time();
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
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
=======
session_start();

$conn=new mysqli ("localhost","root","","d_labour", 3306);
$sql_result=mysqli_query($conn, "select * from user where mobile_no='$_POST[mobile_no]' and password='$_POST[password]' ");
// print_r($sql_result);

if($sql_result->num_rows==0){

    echo "<script type = 'text/javascript'>wrong_credential();</script>";
    echo "<script type ='text/javascript'>eropenPopup();</script>";
      
}else {
    $dbrow=mysqli_fetch_assoc($sql_result);
    
    $_SESSION["login_status"]=true;
    $_SESSION['user_id']=$dbrow['user_ID'];
    $_SESSION['user_name']=$dbrow['user_name'];
    $_SESSION['user_type']=$dbrow['user_type'];
    
    // if (isset($_SESSION['user_name'])) {
    //     echo "<h1>Hello {$_SESSION['user_name']}</h1>";
    //     // echo "<h1>Hello {$_SESSION['user_id'] }</h1>";
        
    
    // } else {
    //     echo "User name is not set.";
    // }
    
    if($dbrow["user_type"]=="User"){
        $redirectUrl = '../client_/availableLabour.php';

        echo "<script>console.log('user');</script>";
    echo "<script type = 'text/javascript'>openPopup()</script>";

    echo "<script>
    setTimeout(function() {
        window.location.href = '$redirectUrl';
    }, 3000); 
  </script>";
  
        

    //     echo "<script>alert('click ok to Start your journey');
    //  window.location.href = '$redirectUrl'; // Redirect after alert</script>";
    }
    // if($dbrow["user_type"]=="Labour"){
    //     header("location:../Labour/postL.php");
    // }
}

>>>>>>> 39578cd55d61ac8c691bf23cfd350dd7248f990a
?>

<?php
// Combined secure login logic with user feedback
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mobile_no = isset($_POST['mobile_no']) ? trim($_POST['mobile_no']) : '';
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validate CSRF token if present
    if (function_exists('validateCSRFToken') && !$csrf_token || (function_exists('validateCSRFToken') && !validateCSRFToken($csrf_token))) {
        $_SESSION['login_error'] = 'Invalid request. Please try again.';
        echo "<script type='text/javascript'>wrong_credential();</script>";
        echo "<script type ='text/javascript'>eropenPopup();</script>";
        exit;
    }

    if (empty($mobile_no) || empty($password)) {
        $_SESSION['login_error'] = 'Please fill in all required fields.';
        echo "<script type='text/javascript'>wrong_credential();</script>";
        echo "<script type ='text/javascript'>eropenPopup();</script>";
        exit;
    }

    try {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT user_ID, user_name, password, user_type, mobile_no FROM user WHERE mobile_no = ?");
        $stmt->bind_param('s', $mobile_no);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            // Support both hashed and plain passwords for backward compatibility
            if (password_verify($password, $user['password']) || $password === $user['password']) {
                $_SESSION['login_status'] = true;
                $_SESSION['user_id'] = $user['user_ID'];
                $_SESSION['user_name'] = $user['user_name'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['last_activity'] = time();
                session_regenerate_id(true);

                // Show popup and redirect based on user type
                if ($user['user_type'] === 'User') {
                    $redirectUrl = '../client_/availableLabour.php';
                } elseif ($user['user_type'] === 'Labour') {
                    $redirectUrl = '../Labour/dashboard.php';
                } else {
                    $redirectUrl = '../admin/dashboard.php';
                }
                echo "<script type='text/javascript'>openPopup();</script>";
                echo "<script>setTimeout(function() { window.location.href = '$redirectUrl'; }, 3000);</script>";
                exit;
            } else {
                $_SESSION['login_error'] = 'Invalid mobile number or password.';
                echo "<script type='text/javascript'>wrong_credential();</script>";
                echo "<script type ='text/javascript'>eropenPopup();</script>";
                exit;
            }
        } else {
            $_SESSION['login_error'] = 'Invalid mobile number or password.';
            echo "<script type='text/javascript'>wrong_credential();</script>";
            echo "<script type ='text/javascript'>eropenPopup();</script>";
            exit;
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log('Login Error: ' . $e->getMessage());
        $_SESSION['login_error'] = 'Login failed. Please try again later.';
        echo "<script type='text/javascript'>wrong_credential();</script>";
        echo "<script type ='text/javascript'>eropenPopup();</script>";
        exit;
    }
}
?>
