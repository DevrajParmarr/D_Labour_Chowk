<?php
require_once '../Shared/config.php';

// Check if user is logged in and is a client
if (!isLoggedIn()) {
    redirect('../Shared/login_form.php');
}

if (getCurrentUserType() !== 'User') {
    redirect('../Shared/login_form.php?error=unauthorized');
}

$db = Database::getInstance();
$conn = $db->getConnection();

$search = '';
$city = '';
$jobTitle = '';
$limit = 9; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // $search = mysqli_real_escape_string($conn, $_POST['search']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $jobTitle = mysqli_real_escape_string($conn, $_POST['jobTitle']);
}

$query = "
SELECT lp.*, u.user_name, u.mobile_no, u.email_id, u.date_created
FROM lab_post lp
JOIN user u ON lp.user_ID = u.user_ID
WHERE 1=1";

if ($jobTitle) {
    $query .= " AND lp.workType = '$jobTitle'";
}
if ($city) {
    $query .= " AND lp.city = '$city'";
}
$query .= " LIMIT $limit OFFSET $offset";

$sql_result = mysqli_query($conn, $query);

$total_query = "
SELECT COUNT(*) AS total 
FROM lab_post lp
JOIN user u ON lp.user_ID = u.user_ID
WHERE 1=1";
if ($jobTitle) {
    $total_query .= " AND lp.workType = '$jobTitle'";
}
if ($city) {
    $total_query .= " AND lp.city = '$city'";
}
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_jobs = $total_row['total'];
$total_pages = ceil($total_jobs / $limit);

include "menu.html";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Workers - D Labour Chowk</title>
    <meta name="description" content="Browse available skilled workers and their profiles. Find the perfect match for your project.">

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
            margin-bottom: 2rem;
        }
        
        .header-content {
            text-align: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
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
            margin-bottom: 2rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .stats-row {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            margin-top: 2rem;
        }
        
        .stat-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            min-width: 140px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: #fbbf24;
            display: block;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 0.5rem;
        }
        
        /* Main Container */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        /* Filter Section */
        .filter-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid #f1f5f9;
        }
        
        .filter-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #1f2937;
            text-align: center;
        }
        
        .filter-form {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .filter-select {
            padding: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            background: white;
            transition: all 0.3s ease;
            color: #374151;
        }
        
        .filter-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .filter-btn {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4);
        }
        
        /* Results Section */
        .results-section {
            margin-bottom: 3rem;
        }
        
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .results-count {
            font-size: 1.1rem;
            font-weight: 600;
            color: #374151;
        }
        
        .view-toggle {
            display: flex;
            gap: 0.5rem;
            background: #f1f5f9;
            padding: 0.5rem;
            border-radius: 12px;
        }
        
        .toggle-btn {
            padding: 0.5rem 1rem;
            border: none;
            background: transparent;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #6b7280;
        }
        
        .toggle-btn.active {
            background: white;
            color: var(--primary-color);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Worker Grid */
        .worker-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 2rem;
        }
        
        .worker-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            border: 1px solid #f1f5f9;
        }
        
        .worker-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border-color: var(--primary-color);
        }
        
        .worker-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-accent);
        }
        
        .worker-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            position: relative;
        }
        
        .worker-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--gradient-primary);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .worker-content {
            padding: 1.5rem;
        }
        
        .worker-header {
            margin-bottom: 1rem;
        }
        
        .worker-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }
        
        .worker-type {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1rem;
        }
        
        .worker-details {
            margin: 1.5rem 0;
        }
        
        .detail-row {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
        }
        
        .detail-icon {
            width: 20px;
            margin-right: 0.75rem;
            color: var(--primary-color);
            text-align: center;
        }
        
        .detail-label {
            font-weight: 600;
            color: #374151;
            margin-right: 0.5rem;
            min-width: 80px;
        }
        
        .detail-value {
            color: #6b7280;
            flex: 1;
        }
        
        .salary-value {
            color: var(--success-color);
            font-weight: 700;
            font-size: 1rem;
        }
        
        .worker-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }
        
        .btn-view {
            flex: 1;
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
        }
        
        .btn-contact {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary-color);
            border: 1px solid rgba(37, 99, 235, 0.2);
            padding: 1rem;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-contact:hover {
            background: rgba(37, 99, 235, 0.2);
            transform: translateY(-2px);
        }
        
        /* No Results */
        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            margin-bottom: 2rem;
        }
        
        .no-results-icon {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }
        
        .no-results-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .no-results-text {
            color: #6b7280;
            margin-bottom: 2rem;
        }
        
        /* Pagination */
        .pagination-section {
            display: flex;
            justify-content: center;
            margin: 3rem 0;
        }
        
        .pagination {
            display: flex;
            gap: 0.5rem;
            background: white;
            padding: 1rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .pagination a {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            color: #6b7280;
            font-weight: 500;
            transition: all 0.3s ease;
            min-width: 44px;
            text-align: center;
        }
        
        .pagination a:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-1px);
        }
        
        .pagination .active {
            background: var(--primary-color);
            color: white;
        }
        
        /* Profile Modal */
        .profile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            backdrop-filter: blur(5px);
        }
        
        .profile-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 2rem;
            border-radius: 20px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
        }
        
        .close-profile {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #f1f5f9;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            color: #6b7280;
        }
        
        .close-profile:hover {
            background: #e5e7eb;
            transform: rotate(90deg);
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .worker-grid {
                grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
                gap: 1.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .filter-form {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .worker-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .results-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .stats-row {
                gap: 1rem;
            }
            
            .stat-item {
                min-width: 110px;
                padding: 1rem;
            }
            
            .worker-actions {
                flex-direction: column;
            }
            
            .profile-content {
                margin: 1rem;
                width: calc(100% - 2rem);
                max-height: calc(100vh - 2rem);
            }
        }
        
        /* Loading Animation */
        .loading-card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            height: 400px;
            position: relative;
            overflow: hidden;
        }
        
        .loading-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.6),
                transparent
            );
            animation: loading 1.5s infinite;
        }
        
        @keyframes loading {
            0% { left: -100%; }
            100% { left: 100%; }
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .fade-in-up {
            animation: fadeInUp 0.6s ease forwards;
        }

        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }

        .pagination a {
            padding: 10px 15px;
            margin: 0 5px;
            background-color: #007bff;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .pagination a:hover {
            background-color: #0056b3;
        }

        .pagination .active {
            background-color: #0056b3;
        }

        .profile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
        }

        .profile-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
        }

        .close-profile {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
            }

            .filter-form select, .filter-form button {
                width: 100%;
                margin-bottom: 10px;
            }

            .job-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header-section">
        <div class="header-content">
            <h1 class="main-title">
                <i class="fas fa-users-cog me-3"></i>Available Workers
            </h1>
            <p class="main-subtitle">
                Discover skilled professionals ready to bring your projects to life. Browse profiles, compare skills, and hire the best talent for your needs.
            </p>
            
            <div class="stats-row">
                <div class="stat-item">
                    <span class="stat-number"><?php echo $total_jobs; ?>+</span>
                    <div class="stat-label">Available Workers</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number">50+</span>
                    <div class="stat-label">Skills Available</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number">25+</span>
                    <div class="stat-label">Cities Covered</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Container -->
    <div class="main-container">
        <!-- Filter Section -->
        <div class="filter-section">
            <h2 class="filter-title">
                <i class="fas fa-filter me-2"></i>Find Your Perfect Worker
            </h2>
            <form class="filter-form" method="POST" action="">
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-tools me-2"></i>Work Type
                    </label>
                    <select name="jobTitle" class="filter-select">
                        <option value="">All Work Types</option>
                        <option value="Plumber" <?php if ($jobTitle == 'Plumber') echo 'selected'; ?>>Plumber</option>
                        <option value="Electrician" <?php if ($jobTitle == 'Electrician') echo 'selected'; ?>>Electrician</option>
                        <option value="Mason" <?php if ($jobTitle == 'Mason') echo 'selected'; ?>>Mason</option>
                        <option value="Carpenter" <?php if ($jobTitle == 'Carpenter') echo 'selected'; ?>>Carpenter</option>
                        <option value="Painter" <?php if ($jobTitle == 'Painter') echo 'selected'; ?>>Painter</option>
                        <option value="Gardener" <?php if ($jobTitle == 'Gardener') echo 'selected'; ?>>Gardener</option>
                        <option value="Laborer" <?php if ($jobTitle == 'Laborer') echo 'selected'; ?>>Laborer</option>
                        <option value="Welder" <?php if ($jobTitle == 'Welder') echo 'selected'; ?>>Welder</option>
                        <option value="Other" <?php if ($jobTitle == 'Other') echo 'selected'; ?>>Other</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-map-marker-alt me-2"></i>City
                    </label>
                    <select name="city" class="filter-select">
                        <option value="">All Cities</option>
                        <option value="Indore" <?php if ($city == 'Indore') echo 'selected'; ?>>Indore</option>
                        <option value="Bhopal" <?php if ($city == 'Bhopal') echo 'selected'; ?>>Bhopal</option>
                        <option value="Ujjain" <?php if ($city == 'Ujjain') echo 'selected'; ?>>Ujjain</option>
                        <option value="Jabalpur" <?php if ($city == 'Jabalpur') echo 'selected'; ?>>Jabalpur</option>
                        <option value="Kota" <?php if ($city == 'Kota') echo 'selected'; ?>>Kota</option>
                        <option value="Jaipur" <?php if ($city == 'Jaipur') echo 'selected'; ?>>Jaipur</option>
                        <option value="Delhi" <?php if ($city == 'Delhi') echo 'selected'; ?>>Delhi</option>
                    </select>
                </div>

                <button type="submit" class="filter-btn">
                    <i class="fas fa-search me-2"></i>Search
                </button>
            </form>

            <div style="text-align: center; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb;">
                <a href="advanced_search.php" class="btn-nav primary" style="display: inline-block; margin: 0;">
                    <i class="fas fa-search-plus me-2"></i>Advanced Search
                </a>
                <p style="color: #6b7280; font-size: 0.9rem; margin-top: 0.5rem;">
                    More filters, sorting options, and better search results
                </p>
            </div>
        </div>
        
        <!-- Results Section -->
        <div class="results-section">
            <div class="results-header">
                <div class="results-count">
                    <i class="fas fa-users me-2"></i>
                    <?php echo mysqli_num_rows($sql_result); ?> worker<?php echo mysqli_num_rows($sql_result) != 1 ? 's' : ''; ?> found
                    <?php if ($jobTitle || $city): ?>
                        <?php if ($jobTitle): ?>
                            for <strong><?php echo htmlspecialchars($jobTitle); ?></strong>
                        <?php endif; ?>
                        <?php if ($city): ?>
                            in <strong><?php echo htmlspecialchars($city); ?></strong>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <div class="view-toggle">
                    <button class="toggle-btn active">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button class="toggle-btn">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
            
            <?php if (mysqli_num_rows($sql_result) > 0): ?>
                <div class="worker-grid">
                    <?php while ($dbrow = mysqli_fetch_assoc($sql_result)): ?>
                        <div class="worker-card fade-in-up">
                            <div class="worker-image-container">
                                <img class="worker-image" src="<?php echo htmlspecialchars($dbrow['impath']); ?>" alt="<?php echo htmlspecialchars($dbrow['workType']); ?>" onerror="this.src='https://via.placeholder.com/400x220/4f46e5/ffffff?text=<?php echo urlencode($dbrow['workType']); ?>'">
                                <div class="worker-badge">Available</div>
                            </div>
                            
                            <div class="worker-content">
                                <div class="worker-header">
                                    <h3 class="worker-name"><?php echo htmlspecialchars($dbrow['user_name']); ?></h3>
                                    <div class="worker-type"><?php echo htmlspecialchars($dbrow['workType']); ?></div>
                                </div>
                                
                                <div class="worker-details">
                                    <div class="detail-row">
                                        <i class="fas fa-map-marker-alt detail-icon"></i>
                                        <span class="detail-label">Location:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($dbrow['city']); ?></span>
                                    </div>
                                    
                                    <div class="detail-row">
                                        <i class="fas fa-rupee-sign detail-icon"></i>
                                        <span class="detail-label">Salary:</span>
                                        <span class="detail-value salary-value">₹<?php echo number_format($dbrow['salary']); ?>/month</span>
                                    </div>
                                    
                                    <div class="detail-row">
                                        <i class="fas fa-star detail-icon"></i>
                                        <span class="detail-label">Experience:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($dbrow['experience']); ?> years</span>
                                    </div>
                                    
                                    <div class="detail-row">
                                        <i class="fas fa-calendar detail-icon"></i>
                                        <span class="detail-label">Joined:</span>
                                        <span class="detail-value"><?php echo date('M Y', strtotime($dbrow['date_created'])); ?></span>
                                    </div>
                                </div>
                                
                                <div class="worker-actions">
                                    <button onclick="showProfile(<?php echo $dbrow['user_ID']; ?>)" class="btn-view">
                                        <i class="fas fa-user me-2"></i>View Profile
                                    </button>
                                    <button onclick="contactWorker(<?php echo $dbrow['user_ID']; ?>)" class="btn-contact">
                                        <i class="fas fa-phone"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <div class="no-results-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="no-results-title">No Workers Found</h3>
                    <p class="no-results-text">
                        We couldn't find any workers matching your search criteria.<br>
                        Try adjusting your filters or search in a different location.
                    </p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination-section">
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&city=<?= urlencode($city) ?>&jobTitle=<?= urlencode($jobTitle) ?>">
                            <i class="fas fa-chevron-left me-2"></i>Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php 
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    if ($start > 1): ?>
                        <a href="?page=1&city=<?= urlencode($city) ?>&jobTitle=<?= urlencode($jobTitle) ?>">1</a>
                        <?php if ($start > 2): ?>
                            <span>...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <a href="?page=<?= $i ?>&city=<?= urlencode($city) ?>&jobTitle=<?= urlencode($jobTitle) ?>" 
                           class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    
                    <?php if ($end < $total_pages): ?>
                        <?php if ($end < $total_pages - 1): ?>
                            <span>...</span>
                        <?php endif; ?>
                        <a href="?page=<?= $total_pages ?>&city=<?= urlencode($city) ?>&jobTitle=<?= urlencode($jobTitle) ?>"><?= $total_pages ?></a>
                    <?php endif; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>&city=<?= urlencode($city) ?>&jobTitle=<?= urlencode($jobTitle) ?>">
                            Next<i class="fas fa-chevron-right ms-2"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>


    <div id="profileOverlay" class="profile-overlay">
        <div class="profile-content">
            <span class="close-profile" onclick="closeProfile()">&times;</span>
            <div id="profileData"></div>
        </div>
    </div>

   
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    // Initialize AOS (Animate On Scroll)
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true
    });

    // Profile modal functions
    function showProfile(userId) {
        const loadingHtml = `
            <div style="text-align: center; padding: 2rem;">
                <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--primary-color);"></i>
                <p style="margin-top: 1rem; color: #6b7280;">Loading profile...</p>
            </div>
        `;
        
        $('#profileData').html(loadingHtml);
        $('#profileOverlay').show();
        
        $.ajax({
            url: 'get_profile.php',
            type: 'GET',
            data: { user_id: userId },
            success: function(response) {
                $('#profileData').html(response);
            },
            error: function() {
                $('#profileData').html(`
                    <div style="text-align: center; padding: 2rem;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: var(--danger-color);"></i>
                        <p style="margin-top: 1rem; color: #6b7280;">Error loading profile. Please try again.</p>
                        <button onclick="closeProfile()" style="margin-top: 1rem; padding: 0.5rem 1rem; background: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer;">Close</button>
                    </div>
                `);
            }
        });
    }

    function closeProfile() {
        $('#profileOverlay').hide();
    }

    function contactWorker(userId) {
        // This would typically open a contact modal or redirect to messaging
        const contactHtml = `
            <div style="text-align: center; padding: 2rem;">
                <i class="fas fa-phone" style="font-size: 2rem; color: var(--success-color); margin-bottom: 1rem;"></i>
                <h3 style="margin-bottom: 1rem; color: #1f2937;">Contact Worker</h3>
                <p style="color: #6b7280; margin-bottom: 2rem;">Contact feature will be implemented soon.<br>Worker ID: ${userId}</p>
                <button onclick="closeProfile()" style="padding: 0.75rem 1.5rem; background: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Close</button>
            </div>
        `;
        
        $('#profileData').html(contactHtml);
        $('#profileOverlay').show();
    }

    // View toggle functionality
    document.querySelectorAll('.toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Remove active class from all buttons
            document.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');
            
            const grid = document.querySelector('.worker-grid');
            if (this.querySelector('.fa-list')) {
                // List view
                grid.style.gridTemplateColumns = '1fr';
                grid.querySelectorAll('.worker-card').forEach(card => {
                    card.style.display = 'flex';
                    card.style.flexDirection = 'row';
                    card.querySelector('.worker-image').style.width = '200px';
                    card.querySelector('.worker-image').style.height = '150px';
                });
            } else {
                // Grid view
                grid.style.gridTemplateColumns = 'repeat(auto-fill, minmax(380px, 1fr))';
                grid.querySelectorAll('.worker-card').forEach(card => {
                    card.style.display = 'block';
                    card.style.flexDirection = 'column';
                    card.querySelector('.worker-image').style.width = '100%';
                    card.querySelector('.worker-image').style.height = '220px';
                });
            }
        });
    });

    // Add loading animation to buttons
    document.querySelectorAll('.btn-view, .filter-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!this.onclick) {  // Only for non-onclick buttons (like filter)
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
                this.disabled = true;
                
                // Re-enable after form submission
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 2000);
            }
        });
    });

    // Add hover effects to cards
    document.querySelectorAll('.worker-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-12px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0) scale(1)';
        });
    });

    // Smooth scroll to results after filter
    if (window.location.search.includes('page=') || document.querySelector('form[method="POST"]')) {
        setTimeout(() => {
            document.querySelector('.results-section')?.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }, 100);
    }

    // Add staggered animation to cards
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.worker-card').forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
            card.classList.add('fade-in-up');
        });
    });

    // Close modal when clicking outside
    document.getElementById('profileOverlay').addEventListener('click', function(e) {
        if (e.target === this) {
            closeProfile();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeProfile();
        }
    });
</script>

</body>
</html>