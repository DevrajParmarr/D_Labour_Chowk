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
    
    // Get user's posted jobs
    $stmt = $db->prepare("
        SELECT jp.*, COUNT(ja.application_id) as application_count 
        FROM job_post jp 
        LEFT JOIN job_applications ja ON jp.post_ID = ja.job_post_id 
        WHERE jp.owner = ? 
        GROUP BY jp.post_ID 
        ORDER BY jp.post_ID DESC 
        LIMIT 5
    ");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $my_jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get hired labourers
    $stmt = $db->prepare("
        SELECT h.*, u.user_name, u.mobile_no, lp.workType, lp.experience 
        FROM hires h 
        JOIN user u ON h.labour_id = u.user_ID 
        LEFT JOIN lab_post lp ON lp.user_ID = h.labour_id
        WHERE h.client_id = ? 
        ORDER BY h.hire_date DESC 
        LIMIT 5
    ");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $hired_labourers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get statistics
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM job_post WHERE owner = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $total_jobs = $stmt->get_result()->fetch_assoc()['count'];
    
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM hires WHERE client_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $total_hires = $stmt->get_result()->fetch_assoc()['count'];
    
    $stmt = $db->prepare("
        SELECT COUNT(*) as count 
        FROM job_applications ja 
        JOIN job_post jp ON ja.job_post_id = jp.post_ID 
        WHERE jp.owner = ?
    ");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $total_applications = $stmt->get_result()->fetch_assoc()['count'];
    
} catch (Exception $e) {
    error_log('Dashboard Error: ' . $e->getMessage());
    $my_jobs = $hired_labourers = [];
    $total_jobs = $total_hires = $total_applications = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - D Labour Chowk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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

        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h4 {
            color: white;
            font-weight: 600;
            margin: 0;
        }

        .sidebar-menu {
            list-style: none;
            padding: 20px 0;
        }

        .sidebar-menu li {
            margin: 5px 0;
        }

        .sidebar-menu a {
            display: block;
            padding: 15px 25px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: #fff;
        }

        .main-content {
            margin-left: 250px;
            padding: 0;
        }

        .top-navbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .dashboard-content {
            padding: 30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #667eea;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
            margin: 0;
        }

        .stat-card p {
            margin: 10px 0 0;
            color: #666;
            font-weight: 500;
        }

        .stat-card i {
            font-size: 2rem;
            color: #667eea;
            opacity: 0.7;
            float: right;
            margin-top: -60px;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .content-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .content-card h5 {
            margin-bottom: 20px;
            color: #333;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .job-item, .labour-item {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            transition: all 0.3s;
        }

        .job-item:hover, .labour-item:hover {
            background: #e3f2fd;
            transform: translateX(5px);
        }

        .job-title, .labour-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .job-meta, .labour-meta {
            color: #666;
            font-size: 0.9rem;
        }

        .badge {
            font-size: 0.75rem;
            padding: 5px 10px;
            border-radius: 20px;
        }

        .quick-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn-action {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #95a5a6, #7f8c8d);
        }

        .btn-secondary:hover {
            box-shadow: 0 5px 15px rgba(149, 165, 166, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-250px);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #667eea;
        }

        @media (max-width: 768px) {
            .mobile-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h4><i class="bi bi-briefcase"></i> Client Panel</h4>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="creatjob.php"><i class="bi bi-plus-circle"></i> Post New Job</a></li>
            <li><a href="view.php"><i class="bi bi-list-ul"></i> My Job Posts</a></li>
            <li><a href="availableLabour.php"><i class="bi bi-people"></i> Browse Workers</a></li>
            <li><a href="hiredLabour.php"><i class="bi bi-person-check"></i> Hired Workers</a></li>
            <li><a href="interested_labour.php"><i class="bi bi-envelope"></i> Applications</a></li>
            <li><a href="search.php"><i class="bi bi-search"></i> Advanced Search</a></li>
            <li><a href="profile1.php"><i class="bi bi-person"></i> My Profile</a></li>
            <li><a href="../Shared/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Top Navbar -->
        <header class="top-navbar">
            <div class="d-flex align-items-center">
                <button class="mobile-toggle" id="mobileToggle">
                    <i class="bi bi-list"></i>
                </button>
                <h4 class="mb-0 ms-3">Dashboard</h4>
            </div>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($user_name); ?></span>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user_name, 0, 1)); ?>
                </div>
            </div>
        </header>

        <!-- Dashboard Content -->
        <section class="dashboard-content">
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $total_jobs; ?></h3>
                    <p>Total Jobs Posted</p>
                    <i class="bi bi-briefcase"></i>
                </div>
                <div class="stat-card">
                    <h3><?php echo $total_applications; ?></h3>
                    <p>Total Applications</p>
                    <i class="bi bi-envelope"></i>
                </div>
                <div class="stat-card">
                    <h3><?php echo $total_hires; ?></h3>
                    <p>Workers Hired</p>
                    <i class="bi bi-person-check"></i>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="creatjob.php" class="btn-action">
                    <i class="bi bi-plus-circle"></i>
                    Post New Job
                </a>
                <a href="availableLabour.php" class="btn-action btn-secondary">
                    <i class="bi bi-people"></i>
                    Browse Workers
                </a>
                <a href="search.php" class="btn-action btn-secondary">
                    <i class="bi bi-search"></i>
                    Advanced Search
                </a>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Recent Job Posts -->
                <div class="content-card">
                    <h5><i class="bi bi-briefcase"></i> Recent Job Posts</h5>
                    <?php if (empty($my_jobs)): ?>
                        <div class="empty-state">
                            <i class="bi bi-briefcase"></i>
                            <p>No job posts yet</p>
                            <a href="creatjob.php" class="btn-action">Post Your First Job</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($my_jobs as $job): ?>
                            <div class="job-item">
                                <div class="job-title"><?php echo htmlspecialchars($job['jobTitle']); ?></div>
                                <div class="job-meta">
                                    <span><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($job['city']); ?></span>
                                    <span class="ms-3"><i class="bi bi-currency-rupee"></i> ₹<?php echo number_format($job['salary']); ?></span>
                                    <span class="badge bg-primary ms-2"><?php echo $job['application_count']; ?> applications</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-3">
                            <a href="view.php" class="btn-action btn-secondary">View All Jobs</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Hired Workers -->
                <div class="content-card">
                    <h5><i class="bi bi-person-check"></i> Recently Hired Workers</h5>
                    <?php if (empty($hired_labourers)): ?>
                        <div class="empty-state">
                            <i class="bi bi-person-check"></i>
                            <p>No workers hired yet</p>
                            <a href="availableLabour.php" class="btn-action">Browse Workers</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($hired_labourers as $labour): ?>
                            <div class="labour-item">
                                <div class="labour-name"><?php echo htmlspecialchars($labour['user_name']); ?></div>
                                <div class="labour-meta">
                                    <span><i class="bi bi-tools"></i> <?php echo htmlspecialchars($labour['workType'] ?? 'General Worker'); ?></span>
                                    <span class="ms-3"><i class="bi bi-clock"></i> <?php echo htmlspecialchars($labour['experience'] ?? 'N/A'); ?></span>
                                    <span class="badge bg-<?php echo $labour['status'] === 'active' ? 'success' : 'secondary'; ?> ms-2">
                                        <?php echo ucfirst($labour['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-3">
                            <a href="hiredLabour.php" class="btn-action btn-secondary">View All Hired</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle
        document.getElementById('mobileToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('mobileToggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !toggle.contains(e.target)) {
                sidebar.classList.remove('active');
            }
        });

        // Auto-refresh dashboard every 30 seconds
        setTimeout(function() {
            window.location.reload();
        }, 300000); // 5 minutes
    </script>
</body>
</html>
