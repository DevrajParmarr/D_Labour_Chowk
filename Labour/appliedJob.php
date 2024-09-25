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
$query = "SELECT job_post.*, job_applications.status FROM job_post 
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
    <link rel="stylesheet" href="styles.css"> <!-- Link to external CSS file -->
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #e9ecef;
            margin: 0;
            padding: 20px;
            color: #495057;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #343a40;
            margin-bottom: 30px;
            font-size: 2.5em;
        }
        .job-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }
        .job-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 25px;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #007bff;
        }
        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }
        .job-title {
            font-size: 1.8em;
            color: #007bff;
            margin-bottom: 10px;
            text-decoration: none;
        }
        .job-card p {
            margin: 5px 0;
            line-height: 1.5;
        }
        .status {
            font-weight: bold;
            margin-top: 15px;
            font-size: 1.2em;
        }
        .status.approved {
            color: #28a745; /* Green for approved status */
        }
        .status.pending {
            color: #ffc107; /* Yellow for pending status */
        }
        .status.rejected {
            color: #dc3545; /* Red for rejected status */
        }
        .no-jobs {
            text-align: center;
            color: #868e96;
            font-size: 1.3em;
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            color: #6c757d;
            font-size: 0.9em;
        }
        .follow-up-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
            position: absolute;
            bottom: 15px;
            right: 15px;
        }
        .follow-up-btn:hover {
            background-color: #0056b3;
        }
        /* Responsive styles */
        @media (max-width: 768px) {
            h1 {
                font-size: 2em;
            }
            .job-title {
                font-size: 1.5em;
            }
            .container {
                padding: 20px;
            }
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
                    <p><strong>Salary:</strong> Rs {$job['salary']}</p>
                    <p><strong>Description:</strong> {$job['detail']}</p>
                    <h4 class='status {$job['status']}'>{$job['status']}</h4>
                    <button onclick=\"alert('You can follow up with the client about this job!')\" class='follow-up-btn'>Follow Up</button>
                </div>
                ";
            }
        } else {
            echo "<p class='no-jobs'>You have not applied for any jobs yet.</p>";
        }
        ?>
    </div>
</div>


<script>
  // Optional JavaScript for enhancing interactivity
document.querySelectorAll('.follow-up-btn').forEach(button => {
    button.addEventListener('click', () => {
        button.innerHTML = 'Followed'; // Corrected assignment
        button.disabled = true; // Optional: disable the button after clicking
    });
});

</script>

</body>
</html>
