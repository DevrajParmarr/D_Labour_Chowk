<?php
print_r($_POST);

$connec = new mysqli("localhost", "root", "", "d_labour", 3306);

if ($connec->connect_error) {
    die("Connection failed: " . $connec->connect_error);
}

// $servername = "localhost";
// $username = "root";
// $password = "";
// $database = "notification";

// $con = mysqli_connect($servername,$username,$password,$database);
// if(!$con){
//     die("connection failed" .mysqli.err($con));
// }

if(isset($_GET['email']) && isset($_GET['vcode'])){

    $veri = $_GET['vcode'];

    $query = "SELECT `verified` FROM `user` WHERE `Verification code` = '$veri'";
    $result = mysqli_query($connec , $query);
    
    
    $result_fetch = mysqli_fetch_assoc($result); 
    $verify_bit = "$result_fetch[verified]";
   

    if($verify_bit == 0){

        $update = "UPDATE `user` SET `Verified` = 1 WHERE `Verification code` = '$veri'";
        $result = mysqli_query($connec , $update);
        header('location: login.html');

        // echo "NOw you are a valid user";
    } else if($verify_bit == 1){
        echo "you have signed up now log in to web for more ";
        echo "<script>
    alert('verify'); // Use single or double quotes around the string
</script>";

    }
}

?>