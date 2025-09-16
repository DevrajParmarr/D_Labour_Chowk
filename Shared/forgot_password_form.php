<?php
require_once '../config/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $user_type = getCurrentUserType();
    if ($user_type === 'User') {
        redirect('../client_/dashboard.php');
    } elseif ($user_type === 'Labour') {
        redirect('../Labour/dashboard.php');
    } else {
        redirect('../admin/dashboard.php');
    }
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - D Labour Chowk</title>
    <meta http-equiv="Content-Security-Policy" content="font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net;">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .forgot-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        
        .forgot-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 40px 30px 30px;
            text-align: center;
        }
        
        .forgot-header h2 {
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .forgot-header p {
            opacity: 0.9;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .forgot-form {
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
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            background: white;
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e1e8ed;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        
        .btn-reset {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        
        .alert-info {
            background: linear-gradient(135deg, #74b9ff, #0984e3);
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
            margin: 0 10px;
        }
        
        .auth-links a:hover {
            color: #764ba2;
        }
        
        .loading {
            display: none;
        }
        
        @media (max-width: 480px) {
            .forgot-container {
                margin: 10px;
            }
            
            .forgot-header,
            .forgot-form,
            .auth-links {
                padding-left: 20px;
                padding-right: 20px;
            }
        }
        
        .spinning {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-header">
            <h2><i class="bi bi-shield-lock"></i> Reset Password</h2>
            <p>Enter your email address and we'll send you a link to reset your password.</p>
        </div>
        
        <form class="forgot-form" action="forgot_password.php" method="POST" id="forgotForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <?php if (isset($_SESSION['forgot_error'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i>
                    <?php echo $_SESSION['forgot_error']; unset($_SESSION['forgot_error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['forgot_success'])): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i>
                    <?php echo $_SESSION['forgot_success']; unset($_SESSION['forgot_success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['sent'])): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    Password reset instructions have been sent to your email address.
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" class="form-control" name="email" placeholder="Enter your email address" 
                           required autocomplete="email">
                </div>
                <small class="text-muted">
                    We'll never share your email with anyone else.
                </small>
            </div>
            
            <button type="submit" class="btn btn-reset">
                <span class="reset-text">
                    <i class="bi bi-envelope-arrow-up"></i> Send Reset Link
                </span>
                <span class="loading">
                    <i class="bi bi-arrow-clockwise spinning"></i> Sending...
                </span>
            </button>
            
            <div class="text-center mt-4">
                <p class="text-muted mb-0">
                    <i class="bi bi-info-circle"></i>
                    You'll receive an email with password reset instructions
                </p>
            </div>
        </form>
        
        <div class="auth-links">
            <a href="login_form.php">
                <i class="bi bi-arrow-left"></i> Back to Login
            </a>
            <a href="signup_form.php">Create New Account</a>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form submission with loading state
        document.getElementById('forgotForm').addEventListener('submit', function(e) {
            const button = this.querySelector('.btn-reset');
            const resetText = button.querySelector('.reset-text');
            const loading = button.querySelector('.loading');
            
            resetText.style.display = 'none';
            loading.style.display = 'inline-block';
            button.disabled = true;
        });
        
        // Auto-hide alerts after 8 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 8000);
    </script>
</body>
</html>
