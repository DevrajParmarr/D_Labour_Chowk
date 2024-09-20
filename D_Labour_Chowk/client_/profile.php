<?php
session_start();
include "../Shared/sqlconnection.php";

if (!isset($_GET['user_id'])) {
    header("Location: index.php"); // Redirect if no user ID is provided
    exit();
}

$user_id = mysqli_real_escape_string($conn, $_GET['user_id']);

// Fetch laborer details
$query = "
SELECT u.*, lp.workType, lp.experience, lp.salary, lp.location
FROM user u
JOIN lab_post lp ON u.user_ID = lp.user_ID
WHERE u.user_ID = '$user_id'";

$result = mysqli_query($conn, $query);
$laborer = mysqli_fetch_assoc($result);

if (!$laborer) {
    echo "<p>Laborer not found.</p>";
    exit();
}

include "menu.html";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($laborer['user_name']) ?>'s Profile</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-header h1 {
            color: #007bff;
            margin-bottom: 10px;
        }

        .profile-header img {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .profile-info {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .profile-info div {
            padding: 10px;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            background-color: #f8f9fa;
        }

        .profile-info div i {
            margin-right: 10px;
            color: #007bff;
        }

        .contact-btn {
            display: inline-block;
            padding: 12px 20px;
            background-color: #28a745;
            border: none;
            color: #fff;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 20px;
            text-decoration: none;
            text-align: center;
        }

        .contact-btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="profile-header">
        <h1><?= htmlspecialchars($laborer['user_name']) ?></h1>
        <p><?= htmlspecialchars($laborer['email_id']) ?></p>
        <p>Mobile: <?= htmlspecialchars($laborer['mobile_no']) ?></p>
    </div>

    <div class="profile-info">
        <div>
            <i class="fas fa-briefcase"></i> Work Type: <?= htmlspecialchars($laborer['workType']) ?>
        </div>
        <div>
            <i class="fas fa-clock"></i> Experience: <?= htmlspecialchars($laborer['experience']) ?> years
        </div>
        <div>
            <i class="fas fa-money-bill-wave"></i> Salary: ₹<?= htmlspecialchars($laborer['salary']) ?>
        </div>
        <div>
            <i class="fas fa-map-marker-alt"></i> Location: <?= htmlspecialchars($laborer['location']) ?>
        </div>
        <div>
            <i class="fas fa-calendar-alt"></i> Date Joined: <?= date('d M Y', strtotime($laborer['date_created'])) ?>
        </div>
    </div>

    <a href="contact.php?user_id=<?= htmlspecialchars($laborer['user_ID']) ?>" class="contact-btn">Contact Now</a>
</div>

</body>
</html>
