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

$db = Database::getInstance();
$conn = $db->getConnection();

// Fetch job posts related to the logged-in user
$query = "SELECT * FROM lab_post WHERE user_ID = ? ORDER BY l_post_ID DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$sql_result = $stmt->get_result();

// Include menu
include "menu.html";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Work Posts - D Labour Chowk</title>
    <meta name="description" content="Manage your work posts and showcase your skills to potential clients.">

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
            max-width: 600px;
            margin: 0 auto;
        }

        /* Main Container */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Results Header */
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

        /* Work Posts Grid */
        .work-posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 2rem;
        }

        .work-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            border: 1px solid #f1f5f9;
        }

        .work-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            border-color: var(--primary-color);
        }

        .work-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-accent);
        }

        .work-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            position: relative;
        }

        .work-badge {
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

        .work-content {
            padding: 1.5rem;
        }

        .work-header {
            margin-bottom: 1rem;
        }

        .work-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .work-type {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1rem;
            text-transform: capitalize;
        }

        .work-details {
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

        .work-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .btn-delete {
            flex: 1;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            text-decoration: none;
            text-align: center;
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(239, 68, 68, 0.4);
            color: white;
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 20px;
            margin-bottom: 2rem;
            grid-column: 1 / -1;
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

        /* Navigation */
        .navigation-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 3rem;
            flex-wrap: wrap;
        }

        .btn-nav {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-nav:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
        }

        .btn-nav.primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-nav.primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .work-posts-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .results-header {
                flex-direction: column;
                align-items: stretch;
            }

            .work-actions {
                flex-direction: column;
            }

            .main-title {
                font-size: 2rem;
            }

            .navigation-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn-nav {
                width: 100%;
                max-width: 300px;
                text-align: center;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
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
            <h1 class="main-title">
                <i class="fas fa-tools me-3"></i>My Work Posts
            </h1>
            <p class="main-subtitle">
                Manage your work posts and showcase your skills to potential clients.
                Keep your portfolio updated to attract more opportunities.
            </p>
        </div>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Results Section -->
        <div class="results-section">
            <div class="results-header">
                <div class="results-count">
                    <i class="fas fa-images me-2"></i>
                    <?php echo $sql_result->num_rows; ?> work post<?php echo $sql_result->num_rows != 1 ? 's' : ''; ?> found
                </div>
            </div>

            <?php if ($sql_result->num_rows > 0): ?>
                <div class="work-posts-grid">
                    <?php while ($dbrow = $sql_result->fetch_assoc()): ?>
                        <div class="work-card fade-in-up">
                            <div class="work-image-container">
                                <img class="work-image" src="<?php echo htmlspecialchars($dbrow['impath']); ?>" alt="<?php echo htmlspecialchars($dbrow['workType']); ?>" onerror="this.src='https://via.placeholder.com/400x220/4f46e5/ffffff?text=<?php echo urlencode($dbrow['workType']); ?>'">
                                <div class="work-badge">Active</div>
                            </div>

                            <div class="work-content">
                                <div class="work-header">
                                    <h3 class="work-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
                                    <div class="work-type"><?php echo htmlspecialchars($dbrow['workType']); ?></div>
                                </div>

                                <div class="work-details">
                                    <div class="detail-row">
                                        <i class="fas fa-map-marker-alt detail-icon"></i>
                                        <span class="detail-label">Location:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($dbrow['location']); ?>, <?php echo htmlspecialchars($dbrow['city']); ?></span>
                                    </div>

                                    <div class="detail-row">
                                        <i class="fas fa-rupee-sign detail-icon"></i>
                                        <span class="detail-label">Salary:</span>
                                        <span class="detail-value salary-value">₹<?php echo number_format($dbrow['salary']); ?>/day</span>
                                    </div>

                                    <div class="detail-row">
                                        <i class="fas fa-star detail-icon"></i>
                                        <span class="detail-label">Experience:</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($dbrow['experience']); ?> years</span>
                                    </div>

                                    <div class="detail-row">
                                        <i class="fas fa-calendar detail-icon"></i>
                                        <span class="detail-label">Posted:</span>
                                        <span class="detail-value">Recently</span>
                                    </div>
                                </div>

                                <div class="work-actions">
                                    <a href="deletePost.php?l_post_ID=<?php echo $dbrow['l_post_ID']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this work post? This action cannot be undone.')">
                                        <i class="fas fa-trash me-2"></i>Delete Post
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <div class="no-results-icon">
                        <i class="fas fa-tools"></i>
                    </div>
                    <h3 class="no-results-title">No Work Posts Yet</h3>
                    <p class="no-results-text">
                        You haven't created any work posts yet. Start by adding your work samples
                        to showcase your skills to potential clients.
                    </p>
                    <div class="navigation-buttons">
                        <a href="post_work.php" class="btn-nav primary">
                            <i class="fas fa-plus me-2"></i>Create Your First Work Post
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="navigation-buttons">
                <a href="post_work.php" class="btn-nav">
                    <i class="fas fa-plus me-2"></i>Add New Work Post
                </a>
                <a href="dashboard.php" class="btn-nav">
                    <i class="fas fa-home me-2"></i>Back to Dashboard
                </a>
                <a href="work_posts.php" class="btn-nav primary">
                    <i class="fas fa-images me-2"></i>View All Work Posts
                </a>
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

        // Add loading animation to buttons
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!this.onclick || confirm('Are you sure you want to delete this work post? This action cannot be undone.')) {
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Deleting...';
                    this.disabled = true;

                    // Reset after navigation delay
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.disabled = false;
                    }, 3000);
                }
            });
        });

        // Add hover effects to cards
        document.querySelectorAll('.work-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-12px) scale(1.02)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Add staggered animation to cards
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.work-card').forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html>
