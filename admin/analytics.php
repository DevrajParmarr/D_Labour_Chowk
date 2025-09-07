<?php
require_once '../Shared/config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn()) {
    redirect('../Shared/login_form.php');
}

if (getCurrentUserType() !== 'Admin') {
    redirect('../Shared/login_form.php?error=unauthorized');
}

$db = Database::getInstance();
$conn = $db->getConnection();

// Get comprehensive analytics data
$analytics = [];

// User analytics
$analytics['users'] = $conn->query("
    SELECT
        COUNT(*) as total_users,
        COUNT(CASE WHEN user_type = 'User' THEN 1 END) as clients,
        COUNT(CASE WHEN user_type = 'Labour' THEN 1 END) as workers,
        COUNT(CASE WHEN user_type = 'Admin' THEN 1 END) as admins,
        COUNT(*) as verified_users,
        COUNT(CASE WHEN date_created >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_users_30d
    FROM user
")->fetch_assoc();

// Job analytics
$analytics['jobs'] = $conn->query("
    SELECT
        COUNT(*) as total_jobs,
        COUNT(*) as active_jobs,
        0 as pending_jobs,
        0 as new_jobs_30d,
        AVG(salary) as avg_salary,
        MAX(salary) as max_salary,
        MIN(salary) as min_salary
    FROM job_post
")->fetch_assoc();

// Application analytics
$analytics['applications'] = $conn->query("
    SELECT
        COUNT(*) as total_applications,
        COUNT(CASE WHEN status = 'accepted' THEN 1 END) as accepted_applications,
        COUNT(CASE WHEN status = 'rejected' THEN 1 END) as rejected_applications,
        COUNT(CASE WHEN applied_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_applications_30d
    FROM job_applications
")->fetch_assoc();

// Hire analytics
$analytics['hires'] = $conn->query("
    SELECT
        COUNT(*) as total_hires,
        COUNT(CASE WHEN hire_id >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN 1 END) as new_hires_30d
    FROM hires
")->fetch_assoc();

// Popular job categories
$analytics['categories'] = $conn->query("
    SELECT workType, COUNT(*) as count
    FROM lab_post
    GROUP BY workType
    ORDER BY count DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Top cities with most jobs
$analytics['cities'] = $conn->query("
    SELECT city, COUNT(*) as job_count
    FROM job_post
    GROUP BY city
    ORDER BY job_count DESC
    LIMIT 10
")->fetch_all(MYSQLI_ASSOC);

// Monthly growth data (last 12 months)
$analytics['monthly_growth'] = $conn->query("
    SELECT
        DATE_FORMAT(date_created, '%Y-%m') as month,
        COUNT(*) as user_count
    FROM user
    WHERE date_created >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(date_created, '%Y-%m')
    ORDER BY month
")->fetch_all(MYSQLI_ASSOC);

// Platform performance metrics
$analytics['performance'] = [
    'user_registration_rate' => round($analytics['users']['new_users_30d'] / 30, 2),
    'job_posting_rate' => round($analytics['jobs']['new_jobs_30d'] / 30, 2),
    'application_rate' => round($analytics['applications']['new_applications_30d'] / 30, 2),
    'hire_rate' => round($analytics['hires']['new_hires_30d'] / 30, 2),
    'acceptance_rate' => $analytics['applications']['total_applications'] > 0 ?
        round(($analytics['applications']['accepted_applications'] / $analytics['applications']['total_applications']) * 100, 2) : 0,
    'verification_rate' => 100 // All users are considered verified for now
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Admin Panel</title>
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

        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin: 2rem;
        }

        .metric-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
        }

        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }

        .metric-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .metric-label {
            color: #6b7280;
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 1rem;
        }

        .metric-change {
            font-size: 0.9rem;
            font-weight: 500;
        }

        .metric-change.positive {
            color: var(--success-color);
        }

        .metric-change.negative {
            color: var(--danger-color);
        }

        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
            margin: 2rem;
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

        .insights-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin: 2rem;
        }

        .insight-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .insight-title {
            color: #1f2937;
            font-weight: 700;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .insight-list {
            list-style: none;
            padding: 0;
        }

        .insight-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .insight-item:last-child {
            border-bottom: none;
        }

        .insight-label {
            color: #6b7280;
            font-weight: 500;
        }

        .insight-value {
            color: var(--primary-color);
            font-weight: 700;
        }

        .performance-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin: 2rem;
        }

        .performance-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .performance-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .performance-label {
            color: #6b7280;
            font-weight: 600;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .analytics-grid {
                grid-template-columns: 1fr;
            }

            .charts-container {
                grid-template-columns: 1fr;
            }

            .insights-grid {
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
                <a class="nav-link active" href="analytics.php">
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
        <h1><i class="fas fa-chart-line me-3"></i>Platform Analytics</h1>
        <p>Comprehensive insights into platform performance, user behavior, and growth metrics.</p>
    </div>

    <!-- Key Metrics -->
    <div class="analytics-grid">
        <div class="metric-card">
            <div class="metric-value"><?php echo number_format($analytics['users']['total_users']); ?></div>
            <div class="metric-label">Total Users</div>
            <div class="metric-change positive">
                <i class="fas fa-arrow-up me-1"></i>+<?php echo $analytics['users']['new_users_30d']; ?> this month
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-value"><?php echo number_format($analytics['jobs']['total_jobs']); ?></div>
            <div class="metric-label">Total Jobs</div>
            <div class="metric-change positive">
                <i class="fas fa-arrow-up me-1"></i>+<?php echo $analytics['jobs']['new_jobs_30d']; ?> this month
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-value"><?php echo number_format($analytics['applications']['total_applications']); ?></div>
            <div class="metric-label">Total Applications</div>
            <div class="metric-change positive">
                <i class="fas fa-arrow-up me-1"></i>+<?php echo $analytics['applications']['new_applications_30d']; ?> this month
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-value"><?php echo number_format($analytics['hires']['total_hires']); ?></div>
            <div class="metric-label">Successful Hires</div>
            <div class="metric-change positive">
                <i class="fas fa-arrow-up me-1"></i>+<?php echo $analytics['hires']['new_hires_30d']; ?> this month
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-value">₹<?php echo number_format($analytics['jobs']['avg_salary']); ?></div>
            <div class="metric-label">Average Salary</div>
            <div class="metric-change">
                <i class="fas fa-info-circle me-1"></i>Monthly average
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-value"><?php echo $analytics['performance']['acceptance_rate']; ?>%</div>
            <div class="metric-label">Acceptance Rate</div>
            <div class="metric-change positive">
                <i class="fas fa-thumbs-up me-1"></i>Application success rate
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="performance-grid">
        <div class="performance-card">
            <div class="performance-value"><?php echo $analytics['performance']['user_registration_rate']; ?>/day</div>
            <div class="performance-label">User Registration Rate</div>
        </div>
        <div class="performance-card">
            <div class="performance-value"><?php echo $analytics['performance']['job_posting_rate']; ?>/day</div>
            <div class="performance-label">Job Posting Rate</div>
        </div>
        <div class="performance-card">
            <div class="performance-value"><?php echo $analytics['performance']['application_rate']; ?>/day</div>
            <div class="performance-label">Application Rate</div>
        </div>
        <div class="performance-card">
            <div class="performance-value"><?php echo $analytics['performance']['hire_rate']; ?>/day</div>
            <div class="performance-label">Hire Rate</div>
        </div>
        <div class="performance-card">
            <div class="performance-value"><?php echo $analytics['performance']['verification_rate']; ?>%</div>
            <div class="performance-label">User Verification Rate</div>
        </div>
    </div>

    <!-- Charts -->
    <div class="charts-container">
        <div class="chart-card">
            <h5 class="chart-title">
                <i class="fas fa-users"></i>User Distribution
            </h5>
            <canvas id="userDistributionChart" style="max-height: 300px;"></canvas>
        </div>

        <div class="chart-card">
            <h5 class="chart-title">
                <i class="fas fa-chart-line"></i>User Growth (12 Months)
            </h5>
            <canvas id="userGrowthChart" style="max-height: 300px;"></canvas>
        </div>
    </div>

    <!-- Insights -->
    <div class="insights-grid">
        <div class="insight-card">
            <h5 class="insight-title">
                <i class="fas fa-tools"></i>Popular Job Categories
            </h5>
            <ul class="insight-list">
                <?php foreach ($analytics['categories'] as $category): ?>
                    <li class="insight-item">
                        <span class="insight-label"><?php echo htmlspecialchars($category['workType']); ?></span>
                        <span class="insight-value"><?php echo $category['count']; ?> posts</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="insight-card">
            <h5 class="insight-title">
                <i class="fas fa-map-marker-alt"></i>Top Cities by Job Posts
            </h5>
            <ul class="insight-list">
                <?php foreach ($analytics['cities'] as $city): ?>
                    <li class="insight-item">
                        <span class="insight-label"><?php echo htmlspecialchars($city['city']); ?></span>
                        <span class="insight-value"><?php echo $city['job_count']; ?> jobs</span>
                    </li>
                <?php endforeach; ?>
            </ul>
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
                        <?php echo $analytics['users']['clients']; ?>,
                        <?php echo $analytics['users']['workers']; ?>,
                        <?php echo $analytics['users']['admins']; ?>
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

        // User Growth Chart
        const userGrowthCtx = document.getElementById('userGrowthChart').getContext('2d');
        new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: [
                    <?php
                    foreach ($analytics['monthly_growth'] as $month) {
                        echo "'" . date('M Y', strtotime($month['month'] . '-01')) . "', ";
                    }
                    ?>
                ],
                datasets: [{
                    label: 'New Users',
                    data: [
                        <?php
                        foreach ($analytics['monthly_growth'] as $month) {
                            echo $month['user_count'] . ", ";
                        }
                        ?>
                    ],
                    borderColor: 'rgba(37, 99, 235, 1)',
                    backgroundColor: 'rgba(37, 99, 235, 0.1)',
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