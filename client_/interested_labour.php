<?php
session_start();
include "../Shared/sqlconnection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['post_ID'])) {
    $post_ID = $_GET['post_ID'];
} else {
    die("Post ID is not set.");
}

$query = "
    SELECT u.user_name, u.user_ID, l.workType, l.experience, l.city
    FROM job_applications ja
    JOIN lab_post l ON l.user_ID = ja.labour_id
    JOIN user u ON u.user_ID = l.user_ID
    WHERE ja.job_post_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $post_ID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interested Laborers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #e9ecef;
            font-family: 'Arial', sans-serif;
            color: #343a40;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            border: none;
            border-radius: 15px;
            transition: transform 0.3s, box-shadow 0.3s;
            overflow: hidden;
            background: linear-gradient(to bottom right, #ffffff, #f8f9fa);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            text-align: center;
            padding: 15px;
        }
        .card-title {
            font-size: 1.25rem;
            font-weight: bold;
        }
        .card-body {
            padding: 20px;
        }
        .no-applications {
            text-align: center;
            margin-top: 50px;
            color: #6c757d;
            font-size: 1.1rem;
        }
        .status-button {
            margin-right: 10px;
        }
        .profile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
        }
        .profile-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.2);
        }
        .close-profile {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
        }
        @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
            }
            .card-body {
                padding: 15px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="text-center mb-4">Interested Laborers</h2>
    <div class="row">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "
                <div class='col-md-4 mb-4'>
                    <div class='card'>
                        <div class='card-header'>
                            <h5 class='card-title'>" . htmlspecialchars($row['user_name']) . "</h5>
                        </div>
                        <div class='card-body'>
                            <p class='card-text'><strong>Work Type:</strong> " . htmlspecialchars($row['workType']) . "</p>
                            <p class='card-text'><strong>Experience:</strong> " . htmlspecialchars($row['experience']) . " years</p>
                            <p class='card-text'><strong>City:</strong> " . htmlspecialchars($row['city']) . "</p>
                            <div class='d-flex justify-content-between align-items-center'>
                                <button onclick='showProfile({$row['user_ID']})' class='btn btn-outline-secondary'>
                                    <i class='fas fa-eye'></i> View Profile
                                </button>
                                <div>
                                    <button class='btn btn-success status-button' onclick='updateStatus(" . $row['user_ID'] . ", \"Hired\")'>Hire</button>
                                    <button class='btn btn-danger status-button' onclick='updateStatus(" . $row['user_ID'] . ", \"Rejected\")'>Reject</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>";
            }
        } else {
            echo "<div class='col-12 no-applications'>
                    <p>No laborers have applied for this job post yet.</p>
                  </div>";
        }

        $stmt->close();
        $conn->close();
        ?>
    </div>
</div>

<div id="profileOverlay" class="profile-overlay">
    <div class="profile-content">
        <span class="close-profile" onclick="closeProfile()">&times;</span>
        <div id="profileData"></div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function showProfile(userId) {
    $.ajax({
        url: 'get_profile.php',
        type: 'GET',
        data: { user_id: userId },
        success: function(response) {
            $('#profileData').html(response);
            $('#profileOverlay').show();
        },
        error: function() {
            alert('Error loading profile');
        }
    });
}

function closeProfile() {
    $('#profileOverlay').hide();
}

function updateStatus(userId, status) {
    $.ajax({
        url: 'update_status.php',
        type: 'POST',
        data: { user_id: userId, status: status },
        success: function(response) {
            alert('Status updated successfully to: ' + status);
            location.reload(); // Refresh the page to reflect changes
        },
        error: function() {
            alert('Error updating status');
        }
    });
}
</script>

</body>
</html>
