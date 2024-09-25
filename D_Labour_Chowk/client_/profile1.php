<?php
session_start();
if(!isset($_SESSION["login_status"])){
    echo "Login in skipped";
    die;
}

if($_SESSION["login_status"]==false){
    echo "Unauthorised Attempt";
    die;
}

// include "menu.html";
include "../Shared/sqlconnection.php";

$user_ID = $_SESSION['user_id'];

$sql = "SELECT user_name, mobile_no 
        FROM user 
        WHERE user_ID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_ID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // print_r($row);
    $pastInteractions = rand(5, 50); 
    $rating = rand(3, 5); 

    echo "
    <div class='profile-card'>
    <img src='../Shared/images/profile.png' alt='Profile Image' class='profile-image'>
        <h2>{$row['user_name']}</h2>
        <p><strong>Mobile:</strong> {$row['mobile_no']}</p>
        <p><strong>Past Interactions:</strong> {$pastInteractions}</p>
        <p><strong>Rating:</strong> {$rating} out of 5</p>
    </div>

    
    <div>
        <a href='hiredLabour.php'>
            <button class='btn btn-outline-secondary '> hired labour </button>
        </a>
    </div>
    ";
} else {
    echo "No user found.";
}
        // 
// Close the connection
$conn->close();
?>

