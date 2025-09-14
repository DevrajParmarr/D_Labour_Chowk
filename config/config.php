<?php
/**
 * D_Labour Chowk - Database Configuration
 * This file contains database connection settings
 */

// Database Configuration - Render optimized
// Check for Render's DATABASE_URL first (PostgreSQL)
$database_url = getenv('DATABASE_URL');
if ($database_url) {
    if (strpos($database_url, 'postgres') !== false) {
        $db_url = parse_url($database_url);
        define('DB_HOST', $db_url['host']);
        define('DB_USERNAME', $db_url['user']);
        define('DB_PASSWORD', $db_url['pass']);
        define('DB_NAME', ltrim($db_url['path'], '/'));
        define('DB_PORT', $db_url['port'] ?: 5432);
        define('DB_TYPE', 'pgsql');
    } elseif (strpos($database_url, 'mysql') !== false) {
        $db_url = parse_url($database_url);
        define('DB_HOST', $db_url['host']);
        define('DB_USERNAME', $db_url['user']);
        define('DB_PASSWORD', $db_url['pass']);
        define('DB_NAME', ltrim($db_url['path'], '/'));
        define('DB_PORT', $db_url['port'] ?: 3306);
        define('DB_TYPE', 'mysql');
    }
} else {
    // Fallback to Railway/MySQL environment variables
    define('DB_HOST', getenv('MYSQLHOST') ?: getenv('DB_HOST') ?: 'localhost');
    define('DB_USERNAME', getenv('MYSQLUSER') ?: getenv('DB_USERNAME') ?: 'root');
    define('DB_PASSWORD', getenv('MYSQLPASSWORD') ?: getenv('DB_PASSWORD') ?: '');
    define('DB_NAME', getenv('MYSQLDATABASE') ?: getenv('DB_NAME') ?: 'd_labour');
    define('DB_PORT', getenv('MYSQLPORT') ?: getenv('DB_PORT') ?: 3306);
    define('DB_TYPE', 'mysql');
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

// Error Reporting - Enable for debugging deployment issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Connection Class - mysqli-like interface for both MySQL and PostgreSQL
class Database {
    private static $instance = null;
    private $connection;
    private $is_pdo = false;

    private function __construct() {
        try {
            if (DB_TYPE === 'pgsql') {
                // PostgreSQL connection using PDO
                $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";user=" . DB_USERNAME . ";password=" . DB_PASSWORD;
                $this->connection = new PDO($dsn, DB_USERNAME, DB_PASSWORD);
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                $this->is_pdo = true;
            } else {
                // MySQL connection using mysqli
                $this->connection = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
                if ($this->connection->connect_error) {
                    throw new Exception("Database connection failed: " . $this->connection->connect_error);
                }
                $this->connection->set_charset("utf8");
                $this->is_pdo = false;
            }

        } catch (Exception $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
    }

    // Auto-initialize database tables on first run (only in production/Render)
    if (getenv('RENDER') || getenv('DATABASE_URL')) {
        try {
            // Check if we need to initialize
            if ($this->is_pdo) {
                $result = $this->connection->query("SELECT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = 'user')");
                $tableExists = $result->fetchColumn();
            } else {
                $result = $this->connection->query("SHOW TABLES LIKE 'user'");
                $tableExists = $result->num_rows > 0;
            }

            if (!$tableExists) {
                // Run setup script
                require_once '../Shared/setup_database.php';
            }
        } catch (Exception $e) {
            error_log("Database initialization check failed: " . $e->getMessage());
            // Don't die here, let the app try to run
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
        if ($this->is_pdo) {
            return substr($this->connection->quote($string), 1, -1); // Remove quotes from PDO quote
        } else {
            return $this->connection->real_escape_string($string);
        }
    }

    public function query($sql, $params = []) {
        try {
            if ($this->is_pdo) {
                $stmt = $this->connection->prepare($sql);
                $stmt->execute($params);
                return new PDOResultWrapper($stmt);
            } else {
                if (!empty($params)) {
                    $stmt = $this->connection->prepare($sql);
                    if ($stmt) {
                        $types = str_repeat('s', count($params));
                        $stmt->bind_param($types, ...$params);
                        $stmt->execute();
                        return $stmt->get_result();
                    }
                    return false;
                } else {
                    return $this->connection->query($sql);
                }
            }
        } catch (Exception $e) {
            error_log("SQL Error: " . $e->getMessage() . " Query: " . $sql);
            return false;
        }
    }

    public function prepare($sql) {
        try {
            if ($this->is_pdo) {
                return $this->connection->prepare($sql);
            } else {
                return $this->connection->prepare($sql);
            }
        } catch (Exception $e) {
            error_log("Prepare Error: " . $e->getMessage() . " Query: " . $sql);
            return false;
        }
    }

    public function getLastInsertId() {
        if ($this->is_pdo) {
            return $this->connection->lastInsertId();
        } else {
            return $this->connection->insert_id;
        }
    }

    public function getAffectedRows($stmt = null) {
        if ($this->is_pdo) {
            return $stmt ? $stmt->rowCount() : null;
        } else {
            return $this->connection->affected_rows;
        }
    }

    // mysqli-like methods for compatibility
    public function real_escape_string($string) {
        return $this->escape($string);
    }

    public function set_charset($charset) {
        if (!$this->is_pdo) {
            return $this->connection->set_charset($charset);
        }
        return true;
    }

    public function connect_error() {
        if (!$this->is_pdo) {
            return $this->connection->connect_error;
        }
        return null;
    }

    public function insert_id() {
        return $this->getLastInsertId();
    }

    public function affected_rows() {
        return $this->getAffectedRows();
    }
}

// PDO Result Wrapper to mimic mysqli result
class PDOResultWrapper {
    private $stmt;
    private $currentRow = 0;
    private $rows = [];

    public function __construct($stmt) {
        $this->stmt = $stmt;
        $this->rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetch_assoc() {
        if ($this->currentRow < count($this->rows)) {
            return $this->rows[$this->currentRow++];
        }
        return null;
    }

    public function num_rows() {
        return count($this->rows);
    }

    public function fetch_all($mode = MYSQLI_ASSOC) {
        return $this->rows;
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

// Performance optimization (disabled for now - PerformanceOptimizer.php not available)
// TODO: Implement performance optimization when needed
// $performance = PerformanceOptimizer::getInstance();
// $performance->enableCompression();

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

// Performance metrics logging (disabled for now)
// TODO: Implement performance monitoring when needed
// if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
//     register_shutdown_function(function() {
//         // Performance metrics logging here
//     });
// }
?>
