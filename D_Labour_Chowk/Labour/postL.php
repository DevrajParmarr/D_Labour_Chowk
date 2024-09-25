<?php
session_start();
include "../Shared/sqlconnection.php";

$search = '';
$location = '';
$jobTitle = '';
$limit = 9; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $search = mysqli_real_escape_string($conn, $_POST['search']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $jobTitle = mysqli_real_escape_string($conn, $_POST['jobTitle']);
}

$query = "SELECT * FROM job_post WHERE 1=1";
if ($jobTitle) {
    $query .= " AND jobTitle = '$jobTitle'";
}
if ($location) {
    $query .= " AND location = '$location'";
}
$query .= " LIMIT $limit OFFSET $offset";

$sql_result = mysqli_query($conn, $query);

$total_query = "SELECT COUNT(*) AS total FROM job_post WHERE 1=1";
if ($jobTitle) {
    $total_query .= " AND jobTitle = '$jobTitle'";
}
if ($location) {
    $total_query .= " AND location = '$location'";
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

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
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
            color: #343a40;
            font-weight: 700;
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
            width: 150px;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .filter-form button:hover {
            background-color: #0056b3;
        }

        .job-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
        }

        .job-card {
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            display: flex;
            flex-direction: column;
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
            margin-bottom: 15px;
            color: #007bff;
            transition: color 0.3s ease;
        }

        .job-title:hover {
            color: #0056b3;
        }

        .job-location, .job-salary {
            font-size: 16px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            color: #6c757d;
        }

        .job-location i, .job-salary i {
            margin-right: 8px;
        }

        .job-detail {
            font-size: 14px;
            color: #495057;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .apply-btn {
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
        }

        .apply-btn:hover {
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

        .load-more-container {
            text-align: center;
            margin-top: 40px;
        }

        .load-more-btn {
            padding: 12px 20px;
            background-color: #007bff;
            border: none;
            color: #fff;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .load-more-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="heading">Explore the Best Suitable Job for You</h1>

    <!-- Filter Form -->
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
            <option value="Other" <?php if ($jobTitle == 'Other') echo 'selected'; ?>>Other</option>
        </select>
        <select name="location">
            <option value="">Select Location</option>
            <option value="Indore" <?php if ($location == 'Indore') echo 'selected'; ?>>Indore</option>
            <option value="Bhopal" <?php if ($location == 'Bhopal') echo 'selected'; ?>>Bhopal</option>
            <option value="Ujjain" <?php if ($location == 'Ujjain') echo 'selected'; ?>>Ujjain</option>
        </select>
        <button type="submit">Search</button>
    </form>

    <!-- Job Listings -->
    <div class="job-grid" id="job-grid">
        <?php
        if (mysqli_num_rows($sql_result) > 0) {
            while ($dbrow = mysqli_fetch_assoc($sql_result)) {
                echo "
                <div class='job-card'>
                    <img src='$dbrow[impath]' alt='$dbrow[jobTitle]' class='job-image'>
                    <div class='job-content'>
                        <h2 class='job-title'>$dbrow[jobTitle]</h2>
                        <div class='job-location'><i class='fas fa-map-marker-alt'></i>$dbrow[location]</div>
                        <div class='job-salary'><i class='fas fa-indian-rupee-sign'></i>Salary: $dbrow[salary]</div>
                        <div class='job-detail'>$dbrow[detail]</div>
                        <button class='apply-btn'>Apply Now</button>
                    </div>
                </div>
                ";
            }
        } else {
            echo "<p>No jobs found. Please adjust your search or filter criteria.</p>";
        }
        ?>
    </div>

    <!-- Pagination -->
    <div class="pagination">
        <?php
        if ($total_pages > 1) {
            for ($i = 1; $i <= $total_pages; $i++) {
                echo "<a href='?page=$i' class='" . ($i == $page ? "active" : "") . "'>$i</a>";
            }
        }
        ?>
    </div>

    <!-- Load More Button -->
    <?php if ($page < $total_pages): ?>
        <div class="load-more-container">
            <a href="?page=<?php echo $page + 1; ?>" class="load-more-btn">Load More</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
