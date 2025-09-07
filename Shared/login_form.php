<?php
require_once 'config.php';
require_once 'notification_system.php';
require_once 'form_validator.php';

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    redirect('login_form.php?logout_success=1');
}

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
    <meta http-equiv="Content-Security-Policy" content="font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net;">
    <title>Login - D Labour Chowk</title>
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
        
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        
        .login-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 40px 30px 30px;
            text-align: center;
        }
        
        .login-header h2 {
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .login-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .login-form {
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
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        
        .btn-login {
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
        
        .btn-login:hover {
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
        
        .loading {
            display: none;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            z-index: 10;
        }
        
        @media (max-width: 480px) {
            .login-container {
                margin: 10px;
            }
            
            .login-header,
            .login-form,
            .auth-links {
                padding-left: 20px;
                padding-right: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2><i class="bi bi-person-check"></i> Welcome Back</h2>
            <p>Sign in to your Digital Labour Chowk account</p>
        </div>
        
        <form class="login-form" action="login.php" method="POST" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <?php if (isset($_SESSION['login_error'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i>
                    <?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['signup_success'])): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i>
                    <?php echo $_SESSION['signup_success']; unset($_SESSION['signup_success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['logout_success'])): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i>
                    You have been logged out successfully.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['timeout'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-clock"></i>
                    Your session has expired. Please login again.
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-phone"></i>
                    </span>
                    <input type="tel" class="form-control" name="mobile_no" placeholder="Mobile Number" 
                           required pattern="[0-9]{10}" maxlength="10" autocomplete="tel">
                </div>
            </div>
            
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" class="form-control" name="password" id="password" 
                           placeholder="Password" required autocomplete="current-password">
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="bi bi-eye" id="passwordIcon"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="btn btn-login">
                <span class="login-text">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </span>
                <span class="loading">
                    <i class="bi bi-arrow-clockwise spinning"></i> Signing in...
                </span>
            </button>
        </form>
        
        <div class="auth-links">
            <p class="mb-0">
                Don't have an account? 
                <a href="signup_form.php">Create Account</a>
            </p>
            <p class="mt-2 mb-0">
                <a href="forgot_password_form.php">Forgot Password?</a>
            </p>
        </div>
    </div>

    <!-- Notification System -->
    <?php
    $notificationSystem = NotificationSystem::getInstance();
    echo $notificationSystem->renderToastContainer();
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordIcon = document.getElementById('passwordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'bi bi-eye-slash';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'bi bi-eye';
            }
        }
        
        // Form submission with loading state
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const button = this.querySelector('.btn-login');
            const loginText = button.querySelector('.login-text');
            const loading = button.querySelector('.loading');
            
            loginText.style.display = 'none';
            loading.style.display = 'inline-block';
            button.disabled = true;
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transition = 'opacity 0.5s ease';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 5000);
        
        // Format mobile number input
        document.querySelector('input[name="mobile_no"]').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
        });
    </script>
    
    <style>
        .spinning {
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
</body>
</html>
