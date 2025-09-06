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

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header("Location: ../Shared/login.php");
    exit();
}

// Validate file upload
if (!isset($_FILES["pdtimg"]) || $_FILES["pdtimg"]["error"] !== UPLOAD_ERR_OK) {
    echo "<script>alert('File upload error'); window.location.href='creatjob.php';</script>";
    exit();
}

// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($_FILES["pdtimg"]["type"], $allowed_types)) {
    echo "<script>alert('Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.'); window.location.href='creatjob.php';</script>";
    exit();
}

// Validate file size (max 5MB)
if ($_FILES["pdtimg"]["size"] > 5 * 1024 * 1024) {
    echo "<script>alert('File too large. Maximum size is 5MB.'); window.location.href='creatjob.php';</script>";
    exit();
}

// Sanitize filename and create unique name
$original_name = basename($_FILES["pdtimg"]["name"]);
$extension = pathinfo($original_name, PATHINFO_EXTENSION);
$safe_filename = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9]/", "", pathinfo($original_name, PATHINFO_FILENAME)) . '.' . $extension;
$file_name = "../Shared/images/" . $safe_filename;

$source_path = $_FILES["pdtimg"]["tmp_name"];

if (!move_uploaded_file($source_path, $file_name)) {
    echo "<script>alert('Failed to upload file'); window.location.href='creatjob.php';</script>";
    exit();
}

include "../Shared/sqlconnection.php";

// Sanitize and validate input
$jobTitle = mysqli_real_escape_string($conn, trim($_POST["jobTitle"]));
$salary = filter_var($_POST["salary"], FILTER_VALIDATE_INT);
$detail = mysqli_real_escape_string($conn, trim($_POST["detail"]));
$city = mysqli_real_escape_string($conn, trim($_POST["city"]));
$location = mysqli_real_escape_string($conn, trim($_POST["location"]));

// Validate required fields
if (empty($jobTitle) || !$salary || empty($detail) || empty($city) || empty($location)) {
    echo "<script>alert('All fields are required'); window.location.href='creatjob.php';</script>";
    exit();
}

$query = "INSERT INTO job_post(jobTitle, salary, detail, city, location, impath, owner) VALUES (?, ?, ?, ?, ?, ?, ?)";

if ($stmt = mysqli_prepare($conn, $query)) {
    mysqli_stmt_bind_param($stmt, "sissssi", $jobTitle, $salary, $detail, $city, $location, $file_name, $_SESSION['user_id']);

    if (mysqli_stmt_execute($stmt)) {
        $redirectUrl = "http://localhost/D_Labour_Chowk/client_/view.php";
//     echo "<script type = 'text/javascript'>openPopup();</script>";
//     echo "<script>
//     setTimeout(function() {
//         window.location.href = '$redirectUrl';
//     }, 3000); 
//   </script>";

     echo "<script>alert('post created to view post click OK');

     setTimeout(function() {
        window.location.href = '$redirectUrl';
     }, 0000)</script>";


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
