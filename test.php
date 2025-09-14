<?php
// Simple test to check database connection
echo "Testing database connection...<br>";

try {
    // Check environment variables
    echo "DATABASE_URL: " . (getenv('DATABASE_URL') ? 'Set' : 'Not set') . "<br>";
    echo "RENDER: " . (getenv('RENDER') ? 'Set' : 'Not set') . "<br>";

    // Try to include config
    require_once 'config/config.php';
    echo "Config loaded successfully<br>";

    // Try database connection
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "Database connection successful<br>";

    // Check if it's PDO or mysqli
    if ($conn instanceof PDO) {
        echo "Using PDO connection<br>";
    } elseif ($conn instanceof mysqli) {
        echo "Using mysqli connection<br>";
    } else {
        echo "Unknown connection type<br>";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
}
?>