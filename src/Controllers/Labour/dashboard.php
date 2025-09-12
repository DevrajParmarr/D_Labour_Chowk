<?php
require_once '../Shared/config.php';

// Check if user is logged in and is a labour
if (!isLoggedIn()) {
    redirect('../Shared/login_form.php');
}

if (getCurrentUserType() !== 'Labour') {
    redirect('../Shared/login_form.php?error=unauthorized');
}

$user_id = getCurrentUserId();
$user_name = $_SESSION['user_name'];

try {
    $db = Database::getInstance();
    
    // Get labour's applications
    $stmt = $db->prepare("
        SELECT ja.*, jp.jobTitle, jp.salary, jp.city, jp.location 
        FROM job_applications ja 
        JOIN job_post jp ON ja.job_post_id = jp.post_ID 
        WHERE ja.labour_id = ? 
        ORDER BY ja.applied_at DESC 
        LIMIT 5
    ");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get labour's work posts
    $stmt = $db->prepare("
        SELECT * FROM work_posts 
        WHERE labour_ID = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $work_posts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get labour profile
    $stmt = $db->prepare("
        SELECT * FROM lab_post 
        WHERE user_ID = ? 
        ORDER BY l_post_ID DESC 
        LIMIT 1
    ");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $profile = $stmt->get_result()->fetch_assoc();
    
    // Get statistics
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM job_applications WHERE labour_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $total_applications = $stmt->get_result()->fetch_assoc()['count'];
    
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM job_applications WHERE labour_id = ? AND status = 'accepted'");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $accepted_applications = $stmt->get_result()->fetch_assoc()['count'];
    
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM hires WHERE labour_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $total_hires = $stmt->get_result()->fetch_assoc()['count'];
    
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM work_posts WHERE labour_ID = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $total_work_posts = $stmt->get_result()->fetch_assoc()['count'];
    
} catch (Exception $e) {
    error_log('Labour Dashboard Error: ' . $e->getMessage());
    $applications = $work_posts = [];
    $profile = null;
    $total_applications = $accepted_applications = $total_hires = $total_work_posts = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Dashboard - D Labour Chowk</title>
    <meta http-equiv="Content-Security-Policy" content="font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net;">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%);
            min-height: 100vh;
        }

        .sidebar {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
            background: linear-gradient(135deg, #28a745, #20c997);
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #28a745;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .stat-card h3 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #28a745;
            margin: 0;
        }

        .stat-card p {
            margin: 10px 0 0;
            color: #666;
            font-weight: 500;
        }

        .stat-card i {
            font-size: 2rem;
            color: #28a745;
            opacity: 0.7;
            float: right;
            margin-top: -60px;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }

        .profile-overview {
            grid-column: 1 / -1;
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
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

        .application-item, .work-post-item {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            background: #f8f9fa;
            border-left: 4px solid #28a745;
            transition: all 0.3s;
        }

        .application-item:hover, .work-post-item:hover {
            background: #e8f5e8;
            transform: translateX(5px);
        }

        .application-title, .work-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }

        .application-meta, .work-meta {
            color: #666;
            font-size: 0.9rem;
        }

        .badge {
            font-size: 0.75rem;
            padding: 5px 10px;
            border-radius: 20px;
        }

        .status-pending { background: #ffc107; color: #212529; }
        .status-accepted { background: #28a745; color: white; }
        .status-rejected { background: #dc3545; color: white; }

        .quick-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .btn-action {
            background: linear-gradient(135deg, #28a745, #20c997);
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
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
            color: white;
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268);
        }

        .btn-secondary:hover {
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.4);
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

        .profile-info {
            display: grid;
            grid-template-columns: auto 1fr auto;
            gap: 20px;
            align-items: center;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #28a745, #20c997);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: 600;
        }

        .profile-details h4 {
            color: #333;
            margin-bottom: 5px;
        }

        .profile-details p {
            color: #666;
            margin-bottom: 2px;
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
            
            .profile-info {
                grid-template-columns: 1fr;
                text-align: center;
            }
        }

        .mobile-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #28a745;
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
            <h4><i class="bi bi-tools"></i> Worker Panel</h4>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="active"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="postL.php"><i class="bi bi-briefcase"></i> Available Jobs</a></li>
            <li><a href="appliedJob.php"><i class="bi bi-envelope"></i> My Applications</a></li>
            <li><a href="work_posts.php"><i class="bi bi-images"></i> Work Portfolio</a></li>
            <li><a href="post_work.php"><i class="bi bi-plus-circle"></i> Add Work Post</a></li>
            <li><a href="creatLpost.php"><i class="bi bi-person-badge"></i> Create Profile</a></li>
            <li><a href="profile.php"><i class="bi bi-person"></i> My Profile</a></li>
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
            <!-- Profile Overview -->
            <?php if ($profile): ?>
                <div class="profile-overview">
                    <div class="profile-info">
                        <div class="profile-avatar">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="profile-details">
                            <h4><?php echo htmlspecialchars($user_name); ?></h4>
                            <p><i class="bi bi-tools"></i> <?php echo htmlspecialchars($profile['workType']); ?></p>
                            <p><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($profile['city']); ?></p>
                            <p><i class="bi bi-clock"></i> <?php echo htmlspecialchars($profile['experience']); ?> experience</p>
                            <p><i class="bi bi-currency-rupee"></i> ₹<?php echo number_format($profile['salary']); ?>/day</p>
                        </div>
                        <div class="profile-actions">
                            <a href="profile.php" class="btn-action btn-secondary">
                                <i class="bi bi-pencil"></i> Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="profile-overview">
                    <div class="empty-state">
                        <i class="bi bi-person-exclamation"></i>
                        <h5>Complete Your Profile</h5>
                        <p>Create your professional profile to get more job opportunities</p>
                        <a href="creatLpost.php" class="btn-action">
                            <i class="bi bi-person-badge"></i> Create Profile
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $total_applications; ?></h3>
                    <p>Total Applications</p>
                    <i class="bi bi-envelope"></i>
                </div>
                <div class="stat-card">
                    <h3><?php echo $accepted_applications; ?></h3>
                    <p>Accepted Applications</p>
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-card">
                    <h3><?php echo $total_hires; ?></h3>
                    <p>Total Hires</p>
                    <i class="bi bi-person-check"></i>
                </div>
                <div class="stat-card">
                    <h3><?php echo $total_work_posts; ?></h3>
                    <p>Work Posts</p>
                    <i class="bi bi-images"></i>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="postL.php" class="btn-action">
                    <i class="bi bi-search"></i>
                    Find Jobs
                </a>
                <a href="post_work.php" class="btn-action btn-secondary">
                    <i class="bi bi-plus-circle"></i>
                    Add Work Post
                </a>
                <a href="creatLpost.php" class="btn-action btn-secondary">
                    <i class="bi bi-person-badge"></i>
                    Update Profile
                </a>
            </div>

            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Recent Applications -->
                <div class="content-card">
                    <h5><i class="bi bi-envelope"></i> Recent Applications</h5>
                    <?php if (empty($applications)): ?>
                        <div class="empty-state">
                            <i class="bi bi-envelope"></i>
                            <p>No applications yet</p>
                            <a href="postL.php" class="btn-action">Find Jobs to Apply</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <div class="application-item">
                                <div class="application-title"><?php echo htmlspecialchars($app['jobTitle']); ?></div>
                                <div class="application-meta">
                                    <span><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($app['city']); ?></span>
                                    <span class="ms-3"><i class="bi bi-currency-rupee"></i> ₹<?php echo number_format($app['salary']); ?></span>
                                    <span class="badge status-<?php echo $app['status']; ?> ms-2">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-3">
                            <a href="appliedJob.php" class="btn-action btn-secondary">View All Applications</a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Work Portfolio -->
                <div class="content-card">
                    <h5><i class="bi bi-images"></i> Work Portfolio</h5>
                    <?php if (empty($work_posts)): ?>
                        <div class="empty-state">
                            <i class="bi bi-images"></i>
                            <p>No work posts yet</p>
                            <a href="post_work.php" class="btn-action">Add Work Sample</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($work_posts as $work): ?>
                            <div class="work-post-item">
                                <div class="work-title">Work Sample</div>
                                <div class="work-meta">
                                    <span><i class="bi bi-calendar"></i> <?php echo date('M d, Y', strtotime($work['created_at'])); ?></span>
                                    <span class="ms-3"><i class="bi bi-eye"></i> View Details</span>
                                </div>
                                <p class="mt-2 mb-0"><?php echo htmlspecialchars(substr($work['description'], 0, 100)) . '...'; ?></p>
                            </div>
                        <?php endforeach; ?>
                        <div class="text-center mt-3">
                            <a href="work_posts.php" class="btn-action btn-secondary">View All Work</a>
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
    </script>
</body>
</html>
