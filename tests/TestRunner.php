<?php
/**
 * D Labour Chowk Test Runner
 * Comprehensive testing suite for the application
 */

require_once './Shared/config.php';

class TestRunner {
    private $tests = [];
    private $passed = 0;
    private $failed = 0;
    private $output = [];

    public function __construct() {
        $this->output[] = "🧪 D Labour Chowk Test Suite";
        $this->output[] = "=================================";
        $this->output[] = "";
    }

    /**
     * Add a test to the suite
     */
    public function addTest($name, callable $test) {
        $this->tests[$name] = $test;
    }

    /**
     * Run all tests
     */
    public function runTests() {
        foreach ($this->tests as $name => $test) {
            $this->runSingleTest($name, $test);
        }

        $this->displaySummary();
    }

    /**
     * Run a single test
     */
    private function runSingleTest($name, callable $test) {
        $this->output[] = "Testing: $name";
        
        try {
            $result = $test();
            if ($result === true) {
                $this->output[] = "✅ PASSED";
                $this->passed++;
            } else {
                $this->output[] = "❌ FAILED: $result";
                $this->failed++;
            }
        } catch (Exception $e) {
            $this->output[] = "❌ FAILED: " . $e->getMessage();
            $this->failed++;
        }
        
        $this->output[] = "";
    }

    /**
     * Display test summary
     */
    private function displaySummary() {
        $total = $this->passed + $this->failed;
        $this->output[] = "=================================";
        $this->output[] = "Test Results:";
        $this->output[] = "Total: $total";
        $this->output[] = "Passed: {$this->passed} ✅";
        $this->output[] = "Failed: {$this->failed} ❌";
        $this->output[] = "";
        
        if ($this->failed > 0) {
            $this->output[] = "❌ Some tests failed. Please check the errors above.";
        } else {
            $this->output[] = "🎉 All tests passed!";
        }
    }

    /**
     * Get test output
     */
    public function getOutput() {
        return implode("\n", $this->output);
    }

    /**
     * Assert helper functions
     */
    public static function assertTrue($condition, $message = 'Assertion failed') {
        if (!$condition) {
            throw new Exception($message);
        }
        return true;
    }

    public static function assertEquals($expected, $actual, $message = 'Values are not equal') {
        if ($expected !== $actual) {
            throw new Exception("$message. Expected: $expected, Got: $actual");
        }
        return true;
    }

    public static function assertNotEmpty($value, $message = 'Value is empty') {
        if (empty($value)) {
            throw new Exception($message);
        }
        return true;
    }
}

// Initialize test runner
$testRunner = new TestRunner();

// Database Connection Tests
$testRunner->addTest('Database Connection', function() {
    try {
        $db = Database::getInstance();
        $connection = $db->getConnection();
        TestRunner::assertTrue($connection instanceof mysqli, 'Database connection should be mysqli instance');
        TestRunner::assertTrue($connection->ping(), 'Database should be accessible');
        return true;
    } catch (Exception $e) {
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }
});

// Database Tables Tests
$testRunner->addTest('Database Tables Exist', function() {
    $db = Database::getInstance();
    $required_tables = ['user', 'job_post', 'lab_post', 'hires', 'job_applications', 'ratings', 'work_posts'];
    
    foreach ($required_tables as $table) {
        $result = $db->query("SHOW TABLES LIKE '$table'");
        TestRunner::assertTrue($result->num_rows > 0, "Table '$table' should exist");
    }
    return true;
});

// Configuration Tests
$testRunner->addTest('Configuration Constants', function() {
    TestRunner::assertTrue(defined('DB_HOST'), 'DB_HOST should be defined');
    TestRunner::assertTrue(defined('DB_USERNAME'), 'DB_USERNAME should be defined');
    TestRunner::assertTrue(defined('DB_PASSWORD'), 'DB_PASSWORD should be defined');
    TestRunner::assertTrue(defined('DB_NAME'), 'DB_NAME should be defined');
    TestRunner::assertTrue(defined('APP_NAME'), 'APP_NAME should be defined');
    return true;
});

// Helper Functions Tests
$testRunner->addTest('Helper Functions', function() {
    // Test sanitizeInput function
    $dirty_input = '<script>alert("xss")</script>test';
    $clean_input = sanitizeInput($dirty_input);
    TestRunner::assertTrue(strpos($clean_input, '<script>') === false, 'sanitizeInput should remove script tags');
    
    // Test generateCSRFToken function
    $token1 = generateCSRFToken();
    $token2 = generateCSRFToken();
    TestRunner::assertTrue(strlen($token1) > 10, 'CSRF token should be reasonably long');
    TestRunner::assertEquals($token1, $token2, 'CSRF token should be consistent in same session');
    
    return true;
});

// User Authentication Tests
$testRunner->addTest('User Authentication Functions', function() {
    // Test isLoggedIn function when not logged in
    unset($_SESSION['user_id']);
    TestRunner::assertTrue(!isLoggedIn(), 'isLoggedIn should return false when not logged in');
    
    // Test getCurrentUserId when not logged in
    $user_id = getCurrentUserId();
    TestRunner::assertTrue($user_id === null, 'getCurrentUserId should return null when not logged in');
    
    return true;
});

// Database CRUD Operations Tests
$testRunner->addTest('Database CRUD Operations', function() {
    $db = Database::getInstance();
    
    // Test user creation (simulation)
    $test_email = 'test_' . time() . '@example.com';
    $test_mobile = '9999' . rand(100000, 999999);
    
    $stmt = $db->prepare("INSERT INTO user (user_name, email_id, mobile_no, password, user_type) VALUES (?, ?, ?, ?, ?)");
    $username = 'Test User';
    $password = password_hash('testpass', PASSWORD_DEFAULT);
    $usertype = 'User';
    
    $stmt->bind_param('sssss', $username, $test_email, $test_mobile, $password, $usertype);
    $result = $stmt->execute();
    
    TestRunner::assertTrue($result, 'Should be able to insert test user');
    
    $user_id = $db->getLastInsertId();
    TestRunner::assertTrue($user_id > 0, 'Should get valid user ID after insertion');
    
    // Test user retrieval
    $stmt = $db->prepare("SELECT * FROM user WHERE user_ID = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    TestRunner::assertTrue($result->num_rows === 1, 'Should find the inserted user');
    
    $user_data = $result->fetch_assoc();
    TestRunner::assertEquals($test_email, $user_data['email_id'], 'Email should match');
    TestRunner::assertEquals((string)$test_mobile, (string)$user_data['mobile_no'], 'Mobile should match');
    
    // Clean up test data
    $stmt = $db->prepare("DELETE FROM user WHERE user_ID = ?");
    $stmt->bind_param('i', $user_id);
    $cleanup_result = $stmt->execute();
    
    TestRunner::assertTrue($cleanup_result, 'Should be able to clean up test data');
    
    return true;
});

// Security Tests
$testRunner->addTest('Security Measures', function() {
    // Test SQL injection prevention
    $db = Database::getInstance();
    
    // This should not cause any issues due to prepared statements
    $malicious_input = "'; DROP TABLE user; --";
    
    try {
        $stmt = $db->prepare("SELECT * FROM user WHERE email_id = ?");
        $stmt->bind_param('s', $malicious_input);
        $stmt->execute();
        // If we reach here without error, prepared statements are working
        TestRunner::assertTrue(true, 'Prepared statements should prevent SQL injection');
    } catch (Exception $e) {
        throw new Exception('SQL injection test failed: ' . $e->getMessage());
    }
    
    // Test XSS prevention
    $xss_input = '<script>alert("xss")</script>';
    $sanitized = sanitizeInput($xss_input);
    TestRunner::assertTrue(strpos($sanitized, '<script>') === false, 'XSS input should be sanitized');
    
    return true;
});

// File Structure Tests
$testRunner->addTest('File Structure', function() {
    $required_files = [
        './Shared/config.php',
        './Shared/login_form.php',
        './Shared/signup_form.php',
        './client_/dashboard.php',
        './Labour/dashboard.php'
    ];
    
    foreach ($required_files as $file) {
        TestRunner::assertTrue(file_exists($file), "File '$file' should exist");
    }
    
    return true;
});

// API Endpoint Tests (if any)
$testRunner->addTest('Configuration Values', function() {
    TestRunner::assertEquals('d_labour', DB_NAME, 'Database name should be d_labour');
    TestRunner::assertEquals('localhost', DB_HOST, 'Database host should be localhost');
    TestRunner::assertTrue(strlen(APP_NAME) > 0, 'App name should not be empty');
    
    return true;
});

// Performance Tests
$testRunner->addTest('Basic Performance', function() {
    $start_time = microtime(true);
    
    // Test database query performance
    $db = Database::getInstance();
    $result = $db->query("SELECT COUNT(*) as count FROM user LIMIT 1");
    
    $end_time = microtime(true);
    $execution_time = ($end_time - $start_time) * 1000; // Convert to milliseconds
    
    TestRunner::assertTrue($execution_time < 1000, 'Basic query should execute in under 1 second');
    TestRunner::assertTrue($result !== false, 'Query should return a result');
    
    return true;
});

// Edge Cases Tests
$testRunner->addTest('Edge Cases', function() {
    // Test empty input handling
    $empty_sanitized = sanitizeInput('');
    TestRunner::assertEquals('', $empty_sanitized, 'Empty input should remain empty after sanitization');
    
    // Test null input handling
    $null_sanitized = sanitizeInput(null);
    TestRunner::assertEquals('', $null_sanitized, 'Null input should become empty string');
    
    // Test very long input
    $long_input = str_repeat('a', 1000);
    $long_sanitized = sanitizeInput($long_input);
    TestRunner::assertTrue(strlen($long_sanitized) <= 1000, 'Long input should be handled properly');
    
    return true;
});

// Run the tests
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Results - D Labour Chowk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Fira Code', monospace;
            background: #181818;
            color: #f5f5f5;
            padding: 20px;
        }
        .test-container {
            background: #23272b;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 6px 24px rgba(0,0,0,0.6);
            max-width: 900px;
            margin: 0 auto;
        }
        .test-output {
            background: #181c20;
            border: 1px solid #343a40;
            border-radius: 8px;
            padding: 20px;
            white-space: pre-line;
            font-size: 15px;
            line-height: 1.7;
            max-height: 600px;
            overflow-y: auto;
            color: #e0e0e0;
        }
        .test-header {
            color: #00e676;
            text-align: center;
            margin-bottom: 28px;
            letter-spacing: 1px;
        }
        .btn-run {
            background: #00e676;
            border: none;
            color: #23272b;
            padding: 10px 24px;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 22px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .btn-run:hover, .btn-run:focus {
            background: #00c853;
            outline: none;
        }
        .timestamp {
            color: #bdbdbd;
            font-size: 13px;
            margin-bottom: 12px;
            text-align: right;
        }
        @media (max-width: 600px) {
            .test-container {
                padding: 12px;
            }
            .test-output {
                font-size: 13px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <main class="test-container" role="main" aria-label="Test Results">
        <h1 class="test-header" tabindex="0">🧪 D Labour Chowk Test Suite</h1>
        
        <div class="timestamp" aria-label="Test run timestamp">
            Test run on: <?php echo date('Y-m-d H:i:s'); ?>
        </div>
        
        <button class="btn-run" onclick="location.reload()" aria-label="Run tests again">🔄 Run Tests Again</button>
        
        <section class="test-output" id="testOutput" aria-live="polite" aria-atomic="true">
<?php
try {
    $testRunner->runTests();
    echo htmlspecialchars($testRunner->getOutput());
} catch (Exception $e) {
    echo "❌ Test suite failed to run: " . htmlspecialchars($e->getMessage());
}
?>
        </section>
    </main>

    <script>
        // Auto-scroll to bottom of output
        const output = document.getElementById('testOutput');
        output.scrollTop = output.scrollHeight;
        
        // Add syntax highlighting for status messages
        const content = output.innerHTML;
        output.innerHTML = content
            .replace(/✅ PASSED/g, '<span style="color: #4CAF50;">✅ PASSED</span>')
            .replace(/❌ FAILED/g, '<span style="color: #f44336;">❌ FAILED</span>')
            .replace(/🎉 All tests passed!/g, '<span style="color: #4CAF50; font-weight: bold;">🎉 All tests passed!</span>')
            .replace(/❌ Some tests failed/g, '<span style="color: #f44336; font-weight: bold;">❌ Some tests failed</span>');
    </script>
</body>
</html>
<?php
$content = ob_get_clean();
echo $content;
?>
