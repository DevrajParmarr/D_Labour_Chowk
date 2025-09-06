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

include "menu.html";

if (isset($_GET['post_ID'])) {
    $post_ID = $_GET['post_ID'];
} else {
    die("Post ID is not set.");
}

$query = "
    SELECT u.user_name, u.user_ID, l.workType, l.experience, l.city, l.salary, u.mobile_no, u.email_id
    FROM job_applications ja
    JOIN lab_post l ON l.user_ID = ja.labour_id
    JOIN user u ON u.user_ID = l.user_ID
    WHERE ja.job_post_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $post_ID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Applicants - D Labour Chowk</title>
    <meta name="description" content="Review and manage job applicants. View profiles, hire workers, and make decisions on applications.">
    
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
            margin: 0 auto;
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
        
        .stats-info {
            display: flex;
            gap: 2rem;
            align-items: center;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary-color);
        }
        
        .stat-label {
            font-size: 0.85rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Applicants Grid */
        .applicants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 2rem;
        }
        
        .applicant-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            position: relative;
            border: 1px solid #f1f5f9;
        }
        
        .applicant-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.15);
        }
        
        .applicant-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-accent);
        }
        
        .applicant-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        .applicant-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }
        
        .applicant-name {
            font-family: 'Poppins', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .applicant-work-type {
            opacity: 0.9;
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        
        .applicant-badge {
            display: inline-flex;
            align-items: center;
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .applicant-body {
            padding: 2rem;
        }
        
        .applicant-details {
            display: grid;
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 12px;
            border-left: 4px solid var(--primary-color);
        }
        
        .detail-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }
        
        .detail-content {
            flex: 1;
        }
        
        .detail-label {
            font-size: 0.8rem;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .detail-value {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
        }
        
        .applicant-actions {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 0.75rem;
            margin-top: 2rem;
        }
        
        .btn-action {
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
        
        .btn-profile {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            color: white;
        }
        
        .btn-profile:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(107, 114, 128, 0.4);
            color: white;
        }
        
        .btn-hire {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .btn-hire:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
            color: white;
        }
        
        .btn-reject {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        .btn-reject:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.4);
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
            backdrop-filter: blur(10px);
            z-index: 1000;
            animation: fadeIn 0.3s ease;
        }
        
        .profile-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: 24px;
            padding: 2rem;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 30px 100px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.3s ease;
        }
        
        .close-profile {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--danger-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }
        
        .close-profile:hover {
            transform: scale(1.1);
            background: #dc2626;
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
            .applicants-grid {
                grid-template-columns: 1fr;
            }
            
            .applicant-actions {
                grid-template-columns: 1fr;
            }
            
            .toolbar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .stats-info {
                justify-content: center;
            }
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translate(-50%, -40%) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
        }
        
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
<<<<<<< HEAD
    <!-- Header Section -->
    <div class="header-section">
        <div class="header-content">
            <div class="welcome-text" data-aos="fade-down">
                Welcome back, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>!
            </div>
            <h1 class="main-title" data-aos="fade-up" data-aos-delay="200">
                <i class="fas fa-users me-3"></i>Job Applicants
            </h1>
            <p class="main-subtitle" data-aos="fade-up" data-aos-delay="400">
                Review applications from skilled workers interested in your job post. 
                Evaluate profiles, make hiring decisions, and build your team.
            </p>
        </div>
    </div>
    
    <!-- Main Container -->
    <div class="main-container">
        <!-- Toolbar -->
        <div class="toolbar" data-aos="fade-up" data-aos-delay="600">
            <div class="filter-section">
                <select class="filter-select" id="experienceFilter">
                    <option value="all">All Experience Levels</option>
                    <option value="0-2">0-2 years</option>
                    <option value="3-5">3-5 years</option>
                    <option value="6+">6+ years</option>
                </select>
                
                <select class="filter-select" id="locationFilter">
                    <option value="all">All Cities</option>
                    <option value="Indore">Indore</option>
                    <option value="Bhopal">Bhopal</option>
                    <option value="Mumbai">Mumbai</option>
                    <option value="Delhi">Delhi</option>
                </select>
            </div>
            
            <div class="stats-info">
                <div class="stat-item">
                    <div class="stat-number" id="totalApplicants">0</div>
                    <div class="stat-label">Applicants</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="avgExperience">0</div>
                    <div class="stat-label">Avg Experience</div>
                </div>
            </div>
        </div>
        
        <!-- Applicants Grid -->
        <div class="applicants-grid" id="applicantsGrid">
            <?php
            $applicantCount = 0;
            $totalExperience = 0;
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $applicantCount++;
                    $totalExperience += intval($row['experience']);
                    $initials = strtoupper(substr($row['user_name'], 0, 2));
                    
                    echo "
                    <div class='applicant-card' data-aos='fade-up' data-aos-delay='" . ($applicantCount * 100) . "' 
                         data-experience='{$row['experience']}' data-city='{$row['city']}'>
                        <div class='applicant-header'>
                            <div class='applicant-avatar'>
                                <i class='fas fa-user'></i>
                            </div>
                            <h3 class='applicant-name'>" . htmlspecialchars($row['user_name']) . "</h3>
                            <div class='applicant-work-type'>
                                <i class='fas fa-tools me-2'></i>" . htmlspecialchars($row['workType']) . "
                            </div>
                            <div class='applicant-badge'>
                                <i class='fas fa-star me-2'></i>
                                Available
=======

<div class="container">
    <h2 class="text-center mb-4">Interested Laborers</h2>
    <div class="row" id="laborers-list">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "
                <div class='col-md-4 mb-4' id='laborer-" . $row['user_ID'] . "'>
                    <div class='card'>
                        <div class='card-header'>
                            <h5 class='card-title'>" . htmlspecialchars($row['user_name']) . "</h5>
                        </div>
                        <div class='card-body'>
                            <p class='card-text'><strong>Work Type:</strong> " . htmlspecialchars($row['workType']) . "</p>
                            <p class='card-text'><strong>Experience:</strong> " . htmlspecialchars($row['experience']) . " years</p>
                            <p class='card-text'><strong>City:</strong> " . htmlspecialchars($row['city']) . "</p>
                            <div class='d-flex justify-content-between align-items-center'>
                                <button onclick='showProfile({$row['user_ID']})' class='btn btn-outline-secondary'>
                                    <i class='fas fa-eye'></i> View Profile
                                </button>
                                <div>
                                    <button class='btn btn-success status-button' onclick='updateStatus({$row['user_ID']}, \"accepted\")'>Hire</button>
                                    <button class='btn btn-danger status-button' onclick='updateStatus({$row['user_ID']}, \"rejected\")'>reject</button>
                                </div>
>>>>>>> 39578cd55d61ac8c691bf23cfd350dd7248f990a
                            </div>
                        </div>
                        
                        <div class='applicant-body'>
                            <div class='applicant-details'>
                                <div class='detail-item'>
                                    <div class='detail-icon'>
                                        <i class='fas fa-envelope'></i>
                                    </div>
                                    <div class='detail-content'>
                                        <div class='detail-label'>Email Address</div>
                                        <div class='detail-value'>" . htmlspecialchars($row['email_id']) . "</div>
                                    </div>
                                </div>
                                
                                <div class='detail-item'>
                                    <div class='detail-icon'>
                                        <i class='fas fa-phone'></i>
                                    </div>
                                    <div class='detail-content'>
                                        <div class='detail-label'>Mobile Number</div>
                                        <div class='detail-value'>" . htmlspecialchars($row['mobile_no']) . "</div>
                                    </div>
                                </div>
                                
                                <div class='detail-item'>
                                    <div class='detail-icon'>
                                        <i class='fas fa-star'></i>
                                    </div>
                                    <div class='detail-content'>
                                        <div class='detail-label'>Experience</div>
                                        <div class='detail-value'>" . htmlspecialchars($row['experience']) . " years</div>
                                    </div>
                                </div>
                                
                                <div class='detail-item'>
                                    <div class='detail-icon'>
                                        <i class='fas fa-rupee-sign'></i>
                                    </div>
                                    <div class='detail-content'>
                                        <div class='detail-label'>Expected Salary</div>
                                        <div class='detail-value'>₹" . number_format($row['salary']) . "</div>
                                    </div>
                                </div>
                                
                                <div class='detail-item'>
                                    <div class='detail-icon'>
                                        <i class='fas fa-map-marker-alt'></i>
                                    </div>
                                    <div class='detail-content'>
                                        <div class='detail-label'>Location</div>
                                        <div class='detail-value'>" . htmlspecialchars($row['city']) . "</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class='applicant-actions'>
                                <button class='btn-action btn-profile' onclick='showProfile({$row['user_ID']})'>
                                    <i class='fas fa-eye'></i>
                                    Profile
                                </button>
                                <button class='btn-action btn-hire' onclick='updateStatus({$row['user_ID']}, \"Hired\")'>
                                    <i class='fas fa-check'></i>
                                    Hire
                                </button>
                                <button class='btn-action btn-reject' onclick='updateStatus({$row['user_ID']}, \"Rejected\")'>
                                    <i class='fas fa-times'></i>
                                    Reject
                                </button>
                            </div>
                        </div>
                    </div>";
                }
            } else {
                echo "
                <div class='empty-state' data-aos='fade-up'>
                    <div class='empty-icon'>
                        <i class='fas fa-inbox'></i>
                    </div>
                    <h3 class='empty-title'>No Applications Yet</h3>
                    <p class='empty-description'>
                        No workers have applied for this job post yet. Share your job post 
                        or wait for interested workers to discover and apply to your listing.
                    </p>
                    <a href='view.php' class='btn-action btn-hire' style='display: inline-flex; width: auto;'>
                        <i class='fas fa-arrow-left me-2'></i>Back to Job Posts
                    </a>
                </div>";
            }
            
            $stmt->close();
            $conn->close();
            ?>
        </div>
    </div>
    
    <!-- Profile Modal -->
    <div id="profileOverlay" class="profile-overlay" onclick="closeProfile()">
        <div class="profile-content" onclick="event.stopPropagation()">
            <button class="close-profile" onclick="closeProfile()">
                <i class="fas fa-times"></i>
            </button>
            <div id="profileData">
                <!-- Profile data will be loaded here -->
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        // Update statistics
        document.addEventListener('DOMContentLoaded', function() {
            const applicantCards = document.querySelectorAll('.applicant-card');
            const totalApplicants = applicantCards.length;
            let totalExp = 0;
            
            applicantCards.forEach(card => {
                const exp = parseInt(card.dataset.experience || 0);
                totalExp += exp;
            });
            
            const avgExp = totalApplicants > 0 ? Math.round(totalExp / totalApplicants) : 0;
            
            document.getElementById('totalApplicants').textContent = totalApplicants;
            document.getElementById('avgExperience').textContent = avgExp + 'y';
            
            // Animate counters
            animateCounters();
        });
        
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent);
                let count = 0;
                const increment = target / 20;
                
                const timer = setInterval(() => {
                    count += increment;
                    if (count >= target) {
                        counter.textContent = target + (counter.textContent.includes('y') ? 'y' : '');
                        clearInterval(timer);
                    } else {
                        counter.textContent = Math.floor(count) + (counter.textContent.includes('y') ? 'y' : '');
                    }
                }, 50);
            });
        }
<<<<<<< HEAD
        
        // Profile functions
        function showProfile(userId) {
            $.ajax({
                url: 'get_profile.php',
                type: 'GET',
                data: { user_id: userId },
                success: function(response) {
                    $('#profileData').html(response);
                    $('#profileOverlay').show();
                },
                error: function() {
                    alert('Error loading profile. Please try again.');
                }
            });
=======
    });
}

function closeProfile() {
    $('#profileOverlay').hide();
}

function updateStatus(userId, status) {
    $.ajax({
        url: 'update_status.php',
        type: 'POST',
        data: { user_id: userId, status: status, post_id: <?php echo $post_ID; ?> },
        success: function(response) {
            alert('Status updated successfully to: ' + status);

            // Hide the laborer card after the update
            $('#laborer-' + userId).fadeOut();

            // Optionally, disable further status updates or take other actions
        },
        error: function() {
            alert('Error updating status');
>>>>>>> 39578cd55d61ac8c691bf23cfd350dd7248f990a
        }
        
        function closeProfile() {
            $('#profileOverlay').hide();
        }
        
        function updateStatus(userId, status) {
            if (confirm(`Are you sure you want to ${status.toLowerCase()} this applicant?`)) {
                $.ajax({
                    url: 'update_status.php',
                    type: 'POST',
                    data: { 
                        user_id: userId, 
                        status: status,
                        job_post_id: <?php echo $post_ID; ?>
                    },
                    success: function(response) {
                        alert(`Applicant ${status.toLowerCase()} successfully!`);
                        location.reload();
                    },
                    error: function() {
                        alert('Error updating status. Please try again.');
                    }
                });
            }
        }
        
        // Filter functionality
        document.getElementById('experienceFilter').addEventListener('change', function() {
            const selectedExp = this.value;
            const cards = document.querySelectorAll('.applicant-card');
            
            cards.forEach(card => {
                const exp = parseInt(card.dataset.experience);
                let show = true;
                
                if (selectedExp === '0-2' && exp > 2) show = false;
                else if (selectedExp === '3-5' && (exp < 3 || exp > 5)) show = false;
                else if (selectedExp === '6+' && exp < 6) show = false;
                
                card.style.display = show ? 'block' : 'none';
            });
        });
        
        document.getElementById('locationFilter').addEventListener('change', function() {
            const selectedCity = this.value;
            const cards = document.querySelectorAll('.applicant-card');
            
            cards.forEach(card => {
                const city = card.dataset.city;
                if (selectedCity === 'all' || city.includes(selectedCity)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        // Enhanced hover effects
        document.querySelectorAll('.applicant-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
        
        // Keyboard navigation for modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('profileOverlay').style.display === 'block') {
                closeProfile();
            }
        });
    </script>
</body>
</html>
