<?php


session_start();

$conn=new mysqli ("localhost","root","","d_labour", 3306);
$main_error = null;
$sql_result = mysqli_query($conn, "select * from user where mobile_no='$_POST[mobile_no]' and password='$_POST[password]' ");
// print_r($sql_result);



if($sql_result->num_rows==0){
    header("Location:../Shared/login.html");   
}else {
    $dbrow=mysqli_fetch_assoc($sql_result);
    print_r($dbrow);
    
    $_SESSION["login_status"]=true;
    $_SESSION['user_id']=$dbrow['user_ID'];
    $_SESSION['user_name']=$dbrow['user_name'];
    $_SESSION['user_type']=$dbrow['user_type'];
    
    if (isset($_SESSION['user_name'])) {
        echo "<h1>Hello {$_SESSION['user_name']}</h1>";
        // echo "<h1>Hello {$_SESSION['user_id'] }</h1>";
        
    
    } else {
        echo "User name is not set.";
    }
    
    sleep(3.1);
    
    if($dbrow["user_type"]=="User"){
        header("location:../client_/availableLabour.php");
    }
    if($dbrow["user_type"]=="Labour"){
        header("location:../Labour/postL.php");
    }
}

?>
