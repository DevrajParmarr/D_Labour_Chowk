<?php
session_start();
include "../Shared/sqlconnection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the laborer ID, status, and post ID from the POST request
if (isset($_POST['user_ID']) && isset($_POST['status']) && isset($_POST['post_ID'])) {
    $user_ID = $_POST['user_ID'];
    $status = $_POST['status'];
    $post_ID = $_POST['post_ID'];

    // Update the status in the database
    $query = "UPDATE job_applications SET status = ? WHERE labour_id = ? AND job_post_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("siii", $status, $user_ID, $post_ID);
    
    if ($stmt->execute()) {
        echo "Status updated successfully.";
    } else {
        echo "Error updating status: " . $conn->error;
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>
