<?php
session_start();

if (!isset($_SESSION["login_status"]) || $_SESSION["login_status"] == false) {
    header('Location: ../Shared/login_form.php');
    exit;
}

include "../Shared/sqlconnection.php";
include "menu.html";

$client_id = $_SESSION['user_id'];

$query = "
SELECT h.hire_id, u.user_name, u.email_id, u.mobile_no, lp.workType, lp.experience, lp.salary, lp.location, h.status, h.labour_id
FROM hires h
JOIN user u ON h.labour_id = u.user_ID
JOIN lab_post lp ON h.labour_id = lp.user_ID
WHERE h.client_id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hired Workers - D Labour Chowk</title>
    <meta name="description" content="Manage your hired workers. View details, communicate, and rate their performance.">
    
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
            margin: 0 auto;
        }
        
        /* Main Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        /* Workers Grid */
        .workers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 2rem;
        }
        
        .worker-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            position: relative;
            border: 1px solid #f1f5f9;
        }
        
        .worker-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.15);
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
        
        .worker-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
            position: relative;
        }
        
        .worker-avatar {
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
        
        .worker-name {
            font-family: 'Poppins', sans-serif;
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .worker-work-type {
            opacity: 0.9;
            font-size: 1rem;
            margin-bottom: 1rem;
        }
        
        .worker-status {
            display: inline-flex;
            align-items: center;
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .worker-body {
            padding: 2rem;
        }
        
        .worker-details {
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
        
        .worker-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
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
        
        .btn-rate {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }
        
        .btn-rate:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.4);
            color: white;
        }
        
        .btn-contact {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .btn-contact:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.4);
            color: white;
        }
        
        /* Rating Modal */
        .rating-modal .modal-content {
            border: none;
            border-radius: 24px;
            overflow: hidden;
        }
        
        .rating-modal .modal-header {
            background: var(--gradient-primary);
            color: white;
            border-bottom: none;
            padding: 2rem;
        }
        
        .rating-modal .modal-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.3rem;
        }
        
        .rating-modal .modal-body {
            padding: 2rem;
        }
        
        .star-rating {
            display: flex;
            gap: 0.5rem;
            justify-content: center;
            margin: 1.5rem 0;
        }
        
        .star {
            font-size: 2rem;
            color: #e5e7eb;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .star:hover,
        .star.active {
            color: #fbbf24;
            transform: scale(1.1);
        }
        
        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        
        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
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
            .workers-grid {
                grid-template-columns: 1fr;
            }
            
            .worker-actions {
                flex-direction: column;
            }
            
            .rating-modal .modal-body {
                padding: 1.5rem;
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
                <i class="fas fa-users me-3"></i>Hired Workers
            </h1>
            <p class="main-subtitle" data-aos="fade-up" data-aos-delay="400">
                Manage your hired workers, track their progress, and maintain 
                professional relationships for future projects.
            </p>
        </div>
    </div>
    
    <!-- Main Container -->
    <div class="main-container">
        <!-- Workers Grid -->
        <div class="workers-grid">
            <?php
            $workerCount = 0;
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $workerCount++;
                    $statusColor = $row['status'] == 'Active' ? '#10b981' : '#6b7280';
                    $initials = strtoupper(substr($row['user_name'], 0, 2));
                    
                    echo "
                    <div class='worker-card' data-aos='fade-up' data-aos-delay='" . ($workerCount * 100) . "'>
                        <div class='worker-header'>
                            <div class='worker-avatar'>
                                <i class='fas fa-user'></i>
                            </div>
                            <h3 class='worker-name'>" . htmlspecialchars($row['user_name']) . "</h3>
                            <div class='worker-work-type'>
                                <i class='fas fa-tools me-2'></i>" . htmlspecialchars($row['workType']) . "
                            </div>
                            <div class='worker-status' style='background: rgba(16, 185, 129, 0.2);'>
                                <i class='fas fa-circle me-2' style='color: {$statusColor};'></i>
                                " . htmlspecialchars($row['status']) . "
                            </div>
                        </div>
                        
                        <div class='worker-body'>
                            <div class='worker-details'>
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
                                        <div class='detail-value'>" . htmlspecialchars($row['location']) . "</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class='worker-actions'>
                                <button type='button' class='btn-action btn-rate' data-bs-toggle='modal' data-bs-target='#rateModal{$row['hire_id']}'>
                                    <i class='fas fa-star'></i>
                                    Rate Worker
                                </button>
                                <a href='tel:" . htmlspecialchars($row['mobile_no']) . "' class='btn-action btn-contact'>
                                    <i class='fas fa-phone'></i>
                                    Contact
                                </a>
                            </div>
                        </div>
                        
                        <!-- Rating Modal -->
                        <div class='modal fade rating-modal' id='rateModal{$row['hire_id']}' tabindex='-1' aria-labelledby='rateModalLabel{$row['hire_id']}' aria-hidden='true'>
                            <div class='modal-dialog modal-dialog-centered'>
                                <div class='modal-content'>
                                    <div class='modal-header'>
                                        <h5 class='modal-title' id='rateModalLabel{$row['hire_id']}'>
                                            <i class='fas fa-star me-2'></i>Rate " . htmlspecialchars($row['user_name']) . "
                                        </h5>
                                        <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal' aria-label='Close'></button>
                                    </div>
                                    <div class='modal-body'>
                                        <form method='POST' action='rate_labour.php'>
                                            <input type='hidden' name='hire_id' value='{$row['hire_id']}'>
                                            <input type='hidden' name='labour_id' value='{$row['labour_id']}'>
                                            <input type='hidden' name='client_id' value='{$client_id}'>
                                            
                                            <div class='mb-4'>
                                                <label class='form-label'>How would you rate this worker's performance?</label>
                                                <div class='star-rating'>
                                                    <i class='fas fa-star star' data-rating='1'></i>
                                                    <i class='fas fa-star star' data-rating='2'></i>
                                                    <i class='fas fa-star star' data-rating='3'></i>
                                                    <i class='fas fa-star star' data-rating='4'></i>
                                                    <i class='fas fa-star star' data-rating='5'></i>
                                                </div>
                                                <input type='hidden' name='rating' id='selectedRating{$row['hire_id']}' required>
                                            </div>
                                            
                                            <div class='mb-4'>
                                                <label for='review{$row['hire_id']}' class='form-label'>Review (Optional)</label>
                                                <textarea name='review' id='review{$row['hire_id']}' class='form-control' rows='4' placeholder='Share your experience working with this person...'></textarea>
                                            </div>
                                            
                                            <div class='d-grid'>
                                                <button type='submit' class='btn-action btn-rate' style='border-radius: 12px;'>
                                                    <i class='fas fa-paper-plane me-2'></i>Submit Rating
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>";
                }
            } else {
                echo "
                <div class='empty-state' data-aos='fade-up'>
                    <div class='empty-icon'>
                        <i class='fas fa-users'></i>
                    </div>
                    <h3 class='empty-title'>No Workers Hired Yet</h3>
                    <p class='empty-description'>
                        You haven't hired any workers yet. Start by posting jobs and 
                        connecting with skilled workers in your area.
                    </p>
                    <a href='creatjob.php' class='btn-action btn-rate' style='display: inline-flex; width: auto;'>
                        <i class='fas fa-plus me-2'></i>Post a Job
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
        
        // Star Rating System
        document.querySelectorAll('.star-rating').forEach(ratingContainer => {
            const stars = ratingContainer.querySelectorAll('.star');
            const modal = ratingContainer.closest('.modal');
            const hiddenInput = modal.querySelector('input[name="rating"]');
            
            stars.forEach((star, index) => {
                star.addEventListener('click', () => {
                    const rating = index + 1;
                    hiddenInput.value = rating;
                    
                    stars.forEach((s, i) => {
                        if (i < rating) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });
                
                star.addEventListener('mouseover', () => {
                    stars.forEach((s, i) => {
                        if (i <= index) {
                            s.style.color = '#fbbf24';
                        } else {
                            s.style.color = '#e5e7eb';
                        }
                    });
                });
            });
            
            ratingContainer.addEventListener('mouseleave', () => {
                stars.forEach((s, i) => {
                    const currentRating = hiddenInput.value;
                    if (i < currentRating) {
                        s.style.color = '#fbbf24';
                    } else {
                        s.style.color = '#e5e7eb';
                    }
                });
            });
        });
        
        // Enhanced hover effects
        document.querySelectorAll('.worker-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
        
        // Form validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const ratingInput = this.querySelector('input[name="rating"]');
                if (!ratingInput.value) {
                    e.preventDefault();
                    alert('Please select a star rating before submitting.');
                }
            });
        });
    </script>
</body>
</html>
