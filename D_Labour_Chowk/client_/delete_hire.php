<?php
session_start();
include "../Shared/sqlconnection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hired_id = $_POST['hire_id'];

    $query = "DELETE FROM hires WHERE hire_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $hired_id);

    if ($stmt->execute()) {
        header("Location: hiredLabour.php");
        exit();
    } else {
        echo "Error deleting record: " . $stmt->error;
    }
}
?>
