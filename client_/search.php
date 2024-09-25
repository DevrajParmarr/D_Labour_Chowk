
<?php


include "../Shared/sqlconnection.php";

// Retrieve search criteria from URL parameters
$workType = $_GET['workType'];
$city = $_GET['city'];

// Prepare SQL query with placeholders
$sql = "SELECT * FROM lab_post WHERE workType LIKE ? AND city LIKE ?";
$stmt = $conn->prepare($sql);

// Bind parameters and execute
$workType_param = "%" . $workType . "%";
$city_param = "%" . $city . "%";
$stmt->bind_param("ss", $workType_param, $city_param);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Search Results</title>
</head>
<body>
    <h1>Search Results</h1>
    <?php if ($result->num_rows > 0): ?>
        <ul>
            <?php while ($post = $result->fetch_assoc()): ?>
                <?php
                // Fetch user details
                $user_sql = "SELECT user_name FROM user WHERE user_id = ?";
                $user_stmt = $conn->prepare($user_sql);
                $user_stmt->bind_param("i", $post['user_id']);
                $user_stmt->execute();
                $user_result = $user_stmt->get_result();
                $user = $user_result->fetch_assoc();
                ?>
                <li>
                    <h3><?php echo htmlspecialchars($post['workType']); ?></h3>
                    <p><strong>Posted by:</strong> <?php echo htmlspecialchars($user['user_name']); ?></p>
                    <p><strong>Salary:</strong> <?php echo htmlspecialchars($post['salary']); ?></p>
                    <p><strong>Experience:</strong> <?php echo htmlspecialchars($post['experience']); ?></p>
                    <p><strong>Location:</strong> <?php echo htmlspecialchars($post['location']); ?></p>
                    <p><strong>City:</strong> <?php echo htmlspecialchars($post['city']); ?></p>
                    <a href="profile.php?user_id=<?php echo htmlspecialchars($post['user_id']); ?>">View Profile</a>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No results found.</p>
    <?php endif; ?>

    <a href="search_form.php">Back to Search</a>
    <a href="index.php">Back to Home</a>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
