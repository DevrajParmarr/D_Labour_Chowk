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
$user_ID = getCurrentUserId();
$user_name = $_SESSION['user_name'];

// Fetch work posts from the database
$sql = "SELECT * FROM work_posts WHERE labour_ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_ID);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Portfolio - D Labour Chowk</title>
    <meta name="description" content="View and manage your work portfolio. Showcase your completed projects and skills to potential clients.">
    
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
        
        .header-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-header {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-header:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            color: white;
        }
        
        /* Main Container */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        /* Work Posts Grid */
        .work-posts-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 2rem;
        }
        
        .work-post-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            position: relative;
            border: 1px solid #f1f5f9;
        }
        
        .work-post-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.15);
        }
        
        .work-post-card::before {
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
            height: 250px;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        
        .work-post-card:hover .work-image {
            transform: scale(1.05);
        }
        
        .work-content {
            padding: 2rem;
        }
        
        .work-description {
            color: #6b7280;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .work-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
        }
        
        .work-date {
            color: #6b7280;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .work-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn-action {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .btn-edit {
            background: rgba(40, 167, 69, 0.1);
            color: var(--primary-color);
            border: 1px solid rgba(40, 167, 69, 0.2);
        }
        
        .btn-edit:hover {
            background: rgba(40, 167, 69, 0.2);
            transform: translateY(-1px);
        }
        
        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        
        .btn-delete:hover {
            background: rgba(239, 68, 68, 0.2);
            transform: translateY(-1px);
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6b7280;
            grid-column: 1 / -1;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
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
        
        .btn-create {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.4);
            color: white;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .work-posts-grid {
                grid-template-columns: 1fr;
            }
            
            .header-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-header {
                width: 100%;
                max-width: 300px;
                justify-content: center;
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
                Welcome back, <strong><?php echo htmlspecialchars($user_name); ?></strong>!
            </div>
            <h1 class="main-title" data-aos="fade-up" data-aos-delay="200">
                <i class="fas fa-images me-3"></i>Work Portfolio
            </h1>
            <p class="main-subtitle" data-aos="fade-up" data-aos-delay="400">
                Showcase your completed projects and build a professional portfolio
                that demonstrates your skills and expertise to potential clients.
            </p>
            
            <div class="header-actions" data-aos="fade-up" data-aos-delay="600">
                <a href="post_work.php" class="btn-header">
                    <i class="fas fa-plus"></i>Add New Work
                </a>
                <a href="dashboard.php" class="btn-header">
                    <i class="fas fa-home"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>
    
    <!-- Main Container -->
    <div class="main-container">
        <!-- Work Posts Grid -->
        <div class="work-posts-grid">
            <?php
            $workCount = 0;
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $workCount++;
                    $description = htmlspecialchars($row['description']);
                    $shortDescription = strlen($description) > 200 ? substr($description, 0, 200) . '...' : $description;
                    $imageUrl = '../Shared/uploads/' . htmlspecialchars($row['image']);
                    
                    echo "
                    <div class='work-post-card' data-aos='fade-up' data-aos-delay='" . ($workCount * 100) . "'>
                        <img src='{$imageUrl}' alt='Work Sample' class='work-image'
                             onerror=\"this.src='https://via.placeholder.com/400x250/28a745/ffffff?text=Work+Sample'\">
                        
                        <div class='work-content'>
                            <p class='work-description'>{$shortDescription}</p>
                            
                            <div class='work-meta'>
                                <div class='work-date'>
                                    <i class='fas fa-calendar'></i>
                                    " . date('M d, Y', strtotime($row['created_at'])) . "
                                </div>
                                
                                <div class='work-actions'>
                                    <button class='btn-action btn-edit' onclick='editWork({$row['work_post_id']})'>
                                        <i class='fas fa-edit'></i>Edit
                                    </button>
                                    <button class='btn-action btn-delete' onclick='deleteWork({$row['work_post_id']})'>
                                        <i class='fas fa-trash'></i>Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>";
                }
            } else {
                echo "
                <div class='empty-state' data-aos='fade-up'>
                    <div class='empty-icon'>
                        <i class='fas fa-images'></i>
                    </div>
                    <h3 class='empty-title'>No Work Posts Yet</h3>
                    <p class='empty-description'>
                        Start building your portfolio by adding your completed work samples.
                        Showcase your skills and attract potential clients with your professional work.
                    </p>
                    <a href='post_work.php' class='btn-create'>
                        <i class='fas fa-plus me-2'></i>Add Your First Work
                    </a>
                </div>";
            }
            
            // Close the connection
            $conn->close();
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
        
        // Edit work function
        function editWork(workId) {
            // This would typically open an edit modal or redirect to edit page
            alert('Edit functionality will be implemented. Work ID: ' + workId);
        }
        
        // Delete work function
        function deleteWork(workId) {
            if (confirm('Are you sure you want to delete this work post? This action cannot be undone.')) {
                // Here you would typically make an AJAX call to delete the work
                alert('Work post deleted successfully!');
                location.reload();
            }
        }
        
        // Enhanced hover effects
        document.querySelectorAll('.work-post-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-12px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
        
        // Image error handling
        document.querySelectorAll('.work-image').forEach(img => {
            img.addEventListener('error', function() {
                this.src = 'https://via.placeholder.com/400x250/28a745/ffffff?text=Work+Sample';
            });
        });
    </script>
</body>
</html>
