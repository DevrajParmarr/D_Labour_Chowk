<?php
session_start();
include "../Shared/sqlconnection.php";

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Capture the POST parameters
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : '';

    // Check for missing parameters
    if (empty($user_id) || empty($status) || empty($post_id)) {
        echo json_encode(['success' => false, 'error' => 'Missing parameters']);
        exit();
    }

    // Update the job application status
    $update_status_query = "UPDATE job_applications SET status = ? WHERE labour_id = ? AND job_post_id = ?";
    $stmt = $conn->prepare($update_status_query);

    if ($stmt === false) {
        echo json_encode(['success' => false, 'error' => 'Failed to prepare statement']);
        exit();
    }

    $stmt->bind_param("sii", $status, $user_id, $post_id);
    $execute_result = $stmt->execute();

    if (!$execute_result) {
        error_log("Error updating status: " . $stmt->error);
        echo json_encode(['success' => false, 'error' => 'Failed to update status']);
        exit();
    }
    $stmt->close();

    // Fetch the client_id related to this job_post_id
    $client_query = "SELECT owner FROM job_post WHERE job_post_id = ?";
    $stmt = $conn->prepare($client_query);

    if ($stmt === false) {
        echo json_encode(['success' => false, 'error' => 'Failed to prepare client query']);
        exit();
    }

    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $stmt->bind_result($client_id);
    $stmt->fetch();
    $stmt->close();

    if (!$client_id) {
        echo json_encode(['success' => false, 'error' => 'Client ID not found for this post']);
        exit();
    }

    // If the status is 'Hired', insert into the 'hires' table
    if (strtolower($status) === 'hired') {
        $insert_hire_query = "INSERT INTO hires (labour_id, client_id ) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_hire_query);

        if ($stmt === false) {
            echo json_encode(['success' => false, 'error' => 'Failed to prepare hire statement']);
            exit();
        }

        $stmt->bind_param("ii", $user_id, $client_id);
        $execute_result = $stmt->execute();

        if (!$execute_result) {
            error_log("Error inserting into hires: " . $stmt->error);
            echo json_encode(['success' => false, 'error' => 'Failed to insert hire']);
            exit();
        }
        $stmt->close();
    }

    // Close the job post after status update
    $update_post_query = "UPDATE job_post SET status = 'closed' WHERE post_ID = ?";
    $stmt = $conn->prepare($update_post_query);

    if ($stmt === false) {
        echo json_encode(['success' => false, 'error' => 'Failed to prepare statement']);
        exit();
    }

    $stmt->bind_param("i", $post_id);
    $execute_result = $stmt->execute();

    if (!$execute_result) {
        error_log("Error closing post: " . $stmt->error);
        echo json_encode(['success' => false, 'error' => 'Failed to close post']);
        exit();
    }
    $stmt->close();

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
}

$conn->close();
?>
