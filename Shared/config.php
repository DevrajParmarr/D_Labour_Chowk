<?php
/**
 * D_Labour Chowk - Database Configuration
 * This file contains database connection settings
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'd_labour');

// Application Configuration
define('APP_NAME', 'D_Labour Chowk');
define('APP_URL', 'http://localhost/D_Labour_Chowk');
define('APP_VERSION', '2.0');

// File Upload Configuration
define('UPLOAD_PATH', 'Shared/images/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx']);

// Session Configuration
define('SESSION_TIMEOUT', 30 * 60); // 30 minutes

// Security Configuration
define('BCRYPT_COST', 12);
define('CSRF_TOKEN_LENGTH', 32);

// Development Mode
define('DEVELOPMENT_MODE', true);

// Error Reporting
if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Database Connection Class
class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
            
            if ($this->connection->connect_error) {
                throw new Exception("Database connection failed: " . $this->connection->connect_error);
            }
            
            // Set charset to UTF-8
            $this->connection->set_charset("utf8");
            
        } catch (Exception $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function escape($string) {
        return $this->connection->real_escape_string($string);
    }
    
    public function query($sql) {
        $result = $this->connection->query($sql);
        if (!$result) {
            error_log("SQL Error: " . $this->connection->error . " Query: " . $sql);
        }
        return $result;
    }
    
    public function prepare($sql) {
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            error_log("Prepare Error: " . $this->connection->error . " Query: " . $sql);
        }
        return $stmt;
    }
    
    public function getLastInsertId() {
        return $this->connection->insert_id;
    }
    
    public function getAffectedRows() {
        return $this->connection->affected_rows;
    }
}

// Helper Functions
function getDB() {
    return Database::getInstance()->getConnection();
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(CSRF_TOKEN_LENGTH));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function redirect($url) {
    header("Location: " . $url);
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUserType() {
    return $_SESSION['user_type'] ?? null;
}

// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
    
    // Session timeout check
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        if (!headers_sent()) {
            redirect('Shared/login_form.php?timeout=1');
        }
    }
    $_SESSION['last_activity'] = time();
}

// Initialize Performance Optimization
require_once __DIR__ . '/PerformanceOptimizer.php';
$performance = PerformanceOptimizer::getInstance();

// Enable compression and caching for better performance
if (!defined('DISABLE_PERFORMANCE_OPTIMIZATION')) {
    $performance->enableCompression();
    
    // Set cache headers for static resources
    $current_file = basename($_SERVER['PHP_SELF']);
    if (in_array($current_file, ['login_form.php', 'signup_form.php', 'dashboard.php'])) {
        $performance->setCacheHeaders(1800); // 30 minutes for forms
    }
}

// Additional Security Headers
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    
    // Content Security Policy - Disabled in development mode for Bootstrap Icons
    if (!defined('DEVELOPMENT_MODE') || !DEVELOPMENT_MODE) {
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com https://unpkg.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://unpkg.com; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net https://unpkg.com; img-src 'self' data: https://via.placeholder.com https://images.unsplash.com;");
    }

    // Prevent CSP caching issues
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");
}

?>

<?php
/**
 * Performance and Security Initialization
 * This section is automatically loaded with config.php
 * 
 * Features enabled:
 * - Output compression
 * - Caching headers
 * - Security headers
 * - Performance monitoring
 * - Image optimization ready
 */

// Log performance metrics in development mode
if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
    register_shutdown_function(function() {
        $performance = PerformanceOptimizer::getInstance();
        $metrics = $performance->getPerformanceMetrics();
        
        // Log to console in development
        echo "\n<!-- Performance Metrics:\n";
        echo "Memory Usage: " . formatBytes($metrics['memory_usage']) . "\n";
        echo "Peak Memory: " . formatBytes($metrics['memory_peak']) . "\n";
        if (isset($metrics['execution_time'])) {
            echo "Execution Time: " . number_format($metrics['execution_time'] * 1000, 2) . "ms\n";
        }
        echo "Cache Files: " . $metrics['cache_files'] . "\n";
        echo "Cache Size: " . formatBytes($metrics['cache_size']) . "\n";
        echo "-->\n";
    });
}
?>
