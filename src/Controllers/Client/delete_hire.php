<?php
session_start();
include "../Shared/sqlconnection.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header("Location: ../Shared/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hired_id = filter_var($_POST['hire_id'], FILTER_VALIDATE_INT);

    if (!$hired_id) {
        echo "Invalid hire ID";
        exit();
    }

    // First verify that this hire belongs to the logged-in client
    $verify_query = "SELECT hire_id FROM hires WHERE hire_id = ? AND client_id = ?";
    $verify_stmt = $conn->prepare($verify_query);
    $verify_stmt->bind_param("ii", $hired_id, $_SESSION['user_id']);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();

    if ($verify_result->num_rows === 0) {
        echo "Unauthorized access - hire not found or doesn't belong to you";
        exit();
    }
    $verify_stmt->close();

    $query = "DELETE FROM hires WHERE hire_id = ? AND client_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $hired_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        header("Location: hiredLabour.php");
        exit();
    } else {
        echo "Error deleting record: " . $stmt->error;
    }
    $stmt->close();
}
?>
