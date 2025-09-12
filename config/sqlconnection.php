<?php
/**
 * Database Connection File
 * Updated to use the proper Database class from config.php
 */

// Include config.php to initialize the Database class
require_once 'config.php';

// Get database connection using the singleton pattern
$conn = Database::getInstance()->getConnection();

// Check connection
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Database connection failed. Please try again later.");
}
?>