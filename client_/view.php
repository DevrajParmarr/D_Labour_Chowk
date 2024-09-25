<?php
session_start();
include "../Shared/sqlconnection.php";

$sql_result = mysqli_query($conn, "SELECT * FROM job_post WHERE owner = {$_SESSION['user_id']}");
include "menu.html";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Job Posts</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f4f4;
            font-family: 'Arial', sans-serif;
            color: #333;
        }

        .container {
            margin-top: 50px;
            margin-bottom: 50px;
        }

        h2 {
            color: #007bff;
            margin-bottom: 40px;
            text-align: center;
            font-weight: 700;
            text-transform: uppercase;
        }

        .pdt-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            padding: 20px;
            margin: 15px;
            position: relative;
            overflow: hidden;
        }

        .pdt-container:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
        }

        .pdt-container img {
            width: 100%;
            height: 200px;
            border-radius: 5px;
            object-fit: cover;
            transition: transform 0.3s;
            margin-bottom: 15px;
        }

        .pdt-container img:hover {
            transform: scale(1.05);
        }

        .name {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
        }

        .location {
            background-color: #28a745;
            color: #ffffff;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            margin-top: 5px;
        }

        .salary {
            font-size: 22px;
            font-weight: bold;
            color: #dc3545;
            margin: 10px 0;
        }

        .detail {
            font-size: 15px;
            color: #555;
            margin: 10px 0;
        }

        .btn-container {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
        }

        .btn {
            flex: 1;
            margin: 0 5px;
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
        }

        .btn-warning {
            background-color: #ffc107;
            border: none;
        }

        .btn:hover {
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .pdt-container {
                margin: 10px;
            }
            .btn-container {
                flex-direction: column;
            }
            .btn {
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>My Job Posts</h2>
    <div class="row justify-content-center">
        <?php
        while ($dbrow = mysqli_fetch_assoc($sql_result)) {
            echo "
            <div class='col-lg-4 col-md-6 col-sm-12'>
                <div class='pdt-container'>
                    <h4>Job Title: <span class='name'>" . htmlspecialchars($dbrow['jobTitle']) . "</span></h4>
                    <div class='location'>" . htmlspecialchars($dbrow['location']) . "</div>
                    <h5 class='salary'>Salary: " . htmlspecialchars($dbrow['salary']) . " Rs</h5>
                    <img src='" . htmlspecialchars($dbrow['impath']) . "' alt='Job Image'>
                    <p class='detail'>Detail: <span>" . htmlspecialchars($dbrow['detail']) . "</span></p>
                    <div class='btn-container'>
                        <a href='dltpost.php?post_ID=" . $dbrow['post_ID'] . "'>
                            <button class='btn btn-danger'>Delete Post</button>
                        </a>
                        <a href='interested_labour.php?post_ID=" . $dbrow['post_ID'] . "'>
                            <button class='btn btn-warning'>View Responses</button>
                        </a>
                    </div>
                </div>
            </div>";
        }
        ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
