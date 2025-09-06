<?php
session_start();
include "../Shared/sqlconnection.php";

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    echo "Error: Unauthorized access";
    exit();
}

if (!isset($_GET['user_id'])) {
    echo "Error: User ID not provided";
    exit();
}

$user_id = filter_var($_GET['user_id'], FILTER_VALIDATE_INT);
if (!$user_id) {
    echo "Error: Invalid User ID";
    exit();
}

$query = "
SELECT u.*, lp.workType, lp.experience, lp.salary, lp.location
FROM user u
JOIN lab_post lp ON u.user_ID = lp.user_ID
WHERE u.user_ID = ?";

if ($stmt = mysqli_prepare($conn, $query)) {
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $laborer = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if (!$laborer) {
        echo "<p>Laborer not found.</p>";
        exit();
    }
} else {
    echo "Error: Database query failed";
    exit();
}

?>

<div class="profile-header">
    <h1><?= htmlspecialchars($laborer['user_name']) ?></h1>
    <p><?= htmlspecialchars($laborer['email_id']) ?></p>
    <p>Mobile: <?= htmlspecialchars($laborer['mobile_no']) ?></p>
</div>

<div class="profile-info">
    <div>
        <i class="fas fa-briefcase"></i> Work Type: <?= htmlspecialchars($laborer['workType']) ?>
    </div>
    <div>
        <i class="fas fa-clock"></i> Experience: <?= htmlspecialchars($laborer['experience']) ?> years
    </div>
    <div>
        <i class="fas fa-money-bill-wave"></i> Salary: ₹<?= htmlspecialchars($laborer['salary']) ?>
    </div>
    <div>
        <i class="fas fa-map-marker-alt"></i> Location: <?= htmlspecialchars($laborer['location']) ?>
    </div>
    <div>
        <i class="fas fa-calendar-alt"></i> Date Joined: <?= date('d M Y', strtotime($laborer['date_created'])) ?>
    </div>
</div>

<form id="hireForm" action="hire_labor.php" method="POST" style="margin-top: 20px;">
    <input type="hidden" name="laborer_id" value="<?= $laborer['user_ID'] ?>">
    <input type="hidden" name="client_id" value="<?= $_SESSION['user_id'] ?>">
    <button type="submit" class="contact-btn">Hire Now</button>
    
</form>

<div id="message"></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    $('#hireForm').submit(function (event) {
        event.preventDefault(); 

        $.ajax({
            url: 'hire_labor.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json', 
            success: function (response) {
                const messageDiv = $('#message');
                messageDiv.removeClass('success error info');

                if (response.status === 'success') {
                    messageDiv.addClass('success').html(response.message).show();
                } else if (response.status === 'error') {
                    messageDiv.addClass('error').html(response.message).show();
                } else if (response.status === 'info') {
                    messageDiv.addClass('info').html(response.message).show();
                }
            },
            error: function () {
                $('#message').addClass('error').html('An error occurred. Please try again.').show();
            }
        });
    });
});
</script>

<!-- Message Styling -->
<style>
#message {
    display: none;
    margin-top: 20px;
    padding: 10px;
    border-radius: 5px;
}
.success {
    background-color: #d4edda;
    color: #155724;
}
.error {
    background-color: #f8d7da;
    color: #721c24;
}
.info {
    background-color: #cce5ff;
    color: #004085;
}
</style>
