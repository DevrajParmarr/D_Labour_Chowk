<?php
session_start();
include "../Shared/sqlconnection.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header("Location: ../Shared/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

   $hire_id = filter_var($_POST['hire_id'], FILTER_VALIDATE_INT);
   $labour_id = filter_var($_POST['labour_id'], FILTER_VALIDATE_INT);
   $client_id = filter_var($_POST['client_id'], FILTER_VALIDATE_INT);
   $rating = filter_var($_POST['rating'], FILTER_VALIDATE_INT);
   $review = mysqli_real_escape_string($conn, trim($_POST['review']));

   // Validate inputs
   if (!$hire_id || !$labour_id || !$client_id || !$rating || $rating < 1 || $rating > 5) {
       echo "<script>alert('Invalid input data'); window.history.back();</script>";
       exit();
   }

   // Verify the client_id matches the logged-in user
   if ($client_id !== $_SESSION['user_id']) {
       echo "<script>alert('Unauthorized access'); window.location.href='../Shared/login.php';</script>";
       exit();
   }

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
