<?php
session_start();
include "../Shared/sqlconnection.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$client_id = filter_var($_POST['client_id'], FILTER_VALIDATE_INT);
$laborer_id = filter_var($_POST['laborer_id'], FILTER_VALIDATE_INT);

if (!$client_id || !$laborer_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
    exit();
}

// Verify the client_id matches the logged-in user
if ($client_id !== $_SESSION['user_id']) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

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
