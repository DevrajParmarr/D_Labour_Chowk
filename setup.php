<?php
/**
 * Manual Database Setup Endpoint
 * Access this file directly to initialize the database
 */

require_once 'config/config.php';

header('Content-Type: text/plain');

try {
    echo "Starting database setup...\n";

    require_once 'Shared/setup_database.php';

    echo "Setup completed successfully!\n";
    echo "<br><a href='" . APP_URL . "'>Click here to visit your site</a>\n";
    echo "<br><br><strong>Important:</strong> Delete this setup.php file for security after confirming the site works.\n";

} catch (Exception $e) {
    echo "Setup failed: " . $e->getMessage() . "\n";
    echo "<br><a href='" . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/') . "'>Go back</a>\n";
}
?>