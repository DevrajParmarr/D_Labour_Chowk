<?php
/**
 * D Labour Chowk - Main Entry Point
 *
 * This is the main entry point for the D Labour Chowk application.
 * It handles routing and initializes the application.
 */

// Include configuration
require_once 'config/config.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple routing based on user type and request
if (isset($_SESSION['user_id'])) {
    // User is logged in
    if ($_SESSION['user_type'] === 'User') {
        // Client/Employer
        header('Location: src/Controllers/Client/dashboard.php');
    } elseif ($_SESSION['user_type'] === 'Labour') {
        // Worker/Labour
        header('Location: src/Controllers/Labour/dashboard.php');
    } elseif ($_SESSION['user_type'] === 'Admin') {
        // Administrator
        header('Location: src/Controllers/Admin/dashboard.php');
    }
    exit();
} else {
    // User not logged in - show landing page
    header('Location: src/Views/index.html');
    exit();
}
?>