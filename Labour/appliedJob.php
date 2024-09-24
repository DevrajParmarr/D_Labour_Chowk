<?php
session_start();
include "../Shared/sqlconnection.php";

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Retrieve laborer ID from session
$laborer_id = $_SESSION['user_id'];

// Prepare SQL query to fetch applied jobs
$query = "SELECT job_post.* , job_applications.status FROM job_post 
          JOIN job_applications ON job_post.post_ID = job_applications.job_post_id 
          WHERE job_applications.labour_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $laborer_id);
$stmt->execute();
$result = $stmt->get_result();

// Include menu
include "menu.html";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Applied Jobs</title>
    <!-- Add your CSS here -->
    <link rel="stylesheet" href="styles.css"> <!-- Link to external CSS file -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .job-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .job-card {
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            transition: transform 0.2s;
        }
        .job-card:hover {
            transform: scale(1.05);
        }
        .job-title {
            font-size: 1.5em;
            color: #007BFF;
        }
        .job-card p {
            margin: 5px 0;
            color: #555;
        }
        .no-jobs {
            text-align: center;
            color: #888;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Your Applied Jobs</h1>
    <div class="job-grid">
        <?php
        // Check if user has applied for any jobs
        if ($result->num_rows > 0) {
            while ($job = $result->fetch_assoc()) {
                echo "
                <div class='job-card'>
                    <h2 class='job-title'>{$job['jobTitle']}</h2>
                     
                    
                    <p><strong>Location:</strong> {$job['location']}</p>
                    <p><strong>Salary:</strong> {$job['salary']}</p>
                    <p>{$job['detail']}</p>
                     <div> <h4 class='job-title'>{$job['status']}</h4></div>
                </div>
               
                ";
            }
        } else {
            echo "<p class='no-jobs'>You have not applied for any jobs yet.</p>";
        }
        ?>
    </div>
</div>

</body>
</html>
