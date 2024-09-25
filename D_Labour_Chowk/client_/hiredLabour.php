<?php
session_start();
include "../Shared/sqlconnection.php";

include "menu.html";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$client_id = $_SESSION['user_id'];

$query = "
SELECT h.hire_id, u.user_name, u.email_id, u.mobile_no, lp.workType, lp.experience, lp.salary, lp.location, h.status
FROM hires h
JOIN user u ON h.labour_id = u.user_ID
JOIN lab_post lp ON h.labour_id = lp.user_ID
WHERE h.client_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hired Laborers</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f4f8;
            font-family: Arial, sans-serif;
        }
        .container {
            margin-top: 30px;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .table {
            margin-top: 20px;
        }
        .thead-dark th {
            background-color: #343a40;
            color: #fff;
        }
        .alert-warning {
            margin-top: 20px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #888;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="text-center">Hired Laborers</h1>
    
    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Work Type</th>
                    <th>Experience</th>
                    <th>Salary</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['user_name']) ?></td>
                        <td><?= htmlspecialchars($row['email_id']) ?></td>
                        <td><?= htmlspecialchars($row['mobile_no']) ?></td>
                        <td><?= htmlspecialchars($row['workType']) ?></td>
                        <td><?= htmlspecialchars($row['experience']) ?> years</td>
                        <td>₹<?= htmlspecialchars($row['salary']) ?></td>
                        <td><?= htmlspecialchars($row['location']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td>
                            <form method="POST" action="delete_hire.php" style="display:inline;">
                                <input type="hidden" name="hire_id" value="<?= $row['hire_id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning text-center">No laborers hired.</div>
    <?php endif; ?>
</div>


<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
