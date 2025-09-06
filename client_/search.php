
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

// Retrieve search criteria from URL parameters
$workType = $_GET['workType'];
$city = $_GET['city'];

// Prepare SQL query with placeholders
$sql = "SELECT * FROM lab_post WHERE workType LIKE ? AND city LIKE ?";
$stmt = $conn->prepare($sql);

// Bind parameters and execute
$workType_param = "%" . $workType . "%";
$city_param = "%" . $city . "%";
$stmt->bind_param("ss", $workType_param, $city_param);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - D Labour Chowk</title>
    <meta name="description" content="Find skilled workers matching your requirements. Browse profiles and hire the best talent.">
    
    <!-- Enhanced CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
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
        
        .header-section {
            background: var(--gradient-primary);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .search-header {
            text-align: center;
        }
        
        .search-title {
            font-family: 'Poppins', sans-serif;
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .search-info {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        
        .search-query {
            background: rgba(255, 255, 255, 0.2);
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-top: 1.5rem;
            display: inline-block;
        }
        
        .results-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
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
        
        .filter-sort {
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        .sort-select {
            padding: 0.5rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            background: white;
            color: #374151;
            font-weight: 500;
        }
        
        .worker-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .worker-card {
            background: white;
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .worker-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
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
        
        .worker-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .worker-avatar {
            width: 60px;
            height: 60px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            margin-right: 1rem;
        }
        
        .worker-info h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }
        
        .worker-type {
            color: #6b7280;
            font-size: 0.9rem;
            text-transform: capitalize;
        }
        
        .worker-details {
            margin-bottom: 1.5rem;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
        }
        
        .detail-icon {
            width: 16px;
            margin-right: 0.75rem;
            color: var(--primary-color);
        }
        
        .detail-label {
            font-weight: 600;
            color: #374151;
            margin-right: 0.5rem;
        }
        
        .detail-value {
            color: #6b7280;
        }
        
        .salary-highlight {
            color: var(--success-color);
            font-weight: 700;
            font-size: 1rem;
        }
        
        .worker-actions {
            display: flex;
            gap: 0.75rem;
        }
        
        .btn-view {
            flex: 1;
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }
        
        .btn-view:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.4);
            color: white;
        }
        
        .btn-contact {
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary-color);
            border: 1px solid rgba(37, 99, 235, 0.2);
            padding: 0.75rem;
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .btn-contact:hover {
            background: rgba(37, 99, 235, 0.2);
            transform: translateY(-1px);
        }
        
        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 16px;
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
            .worker-grid {
                grid-template-columns: 1fr;
            }
            
            .results-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-sort {
                justify-content: center;
            }
            
            .worker-actions {
                flex-direction: column;
            }
            
            .search-title {
                font-size: 1.8rem;
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
        
        /* Loading Animation */
        .loading-placeholder {
            height: 200px;
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: 16px;
        }
        
        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>
<body>
    <div class="header-section">
        <div class="container">
            <div class="search-header">
                <h1 class="search-title">
                    <i class="fas fa-search-location me-2"></i>Search Results
                </h1>
                <p class="search-info">
                    <?php if ($result->num_rows > 0): ?>
                        Found skilled workers matching your requirements
                    <?php else: ?>
                        No workers found matching your search criteria
                    <?php endif; ?>
                </p>
                
                <div class="search-query">
                    <i class="fas fa-tools me-2"></i>
                    <strong><?php echo htmlspecialchars($workType); ?></strong> in 
                    <strong><?php echo htmlspecialchars($city); ?></strong>
                </div>
            </div>
        </div>
    </div>
    
    <div class="results-container">
        <?php if ($result->num_rows > 0): ?>
            <div class="results-header">
                <div class="results-count">
                    <i class="fas fa-users me-2"></i>
                    <?php echo $result->num_rows; ?> worker<?php echo $result->num_rows > 1 ? 's' : ''; ?> found
                </div>
                
                <div class="filter-sort">
                    <select class="sort-select" onchange="sortResults(this.value)">
                        <option value="relevance">Sort by Relevance</option>
                        <option value="salary_high">Salary: High to Low</option>
                        <option value="salary_low">Salary: Low to High</option>
                        <option value="experience">Experience</option>
                        <option value="location">Location</option>
                    </select>
                </div>
            </div>
            
            <div class="worker-grid" id="workerGrid">
                <?php while ($post = $result->fetch_assoc()): ?>
                    <?php
                    // Fetch user details
                    $user_sql = "SELECT user_name FROM user WHERE user_id = ?";
                    $user_stmt = $conn->prepare($user_sql);
                    $user_stmt->bind_param("i", $post['user_id']);
                    $user_stmt->execute();
                    $user_result = $user_stmt->get_result();
                    $user = $user_result->fetch_assoc();
                    
                    // Generate avatar initials
                    $initials = strtoupper(substr($user['user_name'], 0, 2));
                    ?>
                    <div class="worker-card" data-salary="<?php echo htmlspecialchars($post['salary']); ?>" data-experience="<?php echo htmlspecialchars($post['experience']); ?>">
                        <div class="worker-header">
                            <div class="worker-avatar">
                                <?php echo $initials; ?>
                            </div>
                            <div class="worker-info">
                                <h3><?php echo htmlspecialchars($user['user_name']); ?></h3>
                                <div class="worker-type"><?php echo htmlspecialchars($post['workType']); ?></div>
                            </div>
                        </div>
                        
                        <div class="worker-details">
                            <div class="detail-item">
                                <i class="fas fa-rupee-sign detail-icon"></i>
                                <span class="detail-label">Salary:</span>
                                <span class="detail-value salary-highlight">₹<?php echo number_format(htmlspecialchars($post['salary'])); ?>/month</span>
                            </div>
                            
                            <div class="detail-item">
                                <i class="fas fa-star detail-icon"></i>
                                <span class="detail-label">Experience:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($post['experience']); ?> years</span>
                            </div>
                            
                            <div class="detail-item">
                                <i class="fas fa-map-marker-alt detail-icon"></i>
                                <span class="detail-label">Location:</span>
                                <span class="detail-value"><?php echo htmlspecialchars($post['location']); ?>, <?php echo htmlspecialchars($post['city']); ?></span>
                            </div>
                            
                            <div class="detail-item">
                                <i class="fas fa-calendar detail-icon"></i>
                                <span class="detail-label">Available:</span>
                                <span class="detail-value">Immediately</span>
                            </div>
                        </div>
                        
                        <div class="worker-actions">
                            <a href="profile.php?user_id=<?php echo htmlspecialchars($post['user_id']); ?>" class="btn-view">
                                <i class="fas fa-user me-2"></i>View Profile
                            </a>
                            <button class="btn-contact" onclick="contactWorker(<?php echo htmlspecialchars($post['user_id']); ?>)">
                                <i class="fas fa-phone"></i>
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <div class="no-results-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h2 class="no-results-title">No Workers Found</h2>
                <p class="no-results-text">
                    We couldn't find any workers matching your search criteria.<br>
                    Try adjusting your search terms or location.
                </p>
                <div class="navigation-buttons">
                    <a href="home.php" class="btn-nav primary">
                        <i class="fas fa-search me-2"></i>Try New Search
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="navigation-buttons">
            <a href="availableLabour.php" class="btn-nav">
                <i class="fas fa-arrow-left me-2"></i>Back to Browse
            </a>
            <a href="../Shared/index.html" class="btn-nav">
                <i class="fas fa-home me-2"></i>Back to Home
            </a>
            <a href="advanced_search.php" class="btn-nav primary">
                <i class="fas fa-search-plus me-2"></i>Advanced Search
            </a>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sort results functionality
        function sortResults(criteria) {
            const grid = document.getElementById('workerGrid');
            const cards = Array.from(grid.children);
            
            cards.sort((a, b) => {
                switch(criteria) {
                    case 'salary_high':
                        return parseInt(b.dataset.salary) - parseInt(a.dataset.salary);
                    case 'salary_low':
                        return parseInt(a.dataset.salary) - parseInt(b.dataset.salary);
                    case 'experience':
                        return parseInt(b.dataset.experience) - parseInt(a.dataset.experience);
                    default:
                        return 0;
                }
            });
            
            // Re-append sorted cards
            cards.forEach(card => grid.appendChild(card));
            
            // Add animation
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.style.animation = 'fadeInUp 0.5s ease forwards';
            });
        }
        
        // Contact worker functionality
        function contactWorker(userId) {
            // This would typically open a contact modal or redirect to messaging
            alert('Contact feature will be implemented. Worker ID: ' + userId);
        }
        
        // Add hover effects to cards
        document.querySelectorAll('.worker-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
        
        // Add loading animation when buttons are clicked
        document.querySelectorAll('.btn-view, .btn-nav').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (this.href) {
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
                    
                    // Reset after a delay (in case navigation doesn't happen immediately)
                    setTimeout(() => {
                        this.innerHTML = originalText;
                    }, 3000);
                }
            });
        });
        
        // Add CSS animations
        const style = document.createElement('style');
        style.textContent = `
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
        `;
        document.head.appendChild(style);
        
        // Initialize cards with animation
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.worker-card').forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.style.animation = 'fadeInUp 0.6s ease forwards';
            });
        });
    </script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
