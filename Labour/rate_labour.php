<?php
session_start();
include "../Shared/sqlconnection.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $labour_id = $_POST['labour_id'];
    $user_id = $_SESSION['user_id'];  // Assuming user is logged in and has an ID in the session
    $rating = $_POST['rating'];
    $review = $_POST['review'];

    // Insert the rating into the database
    $stmt = $conn->prepare("INSERT INTO ratings (labour_id, user_id, rating, review) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $labour_id, $user_id, $rating, $review);

    if ($stmt->execute()) {
        echo "Rating submitted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
