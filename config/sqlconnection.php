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

// Connection is already validated in Database class constructor
// No additional checks needed here
?>