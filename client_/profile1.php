<?php
session_start();
if (!isset($_SESSION["login_status"])) {
    echo "Login in skipped";
    die;
}

if ($_SESSION["login_status"] == false) {
    echo "Unauthorized Attempt";
    die;
}

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
    $pastInteractions = rand(5, 50); 
    $rating = rand(3, 5); 

    echo "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>User Profile</title>
        <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>
        <style>
            body {
                background-color: #f0f4f8;
                font-family: Arial, sans-serif;
            }
            .profile-card {
                max-width: 400px;
                margin: 50px auto;
                padding: 20px;
                background-color: #ffffff;
                border-radius: 10px;
                box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
                text-align: center;
            }
            .profile-image {
                width: 100px;
                height: 100px;
                border-radius: 50%;
                margin-bottom: 20px;
            }
            .btn {
                margin-top: 20px;
                transition: background-color 0.3s;
            }
            .btn:hover {
                background-color: #e9ecef;
            }
            h2 {
                margin-bottom: 10px;
                color: #333;
            }
            p {
                margin: 5px 0;
                color: #555;
            }
        </style>
    </head>
    <body>
    
    <div class='profile-card'>
        <img src='../Shared/images/profile.png' alt='Profile Image' class='profile-image'>
        <h2>{$row['user_name']}</h2>
        <p><strong>Mobile:</strong> {$row['mobile_no']}</p>
        <p><strong>Past Interactions:</strong> {$pastInteractions}</p>
        <p><strong>Rating:</strong> {$rating} out of 5</p>
        <a href='hiredLabour.php'>
            <button class='btn btn-outline-secondary'>Hired Labour</button>
        </a>
    </div>
    
    </body>
    </html>
    ";
} else {
    echo "No user found.";
}

// Close the connection
$conn->close();
?>
