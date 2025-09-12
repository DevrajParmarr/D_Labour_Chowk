<?php
require_once '../Shared/config.php';

// Check if user is logged in
if (!isLoggedIn() || getCurrentUserType() !== 'User') {
    redirect('../Shared/login_form.php');
}

$user_id = getCurrentUserId();

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$work_types = isset($_GET['work_types']) ? $_GET['work_types'] : [];
$city = isset($_GET['city']) ? trim($_GET['city']) : '';
$min_salary = isset($_GET['min_salary']) ? (int)$_GET['min_salary'] : '';
$max_salary = isset($_GET['max_salary']) ? (int)$_GET['max_salary'] : '';
$min_experience = isset($_GET['min_experience']) ? (int)$_GET['min_experience'] : '';
$max_experience = isset($_GET['max_experience']) ? (int)$_GET['max_experience'] : '';
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'relevance';
$availability = isset($_GET['availability']) ? $_GET['availability'] : '';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

try {
    $db = Database::getInstance();

    // Build the query
    $query = "
        SELECT lp.*, u.user_name, u.mobile_no, u.email_id, u.date_created,
               AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
        FROM lab_post lp
        JOIN user u ON lp.user_ID = u.user_ID
        LEFT JOIN ratings r ON r.labour_id = lp.user_ID
        WHERE u.Verified = 1
    ";

    $params = [];
    $types = '';

    // Add search filter
    if (!empty($search)) {
        $query .= " AND (lp.workType LIKE ? OR u.user_name LIKE ? OR lp.city LIKE ? OR lp.location LIKE ?)";
        $search_param = "%$search%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
        $types .= 'ssss';
    }

    // Add work type filters
    if (!empty($work_types)) {
        $placeholders = str_repeat('?,', count($work_types) - 1) . '?';
        $query .= " AND lp.workType IN ($placeholders)";
        $params = array_merge($params, $work_types);
        $types .= str_repeat('s', count($work_types));
    }

    // Add city filter
    if (!empty($city)) {
        $query .= " AND lp.city = ?";
        $params[] = $city;
        $types .= 's';
    }

    // Add salary filters
    if (!empty($min_salary)) {
        $query .= " AND lp.salary >= ?";
        $params[] = $min_salary;
        $types .= 'i';
    }
    if (!empty($max_salary)) {
        $query .= " AND lp.salary <= ?";
        $params[] = $max_salary;
        $types .= 'i';
    }

    // Add experience filters
    if (!empty($min_experience)) {
        $query .= " AND CAST(lp.experience AS UNSIGNED) >= ?";
        $params[] = $min_experience;
        $types .= 'i';
    }
    if (!empty($max_experience)) {
        $query .= " AND CAST(lp.experience AS UNSIGNED) <= ?";
        $params[] = $max_experience;
        $types .= 'i';
    }

    // Add availability filter (placeholder for future implementation)
    if (!empty($availability)) {
        // This would filter based on availability status
        // For now, we'll skip this as the database doesn't have availability field
    }

    $query .= " GROUP BY lp.l_post_ID";

    // Add sorting
    switch ($sort_by) {
        case 'salary_high':
            $query .= " ORDER BY lp.salary DESC";
            break;
        case 'salary_low':
            $query .= " ORDER BY lp.salary ASC";
            break;
        case 'experience':
            $query .= " ORDER BY CAST(lp.experience AS UNSIGNED) DESC";
            break;
        case 'rating':
            $query .= " ORDER BY avg_rating DESC";
            break;
        case 'newest':
            $query .= " ORDER BY lp.l_post_ID DESC";
            break;
        case 'name':
            $query .= " ORDER BY u.user_name ASC";
            break;
        default:
            $query .= " ORDER BY avg_rating DESC, lp.salary DESC";
    }

    // Get total count for pagination
    $count_query = "
        SELECT COUNT(DISTINCT lp.l_post_ID) as total
        FROM lab_post lp
        JOIN user u ON lp.user_ID = u.user_ID
        LEFT JOIN ratings r ON r.labour_id = lp.user_ID
        WHERE u.Verified = 1
    ";

    $params_count = [];
    $types_count = '';

    // Add search filter
    if (!empty($search)) {
        $count_query .= " AND (lp.workType LIKE ? OR u.user_name LIKE ? OR lp.city LIKE ? OR lp.location LIKE ?)";
        $search_param = "%$search%";
        $params_count = array_merge($params_count, [$search_param, $search_param, $search_param, $search_param]);
        $types_count .= 'ssss';
    }

    // Add work type filters
    if (!empty($work_types)) {
        $placeholders = str_repeat('?,', count($work_types) - 1) . '?';
        $count_query .= " AND lp.workType IN ($placeholders)";
        $params_count = array_merge($params_count, $work_types);
        $types_count .= str_repeat('s', count($work_types));
    }

    // Add city filter
    if (!empty($city)) {
        $count_query .= " AND lp.city = ?";
        $params_count[] = $city;
        $types_count .= 's';
    }

    // Add salary filters
    if (!empty($min_salary)) {
        $count_query .= " AND lp.salary >= ?";
        $params_count[] = $min_salary;
        $types_count .= 'i';
    }
    if (!empty($max_salary)) {
        $count_query .= " AND lp.salary <= ?";
        $params_count[] = $max_salary;
        $types_count .= 'i';
    }

    // Add experience filters
    if (!empty($min_experience)) {
        $count_query .= " AND CAST(lp.experience AS UNSIGNED) >= ?";
        $params_count[] = $min_experience;
        $types_count .= 'i';
    }
    if (!empty($max_experience)) {
        $count_query .= " AND CAST(lp.experience AS UNSIGNED) <= ?";
        $params_count[] = $max_experience;
        $types_count .= 'i';
    }

    $count_stmt = $db->prepare($count_query);
    if (!empty($params_count)) {
        $count_stmt->bind_param($types_count, ...$params_count);
    }
    $count_stmt->execute();
    $count_result = $count_stmt->get_result()->fetch_assoc();
    $total_results = $count_result ? ($count_result['total'] ?? 0) : 0;
    $total_pages = ceil($total_results / $per_page);

    // Add pagination to main query
    $query .= " LIMIT ? OFFSET ?";
    $params[] = $per_page;
    $params[] = $offset;
    $types .= 'ii';

    // Execute main query
    $stmt = $db->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $results = $stmt->get_result();

    // Get available work types for filter
    $work_types_query = "SELECT DISTINCT workType FROM lab_post ORDER BY workType";
    $work_types_result = $db->query($work_types_query);

    // Get available cities for filter
    $cities_query = "SELECT DISTINCT city FROM lab_post ORDER BY city";
    $cities_result = $db->query($cities_query);

} catch (Exception $e) {
    error_log('Advanced Search Error: ' . $e->getMessage());
    $results = [];
    $total_results = 0;
    $total_pages = 0;
    $work_types_result = [];
    $cities_result = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Search - D Labour Chowk</title>
    <meta name="description" content="Advanced search for skilled workers with multiple filters and sorting options.">

    <!-- Enhanced CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #f59e0b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
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
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }

        /* Header */
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
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem;
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
        }

        /* Filters Sidebar */
        .filters-sidebar {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            height: fit-content;
            position: sticky;
            top: 2rem;
        }

        .filters-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: #1f2937;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-group {
            margin-bottom: 2rem;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 1.5rem;
        }

        .filter-group:last-child {
            border-bottom: none;
        }

        .filter-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 1rem;
            display: block;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .filter-input, .filter-select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            background: white;
        }

        .filter-input:focus, .filter-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .checkbox-item input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--primary-color);
        }

        .checkbox-item label {
            font-size: 0.9rem;
            color: #6b7280;
            cursor: pointer;
            margin: 0;
        }

        .btn-apply-filters {
            width: 100%;
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-apply-filters:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4);
        }

        .btn-clear-filters {
            width: 100%;
            background: transparent;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            padding: 0.75rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 0.5rem;
        }

        .btn-clear-filters:hover {
            background: var(--primary-color);
            color: white;
        }

        /* Results Section */
        .results-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
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

        .sort-controls {
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

        /* Worker Grid */
        .worker-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .worker-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid #f1f5f9;
            position: relative;
        }

        .worker-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
            border-color: var(--primary-color);
        }

        .worker-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-bottom: 1px solid #f1f5f9;
        }

        .worker-name {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }

        .worker-type {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: capitalize;
        }

        .worker-rating {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-top: 0.5rem;
        }

        .stars {
            color: #fbbf24;
            font-size: 0.9rem;
        }

        .rating-text {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .worker-details {
            padding: 1.5rem;
        }

        .detail-row {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
            font-size: 0.9rem;
        }

        .detail-icon {
            width: 16px;
            margin-right: 0.75rem;
            color: var(--primary-color);
            text-align: center;
        }

        .detail-label {
            font-weight: 600;
            color: #374151;
            margin-right: 0.5rem;
            min-width: 70px;
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
            padding: 1.5rem;
            padding-top: 0;
            display: flex;
            gap: 0.75rem;
        }

        .btn-view-profile {
            flex: 1;
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            text-decoration: none;
            text-align: center;
        }

        .btn-view-profile:hover {
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
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-contact:hover {
            background: rgba(37, 99, 235, 0.2);
            transform: translateY(-1px);
        }

        /* No Results */
        .no-results {
            text-align: center;
            padding: 4rem 2rem;
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
            margin-top: 3rem;
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

        /* Loading */
        .loading {
            text-align: center;
            padding: 2rem;
        }

        .loading-spinner {
            font-size: 2rem;
            color: var(--primary-color);
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .main-container {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .filters-sidebar {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .worker-grid {
                grid-template-columns: 1fr;
            }

            .results-header {
                flex-direction: column;
                align-items: stretch;
            }

            .sort-controls {
                justify-content: center;
            }

            .main-title {
                font-size: 2rem;
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
    <!-- Header -->
    <div class="header-section">
        <div class="header-content">
            <h1 class="main-title">
                <i class="fas fa-search-plus me-3"></i>Advanced Search
            </h1>
            <p class="main-subtitle">
                Find the perfect skilled worker with our advanced filtering and search options.
                Refine your search to get exactly what you need.
            </p>
        </div>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Filters Sidebar -->
        <div class="filters-sidebar">
            <h3 class="filters-title">
                <i class="fas fa-filter"></i>
                Filters
            </h3>

            <form id="filterForm" method="GET" action="">
                <!-- Search -->
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-search me-2"></i>Search
                    </label>
                    <input type="text" class="filter-input" name="search" placeholder="Name, work type, location..."
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <!-- Work Types -->
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-tools me-2"></i>Work Types
                    </label>
                    <div class="checkbox-group">
                        <?php while ($work_type = $work_types_result->fetch_assoc()): ?>
                            <div class="checkbox-item">
                                <input type="checkbox" name="work_types[]" value="<?php echo htmlspecialchars($work_type['workType']); ?>"
                                       id="work_<?php echo htmlspecialchars($work_type['workType']); ?>"
                                       <?php if (in_array($work_type['workType'], $work_types)) echo 'checked'; ?>>
                                <label for="work_<?php echo htmlspecialchars($work_type['workType']); ?>">
                                    <?php echo htmlspecialchars($work_type['workType']); ?>
                                </label>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>

                <!-- City -->
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-map-marker-alt me-2"></i>City
                    </label>
                    <select class="filter-select" name="city">
                        <option value="">All Cities</option>
                        <?php while ($city_option = $cities_result->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($city_option['city']); ?>"
                                    <?php if ($city === $city_option['city']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($city_option['city']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <!-- Salary Range -->
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-rupee-sign me-2"></i>Salary Range
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 0.5rem; align-items: center;">
                        <input type="number" class="filter-input" name="min_salary" placeholder="Min"
                               value="<?php echo $min_salary; ?>" min="0">
                        <span style="color: #6b7280;">to</span>
                        <input type="number" class="filter-input" name="max_salary" placeholder="Max"
                               value="<?php echo $max_salary; ?>" min="0">
                    </div>
                </div>

                <!-- Experience -->
                <div class="filter-group">
                    <label class="filter-label">
                        <i class="fas fa-clock me-2"></i>Experience (Years)
                    </label>
                    <div style="display: grid; grid-template-columns: 1fr auto 1fr; gap: 0.5rem; align-items: center;">
                        <input type="number" class="filter-input" name="min_experience" placeholder="Min"
                               value="<?php echo $min_experience; ?>" min="0">
                        <span style="color: #6b7280;">to</span>
                        <input type="number" class="filter-input" name="max_experience" placeholder="Max"
                               value="<?php echo $max_experience; ?>" min="0">
                    </div>
                </div>

                <button type="submit" class="btn-apply-filters">
                    <i class="fas fa-search me-2"></i>Apply Filters
                </button>
                <button type="button" class="btn-clear-filters" onclick="clearFilters()">
                    <i class="fas fa-times me-2"></i>Clear All
                </button>
            </form>
        </div>

        <!-- Results Section -->
        <div class="results-section">
            <div class="results-header">
                <div class="results-count">
                    <i class="fas fa-users me-2"></i>
                    <?php echo $total_results; ?> worker<?php echo $total_results !== 1 ? 's' : ''; ?> found
                </div>

                <div class="sort-controls">
                    <label for="sort_by" style="font-weight: 600; color: #374151;">Sort by:</label>
                    <select class="sort-select" id="sort_by" onchange="changeSort(this.value)">
                        <option value="relevance" <?php if ($sort_by === 'relevance') echo 'selected'; ?>>Relevance</option>
                        <option value="rating" <?php if ($sort_by === 'rating') echo 'selected'; ?>>Rating</option>
                        <option value="salary_high" <?php if ($sort_by === 'salary_high') echo 'selected'; ?>>Salary: High to Low</option>
                        <option value="salary_low" <?php if ($sort_by === 'salary_low') echo 'selected'; ?>>Salary: Low to High</option>
                        <option value="experience" <?php if ($sort_by === 'experience') echo 'selected'; ?>>Experience</option>
                        <option value="newest" <?php if ($sort_by === 'newest') echo 'selected'; ?>>Newest</option>
                        <option value="name" <?php if ($sort_by === 'name') echo 'selected'; ?>>Name</option>
                    </select>
                </div>
            </div>

            <?php if ($results->num_rows > 0): ?>
                <div class="worker-grid">
                    <?php while ($worker = $results->fetch_assoc()): ?>
                        <div class="worker-card fade-in-up">
                            <div class="worker-header">
                                <h3 class="worker-name"><?php echo htmlspecialchars($worker['user_name']); ?></h3>
                                <div class="worker-type"><?php echo htmlspecialchars($worker['workType']); ?></div>
                                <div class="worker-rating">
                                    <div class="stars">
                                        <?php
                                        $rating = round($worker['avg_rating'] ?? 0, 1);
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $rating) {
                                                echo '<i class="fas fa-star"></i>';
                                            } elseif ($i - 0.5 <= $rating) {
                                                echo '<i class="fas fa-star-half-alt"></i>';
                                            } else {
                                                echo '<i class="far fa-star"></i>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <span class="rating-text">
                                        <?php echo $rating > 0 ? $rating : 'No rating'; ?>
                                        <?php if ($worker['review_count'] > 0): ?>
                                            (<?php echo $worker['review_count']; ?> reviews)
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="worker-details">
                                <div class="detail-row">
                                    <i class="fas fa-map-marker-alt detail-icon"></i>
                                    <span class="detail-label">Location:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($worker['city']); ?></span>
                                </div>

                                <div class="detail-row">
                                    <i class="fas fa-rupee-sign detail-icon"></i>
                                    <span class="detail-label">Salary:</span>
                                    <span class="detail-value salary-value">₹<?php echo number_format($worker['salary']); ?>/month</span>
                                </div>

                                <div class="detail-row">
                                    <i class="fas fa-clock detail-icon"></i>
                                    <span class="detail-label">Experience:</span>
                                    <span class="detail-value"><?php echo htmlspecialchars($worker['experience']); ?> years</span>
                                </div>

                                <div class="detail-row">
                                    <i class="fas fa-calendar detail-icon"></i>
                                    <span class="detail-label">Joined:</span>
                                    <span class="detail-value"><?php echo date('M Y', strtotime($worker['date_created'])); ?></span>
                                </div>
                            </div>

                            <div class="worker-actions">
                                <a href="profile.php?user_id=<?php echo $worker['user_ID']; ?>" class="btn-view-profile">
                                    <i class="fas fa-user me-2"></i>View Profile
                                </a>
                                <button class="btn-contact" onclick="contactWorker(<?php echo $worker['user_ID']; ?>)">
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
                    <h3 class="no-results-title">No Workers Found</h3>
                    <p class="no-results-text">
                        Try adjusting your search criteria or clearing some filters to see more results.
                    </p>
                </div>
            <?php endif; ?>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination-section">
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        <?php endif; ?>

                        <?php
                        $start = max(1, $page - 2);
                        $end = min($total_pages, $page + 2);

                        if ($start > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">1</a>
                            <?php if ($start > 2): ?>
                                <span>...</span>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php for ($i = $start; $i <= $end; $i++): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"
                               class="<?php if ($i == $page) echo 'active'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($end < $total_pages): ?>
                            <?php if ($end < $total_pages - 1): ?>
                                <span>...</span>
                            <?php endif; ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $total_pages])); ?>">
                                <?php echo $total_pages; ?>
                            </a>
                        <?php endif; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
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

        // Change sort function
        function changeSort(sortValue) {
            const url = new URL(window.location);
            url.searchParams.set('sort_by', sortValue);
            url.searchParams.set('page', '1'); // Reset to first page
            window.location.href = url.toString();
        }

        // Clear filters function
        function clearFilters() {
            const url = new URL(window.location);
            url.search = '?page=1'; // Keep only page parameter
            window.location.href = url.toString();
        }

        // Contact worker function
        function contactWorker(userId) {
            // Show toast notification instead of alert
            showToast('Contact feature will be implemented soon. Worker ID: ' + userId, 'info');
        }

        // Add loading states to buttons
        document.querySelectorAll('.btn-view-profile').forEach(btn => {
            btn.addEventListener('click', function() {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Loading...';
                this.disabled = true;

                // Reset after navigation delay
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 3000);
            });
        });

        // Add hover effects to cards
        document.querySelectorAll('.worker-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Auto-submit form on checkbox change (optional)
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Uncomment below line to auto-submit on checkbox change
                // document.getElementById('filterForm').submit();
            });
        });

        // Add staggered animation to cards
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.worker-card').forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
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
