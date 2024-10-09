<?php 
session_start();
include "../Shared/sqlconnection.php";

$search = '';
$city = '';
$jobTitle = '';
$limit = 9; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // $search = mysqli_real_escape_string($conn, $_POST['search']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $jobTitle = mysqli_real_escape_string($conn, $_POST['jobTitle']);
}

$query = "
SELECT lp.*, u.user_name, u.mobile_no, u.email_id, u.date_created
FROM lab_post lp
JOIN user u ON lp.user_ID = u.user_ID
WHERE 1=1";

if ($jobTitle) {
    $query .= " AND lp.workType = '$jobTitle'";
}
if ($city) {
    $query .= " AND lp.city = '$city'";
}
$query .= " LIMIT $limit OFFSET $offset";

$sql_result = mysqli_query($conn, $query);

$total_query = "
SELECT COUNT(*) AS total 
FROM lab_post lp
JOIN user u ON lp.user_ID = u.user_ID
WHERE 1=1";
if ($jobTitle) {
    $total_query .= " AND lp.workType = '$jobTitle'";
}
if ($city) {
    $total_query .= " AND lp.city = '$city'";
}
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_jobs = $total_row['total'];
$total_pages = ceil($total_jobs / $limit);

include "menu.html";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Listings</title>

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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .heading {
            text-align: center;
            font-size: 36px;
            margin-bottom: 40px;
            color: #007bff;
            font-weight: 700;
            border-bottom: 2px solid #007bff;
            padding-bottom: 10px;
        }

        .filter-form {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            gap: 10px;
        }

        .filter-form input, .filter-form select {
            padding: 12px;
            border-radius: 5px;
            border: 1px solid #ced4da;
            font-size: 16px;
            width: 100%;
        }

        .filter-form button {
            padding: 12px 20px;
            background-color: #007bff;
            border: none;
            color: #fff;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .filter-form button:hover {
            background-color: #0056b3;
        }

        .job-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 20px;
        }

        .job-card {
            background-color: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .job-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid #e9ecef;
        }

        .job-content {
            padding: 20px;
            flex-grow: 1;
        }

        .job-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #007bff;
            transition: color 0.3s ease;
        }

        .job-title:hover {
            color: #0056b3;
        }

        .job-city, .job-salary, .job-name {
            font-size: 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            color: #6c757d;
        }

        .job-city i, .job-salary i, .job-name i {
            margin-right: 8px;
        }

        .job-detail {
            font-size: 14px;
            color: #495057;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .apply-btn, .contact-btn, .profile-btn {
            background-color: #28a745;
            color: #fff;
            padding: 12px 0;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            text-align: center;
            transition: background-color 0.3s ease, transform 0.3s ease;
            width: 100%;
            margin-bottom: 5px;
        }

        .apply-btn:hover, .contact-btn:hover, .profile-btn:hover {
            background-color: #218838;
            transform: scale(1.05);
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }

        .pagination a {
            padding: 10px 15px;
            margin: 0 5px;
            background-color: #007bff;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .pagination a:hover {
            background-color: #0056b3;
        }

        .pagination .active {
            background-color: #0056b3;
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
            padding: 20px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
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

            .filter-form select, .filter-form button {
                width: 100%;
                margin-bottom: 10px;
            }

            .job-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="heading">Explore the Best Labour for Your Project / Work</h1>

    <form class="filter-form" method="POST" action="">
        <select name="jobTitle">
            <option value="">Select Work Type</option>
            <option value="Plumber" <?php if ($jobTitle == 'Plumber') echo 'selected'; ?>>Plumber</option>
            <option value="Electrician" <?php if ($jobTitle == 'Electrician') echo 'selected'; ?>>Electrician</option>
            <option value="Mason" <?php if ($jobTitle == 'Mason') echo 'selected'; ?>>Mason</option>
            <option value="Carpenter" <?php if ($jobTitle == 'Carpenter') echo 'selected'; ?>>Carpenter</option>
            <option value="Painter" <?php if ($jobTitle == 'Painter') echo 'selected'; ?>>Painter</option>
            <option value="Gardener" <?php if ($jobTitle == 'Gardener') echo 'selected'; ?>>Gardener</option>
            <option value="Laborer" <?php if ($jobTitle == 'Laborer') echo 'selected'; ?>>Laborer</option>
            <option value="Welder" <?php if ($jobTitle == 'Laborer') echo 'selected'; ?>>Welder</option>
            <option value="Other" <?php if ($jobTitle == 'Other') echo 'selected'; ?>>Other</option>
        </select>
        <select name="city">
            <option value="">Select city</option>
            <option value="Indore" <?php if ($city == 'Indore') echo 'selected'; ?>>Indore</option>
            <option value="Bhopal" <?php if ($city == 'Bhopal') echo 'selected'; ?>>Bhopal</option>
            <option value="Ujjain" <?php if ($city == 'Ujjain') echo 'selected'; ?>>Ujjain</option>
            <option value="Jabalpur" <?php if ($city == 'Jabalpur') echo 'selected'; ?>>Jabalpur</option>
            <option value="Kota" <?php if ($city == 'Kota') echo 'selected'; ?>>Kota</option>
            <option value="Jaipur" <?php if ($city == 'Jaipur') echo 'selected'; ?>>Jaipur</option>
            <option value="Delhi" <?php if ($city == 'Delhi') echo 'selected'; ?>>Delhi</option>
        </select>
        <button type="submit">Search</button>
    </form>

    <div class="job-grid">
        <?php
        if (mysqli_num_rows($sql_result) > 0) {
            while ($dbrow = mysqli_fetch_assoc($sql_result)) {
                echo "
                <div class='job-card'>
                    <img class='job-image' src='$dbrow[impath]' alt='Job Image'>
                    <div class='job-content'>
                        <h2 class='job-title'>{$dbrow['workType']}</h2>
                        <p class='job-name'><i class='fas fa-user'></i> {$dbrow['user_name']}</p>
                        <p class='job-city'><i class='fas fa-map-marker-alt'></i> {$dbrow['city']}</p>
                        <p class='job-salary'><i class='fas fa-money-bill-wave'></i> ₹{$dbrow['salary']}</p>
                        <p class='job-detail'>Experience: {$dbrow['experience']} years</p>

                        <button onclick='showProfile({$dbrow['user_ID']})' class='profile-btn'>View Profile</button>
                    </div>
                </div>
                ";
            }
        } else {
            echo "<p>No job postings found.</p>";
        }
        ?>
    </div>


    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&search=<?= $search ?>&city=<?= $city ?>&jobTitle=<?= $jobTitle ?>">Previous</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= $search ?>&city=<?= $city ?>&jobTitle=<?= $jobTitle ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page + 1 ?>&search=<?= $search ?>&city=<?= $city ?>&jobTitle=<?= $jobTitle ?>">Next</a>
        <?php endif; ?>
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
</script>

</body>
</html>