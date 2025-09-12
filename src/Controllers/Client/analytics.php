<?php
require_once '../Shared/config.php';

// Check if user is logged in and is a client
if (!isLoggedIn()) {
    redirect('../Shared/login_form.php');
}

if (getCurrentUserType() !== 'User') {
    redirect('../Shared/login_form.php?error=unauthorized');
}

$user_id = getCurrentUserId();
$user_name = $_SESSION['user_name'];

try {
    $db = Database::getInstance();
    
    // Get user's job statistics
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_jobs,
            SUM(CASE WHEN jp.post_ID IS NOT NULL THEN 1 ELSE 0 END) as active_jobs
        FROM job_post jp 
        WHERE jp.owner = ?
    ");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $job_stats = $stmt->get_result()->fetch_assoc();
    
    // Get application statistics
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_applications,
            SUM(CASE WHEN ja.status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN ja.status = 'accepted' THEN 1 ELSE 0 END) as accepted,
            SUM(CASE WHEN ja.status = 'rejected' THEN 1 ELSE 0 END) as rejected
        FROM job_applications ja 
        JOIN job_post jp ON ja.job_post_id = jp.post_ID 
        WHERE jp.owner = ?
    ");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $app_stats = $stmt->get_result()->fetch_assoc();
    
    // Get hire statistics
    $stmt = $db->prepare("
        SELECT 
            COUNT(*) as total_hires,
            SUM(CASE WHEN h.status = 'active' THEN 1 ELSE 0 END) as active_hires,
            SUM(CASE WHEN h.status = 'completed' THEN 1 ELSE 0 END) as completed_hires,
            SUM(CASE WHEN h.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_hires
        FROM hires h 
        WHERE h.client_id = ?
    ");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $hire_stats = $stmt->get_result()->fetch_assoc();
    
    // Get jobs by work type
    $stmt = $db->prepare("
        SELECT jp.jobTitle as work_type, COUNT(*) as count 
        FROM job_post jp 
        WHERE jp.owner = ? 
        GROUP BY jp.jobTitle 
        ORDER BY count DESC
    ");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $jobs_by_type = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get spending by month
    $stmt = $db->prepare("
        SELECT 
            DATE_FORMAT(jp.createdDate, '%Y-%m') as month,
            COUNT(*) as jobs_posted,
            AVG(jp.salary) as avg_salary
        FROM job_post jp 
        WHERE jp.owner = ? AND jp.createdDate > 0
        GROUP BY DATE_FORMAT(jp.createdDate, '%Y-%m')
        ORDER BY month DESC
        LIMIT 12
    ");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $monthly_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get top performing workers
    $stmt = $db->prepare("
        SELECT 
            u.user_name,
            lp.workType,
            lp.salary,
            AVG(r.rating) as avg_rating,
            COUNT(h.hire_id) as total_hires
        FROM hires h
        JOIN user u ON h.labour_id = u.user_ID
        LEFT JOIN lab_post lp ON lp.user_ID = u.user_ID
        LEFT JOIN ratings r ON r.labour_id = u.user_ID
        WHERE h.client_id = ?
        GROUP BY u.user_ID
        HAVING total_hires > 0
        ORDER BY avg_rating DESC, total_hires DESC
        LIMIT 10
    ");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $top_workers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get recent activity
    $stmt = $db->prepare("
        SELECT 
            'job_post' as type,
            jp.jobTitle as title,
            jp.salary as amount,
            jp.city as location,
            UNIX_TIMESTAMP() as timestamp
        FROM job_post jp 
        WHERE jp.owner = ?
        ORDER BY jp.post_ID DESC
        LIMIT 5
    ");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $recent_activity = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    error_log('Analytics Error: ' . $e->getMessage());
    $job_stats = $app_stats = $hire_stats = ['total_jobs' => 0, 'active_jobs' => 0, 'total_applications' => 0, 'pending' => 0, 'accepted' => 0, 'rejected' => 0, 'total_hires' => 0, 'active_hires' => 0, 'completed_hires' => 0, 'cancelled_hires' => 0];
    $jobs_by_type = $monthly_data = $top_workers = $recent_activity = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - D Labour Chowk</title>
    <meta http-equiv="Content-Security-Policy" content="font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net;">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            color: white !important;
            font-weight: 600;
            font-size: 1.5rem;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: white !important;
        }

        .analytics-header {
            background: white;
            padding: 30px;
            margin: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .analytics-header h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .analytics-header p {
            color: #666;
            font-size: 1.1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 10px;
        }

        .stat-label {
            color: #666;
            font-weight: 500;
            margin-bottom: 15px;
        }

        .stat-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2rem;
            color: #667eea;
            opacity: 0.3;
        }

        .chart-container {
            margin: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 30px;
        }

        .chart-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .chart-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .chart-canvas {
            max-height: 300px;
        }

        .insights-section {
            margin: 30px;
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .insights-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .insight-item {
            padding: 15px;
            border-left: 4px solid #667eea;
            margin-bottom: 15px;
            background: #f8f9fa;
            border-radius: 0 10px 10px 0;
        }

        .insight-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .insight-description {
            color: #666;
            font-size: 0.9rem;
        }

        .worker-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            margin-bottom: 10px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s;
        }

        .worker-item:hover {
            background: #e3f2fd;
            transform: translateX(5px);
        }

        .worker-info h6 {
            margin: 0;
            color: #333;
        }

        .worker-info small {
            color: #666;
        }

        .worker-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #ffc107;
        }

        @media (max-width: 768px) {
            .chart-container,
            .insights-section {
                grid-template-columns: 1fr;
            }
            
            .analytics-header,
            .stats-grid,
            .chart-container,
            .insights-section {
                margin: 15px;
            }
        }

        .progress-ring {
            width: 60px;
            height: 60px;
            position: absolute;
            bottom: 15px;
            right: 15px;
        }

        .progress-ring-circle {
            fill: transparent;
            stroke: #e1e8ed;
            stroke-width: 4;
        }

        .progress-ring-progress {
            fill: transparent;
            stroke: #667eea;
            stroke-width: 4;
            stroke-linecap: round;
            stroke-dasharray: 157;
            stroke-dashoffset: 157;
            transform-origin: 50% 50%;
            transform: rotate(-90deg);
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-briefcase"></i> D Labour Chowk
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="availableLabour.php">Browse Workers</a>
                <a class="nav-link" href="advanced_search.php">Advanced Search</a>
                <a class="nav-link" href="../Shared/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Analytics Header -->
    <div class="analytics-header">
        <h1><i class="bi bi-graph-up"></i> Analytics Dashboard</h1>
        <p>Comprehensive insights into your hiring activities and performance metrics</p>
    </div>

    <!-- Key Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <i class="bi bi-briefcase stat-icon"></i>
            <div class="stat-number"><?php echo $job_stats['total_jobs']; ?></div>
            <div class="stat-label">Total Jobs Posted</div>
            <svg class="progress-ring">
                <circle class="progress-ring-circle" cx="30" cy="30" r="25"></circle>
                <circle class="progress-ring-progress" cx="30" cy="30" r="25" style="stroke-dashoffset: 78.5;"></circle>
            </svg>
        </div>
        
        <div class="stat-card">
            <i class="bi bi-envelope stat-icon"></i>
            <div class="stat-number"><?php echo $app_stats['total_applications']; ?></div>
            <div class="stat-label">Total Applications Received</div>
            <svg class="progress-ring">
                <circle class="progress-ring-circle" cx="30" cy="30" r="25"></circle>
                <circle class="progress-ring-progress" cx="30" cy="30" r="25" style="stroke-dashoffset: 47.1;"></circle>
            </svg>
        </div>
        
        <div class="stat-card">
            <i class="bi bi-person-check stat-icon"></i>
            <div class="stat-number"><?php echo $hire_stats['total_hires']; ?></div>
            <div class="stat-label">Workers Hired</div>
            <svg class="progress-ring">
                <circle class="progress-ring-circle" cx="30" cy="30" r="25"></circle>
                <circle class="progress-ring-progress" cx="30" cy="30" r="25" style="stroke-dashoffset: 31.4;"></circle>
            </svg>
        </div>
        
        <div class="stat-card">
            <i class="bi bi-star stat-icon"></i>
            <div class="stat-number"><?php echo round((($app_stats['accepted'] ?: 0) / max($app_stats['total_applications'] ?: 1, 1)) * 100); ?>%</div>
            <div class="stat-label">Success Rate</div>
            <svg class="progress-ring">
                <circle class="progress-ring-circle" cx="30" cy="30" r="25"></circle>
                <circle class="progress-ring-progress" cx="30" cy="30" r="25" style="stroke-dashoffset: 94.2;"></circle>
            </svg>
        </div>
    </div>

    <!-- Charts -->
    <div class="chart-container">
        <div class="chart-card">
            <h5 class="chart-title">
                <i class="bi bi-pie-chart"></i> Applications by Status
            </h5>
            <canvas id="applicationsChart" class="chart-canvas"></canvas>
        </div>

        <div class="chart-card">
            <h5 class="chart-title">
                <i class="bi bi-bar-chart"></i> Jobs by Work Type
            </h5>
            <canvas id="jobTypesChart" class="chart-canvas"></canvas>
        </div>

        <div class="chart-card">
            <h5 class="chart-title">
                <i class="bi bi-graph-up"></i> Monthly Job Posting Trends
            </h5>
            <canvas id="monthlyTrendsChart" class="chart-canvas"></canvas>
        </div>

        <div class="chart-card">
            <h5 class="chart-title">
                <i class="bi bi-trophy"></i> Worker Performance
            </h5>
            <canvas id="workerPerformanceChart" class="chart-canvas"></canvas>
        </div>
    </div>

    <!-- Insights and Top Workers -->
    <div class="insights-section">
        <div class="insights-card">
            <h5 class="chart-title">
                <i class="bi bi-lightbulb"></i> Key Insights
            </h5>
            
            <div class="insight-item">
                <div class="insight-title">Most Popular Job Type</div>
                <div class="insight-description">
                    <?php 
                    if (!empty($jobs_by_type)) {
                        echo htmlspecialchars($jobs_by_type[0]['work_type']) . ' jobs are your most posted category with ' . $jobs_by_type[0]['count'] . ' posts.';
                    } else {
                        echo 'No job data available yet.';
                    }
                    ?>
                </div>
            </div>
            
            <div class="insight-item">
                <div class="insight-title">Application Response Rate</div>
                <div class="insight-description">
                    <?php 
                    $response_rate = $app_stats['total_applications'] > 0 ? 
                        round((($app_stats['accepted'] + $app_stats['rejected']) / $app_stats['total_applications']) * 100) : 0;
                    echo "You've responded to {$response_rate}% of applications received.";
                    ?>
                </div>
            </div>
            
            <div class="insight-item">
                <div class="insight-title">Hiring Efficiency</div>
                <div class="insight-description">
                    <?php 
                    $efficiency = $app_stats['total_applications'] > 0 ? 
                        round(($hire_stats['total_hires'] / $app_stats['total_applications']) * 100) : 0;
                    echo "You hire {$efficiency}% of applicants, showing ";
                    echo $efficiency > 15 ? "high selectivity" : ($efficiency > 5 ? "moderate selectivity" : "careful selection");
                    echo " in your hiring process.";
                    ?>
                </div>
            </div>
            
            <div class="insight-item">
                <div class="insight-title">Active Projects</div>
                <div class="insight-description">
                    You currently have <?php echo $hire_stats['active_hires']; ?> active hires and 
                    <?php echo $hire_stats['completed_hires']; ?> completed projects.
                </div>
            </div>
        </div>
        
        <div class="insights-card">
            <h5 class="chart-title">
                <i class="bi bi-award"></i> Top Performing Workers
            </h5>
            
            <?php if (empty($top_workers)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-people" style="font-size: 3rem; opacity: 0.3;"></i>
                    <p class="mt-3 text-muted">No worker data available yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($top_workers as $worker): ?>
                    <div class="worker-item">
                        <div class="worker-info">
                            <h6><?php echo htmlspecialchars($worker['user_name']); ?></h6>
                            <small><?php echo htmlspecialchars($worker['workType'] ?: 'General Worker'); ?></small>
                        </div>
                        <div class="worker-rating">
                            <i class="bi bi-star-fill"></i>
                            <?php echo $worker['avg_rating'] ? number_format($worker['avg_rating'], 1) : 'N/A'; ?>
                            <small class="text-muted ms-2">(<?php echo $worker['total_hires']; ?> hires)</small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Applications Status Chart
        const applicationsCtx = document.getElementById('applicationsChart').getContext('2d');
        new Chart(applicationsCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Accepted', 'Rejected'],
                datasets: [{
                    data: [
                        <?php echo $app_stats['pending'] ?: 0; ?>,
                        <?php echo $app_stats['accepted'] ?: 0; ?>,
                        <?php echo $app_stats['rejected'] ?: 0; ?>
                    ],
                    backgroundColor: [
                        '#ffc107',
                        '#28a745',
                        '#dc3545'
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

        // Job Types Chart
        const jobTypesCtx = document.getElementById('jobTypesChart').getContext('2d');
        new Chart(jobTypesCtx, {
            type: 'bar',
            data: {
                labels: [
                    <?php foreach ($jobs_by_type as $job): ?>
                        '<?php echo addslashes($job['work_type']); ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    label: 'Jobs Posted',
                    data: [
                        <?php foreach ($jobs_by_type as $job): ?>
                            <?php echo $job['count']; ?>,
                        <?php endforeach; ?>
                    ],
                    backgroundColor: 'rgba(102, 126, 234, 0.8)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 1,
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Animate progress rings
        function animateProgressRing(ring, percentage) {
            const circle = ring.querySelector('.progress-ring-progress');
            const radius = circle.r.baseVal.value;
            const circumference = radius * 2 * Math.PI;
            const offset = circumference - (percentage / 100) * circumference;
            
            circle.style.strokeDashoffset = offset;
        }

        // Monthly Trends Chart
        const monthlyTrendsCtx = document.getElementById('monthlyTrendsChart').getContext('2d');
        new Chart(monthlyTrendsCtx, {
            type: 'line',
            data: {
                labels: [
                    <?php foreach (array_reverse($monthly_data) as $data): ?>
                        '<?php echo date('M Y', strtotime($data['month'] . '-01')); ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    label: 'Jobs Posted',
                    data: [
                        <?php foreach (array_reverse($monthly_data) as $data): ?>
                            <?php echo $data['jobs_posted']; ?>,
                        <?php endforeach; ?>
                    ],
                    borderColor: 'rgba(102, 126, 234, 1)',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: 'rgba(102, 126, 234, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index'
                }
            }
        });

        // Worker Performance Chart
        const workerPerformanceCtx = document.getElementById('workerPerformanceChart').getContext('2d');
        new Chart(workerPerformanceCtx, {
            type: 'radar',
            data: {
                labels: [
                    <?php foreach (array_slice($top_workers, 0, 6) as $worker): ?>
                        '<?php echo addslashes(substr($worker['user_name'], 0, 10)); ?>',
                    <?php endforeach; ?>
                ],
                datasets: [{
                    label: 'Rating',
                    data: [
                        <?php foreach (array_slice($top_workers, 0, 6) as $worker): ?>
                            <?php echo $worker['avg_rating'] ?: 0; ?>,
                        <?php endforeach; ?>
                    ],
                    borderColor: 'rgba(40, 167, 69, 1)',
                    backgroundColor: 'rgba(40, 167, 69, 0.2)',
                    borderWidth: 2,
                    pointBackgroundColor: 'rgba(40, 167, 69, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 5,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Animate all progress rings
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(() => {
                animateProgressRing(document.querySelectorAll('.progress-ring')[0], 50);
                animateProgressRing(document.querySelectorAll('.progress-ring')[1], 70);
                animateProgressRing(document.querySelectorAll('.progress-ring')[2], 80);
                animateProgressRing(document.querySelectorAll('.progress-ring')[3], 40);
            }, 500);
        });
    </script>
</body>
</html>
