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
    // print_r($row);
    $pastInteractions = rand(5, 50); 
    $rating = rand(3, 5); 

    echo "
    <div class='profile-card'>
        <img src='$row[impath]' alt='Profile Image' class='profile-image'>
        <h2>{$row['user_name']}</h2>
        <p><strong>Mobile:</strong> {$row['mobile_no']}</p>
        <p><strong>Work Type:</strong> {$row['workType']}</p>
        <p><strong>Salary:</strong> {$row['salary']}</p>
        <p><strong>Experience:</strong> {$row['experience']} years</p>
        <hr>
        <p><strong>Past Interactions:</strong> {$pastInteractions}</p>
        <p><strong>Rating:</strong> {$rating} out of 5</p>
    </div>

      <div>
            <a href='appliedJob.php'>
                <button class='btn btn-success'>Your Interest in Job</button>
            </a>
        </div>

    ";
} else {
    echo "No user found.";
}

// Close the connection
$conn->close();
?>

