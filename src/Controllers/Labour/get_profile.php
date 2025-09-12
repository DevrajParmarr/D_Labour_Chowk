<?php
require_once '../Shared/config.php';

// Check if user is logged in and is a labour
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (getCurrentUserType() !== 'Labour') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$db = Database::getInstance();
$conn = $db->getConnection();
$user_ID = getCurrentUserId();

// Get user details
$sql = "SELECT user_name, mobile_no, email_id, date_created FROM user WHERE user_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_ID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Format date
    $user['date_created'] = date('M Y', strtotime($user['date_created']));

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($user);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'User not found']);
}

$conn->close();
?>