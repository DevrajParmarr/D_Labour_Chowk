<?php
session_start();

if (!isset($_SESSION["login_status"]) || $_SESSION["login_status"] == false) {
    header('Location: ../Shared/login_form.php');
    exit;
}

include "../Shared/sqlconnection.php";
include "menu.html";

$laborer_id = $_SESSION['user_id'];

// Get applied jobs with detailed information
$query = "SELECT jp.*, ja.status, ja.applied_at, u.user_name as client_name, u.mobile_no as client_phone 
          FROM job_post jp
          JOIN job_applications ja ON jp.post_ID = ja.job_post_id 
          LEFT JOIN user u ON jp.owner = u.user_ID
          WHERE ja.labour_id = ?
          ORDER BY ja.applied_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $laborer_id);
$stmt->execute();
$result = $stmt->get_result();

// Count statistics
$pending = $accepted = $rejected = 0;
while ($row = $result->fetch_assoc()) {
    switch(strtolower($row['status'])) {
        case 'pending': $pending++; break;
        case 'accepted': $accepted++; break;
        case 'rejected': $rejected++; break;
    }
}
$result->data_seek(0); // Reset result pointer

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Job Applications - D Labour Chowk</title>
    <meta name="description" content="Track your job applications, view application status, and manage your job opportunities.">
    
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
        
        /* Filter Toolbar */
        .filter-toolbar {
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
        
        /* Applications Grid */
        .applications-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 2rem;
        }
        
        .application-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            position: relative;
            border: 1px solid #f1f5f9;
        }
        
        .application-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.15);
        }
        
        .application-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }
        
        .application-card.pending::before {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        
        .application-card.accepted::before {
            background: linear-gradient(135deg, #10b981, #059669);
        }
        
        .application-card.rejected::before {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        
        .card-header {
            padding: 2rem 2rem 1rem 2rem;
            position: relative;
        }
        
        .status-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-badge.pending {
            background: rgba(245, 158, 11, 0.1);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }
        
        .status-badge.accepted {
            background: rgba(16, 185, 129, 0.1);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        
        .status-badge.rejected {
            background: rgba(239, 68, 68, 0.1);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        .job-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }
        
        .job-client {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .card-body {
            padding: 0 2rem 2rem 2rem;
        }
        
        .job-details {
            display: grid;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: #f8fafc;
            border-radius: 8px;
        }
        
        .detail-label {
            color: #6b7280;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .detail-value {
            font-weight: 600;
            color: #1f2937;
        }
        
        .salary-value {
            color: var(--success-color);
            font-weight: 700;
        }
        
        .job-description {
            color: #6b7280;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .application-actions {
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
        
        .btn-contact {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn-contact:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .btn-withdraw {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        .btn-withdraw:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.4);
            color: white;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6b7280;
            grid-column: 1 / -1;
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
            .applications-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-toolbar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .application-actions {
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
                <i class="fas fa-clipboard-list me-3"></i>My Job Applications
            </h1>
            <p class="main-subtitle" data-aos="fade-up" data-aos-delay="400">
                Track your job applications, monitor status updates, and manage your 
                career opportunities all in one place.
            </p>
            
            <!-- Header Stats -->
            <div class="header-stats" data-aos="fade-up" data-aos-delay="600">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $pending; ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $accepted; ?></div>
                    <div class="stat-label">Accepted</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $rejected; ?></div>
                    <div class="stat-label">Rejected</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Container -->
    <div class="main-container">
        <!-- Filter Toolbar -->
        <div class="filter-toolbar" data-aos="fade-up" data-aos-delay="800">
            <div class="filter-section">
                <select class="filter-select" id="statusFilter">
                    <option value="all">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="accepted">Accepted</option>
                    <option value="rejected">Rejected</option>
                </select>
                
                <select class="filter-select" id="sortFilter">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="salary-high">Highest Salary</option>
                    <option value="salary-low">Lowest Salary</option>
                </select>
            </div>
            
            <div class="text-muted">
                <i class="fas fa-info-circle me-2"></i>
                Total Applications: <?php echo $pending + $accepted + $rejected; ?>
            </div>
        </div>
        
        <!-- Applications Grid -->
        <div class="applications-grid" id="applicationsGrid">
            <?php
            $appCount = 0;
            if ($result->num_rows > 0) {
                while ($job = $result->fetch_assoc()) {
                    $appCount++;
                    $statusClass = strtolower($job['status']);
                    $description = htmlspecialchars($job['detail']);
                    $shortDescription = strlen($description) > 150 ? substr($description, 0, 150) . '...' : $description;
                    
                    echo "
                    <div class='application-card {$statusClass}' data-aos='fade-up' data-aos-delay='" . ($appCount * 100) . "' data-status='{$statusClass}' data-salary='{$job['salary']}'>
                        <div class='card-header'>
                            <div class='status-badge {$statusClass}'>
                                <i class='fas fa-circle me-1'></i>{$job['status']}
                            </div>
                            
                            <h3 class='job-title'>" . htmlspecialchars($job['jobTitle']) . "</h3>
                            <div class='job-client'>
                                <i class='fas fa-user me-1'></i>
                                Posted by: " . htmlspecialchars($job['client_name'] ?: 'Unknown Client') . "
                            </div>
                        </div>
                        
                        <div class='card-body'>
                            <div class='job-details'>
                                <div class='detail-row'>
                                    <div class='detail-label'>
                                        <i class='fas fa-map-marker-alt'></i>
                                        Location
                                    </div>
                                    <div class='detail-value'>" . htmlspecialchars($job['location']) . "</div>
                                </div>
                                
                                <div class='detail-row'>
                                    <div class='detail-label'>
                                        <i class='fas fa-rupee-sign'></i>
                                        Salary
                                    </div>
                                    <div class='detail-value salary-value'>₹" . number_format($job['salary']) . "</div>
                                </div>
                                
                                <div class='detail-row'>
                                    <div class='detail-label'>
                                        <i class='fas fa-city'></i>
                                        City
                                    </div>
                                    <div class='detail-value'>" . htmlspecialchars($job['city']) . "</div>
                                </div>
                            </div>
                            
                            <div class='job-description'>
                                {$shortDescription}
                            </div>
                            
                            <div class='application-actions'>";
                    
                    if ($job['client_phone']) {
                        echo "<a href='tel:" . htmlspecialchars($job['client_phone']) . "' class='btn-action btn-contact'>
                                <i class='fas fa-phone'></i>
                                Contact Client
                            </a>";
                    } else {
                        echo "<button class='btn-action btn-contact' disabled>
                                <i class='fas fa-phone'></i>
                                Contact Unavailable
                            </button>";
                    }
                    
                    if ($statusClass == 'pending') {
                        echo "<button class='btn-action btn-withdraw' onclick='withdrawApplication({$job['post_ID']})'>
                                <i class='fas fa-times'></i>
                                Withdraw
                            </button>";
                    } else {
                        echo "<button class='btn-action btn-withdraw' disabled style='opacity: 0.5;'>
                                <i class='fas fa-clock'></i>
                                Processed
                            </button>";
                    }
                    
                    echo "          </div>
                        </div>
                    </div>";
                }
            } else {
                echo "
                <div class='empty-state' data-aos='fade-up'>
                    <div class='empty-icon'>
                        <i class='fas fa-clipboard-list'></i>
                    </div>
                    <h3 class='empty-title'>No Job Applications Yet</h3>
                    <p class='empty-description'>
                        You haven't applied for any jobs yet. Start browsing available 
                        opportunities and apply to jobs that match your skills.
                    </p>
                    <a href='../client_/availableLabour.php' class='btn-action btn-contact' style='display: inline-flex; width: auto;'>
                        <i class='fas fa-search me-2'></i>Browse Jobs
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
        
        // Filter functionality
        document.getElementById('statusFilter').addEventListener('change', function() {
            const selectedStatus = this.value;
            const cards = document.querySelectorAll('.application-card');
            
            cards.forEach(card => {
                const cardStatus = card.dataset.status;
                if (selectedStatus === 'all' || cardStatus === selectedStatus) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        // Sort functionality
        document.getElementById('sortFilter').addEventListener('change', function() {
            const sortType = this.value;
            const grid = document.getElementById('applicationsGrid');
            const cards = Array.from(grid.querySelectorAll('.application-card'));
            
            cards.sort((a, b) => {
                switch(sortType) {
                    case 'salary-high':
                        return parseInt(b.dataset.salary) - parseInt(a.dataset.salary);
                    case 'salary-low':
                        return parseInt(a.dataset.salary) - parseInt(b.dataset.salary);
                    default:
                        return 0;
                }
            });
            
            cards.forEach(card => grid.appendChild(card));
        });
        
        // Withdraw application function
        function withdrawApplication(jobId) {
            if (confirm('Are you sure you want to withdraw this application? This action cannot be undone.')) {
                // Here you would typically make an AJAX call to withdraw the application
                alert('Application withdrawn successfully!');
                location.reload();
            }
        }
        
        // Enhanced hover effects
        document.querySelectorAll('.application-card').forEach(card => {
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
