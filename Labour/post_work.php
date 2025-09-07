<?php
require_once '../Shared/config.php';

// Check if user is logged in and is a labour
if (!isLoggedIn()) {
    redirect('../Shared/login_form.php');
}

if (getCurrentUserType() !== 'Labour') {
    redirect('../Shared/login_form.php?error=unauthorized');
}

$db = Database::getInstance();
$conn = $db->getConnection();
$user_ID = getCurrentUserId();
$user_name = $_SESSION['user_name'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate input
        $description = trim($_POST['description'] ?? '');
        if (empty($description)) {
            throw new Exception('Description is required');
        }

        // Handle file upload
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = $_FILES['image']['type'];

            if (!in_array($file_type, $allowed_types)) {
                throw new Exception('Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.');
            }

            if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                throw new Exception('File too large. Maximum size is 5MB.');
            }

            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $safe_filename = uniqid() . '_' . preg_replace("/[^a-zA-Z0-9]/", "", pathinfo($_FILES['image']['name'], PATHINFO_FILENAME)) . '.' . $file_extension;
            $target = "../Shared/uploads/" . $safe_filename;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                throw new Exception('Failed to upload file');
            }

            $image = $safe_filename;
        }

        // Insert the post into the database
        $sql = "INSERT INTO work_posts (labour_ID, description, image) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Database error: ' . $conn->error);
        }

        $stmt->bind_param("iss", $user_ID, $description, $image);

        if ($stmt->execute()) {
            echo "<script>alert('Your work has been posted successfully!'); window.location.href='work_posts.php';</script>";
        } else {
            throw new Exception('Failed to save post to database');
        }

        $stmt->close();

    } catch (Exception $e) {
        error_log('Work post error: ' . $e->getMessage());
        echo "<script>alert('Error: " . addslashes($e->getMessage()) . "'); window.history.back();</script>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Work Post - D Labour Chowk</title>
    <meta name="description" content="Showcase your work and skills by posting your completed projects and portfolio.">
    
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
        
        /* Work Form Card */
        .work-form-card {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid #f1f5f9;
            position: relative;
            overflow: hidden;
        }
        
        .work-form-card::before {
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
        
        /* Textarea */
        .form-textarea {
            min-height: 150px;
            resize: vertical;
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
            display: flex;
            gap: 1rem;
            justify-content: center;
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
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(108, 117, 125, 0.4);
            color: white;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .work-form-card {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }
            
            .submit-section {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-submit, .btn-secondary {
                width: 100%;
                max-width: 300px;
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
                <i class="fas fa-camera me-3"></i>Showcase Your Work
            </h1>
            <p class="main-subtitle" data-aos="fade-up" data-aos-delay="400">
                Share your completed projects and build a portfolio that attracts potential clients.
                Let your work speak for your skills and expertise.
            </p>
        </div>
    </div>
    
    <!-- Main Container -->
    <div class="main-container">
        <!-- Work Form Card -->
        <div class="work-form-card slide-in-up" data-aos="fade-up" data-aos-delay="600">
            <h2 class="form-title">
                <i class="fas fa-plus-circle me-2"></i>Add Work Sample
            </h2>
            <p class="form-subtitle">
                Upload images and descriptions of your completed work to build your professional portfolio
            </p>
            
            <!-- Work Form -->
            <form action="" method="POST" enctype="multipart/form-data">
                <!-- Description -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-file-alt me-2"></i>Work Description
                    </label>
                    <textarea class="form-control form-textarea" name="description"
                              placeholder="Describe the work you completed, techniques used, challenges overcome, and results achieved..."
                              required></textarea>
                </div>
                
                <!-- Image Upload -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-image me-2"></i>Work Image
                    </label>
                    <div class="file-upload-container">
                        <input class="file-upload-input" type="file" name="image"
                               accept="image/*" required>
                        <div class="file-upload-content">
                            <div class="file-upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="file-upload-text">Click to upload work image</div>
                            <div class="file-upload-hint">JPG, PNG or JPEG (Max 5MB)</div>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Section -->
                <div class="submit-section">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-upload me-2"></i>Post Work
                    </button>
                    <a href="work_posts.php" class="btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Portfolio
                    </a>
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
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Posting Work...';
            submitBtn.disabled = true;
        });
        
        // Character counter for description
        const descriptionTextarea = document.querySelector('textarea[name="description"]');
        const charCounter = document.createElement('div');
        charCounter.style.cssText = 'text-align: right; font-size: 0.8rem; color: #6b7280; margin-top: 0.5rem;';
        descriptionTextarea.parentElement.appendChild(charCounter);
        
        descriptionTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCounter.textContent = `${length} characters`;
            
            if (length < 50) {
                charCounter.style.color = 'var(--danger-color)';
            } else if (length < 100) {
                charCounter.style.color = 'var(--warning-color)';
            } else {
                charCounter.style.color = 'var(--success-color)';
            }
        });
        
        // Trigger initial character count
        descriptionTextarea.dispatchEvent(new Event('input'));
    </script>
</body>
</html>
