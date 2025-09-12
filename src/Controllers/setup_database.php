<?php
/**
 * Database Setup Script for Location and Messaging Features
 * Execute the SQL file to create necessary tables
 */

require_once 'config.php';

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();

    // Read the SQL file
    $sql = file_get_contents('../add_location_messaging_features.sql');

    if (!$sql) {
        die("Error: Could not read SQL file");
    }

    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));

    $successCount = 0;
    $errorCount = 0;
    $errors = [];

    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                if ($conn->query($statement) === TRUE) {
                    $successCount++;
                } else {
                    $errorCount++;
                    $errors[] = "Error executing statement: " . $conn->error;
                }
            } catch (Exception $e) {
                $errorCount++;
                $errors[] = "Exception: " . $e->getMessage();
            }
        }
    }

    echo "<h1>Database Setup Complete</h1>";
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>✅ Success!</h3>";
    echo "<p><strong>Successful statements:</strong> $successCount</p>";
    echo "<p><strong>Failed statements:</strong> $errorCount</p>";
    echo "</div>";

    if ($errorCount > 0) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>⚠️ Errors Encountered:</h3>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
        echo "</div>";
    }

    echo "<div style='background: #cce5ff; color: #004085; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>📋 Database Tables Created:</h3>";
    echo "<ul>";
    echo "<li><strong>user_location</strong> - User location tracking</li>";
    echo "<li><strong>conversations</strong> - Chat conversations</li>";
    echo "<li><strong>messages</strong> - Individual messages</li>";
    echo "<li><strong>location_search_history</strong> - Search tracking</li>";
    echo "</ul>";
    echo "</div>";

    echo "<div style='background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>🚀 Next Steps:</h3>";
    echo "<ol>";
    echo "<li><a href='location_map.php'>Test Location Map</a> - Try the location-based search feature</li>";
    echo "<li><a href='messages.php'>Test Messaging</a> - Try the real-time messaging feature</li>";
    echo "<li>Update existing pages to include links to these new features</li>";
    echo "</ol>";
    echo "</div>";

} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h3>❌ Database Setup Failed</h3>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "</div>";
}
?>