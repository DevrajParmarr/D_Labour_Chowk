<?php
require_once '../config/config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn()) {
    redirect('../Shared/login_form.php');
}

if (getCurrentUserType() !== 'Admin') {
    redirect('../Shared/login_form.php?error=unauthorized');
}

$db = Database::getInstance();
$conn = $db->getConnection();

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validateCSRFToken($csrf_token)) {
        $error = "Invalid CSRF token";
    } else {
        $action = $_POST['action'] ?? '';
        $user_id = $_POST['user_id'] ?? 0;

        switch ($action) {
            case 'delete':
                // Check if user has active jobs or applications
                $stmt = $conn->prepare("SELECT COUNT(*) as job_count FROM job_post WHERE owner = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $job_count = $stmt->get_result()->fetch_assoc()['job_count'];

                if ($job_count > 0) {
                    $error = "Cannot delete user with active job posts";
                } else {
                    $stmt = $conn->prepare("DELETE FROM user WHERE user_ID = ?");
                    $stmt->bind_param("i", $user_id);
                    if ($stmt->execute()) {
                        $success = "User deleted successfully";
                    } else {
                        $error = "Failed to delete user";
                    }
                }
                break;

            case 'verify':
                $stmt = $conn->prepare("UPDATE user SET Verified = 1 WHERE user_ID = ?");
                $stmt->bind_param("i", $user_id);
                if ($stmt->execute()) {
                    $success = "User verified successfully";
                } else {
                    $error = "Failed to verify user";
                }
                break;

            case 'unverify':
                $stmt = $conn->prepare("UPDATE user SET Verified = 0 WHERE user_ID = ?");
                $stmt->bind_param("i", $user_id);
                if ($stmt->execute()) {
                    $success = "User unverified successfully";
                } else {
                    $error = "Failed to unverify user";
                }
                break;
        }
    }
}

// Get users with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Get total users count
$total_users = $conn->query("SELECT COUNT(*) as count FROM user")->fetch_assoc()['count'];
$total_pages = ceil($total_users / $per_page);

// Get users for current page
$stmt = $conn->prepare("
    SELECT u.*, COUNT(jp.post_ID) as job_count, COUNT(ja.application_id) as application_count
    FROM user u
    LEFT JOIN job_post jp ON u.user_ID = jp.owner
    LEFT JOIN job_applications ja ON u.user_ID = ja.labour_id
    GROUP BY u.user_ID
    ORDER BY u.date_created DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $per_page, $offset);
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get user statistics
$user_stats = $conn->query("
    SELECT
        COUNT(CASE WHEN user_type = 'User' THEN 1 END) as clients,
        COUNT(CASE WHEN user_type = 'Labour' THEN 1 END) as workers,
        COUNT(CASE WHEN user_type = 'Admin' THEN 1 END) as admins,
        COUNT(CASE WHEN Verified = 1 THEN 1 END) as verified,
        COUNT(CASE WHEN Verified = 0 THEN 1 END) as unverified
    FROM user
")->fetch_assoc();

$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #4f46e5;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }

        .admin-navbar {
            background: var(--gradient-primary);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .admin-navbar .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .admin-navbar .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            transition: color 0.3s;
        }

        .admin-navbar .nav-link:hover {
            color: white !important;
        }

        .page-header {
            background: white;
            padding: 2rem;
            margin: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid var(--primary-color);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6b7280;
            font-weight: 600;
        }

        .users-table {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .user-row {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            transition: background-color 0.3s;
        }

        .user-row:hover {
            background-color: #f8fafc;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 1rem;
            flex-shrink: 0;
        }

        .user-info {
            flex: 1;
        }

        .user-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .user-email {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .user-stats {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .stat-item {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .user-type {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .user-type.client {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary-color);
        }

        .user-type.worker {
            background: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }

        .user-type.admin {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }

        .verification-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }

        .verified {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .unverified {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-action {
            padding: 0.375rem 0.75rem;
            font-size: 0.8rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-verify {
            background: var(--success-color);
            color: white;
        }

        .btn-verify:hover {
            background: #059669;
        }

        .btn-unverify {
            background: var(--warning-color);
            color: white;
        }

        .btn-unverify:hover {
            background: #d97706;
        }

        .btn-delete {
            background: var(--danger-color);
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
        }

        .pagination {
            justify-content: center;
            margin-top: 2rem;
        }

        .alert {
            margin: 2rem;
            border-radius: 12px;
            border: none;
        }

        @media (max-width: 768px) {
            .user-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .action-buttons {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Navigation -->
    <nav class="navbar navbar-expand-lg admin-navbar">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-crown me-2"></i>Admin Panel
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
                <a class="nav-link active" href="users.php">
                    <i class="fas fa-users me-1"></i>Users
                </a>
                <a class="nav-link" href="jobs.php">
                    <i class="fas fa-briefcase me-1"></i>Jobs
                </a>
                <a class="nav-link" href="analytics.php">
                    <i class="fas fa-chart-bar me-1"></i>Analytics
                </a>
                <a class="nav-link" href="settings.php">
                    <i class="fas fa-cog me-1"></i>Settings
                </a>
                <a class="nav-link" href="../Shared/logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-users me-3"></i>User Management</h1>
        <p>Manage all platform users, verify accounts, and monitor user activity.</p>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="container-fluid px-4">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $user_stats['clients']; ?></div>
                <div class="stat-label">Clients</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $user_stats['workers']; ?></div>
                <div class="stat-label">Workers</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $user_stats['admins']; ?></div>
                <div class="stat-label">Admins</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $user_stats['verified']; ?></div>
                <div class="stat-label">Verified</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $user_stats['unverified']; ?></div>
                <div class="stat-label">Unverified</div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="users-table">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fas fa-list me-2"></i>All Users</h4>
            <div class="d-flex gap-2">
                <input type="text" class="form-control" placeholder="Search users..." style="width: 250px;">
                <select class="form-select" style="width: 150px;">
                    <option value="">All Types</option>
                    <option value="User">Clients</option>
                    <option value="Labour">Workers</option>
                    <option value="Admin">Admins</option>
                </select>
            </div>
        </div>

        <?php if (empty($users)): ?>
            <div class="text-center py-5">
                <i class="fas fa-users" style="font-size: 3rem; opacity: 0.3;"></i>
                <p class="mt-3 text-muted">No users found.</p>
            </div>
        <?php else: ?>
            <?php foreach ($users as $user): ?>
                <div class="user-row">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($user['user_name'], 0, 1)); ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name">
                            <?php echo htmlspecialchars($user['user_name']); ?>
                            <span class="verification-badge <?php echo $user['Verified'] ? 'verified' : 'unverified'; ?>">
                                <i class="fas fa-<?php echo $user['Verified'] ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                                <?php echo $user['Verified'] ? 'Verified' : 'Unverified'; ?>
                            </span>
                        </div>
                        <div class="user-email"><?php echo htmlspecialchars($user['email_id']); ?></div>
                        <div class="user-stats">
                            <span class="stat-item">
                                <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($user['mobile_no']); ?>
                            </span>
                            <span class="stat-item">
                                <i class="fas fa-briefcase me-1"></i><?php echo $user['job_count']; ?> jobs
                            </span>
                            <span class="stat-item">
                                <i class="fas fa-paper-plane me-1"></i><?php echo $user['application_count']; ?> applications
                            </span>
                        </div>
                    </div>
                    <div class="user-type <?php echo strtolower($user['user_type']); ?>">
                        <?php echo htmlspecialchars($user['user_type']); ?>
                    </div>
                    <div class="action-buttons">
                        <?php if (!$user['Verified']): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_ID']; ?>">
                                <button type="submit" name="action" value="verify" class="btn-action btn-verify" title="Verify User">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="user_id" value="<?php echo $user['user_ID']; ?>">
                                <button type="submit" name="action" value="unverify" class="btn-action btn-unverify" title="Unverify User">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        <?php endif; ?>

                        <button class="btn-action btn-delete" onclick="deleteUser(<?php echo $user['user_ID']; ?>, '<?php echo htmlspecialchars($user['user_name']); ?>')" title="Delete User">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="User pagination">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- Delete User Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel">
                        <i class="fas fa-exclamation-triangle text-danger me-2"></i>Delete User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete user "<strong id="deleteUserName"></strong>"?</p>
                    <div class="alert alert-danger">
                        <i class="fas fa-info-circle me-2"></i>
                        This action cannot be undone. All user data will be permanently removed.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                        <i class="fas fa-trash me-1"></i>Delete User
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 5000);

        // Delete user function
        function deleteUser(userId, userName) {
            document.getElementById('deleteUserName').textContent = userName;
            document.getElementById('confirmDeleteBtn').onclick = function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" name="user_id" value="${userId}">
                    <input type="hidden" name="action" value="delete">
                `;
                document.body.appendChild(form);
                form.submit();
            };

            const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
            modal.show();
        }

        // Search functionality
        document.querySelector('input[placeholder="Search users..."]').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const userRows = document.querySelectorAll('.user-row');

            userRows.forEach(row => {
                const userName = row.querySelector('.user-name').textContent.toLowerCase();
                const userEmail = row.querySelector('.user-email').textContent.toLowerCase();

                if (userName.includes(searchTerm) || userEmail.includes(searchTerm)) {
                    row.style.display = 'flex';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Filter functionality
        document.querySelector('select').addEventListener('change', function() {
            const filterValue = this.value.toLowerCase();
            const userRows = document.querySelectorAll('.user-row');

            userRows.forEach(row => {
                const userType = row.querySelector('.user-type').textContent.toLowerCase();

                if (filterValue === '' || userType.includes(filterValue)) {
                    row.style.display = 'flex';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>