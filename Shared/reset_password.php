<?php
/**
 * Password Reset Form
 * Allows users to set a new password using the reset token
 */

require_once 'config.php';
session_start();

$token = isset($_GET['token']) ? trim($_GET['token']) : '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';

    // Validate CSRF token
    if (!validateCSRFToken($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($password) || strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        try {
            $db = Database::getInstance();

            // Verify token and check expiry
            $stmt = $db->prepare("SELECT user_ID FROM user WHERE reset_token = ? AND reset_expiry > NOW()");
            $stmt->bind_param('s', $token);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                // Hash new password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Update password and clear reset token
                $update_stmt = $db->prepare("UPDATE user SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE user_ID = ?");
                $update_stmt->bind_param('si', $hashed_password, $user['user_ID']);

                if ($update_stmt->execute()) {
                    $success = 'Password reset successfully! You can now log in with your new password.';
                    $update_stmt->close();
                } else {
                    $error = 'Failed to reset password. Please try again.';
                }
            } else {
                $error = 'Invalid or expired reset token.';
            }

            $stmt->close();

        } catch (Exception $e) {
            error_log('Password Reset Error: ' . $e->getMessage());
            $error = 'Password reset failed. Please try again later.';
        }
    }
} elseif (!empty($token)) {
    // Verify token is valid for GET request
    try {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT user_ID FROM user WHERE reset_token = ? AND reset_expiry > NOW()");
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows !== 1) {
            $error = 'Invalid or expired reset token.';
        }
        $stmt->close();
    } catch (Exception $e) {
        $error = 'Invalid reset token.';
    }
} else {
    $error = 'Invalid reset request.';
}

$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - D Labour Chowk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .reset-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }

        .reset-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 40px 30px 30px;
            text-align: center;
        }

        .reset-header h2 {
            font-weight: 600;
            margin-bottom: 10px;
        }

        .reset-header p {
            opacity: 0.9;
            font-size: 14px;
            line-height: 1.5;
        }

        .reset-form {
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-control {
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            padding: 15px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .btn-reset {
            background: var(--gradient-primary);
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            padding: 15px;
            font-size: 16px;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px;
            margin-bottom: 20px;
        }

        .alert-danger {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
        }

        .alert-success {
            background: linear-gradient(135deg, #51cf66, #40c057);
            color: white;
        }

        .auth-links {
            text-align: center;
            padding: 20px 30px;
            background: #f8f9fa;
            border-top: 1px solid #e1e8ed;
        }

        .auth-links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .auth-links a:hover {
            color: #764ba2;
        }

        .password-strength {
            margin-top: 5px;
            font-size: 12px;
        }

        .strength-bar {
            height: 4px;
            border-radius: 2px;
            margin-top: 5px;
            transition: all 0.3s ease;
        }

        .strength-weak { background: #ff6b6b; width: 33%; }
        .strength-medium { background: #ffa726; width: 66%; }
        .strength-strong { background: #66bb6a; width: 100%; }

        @media (max-width: 480px) {
            .reset-container {
                margin: 10px;
            }

            .reset-header,
            .reset-form,
            .auth-links {
                padding-left: 20px;
                padding-right: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="reset-header">
            <h2><i class="fas fa-key"></i> Reset Password</h2>
            <p>Enter your new password below</p>
        </div>

        <form class="reset-form" action="reset_password.php" method="POST" id="resetForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php else: ?>

            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="fas fa-lock me-2"></i>New Password *
                </label>
                <input type="password" class="form-control" name="password" id="password"
                       placeholder="Enter new password" required minlength="6">
                <div class="password-strength">
                    <div class="strength-bar" id="strengthBar"></div>
                    <small class="text-muted" id="strengthText">Password must be at least 6 characters</small>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password" class="form-label">
                    <i class="fas fa-lock me-2"></i>Confirm Password *
                </label>
                <input type="password" class="form-control" name="confirm_password" id="confirm_password"
                       placeholder="Confirm new password" required>
                <small class="text-danger" id="passwordMatch" style="display: none;">Passwords do not match</small>
            </div>

            <button type="submit" class="btn-reset" id="submitBtn">
                <i class="fas fa-save me-2"></i>Reset Password
            </button>

            <?php endif; ?>
        </form>

        <div class="auth-links">
            <a href="login_form.php">
                <i class="fas fa-arrow-left me-2"></i>Back to Login
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password strength checker
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');

            let score = 0;

            if (password.length >= 8) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;

            strengthBar.className = 'strength-bar';

            if (score <= 2) {
                strengthBar.classList.add('strength-weak');
                strengthText.textContent = 'Weak password';
                strengthText.className = 'text-danger';
            } else if (score <= 3) {
                strengthBar.classList.add('strength-medium');
                strengthText.textContent = 'Medium strength';
                strengthText.className = 'text-warning';
            } else {
                strengthBar.classList.add('strength-strong');
                strengthText.textContent = 'Strong password';
                strengthText.className = 'text-success';
            }

            return score >= 2;
        }

        // Password confirmation checker
        function checkPasswordMatch() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const passwordMatch = document.getElementById('passwordMatch');

            if (confirmPassword && password !== confirmPassword) {
                passwordMatch.style.display = 'block';
                return false;
            } else {
                passwordMatch.style.display = 'none';
                return true;
            }
        }

        // Form validation
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const submitBtn = document.getElementById('submitBtn');

            const isStrong = checkPasswordStrength(password);
            const matches = checkPasswordMatch();

            submitBtn.disabled = !(password.length >= 6 && isStrong && matches && confirmPassword);
        }

        // Event listeners
        document.getElementById('password').addEventListener('input', function() {
            checkPasswordStrength(this.value);
            checkPasswordMatch();
            validateForm();
        });

        document.getElementById('confirm_password').addEventListener('input', function() {
            checkPasswordMatch();
            validateForm();
        });

        // Trigger initial validation
        validateForm();
    </script>
</body>
</html>