<?php
session_start();
include "../Shared/sqlconnection.php";

$labour_id = $_GET['labour_id'];  // Labour ID passed via URL

// Fetch and display the average rating for a specific labour
$sql = "SELECT AVG(rating) as avg_rating FROM ratings WHERE labour_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $labour_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$avg_rating = round($row['avg_rating'], 2);

echo "<h3>Average Rating: $avg_rating / 5</h3>";

// Display individual reviews
$sql_reviews = "SELECT r.rating, r.review, u.username FROM ratings r JOIN users u ON r.user_id = u.id WHERE r.labour_id = ?";
$stmt_reviews = $conn->prepare($sql_reviews);
$stmt_reviews->bind_param("i", $labour_id);
$stmt_reviews->execute();
$reviews_result = $stmt_reviews->get_result();

while ($review = $reviews_result->fetch_assoc()) {
    echo "<div class='review'>
            <strong>{$review['username']}</strong> rated <strong>{$review['rating']}</strong> stars
            <p>{$review['review']}</p>
          </div>";
}
?>
