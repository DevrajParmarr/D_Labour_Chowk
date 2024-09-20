<?php
session_start();
include "../Shared/sqlconnection.php";

// Fetch job posts related to the logged-in user
$sql_result = mysqli_query($conn, "SELECT * FROM lab_post WHERE user_ID=$_SESSION[user_id]");

// Include menu
include "menu.html";

// Loop through the results and display each job post
while ($dbrow = mysqli_fetch_assoc($sql_result)) {
    echo "
    <div class='card-container'>
        <div class='card'>
            <img src='$dbrow[impath]' alt='Job Image' class='card-img'>
            <div class='card-content'>
                <h3 class='card-title'>Job Title: <span class='highlight'>$dbrow[workType]</span></h3>
                <p class='card-text'>Name: <span class='highlight'>{$_SESSION['user_name']}</span></p>
                <p class='card-text'>Location: $dbrow[location]</p>
                <p class='card-text'>Salary: <span class='highlight'>Rs $dbrow[salary]</span></p>
                <a href='deletePost.php?l_post_ID=$dbrow[l_post_ID]'>
        <button class='btn btn-danger w-100'>Delete Post</button>
    </a>
            </div>
        </div>
    </div>
    ";
}

print_r($dbrow);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Posts</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .card-container {
            display: inline-block;
            margin: 20px;
        }

        .card {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 300px;
            transition: transform 0.2s ease-in-out;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .card-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .card-content {
            padding: 15px;
        }

        .card-title {
            font-size: 20px;
            margin: 10px 0;
            color: #333;
        }

        .card-text {
            font-size: 16px;
            margin: 5px 0;
            color: #555;
        }

        .highlight {
            font-weight: bold;
            color: #4CAF50;
        }

        .card-actions {
            margin-top: 15px;
            text-align: center;
        }

        .btn-delete {
            background-color: #ff4c4c;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-delete:hover {
            background-color: #e04343;
        }
    </style>
</head>
<body>
</body>
</html>
