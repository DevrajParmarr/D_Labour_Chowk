<?php
require_once '../Shared/config.php';

// Check if user is logged in and is a client
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (getCurrentUserType() !== 'User') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$db = Database::getInstance();
$conn = $db->getConnection();

// Get the user ID from the request parameter
$profile_user_ID = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

if ($profile_user_ID <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid user ID']);
    exit;
}

// Get user details for the requested profile
$sql = "SELECT u.user_name, u.mobile_no, u.email_id, u.date_created, lp.workType, lp.experience, lp.city, lp.salary
        FROM user u
        LEFT JOIN lab_post lp ON u.user_ID = lp.user_ID
        WHERE u.user_ID = ? AND u.user_type = 'Labour'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $profile_user_ID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $worker = $result->fetch_assoc();

    // Format date
    $worker['date_created'] = date('M Y', strtotime($worker['date_created']));

    // Return HTML response for modal
    header('Content-Type: text/html');
    ?>
    <div class="worker-profile-modal">
        <div class="profile-header">
            <div class="profile-avatar-large">
                <i class="fas fa-user"></i>
            </div>
            <h2><?php echo htmlspecialchars($worker['user_name']); ?></h2>
            <div class="profile-badge">
                <i class="fas fa-tools me-1"></i>
                <?php echo htmlspecialchars($worker['workType'] ?: 'General Worker'); ?>
            </div>
        </div>

        <div class="profile-details">
            <div class="detail-row">
                <i class="fas fa-envelope detail-icon"></i>
                <div class="detail-content">
                    <div class="detail-label">Email</div>
                    <div class="detail-value"><?php echo htmlspecialchars($worker['email_id']); ?></div>
                </div>
            </div>

            <div class="detail-row">
                <i class="fas fa-phone detail-icon"></i>
                <div class="detail-content">
                    <div class="detail-label">Phone</div>
                    <div class="detail-value"><?php echo htmlspecialchars($worker['mobile_no']); ?></div>
                </div>
            </div>

            <div class="detail-row">
                <i class="fas fa-map-marker-alt detail-icon"></i>
                <div class="detail-content">
                    <div class="detail-label">Location</div>
                    <div class="detail-value"><?php echo htmlspecialchars($worker['city']); ?></div>
                </div>
            </div>

            <div class="detail-row">
                <i class="fas fa-rupee-sign detail-icon"></i>
                <div class="detail-content">
                    <div class="detail-label">Expected Salary</div>
                    <div class="detail-value">₹<?php echo number_format($worker['salary']); ?>/month</div>
                </div>
            </div>

            <div class="detail-row">
                <i class="fas fa-clock detail-icon"></i>
                <div class="detail-content">
                    <div class="detail-label">Experience</div>
                    <div class="detail-value"><?php echo htmlspecialchars($worker['experience']); ?> years</div>
                </div>
            </div>

            <div class="detail-row">
                <i class="fas fa-calendar detail-icon"></i>
                <div class="detail-content">
                    <div class="detail-label">Member Since</div>
                    <div class="detail-value"><?php echo htmlspecialchars($worker['date_created']); ?></div>
                </div>
            </div>
        </div>

        <div class="profile-actions">
            <button class="btn btn-primary" onclick="updateStatus(<?php echo $worker['user_ID']; ?>, 'Hired')">
                <i class="fas fa-check me-2"></i>Hire Worker
            </button>
            <button class="btn btn-outline-secondary" onclick="closeProfile()">
                <i class="fas fa-times me-2"></i>Close
            </button>
        </div>
    </div>

    <style>
        .worker-profile-modal {
            padding: 0;
        }

        .profile-header {
            text-align: center;
            padding: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 16px 16px 0 0;
        }

        .profile-avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 1rem;
            border: 4px solid rgba(255, 255, 255, 0.3);
        }

        .profile-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .profile-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .profile-details {
            padding: 2rem;
        }

        .detail-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 12px;
            margin-bottom: 1rem;
            border-left: 4px solid #667eea;
        }

        .detail-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .detail-content {
            flex: 1;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
        }

        .profile-actions {
            padding: 2rem;
            padding-top: 0;
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .profile-actions .btn {
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 12px;
        }
    </style>
    <?php
} else {
    http_response_code(404);
    echo '<div class="text-center p-4"><h4>User not found</h4><p>The worker profile could not be loaded.</p></div>';
}

$conn->close();
?>
