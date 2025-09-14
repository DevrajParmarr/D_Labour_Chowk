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

    echo "Setup completed. You can now remove this file for security.\n";
    echo "Visit your main site: " . APP_URL . "\n";

} catch (Exception $e) {
    echo "Setup failed: " . $e->getMessage() . "\n";
}
?>