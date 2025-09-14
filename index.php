<?php
/**
 * D Labour Chowk - Main Entry Point
 *
 * This is the main entry point for the D Labour Chowk application.
 * It handles routing and initializes the application.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Simple routing based on user type and request
if (isset($_SESSION['user_id'])) {
    // User is logged in - try to include config for database
    try {
        require_once 'config/config.php';
    } catch (Exception $e) {
        // If config fails, redirect to landing page
        header('Location: Shared/index.html');
        exit();
    }

    if ($_SESSION['user_type'] === 'User') {
        // Client/Employer
        header('Location: client_/dashboard.php');
    } elseif ($_SESSION['user_type'] === 'Labour') {
        // Worker/Labour
        header('Location: Labour/dashboard.php');
    } elseif ($_SESSION['user_type'] === 'Admin') {
        // Administrator
        header('Location: admin/dashboard.php');
    }
    exit();
} else {
    // User not logged in - show landing page without database
    header('Location: Shared/index.html');
    exit();
}
?>