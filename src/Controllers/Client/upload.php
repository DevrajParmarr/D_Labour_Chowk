<?php
require_once '../Shared/config.php';
session_start();

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'User') {
    header("Location: ../Shared/login_form.php");
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
        $redirectUrl = APP_URL . "/client_/view.php";
        echo "<script>alert('Post created successfully! Click OK to view your posts.');
        setTimeout(function() {
            window.location.href = '$redirectUrl';
        }, 1000);</script>";
    } else {
        echo "<script>alert('Error creating post: " . mysqli_error($conn) . "'); window.location.href='creatjob.php';</script>";
    }

    mysqli_stmt_close($stmt);
} else {
    echo "<script>alert('Database error: " . mysqli_error($conn) . "'); window.location.href='creatjob.php';</script>";
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Post Created</title>
    <link rel="stylesheet" href="creatjob.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background-color: #f4f4f4;
        }
        .success-message {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 0 auto;
        }
        .tick-icon {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="success-message">
        <img src="tick.webp" alt="Success" class="tick-icon">
        <h2>Job Post Created Successfully!</h2>
        <p>Your job post has been created and published.</p>
        <p>You will be redirected to view your posts shortly...</p>
    </div>
</body>
</html>
