<?php


session_start();
echo "<!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Login Success</title>
        <link rel='stylesheet' href='login.css'>
        <script src='login.js'></script> 
    </head>
    <body>
    <div class='popup' id='popup'>
                    <img src='tick.webp' alt='GreenTick'>
                    <pre>LogIn Success!</pre>
                </div>
                </body>";

$conn=new mysqli ("localhost","root","","d_labour", 3306);
$main_error = null;
$sql_result = mysqli_query($conn, "select * from user where mobile_no='$_POST[mobile_no]' and password='$_POST[password]' ");
// print_r($sql_result);
$redirectUrl = 'login.html';


if($sql_result->num_rows==0){

    // echo "<script>console.log('user');</script>";
    // echo "<script type = 'text/javascript'>openPopup()</script>";

    // sleep(5);
    // echo "<script> window.location.href = '$redirectUrl'</script>";
    


    echo "<script>alert('Check the Inputs or Signup first if not done');
     window.location.href = '$redirectUrl'; // Redirect after alert</script>";
    // header("Location:../Shared/login.html");   
}else {
    $dbrow=mysqli_fetch_assoc($sql_result);
    // print_r($dbrow);
    
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
    
    
    // sleep(3.1);

    

    
    if($dbrow["user_type"]=="User"){
        $redirectUrl = '../client_/availableLabour.php';
        // echo "<script>console.log('user');</script>";
        // echo "<script type = 'text/javascript'>openPopup()</script>";

    //     sleep(10);

        echo "<script>alert('click ok to Start your journey');
     window.location.href = '$redirectUrl'; // Redirect after alert</script>";

        // header("location:../client_/availableLabour.php");
    }
    if($dbrow["user_type"]=="Labour"){
        header("location:../Labour/postL.php");
    }
}

?>
