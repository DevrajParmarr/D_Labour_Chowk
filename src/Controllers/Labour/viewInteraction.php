<?php
session_start();
include "../Shared/sqlconnection.php";

// Get the post ID from the URL
$l_post_ID = $_GET['l_post_ID'];

// Fetch clients interested in this post
$sql_result = mysqli_query($conn, "SELECT * FROM client_interest WHERE l_post_ID=$l_post_ID");

// Include the menu
include "menu.html";

// Display interested clients
echo "<h2>Clients Interested in Your Post</h2>";

if (mysqli_num_rows($sql_result) > 0) {
    while ($client = mysqli_fetch_assoc($sql_result)) {
        echo "
        <div class='card-container'>
            <div class='card'>
                <div class='card-content'>
                    <h3 class='card-title'>Client Name: <span class='highlight'>$client[client_name]</span></h3>
                    <p class='card-text'>Contact: <span class='highlight'>$client[client_contact]</span></p>
                    <p class='card-text'>Message: <span class='highlight'>$client[message]</span></p>
                </div>
            </div>
        </div>
        ";
    }
} else {
    echo "<p>No clients have shown interest yet.</p>";
}
?>
