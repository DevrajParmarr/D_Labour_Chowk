<?php
session_start();
include "../Shared/sqlconnection.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in to apply.']);
    exit();
}

$post_ID = $_POST['post_ID'];
$laborer_id = $_SESSION['user_id'];




$checkQuery = "SELECT * FROM job_applications WHERE job_post_id = ? AND labour_id = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("ii", $post_ID, $laborer_id);
$stmt->execute();
$checkResult = $stmt->get_result();

if ($checkResult->num_rows > 0) {
    echo json_encode(['status' => 'info', 'message' => 'You have already applied for this job.']);
    exit();
}

$insertQuery = "INSERT INTO job_applications (job_post_id, labour_id) VALUES (?, ?)";
$insertStmt = $conn->prepare($insertQuery);
$insertStmt->bind_param("ii", $post_ID, $laborer_id);

if ($insertStmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'You have successfully applied for the job!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred: ' . $insertStmt->error]);
}
?>
