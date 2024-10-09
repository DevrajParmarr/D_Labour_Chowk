<?php
session_start();
if (!isset($_SESSION["login_status"]) || $_SESSION["login_status"] == false) {
    echo "Unauthorized Attempt";
    die;
}

include "../Shared/sqlconnection.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_ID = $_SESSION['user_id'];
    $description = $_POST['description'];
    $image = $_FILES['image']['name'];
    $target = "../Shared/uploads/" . basename($image); // Change the path according to your directory structure

    // Insert the post into the database
    $sql = "INSERT INTO work_posts (labour_ID, description, image) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $user_ID, $description, $image);

    if ($stmt->execute() && move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
        echo "<script>alert('Your work has been posted successfully!'); window.location.href='work_posts.php';</script>";
    } else {
        echo "<script>alert('Error posting your work. Please try again.');</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Your Work</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #e9ecef;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            margin-top: 50px;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        h2 {
            color: #007bff;
            margin-bottom: 20px;
        }
        .btn {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Post Your Work</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
        </div>
        <div class="form-group">
            <label for="image">Upload Image:</label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
        <a href="profile.php" class="btn btn-secondary">Back to Profile</a>
    </form>
</div>

</body>
</html>
