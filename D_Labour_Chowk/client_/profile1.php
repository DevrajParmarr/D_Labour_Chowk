<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION["login_status"]) || $_SESSION["login_status"] === false) {
    echo "Unauthorised Attempt";
    exit;
}

// Include the database connection
include "../Shared/sqlconnection.php";

// Use user ID from GET request or session
$user_ID = isset($_GET['user_ID']) ? intval($_GET['user_ID']) : $_SESSION['user_id'];

// Prepare the SQL statement to prevent SQL injection
$sql = "SELECT u.user_name, u.mobile_no, lp.workType, lp.salary, lp.experience, lp.impath 
        FROM user u 
        JOIN lab_post lp ON u.user_ID = lp.user_ID 
        WHERE u.user_ID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_ID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $pastInteractions = rand(5, 50); 
    $rating = rand(3, 5); 

    echo "
    <style>
        .profile-card {
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            background-color: #fff;
            max-width: 300px;
            margin: auto;
        }
        .profile-image {
            width: 100%;
            height: auto;
            border-radius: 50%;
        }
    </style>

    <div class='profile-card'>
        <img src='{$row['impath']}' alt='Profile Image' class='profile-image'>
        <h2>{$row['user_name']}</h2>
        <p><strong>Mobile:</strong> {$row['mobile_no']}</p>
        <p><strong>Job Offered:</strong> {$row['workType']}</p>
        <hr>
        <p><strong>Past Interactions:</strong> $pastInteractions</p>
        <p><strong>Rating:</strong> $rating out of 5</p>
    </div>
    ";
} else {
    echo "No user found.";
}

// Close the connection
$stmt->close();
$conn->close();
?>
