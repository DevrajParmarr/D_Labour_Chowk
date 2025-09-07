<?php
session_start();
include "../Shared/sqlconnection.php";

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'You must be logged in.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

$job_post_id = $_POST['job_post_id'] ?? '';
$labour_id = $_SESSION['user_id'];

if (empty($job_post_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Job post ID is required.']);
    exit();
}

// Check if the application exists and belongs to the current user
$checkQuery = "SELECT ja.*, jp.jobTitle FROM job_applications ja
               JOIN job_post jp ON ja.job_post_id = jp.post_ID
               WHERE ja.job_post_id = ? AND ja.labour_id = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("ii", $job_post_id, $labour_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Application not found.']);
    exit();
}

$application = $result->fetch_assoc();

// Only allow withdrawal if status is pending
if (strtolower($application['status']) !== 'pending') {
    echo json_encode(['status' => 'error', 'message' => 'You can only withdraw pending applications.']);
    exit();
}

// Delete the application
$deleteQuery = "DELETE FROM job_applications WHERE job_post_id = ? AND labour_id = ?";
$deleteStmt = $conn->prepare($deleteQuery);
$deleteStmt->bind_param("ii", $job_post_id, $labour_id);

if ($deleteStmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Application withdrawn successfully.',
        'job_title' => $application['jobTitle']
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to withdraw application.']);
}

$deleteStmt->close();
$conn->close();
?>