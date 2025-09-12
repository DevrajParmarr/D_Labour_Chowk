<?php
session_start();
include "../Shared/sqlconnection.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'User') {
    header("Location: ../Shared/login_form.php");
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
       $_SESSION['rating_error'] = 'Invalid input data. Please check your rating and try again.';
       header("Location: hiredLabour.php");
       exit();
   }

   // Verify the client_id matches the logged-in user
   if ($client_id !== $_SESSION['user_id']) {
       $_SESSION['rating_error'] = 'Unauthorized access. Please login and try again.';
       header("Location: ../Shared/login_form.php");
       exit();
   }

   $checkQuery = "SELECT * FROM ratings WHERE client_id = ? AND labour_id = ?";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ii", $client_id, $labour_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $_SESSION['rating_error'] = 'You have already rated this labourer.';
        header("Location: hiredLabour.php");
        exit();
    } else {

      $query = "INSERT INTO ratings (hire_id, labour_id, client_id, rating, review) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiiss", $hire_id, $labour_id, $client_id, $rating, $review);

        if ($stmt->execute()) {
            $_SESSION['rating_success'] = 'Thank you for your rating! Your feedback helps improve our platform.';
            header("Location: hiredLabour.php");
            exit();
        } else {
            $_SESSION['rating_error'] = 'Error submitting rating. Please try again.';
            header("Location: hiredLabour.php");
            exit();
        }

        $stmt->close();
    }

    $checkStmt->close();
} else {
    http_response_code(405);
}
$conn->close();
?>
