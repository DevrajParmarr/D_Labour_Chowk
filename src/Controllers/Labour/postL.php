<?php
require_once '../Shared/config.php';

// Check if user is logged in and is a labour
if (!isLoggedIn()) {
    redirect('../Shared/login_form.php');
}

if (getCurrentUserType() !== 'Labour') {
    redirect('../Shared/login_form.php?error=unauthorized');
}

$db = Database::getInstance();
$conn = $db->getConnection();

$search = '';
$location = '';
$jobTitle = '';
$limit = 9; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure keys exist before accessing them
    $search = isset($_POST['search']) ? mysqli_real_escape_string($conn, $_POST['search']) : '';
    $location = isset($_POST['location']) ? mysqli_real_escape_string($conn, $_POST['location']) : '';
    $jobTitle = isset($_POST['jobTitle']) ? mysqli_real_escape_string($conn, $_POST['jobTitle']) : '';
}
$query = "SELECT * FROM job_post WHERE 1=1";
if ($jobTitle) {
    $query .= " AND jobTitle = '$jobTitle'";
}
if ($location) {
    $query .= " AND location = '$location'";
}
$query .= " LIMIT $limit OFFSET $offset";

$sql_result = mysqli_query($conn, $query);

$total_query = "SELECT COUNT(*) AS total FROM job_post WHERE 1=1";
if ($jobTitle) {
    $total_query .= " AND jobTitle = '$jobTitle'";
}
if ($location) {
    $total_query .= " AND location = '$location'";
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
    <title>Available Jobs - D Labour Chowk</title>
    <meta name="description" content="Browse available job opportunities and apply for positions that match your skills.">

    <!-- Enhanced CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #28a745;
            --secondary-color: #20c997;
            --accent-color: #f59e0b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --gradient-primary: linear-gradient(135deg, #28a745 0%, #20c997 100%);
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
            background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%);
            min-height: 100vh;
        }

        /* Header Section */
        .header-section {
            background: var(--gradient-primary);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
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
            padding: 0 1rem;
            position: relative;
            z-index: 2;
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
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
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
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.4);
        }

        /* Job Grid */
        .job-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 2rem;
        }

        .job-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            border: 1px solid #f1f5f9;
        }

        .job-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border-color: var(--primary-color);
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
            height: 220px;
            object-fit: cover;
            position: relative;
        }

        .job-badge {
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

        .job-content {
            padding: 1.5rem;
        }

        .job-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .job-details {
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

        .apply-btn {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .apply-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.4);
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .filter-form {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .job-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .stats-row {
                gap: 1rem;
            }

            .stat-item {
                min-width: 110px;
                padding: 1rem;
            }
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
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header-section">
        <div class="header-content">
            <h1 class="main-title" data-aos="fade-up">
                <i class="fas fa-search me-3"></i>Find Your Perfect Job
            </h1>
            <p class="main-subtitle" data-aos="fade-up" data-aos-delay="200">
                Discover exciting job opportunities that match your skills and experience.
                Apply to jobs from verified employers and grow your career.
            </p>
            
            <div class="stats-row" data-aos="fade-up" data-aos-delay="400">
                <div class="stat-item">
                    <span class="stat-number"><?php echo $total_jobs; ?>+</span>
                    <div class="stat-label">Available Jobs</div>
                </div>
                <div class="stat-item">
                    <span class="stat-number">500+</span>
                    <div class="stat-label">Active Employers</div>
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
        <div class="filter-section" data-aos="fade-up" data-aos-delay="600">
            <h2 class="filter-title">
                <i class="fas fa-filter me-2"></i>Find Jobs That Match Your Skills
            </h2>
            <form class="filter-form" method="POST" action="">
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-briefcase me-2"></i>Job Type
                    </label>
                    <select name="jobTitle" class="filter-select">
                        <option value="">All Job Types</option>
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
                        <i class="fas fa-map-marker-alt me-2"></i>Location
                    </label>
                    <select name="location" class="filter-select">
                        <option value="">All Locations</option>
                        <option value="Indore" <?php if ($location == 'Indore') echo 'selected'; ?>>Indore</option>
                        <option value="Bhopal" <?php if ($location == 'Bhopal') echo 'selected'; ?>>Bhopal</option>
                        <option value="Ujjain" <?php if ($location == 'Ujjain') echo 'selected'; ?>>Ujjain</option>
                        <option value="Jabalpur" <?php if ($location == 'Jabalpur') echo 'selected'; ?>>Jabalpur</option>
                        <option value="Kota" <?php if ($location == 'Kota') echo 'selected'; ?>>Kota</option>
                        <option value="Jaipur" <?php if ($location == 'Jaipur') echo 'selected'; ?>>Jaipur</option>
                        <option value="Delhi" <?php if ($location == 'Delhi') echo 'selected'; ?>>Delhi</option>
                    </select>
                </div>

                <button type="submit" class="filter-btn">
                    <i class="fas fa-search me-2"></i>Search Jobs
                </button>
            </form>
        </div>

        <!-- Job Listings -->
        <div class="job-grid" id="job-grid" data-aos="fade-up" data-aos-delay="800">
            <?php
            if (mysqli_num_rows($sql_result) > 0) {
                $jobCount = 0;
                while ($dbrow = mysqli_fetch_assoc($sql_result)) {
                    $jobCount++;
                    $imageUrl = !empty($dbrow['impath']) ? htmlspecialchars($dbrow['impath']) : 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=400';
                    $description = htmlspecialchars($dbrow['detail']);
                    $shortDescription = strlen($description) > 120 ? substr($description, 0, 120) . '...' : $description;
                    
                    echo "
                    <div class='job-card fade-in-up' data-aos='fade-up' data-aos-delay='" . ($jobCount * 100) . "'>
                        <div class='job-image-container'>
                            <img src='{$imageUrl}' alt='" . htmlspecialchars($dbrow['jobTitle']) . "' class='job-image' onerror=\"this.src='https://via.placeholder.com/400x220/28a745/ffffff?text=" . urlencode($dbrow['jobTitle']) . "'\">
                            <div class='job-badge'>New</div>
                        </div>
                        
                        <div class='job-content'>
                            <h3 class='job-title'>" . htmlspecialchars($dbrow['jobTitle']) . "</h3>
                            
                            <div class='job-details'>
                                <div class='detail-row'>
                                    <i class='fas fa-map-marker-alt detail-icon'></i>
                                    <span class='detail-label'>Location:</span>
                                    <span class='detail-value'>" . htmlspecialchars($dbrow['location']) . "</span>
                                </div>
                                
                                <div class='detail-row'>
                                    <i class='fas fa-rupee-sign detail-icon'></i>
                                    <span class='detail-label'>Salary:</span>
                                    <span class='detail-value salary-value'>₹" . number_format($dbrow['salary']) . "</span>
                                </div>
                                
                                <div class='detail-row'>
                                    <i class='fas fa-city detail-icon'></i>
                                    <span class='detail-label'>City:</span>
                                    <span class='detail-value'>" . htmlspecialchars($dbrow['city']) . "</span>
                                </div>
                            </div>
                            
                            <p class='job-description'>{$shortDescription}</p>
                            
                            <button class='apply-btn' onclick='applyForJob(" . $dbrow['post_ID'] . ")'>
                                <i class='fas fa-paper-plane me-2'></i>Apply Now
                            </button>
                        </div>
                    </div>";
                }
            } else {
                echo "
                <div style='grid-column: 1 / -1; text-align: center; padding: 4rem 2rem; background: white; border-radius: 20px; margin-bottom: 2rem;'>
                    <div style='font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;'>
                        <i class='fas fa-search'></i>
                    </div>
                    <h3 style='font-size: 1.5rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem;'>No Jobs Found</h3>
                    <p style='color: #6b7280; margin-bottom: 2rem;'>
                        We couldn't find any jobs matching your search criteria.<br>
                        Try adjusting your filters or search in a different location.
                    </p>
                </div>";
            }
            ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination-section">
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?>&location=<?= urlencode($location) ?>&jobTitle=<?= urlencode($jobTitle) ?>">
                            <i class="fas fa-chevron-left me-2"></i>Previous
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    $start = max(1, $page - 2);
                    $end = min($total_pages, $page + 2);
                    
                    if ($start > 1): ?>
                        <a href="?page=1&location=<?= urlencode($location) ?>&jobTitle=<?= urlencode($jobTitle) ?>">1</a>
                        <?php if ($start > 2): ?>
                            <span>...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <a href="?page=<?= $i ?>&location=<?= urlencode($location) ?>&jobTitle=<?= urlencode($jobTitle) ?>"
                           class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
                    <?php endfor; ?>
                    
                    <?php if ($end < $total_pages): ?>
                        <?php if ($end < $total_pages - 1): ?>
                            <span>...</span>
                        <?php endif; ?>
                        <a href="?page=<?= $total_pages ?>&location=<?= urlencode($location) ?>&jobTitle=<?= urlencode($jobTitle) ?>"><?= $total_pages ?></a>
                    <?php endif; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?>&location=<?= urlencode($location) ?>&jobTitle=<?= urlencode($jobTitle) ?>">
                            Next<i class="fas fa-chevron-right ms-2"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        function applyForJob(post_ID) {
            // Show confirmation modal instead of alert
            const modalHtml = `
                <div class="modal fade" id="applyModal" tabindex="-1" aria-labelledby="applyModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="applyModalLabel">
                                    <i class="fas fa-paper-plane text-primary me-2"></i>Apply for Job
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to apply for this job? Your application will be sent to the employer.</p>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    Make sure your profile is complete and up-to-date for better chances of getting hired.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </button>
                                <button type="button" class="btn btn-primary" onclick="confirmApply(${post_ID})">
                                    <i class="fas fa-paper-plane me-1"></i>Apply Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Remove existing modal if present
            const existingModal = document.getElementById('applyModal');
            if (existingModal) {
                existingModal.remove();
            }

            // Add modal to body
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('applyModal'));
            modal.show();
        }

        function confirmApply(post_ID) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('applyModal'));
            modal.hide();

            // Find the button that triggered the application
            const button = event.target.closest('.apply-btn');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Applying...';
            button.disabled = true;

            fetch('apply_job.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `post_ID=${post_ID}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast(data.message, 'success');
                    button.innerHTML = '<i class="fas fa-check me-2"></i>Applied';
                    button.style.background = 'var(--success-color)';
                    button.disabled = true;
                } else {
                    showToast(data.message, data.status === 'info' ? 'warning' : 'error');
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('An error occurred. Please try again.', 'error');
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }

        // Add hover effects to cards
        document.querySelectorAll('.job-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-12px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Add loading animation to filter button
        document.querySelector('.filter-btn').addEventListener('click', function(e) {
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Searching...';
            this.disabled = true;
            
            // Re-enable after form submission
            setTimeout(() => {
                this.innerHTML = originalText;
                this.disabled = false;
            }, 2000);
        });

        // Smooth scroll to results after filter
        if (window.location.search.includes('page=') || document.querySelector('form[method="POST"]')) {
            setTimeout(() => {
                document.querySelector('.job-grid')?.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }, 100);
        }

        // Add staggered animation to cards
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.job-card').forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('fade-in-up');
            });
        });

        // Toast notification functions
        function showToast(message, type = 'info') {
            const toastContainer = document.querySelector('.toast-container') || createToastContainer();

            const toastHtml = `
                <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas ${getToastIcon(type)} me-2"></i>
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;

            toastContainer.insertAdjacentHTML('beforeend', toastHtml);

            const toastElement = toastContainer.lastElementChild;
            const toast = new bootstrap.Toast(toastElement);
            toast.show();

            toastElement.addEventListener('hidden.bs.toast', function() {
                this.remove();
            });
        }

        function createToastContainer() {
            const container = document.createElement('div');
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
            return container;
        }

        function getToastIcon(type) {
            const icons = {
                'success': 'fa-check-circle',
                'error': 'fa-exclamation-circle',
                'warning': 'fa-exclamation-triangle',
                'info': 'fa-info-circle'
            };
            return icons[type] || icons.info;
        }
    </script>

</body>
</html>
