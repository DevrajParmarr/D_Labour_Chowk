<?php
require_once 'config.php';

// Check if session is already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
    <title>Sign Up - D Labour Chowk</title>
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
        
        .signup-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            margin: 20px;
        }
        
        .signup-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 40px 30px 30px;
            text-align: center;
        }
        
        .signup-header h2 {
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .signup-header p {
            opacity: 0.9;
            font-size: 14px;
        }
        
        .signup-form {
            padding: 40px 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-control, .form-select {
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            padding: 15px 20px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }
        
        .form-control:focus, .form-select:focus {
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
        
        .btn-signup {
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
        
        .btn-signup:hover {
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
        }
        
        .auth-links a:hover {
            color: #764ba2;
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
        
        .strength-weak { background: #ff6b6b; width: 25%; }
        .strength-medium { background: #ffa726; width: 50%; }
        .strength-strong { background: #66bb6a; width: 75%; }
        .strength-very-strong { background: #4caf50; width: 100%; }
        
        .user-type-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }
        
        .user-type-card {
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }
        
        .user-type-card:hover,
        .user-type-card.selected {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }
        
        .user-type-card i {
            font-size: 24px;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        .loading {
            display: none;
        }
        
        @media (max-width: 480px) {
            .signup-container {
                margin: 10px;
            }
            
            .signup-header,
            .signup-form,
            .auth-links {
                padding-left: 20px;
                padding-right: 20px;
            }
            
            .user-type-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-header">
            <h2><i class="bi bi-person-plus"></i> Join Us Today</h2>
            <p>Create your Digital Labour Chowk account</p>
        </div>
        
        <form class="signup-form" action="sign_up.php" method="POST" id="signupForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <?php if (isset($_SESSION['signup_error'])): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-circle"></i>
                    <?php echo $_SESSION['signup_error']; unset($_SESSION['signup_error']); ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-person"></i>
                    </span>
                    <input type="text" class="form-control" name="username" placeholder="Full Name" 
                           required minlength="3" autocomplete="name">
                </div>
            </div>
            
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" class="form-control" name="email" placeholder="Email Address" 
                           required autocomplete="email">
                </div>
            </div>
            
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-phone"></i>
                    </span>
                    <input type="tel" class="form-control" name="mobile" placeholder="Mobile Number" 
                           required pattern="[0-9]{10}" maxlength="10" autocomplete="tel">
                </div>
            </div>
            
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-lock"></i>
                    </span>
                    <input type="password" class="form-control" name="password" id="password" 
                           placeholder="Password" required minlength="6" autocomplete="new-password">
                    <button type="button" class="password-toggle" onclick="togglePassword()">
                        <i class="bi bi-eye" id="passwordIcon"></i>
                    </button>
                </div>
                <div class="password-strength">
                    <div class="strength-bar" id="strengthBar"></div>
                    <small class="text-muted" id="strengthText">Enter a password to see strength</small>
                </div>
            </div>
            
            <div class="form-group">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-lock-fill"></i>
                    </span>
                    <input type="password" class="form-control" id="confirmPassword" 
                           placeholder="Confirm Password" required>
                    <button type="button" class="password-toggle" onclick="toggleConfirmPassword()">
                        <i class="bi bi-eye" id="confirmPasswordIcon"></i>
                    </button>
                </div>
                <small class="text-danger" id="passwordMatch" style="display: none;"></small>
            </div>
            
            <div class="form-group">
                <label class="form-label">I am a:</label>
                <input type="hidden" name="usertype" id="usertype" value="User">
                <div class="user-type-cards">
                    <div class="user-type-card selected" onclick="selectUserType('User')" id="userCard">
                        <i class="bi bi-briefcase"></i>
                        <h6>Client</h6>
                        <p class="mb-0 small">Looking for workers</p>
                    </div>
                    <div class="user-type-card" onclick="selectUserType('Labour')" id="labourCard">
                        <i class="bi bi-tools"></i>
                        <h6>Worker</h6>
                        <p class="mb-0 small">Offering services</p>
                    </div>
                </div>
                <small class="text-danger" id="usertypeError" style="display: none;">Please select your user type</small>
            </div>
            
            <button type="submit" class="btn btn-signup" id="submitBtn">
                <span class="signup-text">
                    <i class="bi bi-person-plus"></i> Create Account
                </span>
                <span class="loading">
                    <i class="bi bi-arrow-clockwise spinning"></i> Creating account...
                </span>
            </button>
        </form>
        
        <div class="auth-links">
            <p class="mb-0">
                Already have an account? 
                <a href="login_form.php">Sign In</a>
            </p>
        </div>
    </div>
    
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
        
        function toggleConfirmPassword() {
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const confirmPasswordIcon = document.getElementById('confirmPasswordIcon');
            
            if (confirmPasswordInput.type === 'password') {
                confirmPasswordInput.type = 'text';
                confirmPasswordIcon.className = 'bi bi-eye-slash';
            } else {
                confirmPasswordInput.type = 'password';
                confirmPasswordIcon.className = 'bi bi-eye';
            }
        }
        
        function selectUserType(type) {
            document.getElementById('usertype').value = type;
            
            // Remove selection from all cards
            document.querySelectorAll('.user-type-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selection to clicked card
            if (type === 'User') {
                document.getElementById('userCard').classList.add('selected');
            } else {
                document.getElementById('labourCard').classList.add('selected');
            }
            
            validateForm();
        }
        
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');

            let score = 0;

            if (password.length >= 6) score++;
            if (/[a-z]/.test(password)) score++;
            if (/[A-Z]/.test(password)) score++;
            if (/[0-9]/.test(password)) score++;
            if (/[^A-Za-z0-9]/.test(password)) score++;

            strengthBar.className = 'strength-bar';

            if (score <= 1) {
                strengthBar.classList.add('strength-weak');
                strengthText.textContent = 'Weak password';
                strengthText.className = 'text-danger';
            } else if (score <= 2) {
                strengthBar.classList.add('strength-medium');
                strengthText.textContent = 'Medium strength';
                strengthText.className = 'text-warning';
            } else if (score <= 3) {
                strengthBar.classList.add('strength-strong');
                strengthText.textContent = 'Strong password';
                strengthText.className = 'text-info';
            } else {
                strengthBar.classList.add('strength-very-strong');
                strengthText.textContent = 'Very strong password';
                strengthText.className = 'text-success';
            }

            return score >= 1; // Allow any password with at least 6 characters
        }
        
        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const usertype = document.getElementById('usertype').value;
            const submitBtn = document.getElementById('submitBtn');
            const passwordMatch = document.getElementById('passwordMatch');
            const usertypeError = document.getElementById('usertypeError');

            let isValid = true;

            // Check password match (only if both fields have content)
            if (confirmPassword && password && password !== confirmPassword) {
                passwordMatch.textContent = 'Passwords do not match';
                passwordMatch.style.display = 'block';
                // Don't set isValid to false - let server handle this
            } else {
                passwordMatch.style.display = 'none';
            }

            // Check if user type is selected
            if (!usertype) {
                usertypeError.textContent = 'Please select your user type';
                usertypeError.style.display = 'block';
                isValid = false;
            } else {
                usertypeError.style.display = 'none';
            }

            // Basic password check (minimum 6 characters)
            if (password && password.length < 6) {
                isValid = false;
            }

            // Don't disable the submit button - let server handle validation
            submitBtn.disabled = false;
            return isValid;
        }
        
        // Form validation - only validate on input, don't block submission
        document.getElementById('password').addEventListener('input', function() {
            validateForm();
            checkPasswordStrength(this.value);
        });
        document.getElementById('confirmPassword').addEventListener('input', validateForm);

        // Also validate on form submit (but allow submission for server-side validation)
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            // Don't prevent submission, let server handle validation
            return true;
        });
        
        // Format mobile number input
        document.querySelector('input[name="mobile"]').addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
        });
        
        // Form submission with loading state
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            const button = this.querySelector('.btn-signup');
            const signupText = button.querySelector('.signup-text');
            const loading = button.querySelector('.loading');
            
            signupText.style.display = 'none';
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
