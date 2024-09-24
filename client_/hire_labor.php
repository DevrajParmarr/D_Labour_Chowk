<?php
session_start();
include "../Shared/sqlconnection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$client_id = $_POST['client_id'];
$laborer_id = $_POST['laborer_id'];

$checkQuery = "SELECT * FROM hires WHERE client_id = ? AND labour_id = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("ii", $client_id, $laborer_id);
$stmt->execute();
$checkResult = $stmt->get_result();

if ($checkResult->num_rows > 0) {
    echo json_encode(['status' => 'info', 'message' => 'You have already hired this laborer.']);
    exit();
}

$insertQuery = "INSERT INTO hires (client_id, labour_id) VALUES (?, ?)";
$insertStmt = $conn->prepare($insertQuery);
$insertStmt->bind_param("ii", $client_id, $laborer_id);

if ($insertStmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Laborer successfully hired... Labor will be notified about it']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $insertStmt->error]);
}
?>
