<?php
session_start();
include "../Shared/sqlconnection.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
   $hire_id = $_POST['hire_id'];
    $labour_id = $_POST['labour_id'];
    $client_id = $_POST['client_id'];
    $rating = $_POST['rating'];
    $review = $_POST['review'];

   $checkQuery = "SELECT * FROM ratings WHERE client_id = ? AND labour_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ii", $client_id, $labour_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        echo "<script>alert('You have already rated this laborer.'); window.location.href='hiredlabour.php';</script>";
    } else {
    
      $query = "INSERT INTO ratings (hire_id, labour_id, client_id, rating, review) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiss", $hire_id, $labour_id, $client_id, $rating, $review);

        if ($stmt->execute()) {
            echo "<script>alert('Thank you for Rating!'); window.location.href='hiredlabour.php';</script>";
        } else {
            echo "<script>alert('Error: {$stmt->error}'); window.history.back();</script>";
        }

        $stmt->close();
    }

    $checkStmt->close();
} else {
    http_response_code(405);
}
$conn->close();
?>
