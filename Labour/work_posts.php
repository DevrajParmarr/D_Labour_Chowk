<?php
session_start();
if (!isset($_SESSION["login_status"]) || $_SESSION["login_status"] == false) {
    echo "Unauthorized Attempt";
    die;
}

include "../Shared/sqlconnection.php";

$user_ID = $_SESSION['user_id'];

// Fetch work posts from the database
$sql = "SELECT * FROM work_posts WHERE labour_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_ID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Work Posts</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f4f4f4;
            font-family: 'Roboto', sans-serif;
            color: #333;
        }
        .container {
            margin-top: 50px;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #007bff;
            margin-bottom: 30px;
            text-align: center;
            font-weight: 500;
        }
        .work-post {
            background: #fff;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #e0e0e0;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .work-post:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 20px rgba(0, 123, 255, 0.2);
        }
        .work-image {
            width: 100%;
            height: auto;
            border-radius: 5px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
        }
        .description {
            font-size: 0.9em;
            margin-bottom: 10px;
            line-height: 1.4;
            color: #555;
        }
        .footer {
            font-size: 0.8em;
            color: #999;
            margin-top: 10px;
        }
        .btn {
            margin-top: 20px;
        }
        .alert {
            text-align: center;
            font-size: 1.2em;
            margin-top: 20px;
        }
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            .work-post {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Your Work Posts</h2>

    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "
            <div class='work-post'>
                <h6>Description:</h6>
                <p class='description'>{$row['description']}</p>
                <img src='../Shared/uploads/{$row['image']}' alt='Work Image' class='work-image'>
                <div class='footer'>
                    Posted on: " . date('F j, Y', strtotime($row['created_at'])) . "
                </div>
            </div>
            ";
        }
    } else {
        echo "<div class='alert alert-warning'>You have not posted any work yet.</div>";
    }

    // Close the connection
    $conn->close();
    ?>

    <a href="profile.php" class="btn btn-secondary btn-block">Back to Home</a>
</div>

</body>
</html>
