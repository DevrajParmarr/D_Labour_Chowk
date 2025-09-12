<?php
/**
 * D_Labour Chowk - Database Configuration
 * This file contains database connection settings
 */

// Database Configuration - Railway optimized
// Railway provides these environment variables for MySQL
define('DB_HOST', getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost');
define('DB_USERNAME', getenv('MYSQLUSER') ?: getenv('DB_USERNAME') ?: 'root');
define('DB_PASSWORD', getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD') ?: '');
define('DB_NAME', getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'd_labour');
define('DB_PORT', getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: 3306);
define('DB_TYPE', 'mysql');

// Alternative: If Railway provides DATABASE_URL (for PostgreSQL)
$database_url = getenv('DATABASE_URL');
if ($database_url && strpos($database_url, 'postgres') !== false) {
    $db_url = parse_url($database_url);
    define('DB_HOST', $db_url['host']);
    define('DB_USERNAME', $db_url['user']);
    define('DB_PASSWORD', $db_url['pass']);
    define('DB_NAME', ltrim($db_url['path'], '/'));
    define('DB_PORT', $db_url['port'] ?: 5432);
    define('DB_TYPE', 'pgsql');
}

// Application Configuration
define('APP_NAME', 'D_Labour Chowk');
define('APP_URL', getenv('RENDER_EXTERNAL_URL') ?: getenv('RAILWAY_STATIC_URL') ?: 'http://localhost/D_Labour_Chowk');
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
define('DEVELOPMENT_MODE', getenv('DEVELOPMENT_MODE') ?: false);

// Error Reporting
if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Database Connection Class - Support both MySQL and PostgreSQL
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            if (DB_TYPE === 'pgsql') {
                // PostgreSQL connection
                $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";user=" . DB_USERNAME . ";password=" . DB_PASSWORD;
                $this->connection = new PDO($dsn);
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } else {
                // MySQL connection
                $this->connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);

                if ($this->connection->connect_error) {
                    throw new Exception("Database connection failed: " . $this->connection->connect_error);
                }

                // Set charset to UTF-8
                $this->connection->set_charset("utf8");
            }

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
        if (DB_TYPE === 'pgsql') {
            try {
                $stmt = $this->connection->query($sql);
                return $stmt;
            } catch (Exception $e) {
                error_log("SQL Error: " . $e->getMessage() . " Query: " . $sql);
                return false;
            }
        } else {
            $result = $this->connection->query($sql);
            if (!$result) {
                error_log("SQL Error: " . $this->connection->error . " Query: " . $sql);
            }
            return $result;
        }
    }

    public function prepare($sql) {
        if (DB_TYPE === 'pgsql') {
            try {
                $stmt = $this->connection->prepare($sql);
                return $stmt;
            } catch (Exception $e) {
                error_log("Prepare Error: " . $e->getMessage() . " Query: " . $sql);
                return false;
            }
        } else {
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                error_log("Prepare Error: " . $this->connection->error . " Query: " . $sql);
            }
            return $stmt;
        }
    }

    public function getLastInsertId() {
        if (DB_TYPE === 'pgsql') {
            return $this->connection->lastInsertId();
        } else {
            return $this->connection->insert_id;
        }
    }

    public function getAffectedRows() {
        if (DB_TYPE === 'pgsql') {
            // For PostgreSQL, we need to use rowCount() on the statement
            return null; // Will be handled by individual queries
        } else {
            return $this->connection->affected_rows;
        }
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
