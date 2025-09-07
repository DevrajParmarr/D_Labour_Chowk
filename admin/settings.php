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

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';

    if (!validateCSRFToken($csrf_token)) {
        $error = "Invalid CSRF token";
    } else {
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'update_general':
                // Update general settings
                $app_name = sanitizeInput($_POST['app_name']);
                $app_url = sanitizeInput($_POST['app_url']);
                $session_timeout = (int)$_POST['session_timeout'];
                $max_file_size = (int)$_POST['max_file_size'];

                // Update config file (simplified - in real app, use database)
                $config_content = file_get_contents('../Shared/config.php');
                $config_content = preg_replace("/define\('APP_NAME', '[^']+'\);/", "define('APP_NAME', '$app_name');", $config_content);
                $config_content = preg_replace("/define\('APP_URL', '[^']+'\);/", "define('APP_URL', '$app_url');", $config_content);
                $config_content = preg_replace("/define\('SESSION_TIMEOUT', [0-9]+\);/", "define('SESSION_TIMEOUT', $session_timeout);", $config_content);
                $config_content = preg_replace("/define\('MAX_FILE_SIZE', [0-9]+\);/", "define('MAX_FILE_SIZE', $max_file_size);", $config_content);

                if (file_put_contents('../Shared/config.php', $config_content)) {
                    $success = "General settings updated successfully";
                } else {
                    $error = "Failed to update general settings";
                }
                break;

            case 'update_security':
                $bcrypt_cost = (int)$_POST['bcrypt_cost'];
                $csrf_length = (int)$_POST['csrf_length'];

                $config_content = file_get_contents('../Shared/config.php');
                $config_content = preg_replace("/define\('BCRYPT_COST', [0-9]+\);/", "define('BCRYPT_COST', $bcrypt_cost);", $config_content);
                $config_content = preg_replace("/define\('CSRF_TOKEN_LENGTH', [0-9]+\);/", "define('CSRF_TOKEN_LENGTH', $csrf_length);", $config_content);

                if (file_put_contents('../Shared/config.php', $config_content)) {
                    $success = "Security settings updated successfully";
                } else {
                    $error = "Failed to update security settings";
                }
                break;

            case 'clear_cache':
                // Clear cache directory
                $cache_dir = '../cache/';
                if (is_dir($cache_dir)) {
                    $files = glob($cache_dir . '*');
                    foreach ($files as $file) {
                        if (is_file($file)) {
                            unlink($file);
                        }
                    }
                    $success = "Cache cleared successfully";
                } else {
                    $error = "Cache directory not found";
                }
                break;

            case 'backup_database':
                // Simple database backup (in real app, use proper backup tools)
                $backup_file = '../backups/backup_' . date('Y-m-d_H-i-s') . '.sql';
                $command = "mysqldump -u " . DB_USERNAME . " -p" . DB_PASSWORD . " " . DB_NAME . " > $backup_file";

                if (shell_exec($command)) {
                    $success = "Database backup created successfully";
                } else {
                    $error = "Failed to create database backup";
                }
                break;
        }
    }
}

// Get current settings
$current_settings = [
    'app_name' => APP_NAME,
    'app_url' => APP_URL,
    'session_timeout' => SESSION_TIMEOUT,
    'max_file_size' => MAX_FILE_SIZE,
    'bcrypt_cost' => BCRYPT_COST,
    'csrf_length' => CSRF_TOKEN_LENGTH,
    'development_mode' => DEVELOPMENT_MODE
];

// Get system information
$system_info = [
    'php_version' => PHP_VERSION,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'database_version' => $conn->query('SELECT VERSION() as version')->fetch_assoc()['version'],
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time')
];

$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
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

        .settings-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .settings-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e5e7eb;
        }

        .section-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-control {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .form-text {
            color: #6b7280;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .btn-save {
            background: var(--gradient-primary);
            border: none;
            border-radius: 8px;
            padding: 0.75rem 2rem;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.3);
            color: white;
        }

        .system-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
        }

        .info-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            border-left: 4px solid var(--primary-color);
        }

        .info-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .info-value {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 1.1rem;
        }

        .alert {
            border-radius: 12px;
            border: none;
            margin-bottom: 2rem;
        }

        .maintenance-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .maintenance-card {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            transition: all 0.3s;
        }

        .maintenance-card:hover {
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .maintenance-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .maintenance-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .maintenance-description {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .btn-maintenance {
            background: var(--primary-color);
            border: none;
            border-radius: 8px;
            padding: 0.5rem 1rem;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-maintenance:hover {
            background: var(--secondary-color);
            color: white;
        }

        @media (max-width: 768px) {
            .settings-container {
                padding: 0 1rem;
            }

            .section-header {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }

            .system-info {
                grid-template-columns: 1fr;
            }

            .maintenance-actions {
                grid-template-columns: 1fr;
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
                <a class="nav-link" href="jobs.php">
                    <i class="fas fa-briefcase me-1"></i>Jobs
                </a>
                <a class="nav-link" href="analytics.php">
                    <i class="fas fa-chart-bar me-1"></i>Analytics
                </a>
                <a class="nav-link active" href="settings.php">
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
        <h1><i class="fas fa-cog me-3"></i>System Settings</h1>
        <p>Configure platform settings, security options, and system maintenance.</p>
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

    <div class="settings-container">
        <!-- General Settings -->
        <div class="settings-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-globe"></i>
                </div>
                <h2 class="section-title">General Settings</h2>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="update_general">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="app_name" class="form-label">Application Name</label>
                            <input type="text" class="form-control" id="app_name" name="app_name"
                                   value="<?php echo htmlspecialchars($current_settings['app_name']); ?>" required>
                            <div class="form-text">The name displayed throughout the application</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="app_url" class="form-label">Application URL</label>
                            <input type="url" class="form-control" id="app_url" name="app_url"
                                   value="<?php echo htmlspecialchars($current_settings['app_url']); ?>" required>
                            <div class="form-text">Base URL of the application</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="session_timeout" class="form-label">Session Timeout (minutes)</label>
                            <input type="number" class="form-control" id="session_timeout" name="session_timeout"
                                   value="<?php echo $current_settings['session_timeout'] / 60; ?>" min="5" max="480" required>
                            <div class="form-text">Time before user session expires</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_file_size" class="form-label">Max File Size (MB)</label>
                            <input type="number" class="form-control" id="max_file_size" name="max_file_size"
                                   value="<?php echo $current_settings['max_file_size'] / (1024 * 1024); ?>" min="1" max="50" required>
                            <div class="form-text">Maximum file size for uploads</div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-save">
                    <i class="fas fa-save me-2"></i>Save General Settings
                </button>
            </form>
        </div>

        <!-- Security Settings -->
        <div class="settings-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <h2 class="section-title">Security Settings</h2>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="action" value="update_security">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="bcrypt_cost" class="form-label">Bcrypt Cost</label>
                            <input type="number" class="form-control" id="bcrypt_cost" name="bcrypt_cost"
                                   value="<?php echo $current_settings['bcrypt_cost']; ?>" min="8" max="16" required>
                            <div class="form-text">Password hashing complexity (higher = more secure but slower)</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="csrf_length" class="form-label">CSRF Token Length</label>
                            <input type="number" class="form-control" id="csrf_length" name="csrf_length"
                                   value="<?php echo $current_settings['csrf_length']; ?>" min="16" max="64" required>
                            <div class="form-text">Length of CSRF tokens for security</div>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-save">
                    <i class="fas fa-save me-2"></i>Save Security Settings
                </button>
            </form>
        </div>

        <!-- System Information -->
        <div class="settings-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-info-circle"></i>
                </div>
                <h2 class="section-title">System Information</h2>
            </div>

            <div class="system-info">
                <div class="info-card">
                    <div class="info-label">PHP Version</div>
                    <div class="info-value"><?php echo $system_info['php_version']; ?></div>
                </div>

                <div class="info-card">
                    <div class="info-label">Database Version</div>
                    <div class="info-value"><?php echo $system_info['database_version']; ?></div>
                </div>

                <div class="info-card">
                    <div class="info-label">Server Software</div>
                    <div class="info-value"><?php echo $system_info['server_software']; ?></div>
                </div>

                <div class="info-card">
                    <div class="info-label">Upload Max Filesize</div>
                    <div class="info-value"><?php echo $system_info['upload_max_filesize']; ?></div>
                </div>

                <div class="info-card">
                    <div class="info-label">Memory Limit</div>
                    <div class="info-value"><?php echo $system_info['memory_limit']; ?></div>
                </div>

                <div class="info-card">
                    <div class="info-label">Max Execution Time</div>
                    <div class="info-value"><?php echo $system_info['max_execution_time']; ?>s</div>
                </div>
            </div>
        </div>

        <!-- Maintenance Actions -->
        <div class="settings-section">
            <div class="section-header">
                <div class="section-icon">
                    <i class="fas fa-tools"></i>
                </div>
                <h2 class="section-title">Maintenance Actions</h2>
            </div>

            <div class="maintenance-actions">
                <div class="maintenance-card">
                    <div class="maintenance-icon">
                        <i class="fas fa-broom"></i>
                    </div>
                    <div class="maintenance-title">Clear Cache</div>
                    <div class="maintenance-description">Remove all cached files to free up space</div>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="action" value="clear_cache">
                        <button type="submit" class="btn btn-maintenance">
                            <i class="fas fa-broom me-1"></i>Clear Cache
                        </button>
                    </form>
                </div>

                <div class="maintenance-card">
                    <div class="maintenance-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="maintenance-title">Backup Database</div>
                    <div class="maintenance-description">Create a backup of the entire database</div>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <input type="hidden" name="action" value="backup_database">
                        <button type="submit" class="btn btn-maintenance">
                            <i class="fas fa-download me-1"></i>Backup Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
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