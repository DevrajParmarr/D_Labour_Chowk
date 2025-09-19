<?php
require_once '../config/config.php';

// Check if user is logged in and is a labour
if (!isLoggedIn()) {
    redirect('../Shared/login_form.php');
}

if (getCurrentUserType() !== 'Labour') {
    redirect('../Shared/login_form.php?error=unauthorized');
}

$user_name = $_SESSION['user_name'];

include "menu.html";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Profile - D Labour Chowk</title>
    <meta name="description" content="Create your professional worker profile to attract potential clients and showcase your skills.">
    
    <!-- Enhanced CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #28a745;
            --secondary-color: #20c997;
            --accent-color: #f59e0b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --gradient-primary: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --gradient-accent: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            color: #1f2937;
            background: linear-gradient(135deg, #e8f5e8 0%, #d4edda 100%);
            min-height: 100vh;
        }
        
        /* Header Section */
        .header-section {
            background: var(--gradient-primary);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .header-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.1);
            z-index: 1;
        }
        
        .header-content {
            text-align: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            position: relative;
            z-index: 2;
        }
        
        .welcome-text {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 1rem;
        }
        
        .main-title {
            font-family: 'Poppins', sans-serif;
            font-size: clamp(2rem, 4vw, 3.5rem);
            font-weight: 800;
            margin-bottom: 1rem;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
        }
        
        .main-subtitle {
            font-size: clamp(1rem, 2vw, 1.3rem);
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }
        
        /* Main Container */
        .main-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        /* Profile Form Card */
        .profile-form-card {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid #f1f5f9;
            position: relative;
            overflow: hidden;
        }
        
        .profile-form-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-accent);
        }
        
        .form-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1.8rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        
        .form-subtitle {
            color: #6b7280;
            text-align: center;
            margin-bottom: 2.5rem;
            font-size: 1rem;
        }
        
        /* Form Groups */
        .form-group {
            margin-bottom: 2rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-control {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fafbfc;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
            background: white;
            transform: translateY(-1px);
        }
        
        .form-control::placeholder {
            color: #9ca3af;
        }
        
        /* File Upload */
        .file-upload-container {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed #d1d5db;
            border-radius: 12px;
            padding: 2rem;
            background: #fafbfc;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .file-upload-container:hover {
            border-color: var(--primary-color);
            background: rgba(40, 167, 69, 0.05);
        }
        
        .file-upload-input {
            position: absolute;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        
        .file-upload-content {
            text-align: center;
            pointer-events: none;
        }
        
        .file-upload-icon {
            font-size: 3rem;
            color: #9ca3af;
            margin-bottom: 1rem;
        }
        
        .file-upload-text {
            color: #6b7280;
            margin-bottom: 0.5rem;
        }
        
        .file-upload-hint {
            color: #9ca3af;
            font-size: 0.875rem;
        }
        
        /* Submit Section */
        .submit-section {
            text-align: center;
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .btn-submit {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 1rem 3rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            min-width: 200px;
        }
        
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(40, 167, 69, 0.4);
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .profile-form-card {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }
            
            .btn-submit {
                width: 100%;
            }
        }
        
        /* Animations */
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .slide-in-up {
            animation: slideInUp 0.6s ease forwards;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header-section">
        <div class="header-content">
            <div class="welcome-text" data-aos="fade-down">
                Hello, <strong><?php echo htmlspecialchars($user_name); ?></strong>!
            </div>
            <h1 class="main-title" data-aos="fade-up" data-aos-delay="200">
                <i class="fas fa-user-edit me-3"></i>Create Your Profile
            </h1>
            <p class="main-subtitle" data-aos="fade-up" data-aos-delay="400">
                Let potential clients know about your skills and experience.
                Create a professional profile to attract more job opportunities.
            </p>
        </div>
    </div>
    
    <!-- Main Container -->
    <div class="main-container">
        <!-- Profile Form Card -->
        <div class="profile-form-card slide-in-up" data-aos="fade-up" data-aos-delay="600">
            <h2 class="form-title">
                <i class="fas fa-id-card me-2"></i>Professional Profile
            </h2>
            <p class="form-subtitle">
                Fill in your details to create an attractive profile for potential clients
            </p>
            
            <!-- Profile Form -->
            <form action="uploadL.php" method="post" enctype="multipart/form-data">
                <!-- Work Type -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-tools me-2"></i>Work Type / Specialization
                    </label>
                    <select class="form-control" name="workType" required>
                        <option value="">Select Your Work Type</option>
                        <option value="Electrician">Electrician</option>
                        <option value="Plumber">Plumber</option>
                        <option value="Carpenter">Carpenter</option>
                        <option value="Painter">Painter</option>
                        <option value="Mason">Mason</option>
                        <option value="Welder">Welder</option>
                        <option value="Gardener">Gardener</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <!-- Salary and Experience Row -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-rupee-sign me-2"></i>Expected Daily Salary
                        </label>
                        <input class="form-control" type="number" name="salary"
                               placeholder="Enter amount in ₹" min="100" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-clock me-2"></i>Experience
                        </label>
                        <input class="form-control" type="text" name="experience"
                               placeholder="e.g., 5 years" required>
                    </div>
                </div>
                
                <!-- City and Location Row -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-city me-2"></i>Preferred City
                        </label>
                        <select class="form-control" name="city" required>
                            <option value="">Select City</option>
                            <option value="Indore">Indore</option>
                            <option value="Bhopal">Bhopal</option>
                            <option value="Ujjain">Ujjain</option>
                            <option value="Jabalpur">Jabalpur</option>
                            <option value="Kota">Kota</option>
                            <option value="Jaipur">Jaipur</option>
                            <option value="Delhi">Delhi</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-map-marker-alt me-2"></i>Specific Location
                        </label>
                        <input class="form-control" type="text" name="location"
                               placeholder="Area where clients can find you" required>
                    </div>
                </div>
                
                <!-- Profile Image -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-camera me-2"></i>Profile Image
                    </label>
                    <div class="file-upload-container">
                        <input class="file-upload-input" type="file" name="pdtimg"
                               accept=".jpg,.png,.jpeg" required>
                        <div class="file-upload-content">
                            <div class="file-upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="file-upload-text">Click to upload your profile photo</div>
                            <div class="file-upload-hint">JPG, PNG or JPEG (Max 5MB)</div>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Section -->
                <div class="submit-section">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-user-plus me-2"></i>Create Profile
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });
        
        // File upload handling
        const fileInput = document.querySelector('.file-upload-input');
        const fileUploadContainer = document.querySelector('.file-upload-container');
        const fileUploadContent = document.querySelector('.file-upload-content');
        
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                fileUploadContent.innerHTML = `
                    <div class="file-upload-icon" style="color: var(--success-color);">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="file-upload-text" style="color: var(--success-color);">File selected: ${file.name}</div>
                    <div class="file-upload-hint">Ready to upload</div>
                `;
                fileUploadContainer.style.borderColor = 'var(--success-color)';
                fileUploadContainer.style.background = 'rgba(16, 185, 129, 0.05)';
            }
        });
        
        // Form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = document.querySelector('.btn-submit');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Profile...';
            submitBtn.disabled = true;
        });
        
        // Add focus effects to form controls
        document.querySelectorAll('.form-control').forEach(control => {
            control.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
                this.parentElement.style.transition = 'transform 0.3s ease';
            });
            
            control.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
    </script>
</body>
</html>