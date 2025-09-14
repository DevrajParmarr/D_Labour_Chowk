<?php
/**
 * Database Connection File
 * Updated to use the proper Database class from config.php
 */

// Include config.php to initialize the Database class
require_once 'config.php';

// Get database connection using the singleton pattern
$db = Database::getInstance();
$conn = $db->getConnection();

// Check connection based on database type
if (DB_TYPE === 'pgsql') {
    // For PostgreSQL (PDO), check if connection is null
    if (!$conn) {
        error_log("Database connection failed: PDO connection is null");
        die("Database connection failed. Please try again later.");
    }
} else {
    // For MySQL (mysqli), check connect_error
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        die("Database connection failed. Please try again later.");
    }
}
?>