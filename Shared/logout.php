<?php
require_once '../config/config.php';

// Destroy the session
session_unset();
session_destroy();

// Clear any cookies if they exist
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect to login page with logout success message
redirect('login_form.php?logout_success=1');
?>
