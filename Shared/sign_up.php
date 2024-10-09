<?php
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


