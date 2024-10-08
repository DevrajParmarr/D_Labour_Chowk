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

// Include database connection
include "../Shared/sqlconnection.php";

$user_ID = $_SESSION['user_id'];

$sql = "SELECT user_name, mobile_no 
        FROM user 
        WHERE user_ID = ?";

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
    <title>Profile</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #e9ecef;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .profile-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }
        .profile-card:hover {
            transform: scale(1.02);
        }
        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-bottom: 15px;
            border: 3px solid #007bff;
        }
        .button-container {
            text-align: center;
            margin-top: 20px;
        }
        .button-container .btn {
            margin: 5px;
            width: 180px;
            font-weight: bold;
        }
        h2 {
            color: #007bff;
            margin-bottom: 10px;
        }
        .alert {
            margin-top: 20px;
            font-size: 1.1em;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <?php
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
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

        <div class='button-container'>
            <a href='post_work.php'>
                <button class='btn btn-primary'>Post Your Work</button>
            </a>
            <a href='work_posts.php'>
                <button class='btn btn-secondary'>View Your Past Work</button>
            </a>
            <a href='appliedJob.php'>
                <button class='btn btn-success'>Your Interest in Job</button>
            </a>
        </div>
        ";
    } else {
        echo "<div class='alert alert-warning text-center'>No user found.</div>";
    }

    // Close the connection
    $conn->close();
    ?>
</div>

</body>
</html>
