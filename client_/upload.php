<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post pop up</title>
    <link rel="stylesheet" href="creatjob.css">
    <script> 
    function openPopup(){
     let Timeout;
     let popup = document.getElementById("popup");
     popup.classList.add("openPopup");
     Timeout = setTimeout(closePopup, 2000);
    }
function closePopup(){
    popup.classList.remove("openPopup");
}
    </script>
</head>
<body>
<div class="popup" id="popup">
       <img src="tick.webp" alt="GreenTick">
       <pre>Job Created</pre> 
</div>
</body>
</html>

<?php

session_start();

// print_r($_POST);
// echo "<br>";
// print_r($_FILES);

$source_path=$_FILES["pdtimg"]["tmp_name"];
$file_name="../Shared/images/".$_FILES["pdtimg"]['name'];

// <br>
// echo "temp file is in $source_path";
// <br>
// echo "File name= $file_name";

move_uploaded_file($source_path,$file_name);

include "../Shared/sqlconnection.php";
$jobTitle=$_POST["jobTitle"];
$salary=$_POST["salary"];
$detail=$_POST["detail"];

$city=$_POST["city"];
$location=$_POST["location"];

$query="insert into job_post(jobTitle,salary,detail,city,location,impath,owner) values('$jobTitle', $salary , '$detail' ,'$city','$location', '$file_name' ,{$_SESSION['user_id']} )";

// echo "$query";


if (mysqli_query($conn, $query)) {
    $redirectUrl = 'view.php';


    echo "<script type = 'text/javascript'>openPopup();</script>";
    echo "<script>
    setTimeout(function() {
        window.location.href = '$redirectUrl';
    }, 3000); 
  </script>";


    //   echo "<h1>Successful Insertion</h1>";
    //   header('location:view.php');
} else {
    
    echo "Error: " . $query . "<br>" . mysqli_error($conn);
}

mysqli_close($conn);
?>


<!-- mysqli_query($conn,$query ); -->


<!-- <form action="view.php" method="get">
    <button type="submit">View Product</button>
</form> -->
