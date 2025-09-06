<?php
session_start();

if (!isset($_SESSION["login_status"]) || $_SESSION["login_status"] == false) {
    header('Location: ../Shared/login_form.php');
    exit;
}

include "../Shared/sqlconnection.php";
include "menu.html";

$sql_result = mysqli_query($conn, "SELECT * FROM job_post WHERE owner = {$_SESSION['user_id']}");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Job Posts - D Labour Chowk</title>
    <meta name="description" content="View and manage all your job posts. Track applications, edit details, and monitor responses.">
    
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
            text-align: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            position: relative;
            z-index: 2;
        }
        
        .welcome-text {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 1rem;
        }
        
        .main-title {
            font-family: 'Poppins', sans-serif;
            font-size: clamp(2rem, 4vw, 3.5rem);
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .main-subtitle {
            font-size: clamp(1rem, 2vw, 1.3rem);
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto 2rem;
        }
        
        .header-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            opacity: 0.9;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Main Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        /* Filters and Actions */
        .toolbar {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .filter-section {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .filter-select {
            padding: 0.75rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.9rem;
            min-width: 150px;
            transition: all 0.3s ease;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .btn-create {
            background: var(--gradient-accent);
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 172, 254, 0.4);
            color: white;
        }
        
        /* Job Cards Grid */
        .jobs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 2rem;
        }
        
        .job-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            position: relative;
            border: 1px solid #f1f5f9;
        }
        
        .job-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.15);
        }
        
        .job-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-accent);
        }
        
        .job-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        
        .job-card:hover .job-image {
            transform: scale(1.05);
        }
        
        .job-content {
            padding: 2rem;
        }
        
        .job-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1rem;
            line-height: 1.3;
        }
        
        .job-location {
            display: inline-flex;
            align-items: center;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .job-salary {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--danger-color);
            margin-bottom: 1rem;
        }
        
        .job-description {
            color: #6b7280;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 2rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .job-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #6b7280;
        }
        
        .meta-icon {
            color: var(--primary-color);
            font-size: 1rem;
        }
        
        .job-actions {
            display: flex;
            gap: 1rem;
        }
        
        .btn-action {
            flex: 1;
            padding: 0.75rem 1rem;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .btn-responses {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
        
        .btn-responses:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.4);
            color: white;
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.4);
            color: white;
        }
        
        /* Status Badge */
        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6b7280;
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 2rem;
            opacity: 0.5;
        }
        
        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #374151;
        }
        
        .empty-description {
            font-size: 1rem;
            margin-bottom: 2rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .jobs-grid {
                grid-template-columns: 1fr;
            }
            
            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-section {
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .job-actions {
                flex-direction: column;
            }
            
            .header-stats {
                grid-template-columns: 1fr;
                gap: 1rem;
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
        
        /* Loading State */
        .loading {
            display: inline-block;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header-section">
        <div class="header-content">
            <div class="welcome-text" data-aos="fade-down">
                Welcome back, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>!
            </div>
            <h1 class="main-title" data-aos="fade-up" data-aos-delay="200">
                <i class="fas fa-briefcase me-3"></i>My Job Posts
            </h1>
            <p class="main-subtitle" data-aos="fade-up" data-aos-delay="400">
                Manage your job posts, track applications, and connect with skilled workers.
                Monitor the progress of your projects from start to finish.
            </p>
            
            <!-- Header Stats -->
            <div class="header-stats" data-aos="fade-up" data-aos-delay="600">
                <div class="stat-card">
                    <div class="stat-number" id="totalJobs">0</div>
                    <div class="stat-label">Total Jobs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="activeJobs">0</div>
                    <div class="stat-label">Active Posts</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="totalApplications">0</div>
                    <div class="stat-label">Applications</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Container -->
    <div class="main-container">
        <!-- Toolbar -->
        <div class="toolbar" data-aos="fade-up" data-aos-delay="800">
            <div class="filter-section">
                <select class="filter-select" id="sortFilter">
                    <option value="newest">Sort by Newest</option>
                    <option value="oldest">Sort by Oldest</option>
                    <option value="salary-high">Highest Salary</option>
                    <option value="salary-low">Lowest Salary</option>
                </select>
                
                <select class="filter-select" id="locationFilter">
                    <option value="all">All Locations</option>
                    <option value="Indore">Indore</option>
                    <option value="Bhopal">Bhopal</option>
                    <option value="Mumbai">Mumbai</option>
                    <option value="Delhi">Delhi</option>
                </select>
            </div>
            
            <a href="creatjob.php" class="btn-create">
                <i class="fas fa-plus me-2"></i>Create New Job
            </a>
        </div>
        
        <!-- Jobs Grid -->
        <div class="jobs-grid" id="jobsGrid">
            <?php
            $jobCount = 0;
            while ($dbrow = mysqli_fetch_assoc($sql_result)) {
                $jobCount++;
                $imageUrl = !empty($dbrow['impath']) ? htmlspecialchars($dbrow['impath']) : 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=400';
                $description = htmlspecialchars($dbrow['detail']);
                $shortDescription = strlen($description) > 150 ? substr($description, 0, 150) . '...' : $description;
                
                echo "
                <div class='job-card' data-aos='fade-up' data-aos-delay='" . ($jobCount * 100) . "'>
                    <div class='status-badge'>
                        <i class='fas fa-circle me-1'></i>Active
                    </div>
                    
                    <img src='{$imageUrl}' alt='Job Image' class='job-image'>
                    
                    <div class='job-content'>
                        <h3 class='job-title'>" . htmlspecialchars($dbrow['jobTitle']) . "</h3>
                        
                        <div class='job-location'>
                            <i class='fas fa-map-marker-alt me-2'></i>
                            " . htmlspecialchars($dbrow['location']) . "
                        </div>
                        
                        <div class='job-salary'>
                            <i class='fas fa-rupee-sign me-1'></i>" . number_format($dbrow['salary']) . "
                        </div>
                        
                        <p class='job-description'>{$shortDescription}</p>
                        
                        <div class='job-meta'>
                            <div class='meta-item'>
                                <i class='fas fa-calendar-alt meta-icon'></i>
                                <span>Posted Recently</span>
                            </div>
                            <div class='meta-item'>
                                <i class='fas fa-eye meta-icon'></i>
                                <span>View Details</span>
                            </div>
                        </div>
                        
                        <div class='job-actions'>
                            <a href='interested_labour.php?post_ID=" . $dbrow['post_ID'] . "' class='btn-action btn-responses'>
                                <i class='fas fa-users'></i>
                                View Responses
                            </a>
                            <a href='dltpost.php?post_ID=" . $dbrow['post_ID'] . "' class='btn-action btn-delete' onclick='return confirmDelete()'>
                                <i class='fas fa-trash'></i>
                                Delete
                            </a>
                        </div>
                    </div>
                </div>";
            }
            
            if ($jobCount == 0) {
                echo "
                <div class='empty-state' data-aos='fade-up'>
                    <div class='empty-icon'>
                        <i class='fas fa-briefcase'></i>
                    </div>
                    <h3 class='empty-title'>No Job Posts Yet</h3>
                    <p class='empty-description'>
                        You haven't created any job posts yet. Start by posting your first job 
                        to connect with skilled workers in your area.
                    </p>
                    <a href='creatjob.php' class='btn-create' style='display: inline-flex; align-items: center;'>
                        <i class='fas fa-plus me-2'></i>Create Your First Job Post
                    </a>
                </div>";
            }
            ?>
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
        
        // Update stats
        document.addEventListener('DOMContentLoaded', function() {
            const jobCards = document.querySelectorAll('.job-card');
            const totalJobs = jobCards.length;
            
            document.getElementById('totalJobs').textContent = totalJobs;
            document.getElementById('activeJobs').textContent = totalJobs;
            document.getElementById('totalApplications').textContent = Math.floor(totalJobs * 2.5); // Mock data
            
            // Animate counters
            animateCounters();
        });
        
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent);
                let count = 0;
                const increment = target / 30;
                
                const timer = setInterval(() => {
                    count += increment;
                    if (count >= target) {
                        counter.textContent = target;
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(count);
                    }
                }, 50);
            });
        }
        
        // Confirm delete
        function confirmDelete() {
            return confirm('Are you sure you want to delete this job post? This action cannot be undone.');
        }
        
        // Filter functionality
        document.getElementById('sortFilter').addEventListener('change', function() {
            const sortType = this.value;
            const jobsGrid = document.getElementById('jobsGrid');
            const jobCards = Array.from(jobsGrid.querySelectorAll('.job-card'));
            
            jobCards.sort((a, b) => {
                switch(sortType) {
                    case 'salary-high':
                        const salaryA = parseInt(a.querySelector('.job-salary').textContent.replace(/[^0-9]/g, ''));
                        const salaryB = parseInt(b.querySelector('.job-salary').textContent.replace(/[^0-9]/g, ''));
                        return salaryB - salaryA;
                    case 'salary-low':
                        const salaryA2 = parseInt(a.querySelector('.job-salary').textContent.replace(/[^0-9]/g, ''));
                        const salaryB2 = parseInt(b.querySelector('.job-salary').textContent.replace(/[^0-9]/g, ''));
                        return salaryA2 - salaryB2;
                    default:
                        return 0;
                }
            });
            
            jobCards.forEach(card => jobsGrid.appendChild(card));
        });
        
        // Location filter
        document.getElementById('locationFilter').addEventListener('change', function() {
            const selectedLocation = this.value;
            const jobCards = document.querySelectorAll('.job-card');
            
            jobCards.forEach(card => {
                const location = card.querySelector('.job-location').textContent.trim();
                if (selectedLocation === 'all' || location.includes(selectedLocation)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        // Add hover effects
        document.querySelectorAll('.job-card').forEach(card => {
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
