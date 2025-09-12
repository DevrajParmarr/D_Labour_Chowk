<?php
session_start();
include "../Shared/sqlconnection.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'labour') {
    header("Location: ../Shared/login.php");
    exit();
}

$post_id = filter_var($_GET["l_post_ID"], FILTER_VALIDATE_INT);

if (!$post_id) {
    echo "Invalid post ID";
    exit();
}

// Verify that the post belongs to the logged-in labour
$verify_query = "SELECT post_ID FROM lab_post WHERE post_ID = ? AND user_ID = ?";
$verify_stmt = mysqli_prepare($conn, $verify_query);
mysqli_stmt_bind_param($verify_stmt, "ii", $post_id, $_SESSION['user_id']);
mysqli_stmt_execute($verify_stmt);
$verify_result = mysqli_stmt_get_result($verify_stmt);

if (mysqli_num_rows($verify_result) === 0) {
    echo "Unauthorized access - post not found or doesn't belong to you";
    exit();
}
mysqli_stmt_close($verify_stmt);

$query = "DELETE FROM lab_post WHERE post_ID = ? AND user_ID = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $post_id, $_SESSION['user_id']);

if (mysqli_stmt_execute($stmt)) {
    header('location:viewLP.php');
} else {
    echo "Error deleting post: " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>