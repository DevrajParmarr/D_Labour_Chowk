<?php
session_start();
include "../Shared/sqlconnection.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header("Location: ../Shared/login.php");
    exit();
}

$post_id = filter_var($_GET["post_ID"], FILTER_VALIDATE_INT);

if (!$post_id) {
    echo "Invalid post ID";
    exit();
}

// Verify that the post belongs to the logged-in client
$verify_query = "SELECT job_post_id FROM job_post WHERE job_post_id = ? AND owner = ?";
$verify_stmt = mysqli_prepare($conn, $verify_query);
mysqli_stmt_bind_param($verify_stmt, "ii", $post_id, $_SESSION['user_id']);
mysqli_stmt_execute($verify_stmt);
$verify_result = mysqli_stmt_get_result($verify_stmt);

if (mysqli_num_rows($verify_result) === 0) {
    echo "Unauthorized access - post not found or doesn't belong to you";
    exit();
}
mysqli_stmt_close($verify_stmt);

$query = "DELETE FROM job_post WHERE job_post_id = ? AND owner = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $post_id, $_SESSION['user_id']);

$redirectUrl = "view.php";

if (mysqli_stmt_execute($stmt)) {
    echo "<script type='text/javascript'>window.location.href='$redirectUrl';</script>";
} else {
    echo "Error deleting post: " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>