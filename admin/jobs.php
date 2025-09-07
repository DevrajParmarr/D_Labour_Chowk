<?php
require_once '../Shared/config.php';

// Check if user is logged in and is an admin
if (!isLoggedIn()) {
    redirect('../Shared/login_form.php');
}

if (getCurrentUserType() !== 'Admin') {
    redirect('../Shared/login_form.php?error=unauthorized');
}

$db = Database::getInstance();
$conn = $db->getConnection();

// Handle job actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validateCSRFToken($csrf_token)) {
        $error = "Invalid CSRF token";
    } else {
        $action = $_POST['action'] ?? '';
        $job_id = $_POST['job_id'] ?? 0;

        switch ($action) {
            case 'delete':
                // Check if job has applications
                $stmt = $conn->prepare("SELECT COUNT(*) as app_count FROM job_applications WHERE job_post_id = ?");
                $stmt->bind_param("i", $job_id);
                $stmt->execute();
                $app_count = $stmt->get_result()->fetch_assoc()['app_count'];

                if ($app_count > 0) {
                    $error = "Cannot delete job with active applications";
                } else {
                    $stmt = $conn->prepare("DELETE FROM job_post WHERE post_ID = ?");
                    $stmt->bind_param("i", $job_id);
                    if ($stmt->execute()) {
                        $success = "Job deleted successfully";
                    } else {
                        $error = "Failed to delete job";
                    }
                }
                break;
        }
    }
}

// Get jobs with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

// Get total jobs count
$total_jobs = $conn->query("SELECT COUNT(*) as count FROM job_post")->fetch_assoc()['count'];
$total_pages = ceil($total_jobs / $per_page);

// Get jobs for current page
$stmt = $conn->prepare("
    SELECT jp.*, u.user_name, u.email_id,
           COUNT(ja.application_id) as application_count,
           COUNT(CASE WHEN ja.status = 'accepted' THEN 1 END) as accepted_count
    FROM job_post jp
    JOIN user u ON jp.owner = u.user_ID
    LEFT JOIN job_applications ja ON jp.post_ID = ja.job_post_id
    GROUP BY jp.post_ID
    ORDER BY jp.createdDate DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("ii", $per_page, $offset);
$stmt->execute();
$jobs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get job statistics
$job_stats = $conn->query("
    SELECT
        COUNT(*) as total_jobs,
        COUNT(*) as active_jobs,
        0 as pending_jobs,
        0 as rejected_jobs
    FROM job_post
")->fetch_assoc();

$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #4f46e5;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        .admin-navbar {
            background: var(--gradient-primary);
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .admin-navbar .navbar-brand {
            color: white !important;
            font-weight: 700;
            font-size: 1.5rem;
        }

        .admin-navbar .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            transition: color 0.3s;
        }

        .admin-navbar .nav-link:hover {
            color: white !important;
        }

        .page-header {
            background: white;
            padding: 2rem;
            margin: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border-left: 4px solid var(--primary-color);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6b7280;
            font-weight: 600;
        }

        .jobs-table {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .job-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }

        .job-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .job-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .job-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .job-meta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.9rem;
            color: #6b7280;
        }

        .job-description {
            color: #4b5563;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .job-stats {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .stat-item {
            background: #f3f4f6;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            color: #6b7280;
        }

        .job-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .status-pending {
            background: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }

        .status-rejected {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .btn-action {
            padding: 0.375rem 0.75rem;
            font-size: 0.8rem;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-approve {
            background: var(--success-color);
            color: white;
        }

        .btn-approve:hover {
            background: #059669;
        }

        .btn-reject {
            background: var(--danger-color);
            color: white;
        }

        .btn-reject:hover {
            background: #dc2626;
        }

        .btn-delete {
            background: #6b7280;
            color: white;
        }

        .btn-delete:hover {
            background: #4b5563;
        }

        .pagination {
            justify-content: center;
            margin-top: 2rem;
        }

        .alert {
            margin: 2rem;
            border-radius: 12px;
            border: none;
        }

        .filters {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .job-header {
                flex-direction: column;
                gap: 1rem;
            }

            .action-buttons {
                justify-content: center;
            }

            .job-meta {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Navigation -->
    <nav class="navbar navbar-expand-lg admin-navbar">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-crown me-2"></i>Admin Panel
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-home me-1"></i>Dashboard
                </a>
                <a class="nav-link" href="users.php">
                    <i class="fas fa-users me-1"></i>Users
                </a>
                <a class="nav-link active" href="jobs.php">
                    <i class="fas fa-briefcase me-1"></i>Jobs
                </a>
                <a class="nav-link" href="analytics.php">
                    <i class="fas fa-chart-bar me-1"></i>Analytics
                </a>
                <a class="nav-link" href="settings.php">
                    <i class="fas fa-cog me-1"></i>Settings
                </a>
                <a class="nav-link" href="../Shared/logout.php">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <div class="page-header">
        <h1><i class="fas fa-briefcase me-3"></i>Job Management</h1>
        <p>Review, approve, and manage all job postings on the platform.</p>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($success)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
        </div>
    <?php endif; ?>

    <!-- Statistics -->
    <div class="container-fluid px-4">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $job_stats['total_jobs']; ?></div>
                <div class="stat-label">Total Jobs</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $job_stats['active_jobs']; ?></div>
                <div class="stat-label">Active Jobs</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $job_stats['pending_jobs']; ?></div>
                <div class="stat-label">Pending Review</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $job_stats['rejected_jobs']; ?></div>
                <div class="stat-label">Rejected Jobs</div>
            </div>
        </div>
    </div>

    <!-- Jobs Management -->
    <div class="jobs-table">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4><i class="fas fa-list me-2"></i>All Job Posts</h4>
            <div class="d-flex gap-2">
                <input type="text" class="form-control" placeholder="Search jobs..." style="width: 250px;">
                <select class="form-select" style="width: 150px;">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>

        <?php if (empty($jobs)): ?>
            <div class="text-center py-5">
                <i class="fas fa-briefcase" style="font-size: 3rem; opacity: 0.3;"></i>
                <p class="mt-3 text-muted">No job posts found.</p>
            </div>
        <?php else: ?>
            <?php foreach ($jobs as $job): ?>
                <div class="job-card">
                    <div class="job-header">
                        <div>
                            <h5 class="job-title"><?php echo htmlspecialchars($job['jobTitle']); ?></h5>
                            <div class="job-meta">
                                <div class="meta-item">
                                    <i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($job['user_name']); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-envelope"></i>
                                    <?php echo htmlspecialchars($job['email_id']); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($job['city']); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('M d, Y', strtotime($job['post_ID'])); ?>
                                </div>
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="job-status status-active">
                                Active
                            </div>
                        </div>
                    </div>

                    <div class="job-description">
                        <?php echo htmlspecialchars(substr($job['detail'], 0, 200) . (strlen($job['detail']) > 200 ? '...' : '')); ?>
                    </div>

                    <div class="job-stats">
                        <span class="stat-item">
                            <i class="fas fa-rupee-sign me-1"></i><?php echo number_format($job['salary']); ?>/month
                        </span>
                        <span class="stat-item">
                            <i class="fas fa-paper-plane me-1"></i><?php echo $job['application_count']; ?> applications
                        </span>
                        <span class="stat-item">
                            <i class="fas fa-check-circle me-1"></i><?php echo $job['accepted_count']; ?> accepted
                        </span>
                    </div>

                    <div class="action-buttons">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <input type="hidden" name="job_id" value="<?php echo $job['post_ID']; ?>">
                            <button type="submit" name="action" value="delete" class="btn-action btn-delete"
                                    onclick="return confirm('Are you sure you want to delete this job post?')" title="Delete Job">
                                <i class="fas fa-trash me-1"></i>Delete
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <nav aria-label="Job pagination">
                    <ul class="pagination">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 5000);
    </script>
</body>
</html>