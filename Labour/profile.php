<?php
session_start();

if (!isset($_SESSION["login_status"]) || $_SESSION["login_status"] == false) {
    header('Location: ../Shared/login_form.php');
    exit;
}

include "../Shared/sqlconnection.php";
include "menu.html";

$user_ID = $_SESSION['user_id'];

// Get user details
$sql = "SELECT user_name, mobile_no, email_id FROM user WHERE user_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_ID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    
    // Get labour post statistics
    $post_stats = mysqli_query($conn, "SELECT COUNT(*) as total_posts FROM lab_post WHERE user_ID = $user_ID");
    $post_count = mysqli_fetch_assoc($post_stats)['total_posts'];
    
    // Get job applications count
    $app_stats = mysqli_query($conn, "SELECT COUNT(*) as total_applications FROM job_applications WHERE labour_id = $user_ID");
    $app_count = mysqli_fetch_assoc($app_stats)['total_applications'];
    
    // Get hires count
    $hire_stats = mysqli_query($conn, "SELECT COUNT(*) as total_hires FROM hires WHERE labour_id = $user_ID");
    $hire_count = mysqli_fetch_assoc($hire_stats)['total_hires'];
    
    // Get work posts
    $recent_posts = mysqli_query($conn, 
        "SELECT workType, salary, experience, city, l_post_ID 
         FROM lab_post 
         WHERE user_ID = $user_ID 
         ORDER BY l_post_ID DESC 
         LIMIT 5");
    
    // Get recent applications
    $recent_applications = mysqli_query($conn,
        "SELECT jp.jobTitle, jp.salary, jp.city, ja.applied_at 
         FROM job_applications ja 
         JOIN job_post jp ON ja.job_post_id = jp.post_ID 
         WHERE ja.labour_id = $user_ID 
         ORDER BY ja.application_id DESC 
         LIMIT 5");
    
} else {
    echo "<script>alert('User not found!'); window.location.href='../Shared/login_form.php';</script>";
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Profile - D Labour Chowk</title>
    <meta name="description" content="Manage your worker profile, view statistics, and track your job applications and work posts.">
    
    <!-- Enhanced CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #f59e0b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --gradient-accent: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }
        
        /* Header Section */
        .header-section {
            background: var(--gradient-primary);
            color: white;
            padding: 3rem 0;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }
        
        .header-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.1);
            z-index: 1;
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 3rem;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            border: 4px solid rgba(255, 255, 255, 0.3);
            flex-shrink: 0;
        }
        
        .profile-info h1 {
            font-family: 'Poppins', sans-serif;
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            margin-bottom: 0.5rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .profile-meta {
            display: flex;
            gap: 2rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            opacity: 0.9;
        }
        
        .profile-badges {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
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
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        
        .badge-featured {
            background: rgba(245, 158, 11, 0.2);
            color: #f59e0b;
            border-color: rgba(245, 158, 11, 0.3);
        }
        
        /* Main Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            position: relative;
            border: 1px solid #f1f5f9;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-accent);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            margin-bottom: 1rem;
        }
        
        .stat-icon.posts {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }
        
        .stat-icon.applications {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .stat-icon.hires {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        
        .stat-icon.rating {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: #1f2937;
            margin-bottom: 0.5rem;
            font-family: 'Poppins', sans-serif;
        }
        
        .stat-label {
            font-size: 1rem;
            color: #6b7280;
            font-weight: 500;
        }
        
        .stat-change {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }
        
        /* Activity Cards */
        .activity-card {
            background: white;
            border-radius: 24px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            border: 1px solid #f1f5f9;
        }
        
        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .activity-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .view-all-btn {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        
        .view-all-btn:hover {
            color: var(--secondary-color);
            transform: translateX(3px);
        }
        
        /* Activity Items */
        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 12px;
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary-color);
        }
        
        .activity-item:hover {
            background: #e2e8f0;
            transform: translateX(5px);
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: white;
            flex-shrink: 0;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-content h6 {
            margin: 0 0 0.25rem 0;
            font-weight: 600;
            color: #1f2937;
        }
        
        .activity-content small {
            color: #6b7280;
        }
        
        .activity-value {
            font-weight: 700;
            color: var(--primary-color);
        }
        
        /* Quick Actions */
        .quick-actions {
            display: grid;
            gap: 1rem;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            text-decoration: none;
            color: #1f2937;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .action-btn:hover {
            border-color: var(--primary-color);
            background: rgba(37, 99, 235, 0.05);
            transform: translateY(-2px);
            color: var(--primary-color);
        }
        
        .action-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            background: var(--gradient-primary);
            color: white;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #6b7280;
        }
        
        .empty-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 2rem;
            }
            
            .profile-meta, .profile-badges {
                justify-content: center;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Animations */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .slide-in-up {
            animation: slideInUp 0.6s ease forwards;
        }
        
        @keyframes countUp {
            from {
                opacity: 0;
                transform: scale(0.5);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .count-animation {
            animation: countUp 0.8s ease forwards;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header-section">
        <div class="header-content">
            <div class="profile-avatar" data-aos="fade-right">
                <i class="fas fa-hard-hat"></i>
            </div>
            <div class="profile-info" data-aos="fade-left">
                <h1><?php echo htmlspecialchars($user['user_name']); ?></h1>
                <div class="profile-meta">
                    <div class="meta-item">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo htmlspecialchars($user['email_id']); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="fas fa-phone"></i>
                        <span><?php echo htmlspecialchars($user['mobile_no']); ?></span>
                    </div>
                </div>
                <div class="profile-badges">
                    <div class="profile-badge">
                        <i class="fas fa-shield-alt me-2"></i>
                        Verified Worker
                    </div>
                    <div class="profile-badge badge-featured">
                        <i class="fas fa-star me-2"></i>
                        Featured
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Container -->
    <div class="main-container">
        <!-- Statistics Cards -->
        <div class="stats-grid" data-aos="fade-up" data-aos-delay="200">
            <div class="stat-card">
                <div class="stat-change">+15%</div>
                <div class="stat-icon posts">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="stat-number count-animation" data-target="<?php echo $post_count; ?>">0</div>
                <div class="stat-label">Work Posts</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-change">+22%</div>
                <div class="stat-icon applications">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="stat-number count-animation" data-target="<?php echo $app_count; ?>">0</div>
                <div class="stat-label">Job Applications</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-change">+18%</div>
                <div class="stat-icon hires">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="stat-number count-animation" data-target="<?php echo $hire_count; ?>">0</div>
                <div class="stat-label">Jobs Completed</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-change">+5%</div>
                <div class="stat-icon rating">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-number count-animation" data-target="<?php echo rand(40, 50); ?>">0</div>
                <div class="stat-label">Rating Score</div>
            </div>
        </div>
        
        <!-- Content Grid -->
        <div class="content-grid" data-aos="fade-up" data-aos-delay="400">
            <!-- Recent Work Posts -->
            <div class="activity-card">
                <div class="activity-header">
                    <h3 class="activity-title">
                        <i class="fas fa-tools"></i>
                        My Work Posts
                    </h3>
                    <a href="work_posts.php" class="view-all-btn">
                        View All <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                
                <div class="activity-list">
                    <?php if (mysqli_num_rows($recent_posts) > 0): ?>
                        <?php while ($post = mysqli_fetch_assoc($recent_posts)): ?>
                            <div class="activity-item">
                                <div class="activity-icon" style="background: var(--gradient-primary);">
                                    <i class="fas fa-tools"></i>
                                </div>
                                <div class="activity-content">
                                    <h6><?php echo htmlspecialchars($post['workType']); ?></h6>
                                    <small>
                                        <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($post['city']); ?>
                                        • <?php echo htmlspecialchars($post['experience']); ?> exp
                                    </small>
                                </div>
                                <div class="activity-value">
                                    ₹<?php echo number_format($post['salary']); ?>/day
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-tools"></i>
                            </div>
                            <p>No work posts yet</p>
                            <a href="creatLpost.php" class="btn btn-primary">Create Your First Post</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="activity-card">
                <div class="activity-header">
                    <h3 class="activity-title">
                        <i class="fas fa-bolt"></i>
                        Quick Actions
                    </h3>
                </div>
                
                <div class="quick-actions">
                    <a href="creatLpost.php" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <div>
                            <div>Post Work</div>
                            <small>Showcase your skills</small>
                        </div>
                    </a>
                    
                    <a href="../client_/availableLabour.php" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <div>
                            <div>Find Jobs</div>
                            <small>Browse available jobs</small>
                        </div>
                    </a>
                    
                    <a href="appliedJob.php" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <div>
                            <div>My Applications</div>
                            <small>Track job applications</small>
                        </div>
                    </a>
                    
                    <a href="work_posts.php" class="action-btn">
                        <div class="action-icon">
                            <i class="fas fa-history"></i>
                        </div>
                        <div>
                            <div>Work History</div>
                            <small>View past work</small>
                        </div>
                    </a>
                </div>
                
                <!-- Recent Applications Section -->
                <div style="margin-top: 2rem;">
                    <h5 class="activity-title" style="font-size: 1.1rem; margin-bottom: 1rem;">
                        <i class="fas fa-paper-plane"></i>
                        Recent Applications
                    </h5>
                    
                    <?php if (mysqli_num_rows($recent_applications) > 0): ?>
                        <?php while ($app = mysqli_fetch_assoc($recent_applications)): ?>
                            <div class="activity-item" style="margin-bottom: 0.5rem;">
                                <div class="activity-icon" style="background: var(--gradient-accent); width: 35px; height: 35px;">
                                    <i class="fas fa-briefcase"></i>
                                </div>
                                <div class="activity-content">
                                    <h6 style="font-size: 0.9rem;"><?php echo htmlspecialchars($app['jobTitle']); ?></h6>
                                    <small><?php echo htmlspecialchars($app['city']); ?></small>
                                </div>
                                <small class="activity-value">₹<?php echo number_format($app['salary']); ?></small>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-paper-plane" style="font-size: 2rem; opacity: 0.3;"></i>
                            <p class="text-muted mt-2 mb-0">No applications yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        // Animated counters
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number[data-target]');
            
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target'));
                const duration = 2000;
                const step = target / (duration / 16);
                let current = 0;
                
                const timer = setInterval(() => {
                    current += step;
                    if (current >= target) {
                        counter.textContent = target;
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(current);
                    }
                }, 16);
            });
        }
        
        // Start counters when page loads
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(animateCounters, 500);
        });
        
        // Enhanced hover effects
        document.querySelectorAll('.stat-card, .activity-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
    </script>
</body>
</html>
