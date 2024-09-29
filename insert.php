<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


$msg = "";
$servername = "localhost";
$username = "root";
$password = "";
$database = "notification";

$con = mysqli_connect($servername,$username,$password,$database);
if(!$con){
    die("connection failed" .mysqli.err($con));
}

$name = $_POST['name'];
$email = $_POST['email'];
$vcode = bin2hex(random_bytes(16));


$check = mysqli_num_rows(mysqli_query($con,"select * from notif where email = '$email'"));
if($check > 0){
    $msg = "Email Already Exists";

}else{
    

    $sql = "INSERT INTO `notif` (`Name`, `email`,`Verification Code`, `Verified`) VALUES ('$name' , '$email','$vcode','0')";
    $query = (mysqli_query($con , $sql) && sendmail($_POST['email'] , $vcode));

    

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
        Click On => <a href='http://localhost/phpt/verify.php?email=" . urlencode($email) . "&vcode=" . $vcode . "'>Verify</a>
        ";

       
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->send();
       return true;
    //    
       

    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        echo "THis error is in catch";
        return false;
    }
    // header('Location : http://localhost/phpt/home.html');
}


?>


        