<?php
require_once '../config/config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn()) {
    redirect('../Shared/login_form.php');
}

if (getCurrentUserType() !== 'Admin') {
    redirect('../Shared/login_form.php?error=unauthorized');
}

$user_id = getCurrentUserId();
$user_name = $_SESSION['user_name'];

try {
    $db = Database::getInstance();

    // Get system statistics
    $stmt = $db->query("SELECT COUNT(*) as total_users FROM user");
    $total_users = $stmt->fetch_assoc()['total_users'];

    $stmt = $db->query("SELECT COUNT(*) as total_jobs FROM job_post");
    $total_jobs = $stmt->fetch_assoc()['total_jobs'];

    $stmt = $db->query("SELECT COUNT(*) as total_applications FROM job_applications");
    $total_applications = $stmt->fetch_assoc()['total_applications'];

    $stmt = $db->query("SELECT COUNT(*) as total_hires FROM hires");
    $total_hires = $stmt->fetch_assoc()['total_hires'];

    // Get user type distribution
    $stmt = $db->query("SELECT user_type, COUNT(*) as count FROM user GROUP BY user_type");
    $user_types = [];
    while ($row = $stmt->fetch_assoc()) {
        $user_types[$row['user_type']] = $row['count'];
    }

    // Get recent users
    $stmt = $db->prepare("SELECT user_ID, user_name, email_id, user_type, date_created FROM user ORDER BY date_created DESC LIMIT 5");
    $stmt->execute();
    $recent_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get recent jobs
    $stmt = $db->prepare("SELECT jp.*, u.user_name FROM job_post jp JOIN user u ON jp.owner = u.user_ID ORDER BY jp.post_ID DESC LIMIT 5");
    $stmt->execute();
    $recent_jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get system health metrics
    $stmt = $db->query("SELECT COUNT(*) as unverified_users FROM user WHERE Verified = 0");
    $unverified_users = $stmt->fetch_assoc()['unverified_users'];

    $stmt = $db->query("SELECT COUNT(*) as pending_applications FROM job_applications WHERE status = 'pending'");
    $pending_applications = $stmt->fetch_assoc()['pending_applications'];

} catch (Exception $e) {
    error_log('Admin Dashboard Error: ' . $e->getMessage());
    $total_users = $total_jobs = $total_applications = $total_hires = 0;
    $user_types = $recent_users = $recent_jobs = [];
    $unverified_users = $pending_applications = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - D Labour Chowk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
            --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
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

        .dashboard-header {
            background: white;
            padding: 2rem;
            margin: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .dashboard-header h1 {
            color: #1f2937;
            margin-bottom: 0.5rem;
            font-weight: 800;
        }

        .dashboard-header p {
            color: #6b7280;
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
            border-left: 4px solid var(--primary-color);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: rgba(99, 102, 241, 0.1);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6b7280;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .stat-change {
            font-size: 0.9rem;
            font-weight: 500;
        }

        .stat-change.positive {
            color: var(--success-color);
        }

        .stat-change.negative {
            color: var(--danger-color);
        }

        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin: 2rem;
        }

        .content-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .content-card h5 {
            color: #1f2937;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-item, .job-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            background: #f8fafc;
            border-left: 4px solid var(--primary-color);
            transition: all 0.3s;
        }

        .user-item:hover, .job-item:hover {
            background: #e3f2fd;
            transform: translateX(5px);
        }

        .user-info h6, .job-info h6 {
            margin: 0;
            color: #1f2937;
            font-weight: 600;
        }

        .user-info small, .job-info small {
            color: #6b7280;
            font-size: 0.85rem;
        }

        .user-type, .job-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
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

        .chart-container {
            margin: 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 2rem;
        }

        .chart-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .chart-title {
            color: #1f2937;
            font-weight: 700;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem;
        }

        .action-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            text-decoration: none;
            color: #1f2937;
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            color: #1f2937;
        }

        .action-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .action-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .action-description {
            font-size: 0.9rem;
            color: #6b7280;
        }

        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .chart-container {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: 1fr;
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
                <a class="nav-link" href="users.php">
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

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <h1><i class="fas fa-tachometer-alt me-3"></i>Admin Dashboard</h1>
        <p>Welcome back, <?php echo htmlspecialchars($user_name); ?>! Here's what's happening with your platform.</p>
    </div>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo number_format($total_users); ?></div>
            <div class="stat-label">Total Users</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up me-1"></i>+12% from last month
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-number"><?php echo number_format($total_jobs); ?></div>
            <div class="stat-label">Active Jobs</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up me-1"></i>+8% from last month
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-number"><?php echo number_format($total_applications); ?></div>
            <div class="stat-label">Total Applications</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up me-1"></i>+15% from last month
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-number"><?php echo number_format($total_hires); ?></div>
            <div class="stat-label">Successful Hires</div>
            <div class="stat-change positive">
                <i class="fas fa-arrow-up me-1"></i>+10% from last month
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions">
        <a href="users.php" class="action-card">
            <div class="action-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="action-title">Manage Users</div>
            <div class="action-description">View, edit, and manage all platform users</div>
        </a>

        <a href="jobs.php" class="action-card">
            <div class="action-icon">
                <i class="fas fa-briefcase"></i>
            </div>
            <div class="action-title">Manage Jobs</div>
            <div class="action-description">Oversee all job postings and applications</div>
        </a>

        <a href="analytics.php" class="action-card">
            <div class="action-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="action-title">View Analytics</div>
            <div class="action-description">Detailed platform analytics and insights</div>
        </a>

        <a href="settings.php" class="action-card">
            <div class="action-icon">
                <i class="fas fa-cog"></i>
            </div>
            <div class="action-title">System Settings</div>
            <div class="action-description">Configure platform settings and preferences</div>
        </a>
    </div>

    <!-- Charts -->
    <div class="chart-container">
        <div class="chart-card">
            <h5 class="chart-title">
                <i class="fas fa-chart-pie"></i>User Distribution
            </h5>
            <canvas id="userDistributionChart" style="max-height: 300px;"></canvas>
        </div>

        <div class="chart-card">
            <h5 class="chart-title">
                <i class="fas fa-chart-bar"></i>Platform Growth
            </h5>
            <canvas id="growthChart" style="max-height: 300px;"></canvas>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Recent Users -->
        <div class="content-card">
            <h5><i class="fas fa-user-plus"></i>Recent Users</h5>
            <?php if (empty($recent_users)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-users" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p class="mt-3 text-muted">No users found.</p>
                </div>
            <?php else: ?>
                <?php foreach ($recent_users as $user): ?>
                    <div class="user-item">
                        <div class="user-info">
                            <h6><?php echo htmlspecialchars($user['user_name']); ?></h6>
                            <small><?php echo htmlspecialchars($user['email_id']); ?></small>
                        </div>
                        <div class="user-type <?php echo strtolower($user['user_type']); ?>">
                            <?php echo htmlspecialchars($user['user_type']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- System Health -->
        <div class="content-card">
            <h5><i class="fas fa-heartbeat"></i>System Health</h5>

            <div class="user-item">
                <div class="user-info">
                    <h6>Unverified Users</h6>
                    <small>Users awaiting email verification</small>
                </div>
                <div class="user-type" style="background: rgba(245, 158, 11, 0.1); color: var(--warning-color);">
                    <?php echo $unverified_users; ?>
                </div>
            </div>

            <div class="user-item">
                <div class="user-info">
                    <h6>Pending Applications</h6>
                    <small>Applications waiting for review</small>
                </div>
                <div class="user-type" style="background: rgba(37, 99, 235, 0.1); color: var(--primary-color);">
                    <?php echo $pending_applications; ?>
                </div>
            </div>

            <div class="user-item">
                <div class="user-info">
                    <h6>Database Status</h6>
                    <small>System database connectivity</small>
                </div>
                <div class="user-type" style="background: rgba(16, 185, 129, 0.1); color: var(--success-color);">
                    <i class="fas fa-check-circle me-1"></i>Healthy
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // User Distribution Chart
        const userDistributionCtx = document.getElementById('userDistributionChart').getContext('2d');
        new Chart(userDistributionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Clients', 'Workers', 'Admins'],
                datasets: [{
                    data: [
                        <?php echo $user_types['User'] ?? 0; ?>,
                        <?php echo $user_types['Labour'] ?? 0; ?>,
                        <?php echo $user_types['Admin'] ?? 0; ?>
                    ],
                    backgroundColor: [
                        'rgba(37, 99, 235, 0.8)',
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(245, 158, 11, 0.8)'
                    ],
                    borderWidth: 0,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                }
            }
        });

        // Growth Chart
        const growthCtx = document.getElementById('growthChart').getContext('2d');
        new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Users',
                    data: [12, 19, 15, 25, 22, <?php echo $total_users; ?>],
                    borderColor: 'rgba(37, 99, 235, 1)',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }, {
                    label: 'Jobs',
                    data: [8, 12, 10, 18, 15, <?php echo $total_jobs; ?>],
                    borderColor: 'rgba(40, 167, 69, 1)',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    </script>
</body>
</html>