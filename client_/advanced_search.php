<?php
require_once '../Shared/config.php';

// Check if user is logged in and is a client
if (!isLoggedIn()) {
    redirect('../Shared/login_form.php');
}

if (getCurrentUserType() !== 'User') {
    redirect('../Shared/login_form.php?error=unauthorized');
}

$user_id = getCurrentUserId();
$user_name = $_SESSION['user_name'];

// Get search parameters
$work_type = sanitizeInput($_GET['work_type'] ?? '');
$city = sanitizeInput($_GET['city'] ?? '');
$min_salary = (int)($_GET['min_salary'] ?? 0);
$max_salary = (int)($_GET['max_salary'] ?? 100000);
$experience = sanitizeInput($_GET['experience'] ?? '');
$sort_by = sanitizeInput($_GET['sort_by'] ?? 'salary');
$sort_order = sanitizeInput($_GET['sort_order'] ?? 'DESC');

try {
    $db = Database::getInstance();
    
    // Build search query
    $conditions = [];
    $params = [];
    $param_types = '';
    
    if (!empty($work_type)) {
        $conditions[] = "lp.workType LIKE ?";
        $params[] = "%$work_type%";
        $param_types .= 's';
    }
    
    if (!empty($city)) {
        $conditions[] = "lp.city LIKE ?";
        $params[] = "%$city%";
        $param_types .= 's';
    }
    
    if ($min_salary > 0) {
        $conditions[] = "CAST(lp.salary AS UNSIGNED) >= ?";
        $params[] = $min_salary;
        $param_types .= 'i';
    }
    
    if ($max_salary < 100000) {
        $conditions[] = "CAST(lp.salary AS UNSIGNED) <= ?";
        $params[] = $max_salary;
        $param_types .= 'i';
    }
    
    if (!empty($experience)) {
        $conditions[] = "lp.experience LIKE ?";
        $params[] = "%$experience%";
        $param_types .= 's';
    }
    
    // Validate sort parameters
    $allowed_sort = ['salary', 'experience', 'workType', 'city'];
    $allowed_order = ['ASC', 'DESC'];
    
    if (!in_array($sort_by, $allowed_sort)) $sort_by = 'salary';
    if (!in_array($sort_order, $allowed_order)) $sort_order = 'DESC';
    
    $where_clause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
    
    $sql = "
        SELECT lp.*, u.user_name, u.email_id, u.mobile_no,
               AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
        FROM lab_post lp 
        JOIN user u ON lp.user_ID = u.user_ID 
        LEFT JOIN hires h ON h.labour_id = u.user_ID
        LEFT JOIN ratings r ON r.labour_id = u.user_ID
        $where_clause 
        GROUP BY lp.l_post_ID, u.user_ID
        ORDER BY $sort_by $sort_order
        LIMIT 50
    ";
    
    $stmt = $db->prepare($sql);
    
    if (!empty($params)) {
        $stmt->bind_param($param_types, ...$params);
    }
    
    $stmt->execute();
    $labourers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get filter options
    $work_types_query = "SELECT DISTINCT workType FROM lab_post WHERE workType != '' ORDER BY workType";
    $work_types = $db->query($work_types_query)->fetch_all(MYSQLI_ASSOC);
    
    $cities_query = "SELECT DISTINCT city FROM lab_post WHERE city != '' ORDER BY city";
    $cities = $db->query($cities_query)->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    error_log('Search Error: ' . $e->getMessage());
    $labourers = [];
    $work_types = $cities = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Search - D Labour Chowk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            color: white !important;
            font-weight: 600;
            font-size: 1.5rem;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: white !important;
        }

        .search-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .search-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .search-header h2 {
            color: #333;
            margin-bottom: 10px;
        }

        .search-header p {
            color: #666;
        }

        .filter-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .filter-title {
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e1e8ed;
            padding: 12px 15px;
            transition: all 0.3s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-search {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
        }

        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .results-header {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .worker-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s;
            border-left: 4px solid #667eea;
        }

        .worker-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .worker-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .worker-info h5 {
            color: #333;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .worker-meta {
            color: #666;
            font-size: 0.9rem;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 15px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .worker-salary {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 1.1rem;
        }

        .rating-display {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #ffc107;
        }

        .worker-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-hire {
            background: linear-gradient(135deg, #28a745, #20c997);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-hire:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
            color: white;
        }

        .btn-contact {
            background: linear-gradient(135deg, #17a2b8, #138496);
            border: none;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-contact:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(23, 162, 184, 0.4);
            color: white;
        }

        .no-results {
            text-align: center;
            padding: 60px;
            color: #666;
        }

        .no-results i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .salary-range-display {
            text-align: center;
            margin-top: 10px;
            color: #667eea;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .search-container {
                margin: 15px;
                padding: 20px;
            }
            
            .worker-header {
                flex-direction: column;
                gap: 10px;
            }
            
            .worker-actions {
                flex-direction: column;
            }
            
            .results-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-briefcase"></i> D Labour Chowk
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
                <a class="nav-link" href="availableLabour.php">Browse Workers</a>
                <a class="nav-link" href="../Shared/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <!-- Search Form -->
        <div class="search-container">
            <div class="search-header">
                <h2><i class="bi bi-search"></i> Advanced Worker Search</h2>
                <p>Find the perfect worker for your project with advanced filters</p>
            </div>

            <form method="GET" action="">
                <div class="filter-section">
                    <h5 class="filter-title">
                        <i class="bi bi-funnel"></i> Search Filters
                    </h5>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Work Type</label>
                            <select class="form-select" name="work_type">
                                <option value="">All Work Types</option>
                                <?php foreach ($work_types as $type): ?>
                                    <option value="<?php echo htmlspecialchars($type['workType']); ?>" 
                                            <?php echo $work_type === $type['workType'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(ucfirst($type['workType'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">City</label>
                            <select class="form-select" name="city">
                                <option value="">All Cities</option>
                                <?php foreach ($cities as $c): ?>
                                    <option value="<?php echo htmlspecialchars($c['city']); ?>" 
                                            <?php echo $city === $c['city'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(ucfirst($c['city'])); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Minimum Salary (₹/day)</label>
                            <input type="range" class="form-range" name="min_salary" min="0" max="10000" 
                                   value="<?php echo $min_salary; ?>" id="minSalaryRange">
                            <div class="salary-range-display" id="minSalaryDisplay">₹<?php echo number_format($min_salary); ?></div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Maximum Salary (₹/day)</label>
                            <input type="range" class="form-range" name="max_salary" min="1000" max="20000" 
                                   value="<?php echo $max_salary; ?>" id="maxSalaryRange">
                            <div class="salary-range-display" id="maxSalaryDisplay">₹<?php echo number_format($max_salary); ?></div>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Experience</label>
                            <input type="text" class="form-control" name="experience" 
                                   placeholder="e.g., 5 years, Experienced" 
                                   value="<?php echo htmlspecialchars($experience); ?>">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sort By</label>
                            <div class="row">
                                <div class="col-8">
                                    <select class="form-select" name="sort_by">
                                        <option value="salary" <?php echo $sort_by === 'salary' ? 'selected' : ''; ?>>Salary</option>
                                        <option value="experience" <?php echo $sort_by === 'experience' ? 'selected' : ''; ?>>Experience</option>
                                        <option value="workType" <?php echo $sort_by === 'workType' ? 'selected' : ''; ?>>Work Type</option>
                                        <option value="city" <?php echo $sort_by === 'city' ? 'selected' : ''; ?>>City</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <select class="form-select" name="sort_order">
                                        <option value="DESC" <?php echo $sort_order === 'DESC' ? 'selected' : ''; ?>>High to Low</option>
                                        <option value="ASC" <?php echo $sort_order === 'ASC' ? 'selected' : ''; ?>>Low to High</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-search">
                                <i class="bi bi-search"></i> Search Workers
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results -->
        <div class="results-header">
            <h4><i class="bi bi-people"></i> Search Results (<?php echo count($labourers); ?> found)</h4>
            <?php if (!empty($labourers)): ?>
                <div class="text-muted">
                    Sorted by <?php echo ucfirst($sort_by); ?> (<?php echo $sort_order === 'DESC' ? 'High to Low' : 'Low to High'; ?>)
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($labourers)): ?>
            <div class="no-results">
                <i class="bi bi-search"></i>
                <h4>No Workers Found</h4>
                <p>Try adjusting your search filters to find more workers.</p>
                <a href="?" class="btn btn-search" style="width: auto;">
                    <i class="bi bi-arrow-clockwise"></i> Clear Filters
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($labourers as $labour): ?>
                <div class="worker-card">
                    <div class="worker-header">
                        <div class="worker-info">
                            <h5><?php echo htmlspecialchars($labour['user_name']); ?></h5>
                            <div class="worker-meta">
                                <span class="meta-item">
                                    <i class="bi bi-tools"></i>
                                    <?php echo htmlspecialchars(ucfirst($labour['workType'])); ?>
                                </span>
                                <span class="meta-item">
                                    <i class="bi bi-geo-alt"></i>
                                    <?php echo htmlspecialchars($labour['city']); ?>
                                </span>
                                <span class="meta-item">
                                    <i class="bi bi-clock"></i>
                                    <?php echo htmlspecialchars($labour['experience']); ?>
                                </span>
                                <span class="meta-item">
                                    <i class="bi bi-phone"></i>
                                    <?php echo htmlspecialchars($labour['mobile_no']); ?>
                                </span>
                                <?php if ($labour['avg_rating']): ?>
                                    <span class="meta-item rating-display">
                                        <i class="bi bi-star-fill"></i>
                                        <?php echo number_format($labour['avg_rating'], 1); ?>
                                        <span class="text-muted">(<?php echo $labour['review_count']; ?> reviews)</span>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="worker-salary">
                            ₹<?php echo number_format($labour['salary']); ?>/day
                        </div>
                    </div>
                    
                    <div class="worker-description">
                        <p class="text-muted mb-3">
                            <i class="bi bi-geo-alt-fill"></i>
                            <?php echo htmlspecialchars($labour['location']); ?>
                        </p>
                    </div>
                    
                    <div class="worker-actions">
                        <a href="hire_labor.php?labour_id=<?php echo $labour['user_ID']; ?>" class="btn-hire">
                            <i class="bi bi-person-plus"></i> Hire Now
                        </a>
                        <a href="get_profile.php?labour_id=<?php echo $labour['user_ID']; ?>" class="btn-contact">
                            <i class="bi bi-eye"></i> View Profile
                        </a>
                        <a href="tel:<?php echo $labour['mobile_no']; ?>" class="btn-contact">
                            <i class="bi bi-telephone"></i> Call
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update salary range displays
        document.getElementById('minSalaryRange').addEventListener('input', function() {
            document.getElementById('minSalaryDisplay').textContent = '₹' + parseInt(this.value).toLocaleString();
        });
        
        document.getElementById('maxSalaryRange').addEventListener('input', function() {
            document.getElementById('maxSalaryDisplay').textContent = '₹' + parseInt(this.value).toLocaleString();
        });
        
        // Auto-submit form on range change (optional)
        const ranges = document.querySelectorAll('input[type="range"]');
        ranges.forEach(range => {
            range.addEventListener('change', function() {
                // Uncomment to auto-submit on range change
                // document.querySelector('form').submit();
            });
        });
    </script>
</body>
</html>
