<?php

session_start();

// Check if user is logged in and is a labour
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'labour') {
    header("Location: ../Shared/login.php");
    exit();
}

// Validate file upload
if (!isset($_FILES["pdtimg"]) || $_FILES["pdtimg"]["error"] !== UPLOAD_ERR_OK) {
    echo "<script>alert('File upload error'); window.location.href='post_work.php';</script>";
    exit();
}

// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($_FILES["pdtimg"]["type"], $allowed_types)) {
    echo "<script>alert('Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.'); window.location.href='post_work.php';</script>";
    exit();
}

// Validate file size (max 5MB)
if ($_FILES["pdtimg"]["size"] > 5 * 1024 * 1024) {
    echo "<script>alert('File too large. Maximum size is 5MB.'); window.location.href='post_work.php';</script>";
    exit();
}

// Sanitize filename and create unique name
$original_name = basename($_FILES["pdtimg"]["name"]);
$extension = pathinfo($original_name, PATHINFO_EXTENSION);
$safe_filename = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9]/", "", pathinfo($original_name, PATHINFO_FILENAME)) . '.' . $extension;
$file_name = "../Shared/images/L_images/" . $safe_filename;

$source_path = $_FILES["pdtimg"]["tmp_name"];

if (!move_uploaded_file($source_path, $file_name)) {
    echo "<script>alert('Failed to upload file'); window.location.href='post_work.php';</script>";
    exit();
}

include "../Shared/sqlconnection.php";

// Sanitize and validate input
$workType = mysqli_real_escape_string($conn, trim($_POST["workType"]));
$salary = filter_var($_POST["salary"], FILTER_VALIDATE_INT);
$experience = mysqli_real_escape_string($conn, trim($_POST["experience"]));
$location = mysqli_real_escape_string($conn, trim($_POST["location"]));
$city = mysqli_real_escape_string($conn, trim($_POST["city"]));

// Validate required fields
if (empty($workType) || !$salary || empty($experience) || empty($location) || empty($city)) {
    echo "<script>alert('All fields are required'); window.location.href='post_work.php';</script>";
    exit();
}

$query = "INSERT INTO lab_post(user_ID, workType, experience, salary, location, city, impath) VALUES (?, ?, ?, ?, ?, ?, ?)";

if ($stmt = mysqli_prepare($conn, $query)) {
    mysqli_stmt_bind_param($stmt, "ississs", $_SESSION['user_id'], $workType, $experience, $salary, $location, $city, $file_name);

    if (mysqli_stmt_execute($stmt)) {
        echo "<script>alert('Post created successfully!'); window.location.href='viewLP.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Error preparing statement: " . mysqli_error($conn);
}

mysqli_close($conn);
?>


<!-- mysqli_query($conn,$query ); -->


<!-- <form action="view.php" method="get">
    <button type="submit">View Product</button>
</form> -->
