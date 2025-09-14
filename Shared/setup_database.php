<?php
/**
 * Database Setup Script for Render Deployment
 * Automatically creates tables if they don't exist
 */

require_once '../config/config.php';

try {
    $db = Database::getInstance()->getConnection();

    // Check if tables exist
    $result = $db->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
    $existingTables = $result->fetchAll(PDO::FETCH_COLUMN);

    $requiredTables = ['user', 'job_post', 'lab_post', 'job_applications', 'hires', 'ratings', 'work_posts'];

    $missingTables = array_diff($requiredTables, $existingTables);

    if (!empty($missingTables)) {
        // Only output if running from command line or in debug mode
        $debug = php_sapi_name() === 'cli' || defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE;

        if ($debug) echo "Initializing database tables...\n";

        // Read and execute the PostgreSQL schema
        $schemaPath = '../database/d_labour_postgres.sql';
        if (file_exists($schemaPath)) {
            $schema = file_get_contents($schemaPath);

            // Split into individual statements
            $statements = array_filter(array_map('trim', explode(';', $schema)));

            foreach ($statements as $statement) {
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    try {
                        $db->exec($statement);
                        if ($debug) echo "Executed: " . substr($statement, 0, 50) . "...\n";
                    } catch (Exception $e) {
                        if ($debug) echo "Error executing statement: " . $e->getMessage() . "\n";
                        error_log("Schema execution error: " . $e->getMessage() . " Statement: " . substr($statement, 0, 100));
                        // Continue with other statements
                    }
                }
            }

            if ($debug) echo "Database initialization completed!\n";
        } else {
            if ($debug) echo "Schema file not found: $schemaPath\n";
            error_log("Schema file not found: $schemaPath");
        }
    } else {
        if ($debug) echo "Database tables already exist.\n";
    }

} catch (Exception $e) {
    if ($debug) echo "Database setup error: " . $e->getMessage() . "\n";
    error_log("Database setup error: " . $e->getMessage());
}
?>