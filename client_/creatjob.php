session_start();

<<<<<<< HEAD
=======
sleep(3.1);
>>>>>>> 39578cd55d61ac8c691bf23cfd350dd7248f990a
if(!isset($_SESSION["login_status"])){
    header('Location: ../Shared/login_form.php');
    exit;
}

if($_SESSION["login_status"]==false){
    header('Location: ../Shared/login_form.php');
    exit;
}

include "menu.html";
<<<<<<< HEAD
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Job Post - D Labour Chowk</title>
    <meta name="description" content="Post a new job and find skilled workers for your project. Easy job posting with detailed requirements.">
    
    <!-- Enhanced CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --accent-color: #f59e0b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
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
        
        /* Job Form Card */
        .job-form-card {
            background: white;
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            border: 1px solid #f1f5f9;
            position: relative;
            overflow: hidden;
        }
        
        .job-form-card::before {
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
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            background: white;
            transform: translateY(-1px);
        }
        
        .form-control::placeholder {
            color: #9ca3af;
        }
        
        /* Textarea */
        .form-textarea {
            min-height: 120px;
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
            background: rgba(37, 99, 235, 0.05);
        }
        
        .file-upload-container.dragover {
            border-color: var(--primary-color);
            background: rgba(37, 99, 235, 0.1);
            transform: scale(1.02);
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
        
        .file-selected {
            display: none;
            background: rgba(16, 185, 129, 0.1);
            border-color: var(--success-color);
            color: var(--success-color);
        }
        
        /* Row Layout */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
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
            box-shadow: 0 15px 35px rgba(37, 99, 235, 0.4);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }
        
        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Progress Steps */
        .progress-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
            padding: 0 1rem;
        }
        
        .step {
            display: flex;
            align-items: center;
            margin: 0 1rem;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e5e7eb;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 0.5rem;
            transition: all 0.3s ease;
        }
        
        .step.active .step-number {
            background: var(--gradient-primary);
            color: white;
        }
        
        .step.completed .step-number {
            background: var(--success-color);
            color: white;
        }
        
        .step-text {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 500;
        }
        
        .step.active .step-text {
            color: #1f2937;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .job-form-card {
                padding: 2rem 1.5rem;
                margin: 1rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
            
            .progress-steps {
                flex-direction: column;
                align-items: center;
            }
            
            .step {
                margin: 0.5rem 0;
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
        
        /* Loading State */
        .loading {
            display: none;
        }
        
        .loading.show {
            display: inline-block;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Success Message */
        .success-message {
            display: none;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid var(--success-color);
            color: var(--success-color);
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        /* Error Message */
        .error-message {
            display: none;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid var(--danger-color);
            color: var(--danger-color);
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header-section">
        <div class="header-content">
            <div class="welcome-text" data-aos="fade-down">
                Welcome back, <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>!
            </div>
            <h1 class="main-title" data-aos="fade-up" data-aos-delay="200">
                <i class="fas fa-briefcase me-3"></i>Create Job Post
            </h1>
            <p class="main-subtitle" data-aos="fade-up" data-aos-delay="400">
                Post your job requirements and connect with skilled workers in your area. 
                Get quality work done efficiently and reliably.
            </p>
        </div>
    </div>
    
    <!-- Main Container -->
    <div class="main-container">
        <!-- Job Form Card -->
        <div class="job-form-card slide-in-up" data-aos="fade-up" data-aos-delay="600">
            <!-- Progress Steps -->
            <div class="progress-steps">
                <div class="step active">
                    <div class="step-number">1</div>
                    <div class="step-text">Job Details</div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-text">Review</div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-text">Publish</div>
                </div>
            </div>
            
            <h2 class="form-title">
                <i class="fas fa-edit me-2"></i>Job Details
            </h2>
            <p class="form-subtitle">
                Provide detailed information about your job to attract the right workers
            </p>
            
            <!-- Success/Error Messages -->
            <div id="successMessage" class="success-message">
                <i class="fas fa-check-circle me-2"></i>Job post created successfully!
            </div>
            
            <div id="errorMessage" class="error-message">
                <i class="fas fa-exclamation-triangle me-2"></i>Please fix the errors below and try again.
            </div>
            
            <!-- Job Form -->
            <form id="jobForm" action="upload.php" method="post" enctype="multipart/form-data">
                <!-- Job Title and Salary Row -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-briefcase me-2"></i>Job Title *
                        </label>
                        <input class="form-control" type="text" name="jobTitle" id="jobTitle" 
                               placeholder="e.g., Plumber for Home Renovation" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-rupee-sign me-2"></i>Budget/Salary *
                        </label>
                        <input class="form-control" type="number" name="salary" id="salary" 
                               placeholder="Enter amount in ₹" min="100" required>
                    </div>
                </div>
                
                <!-- Job Description -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-file-alt me-2"></i>Job Description *
                    </label>
                    <textarea class="form-control form-textarea" name="detail" id="detail" 
                              placeholder="Describe the work requirements, skills needed, timeline, and any specific instructions..." 
                              required></textarea>
                </div>
                
                <!-- City and Location Row -->
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-city me-2"></i>City *
                        </label>
                        <select class="form-control" name="city" id="city" required>
                            <option value="">Select your city</option>
                            <option value="Indore">Indore</option>
                            <option value="Bhopal">Bhopal</option>
                            <option value="Ujjain">Ujjain</option>
                            <option value="Jabalpur">Jabalpur</option>
                            <option value="Kota">Kota</option>
                            <option value="Jaipur">Jaipur</option>
                            <option value="Delhi">Delhi</option>
                            <option value="Mumbai">Mumbai</option>
                            <option value="Pune">Pune</option>
                            <option value="Bangalore">Bangalore</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-map-marker-alt me-2"></i>Specific Location *
                        </label>
                        <input class="form-control" type="text" name="location" id="location" 
                               placeholder="e.g., Vijay Nagar, Near City Center" required>
                    </div>
                </div>
                
                <!-- File Upload -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-image me-2"></i>Job Image (Optional)
                    </label>
                    <div class="file-upload-container" id="fileUploadContainer">
                        <input class="file-upload-input" type="file" name="pdtimg" id="pdtimg" 
                               accept=".jpg,.png,.jpeg">
                        <div class="file-upload-content" id="uploadContent">
                            <div class="file-upload-icon">
                                <i class="fas fa-cloud-upload-alt"></i>
                            </div>
                            <div class="file-upload-text">Click to upload or drag and drop</div>
                            <div class="file-upload-hint">JPG, PNG or JPEG (Max 5MB)</div>
                        </div>
                        <div class="file-selected" id="fileSelected">
                            <div class="file-upload-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="file-upload-text">File selected successfully!</div>
                            <div class="file-upload-hint" id="fileName"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Submit Section -->
                <div class="submit-section">
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <i class="fas fa-paper-plane me-2"></i>Create Job Post
                        <i class="fas fa-spinner loading" id="loadingIcon"></i>
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
        
        // Form elements
        const jobForm = document.getElementById('jobForm');
        const submitBtn = document.getElementById('submitBtn');
        const loadingIcon = document.getElementById('loadingIcon');
        const fileInput = document.getElementById('pdtimg');
        const fileUploadContainer = document.getElementById('fileUploadContainer');
        const uploadContent = document.getElementById('uploadContent');
        const fileSelected = document.getElementById('fileSelected');
        const fileName = document.getElementById('fileName');
        
        // File upload handling
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                fileName.textContent = file.name;
                uploadContent.style.display = 'none';
                fileSelected.style.display = 'block';
                fileUploadContainer.style.borderColor = 'var(--success-color)';
                fileUploadContainer.style.background = 'rgba(16, 185, 129, 0.05)';
            }
        });
        
        // Drag and drop functionality
        fileUploadContainer.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('dragover');
        });
        
        fileUploadContainer.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
        });
        
        fileUploadContainer.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                fileInput.dispatchEvent(new Event('change'));
            }
        });
        
        // Form validation and submission
        jobForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Basic validation
            const jobTitle = document.getElementById('jobTitle').value.trim();
            const salary = document.getElementById('salary').value;
            const detail = document.getElementById('detail').value.trim();
            const city = document.getElementById('city').value;
            const location = document.getElementById('location').value.trim();
            
            if (!jobTitle || !salary || !detail || !city || !location) {
                showError('Please fill in all required fields.');
                return;
            }
            
            if (salary < 100) {
                showError('Salary must be at least ₹100.');
                return;
            }
            
            if (detail.length < 20) {
                showError('Job description must be at least 20 characters long.');
                return;
            }
            
            // Show loading state
            submitBtn.disabled = true;
            loadingIcon.classList.add('show');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating Post...';
            
            // Update progress steps
            updateProgressSteps(2);
            
            // Submit form after a short delay (for UX)
            setTimeout(() => {
                updateProgressSteps(3);
                setTimeout(() => {
                    jobForm.submit();
                }, 500);
            }, 1000);
        });
        
        // Helper functions
        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>' + message;
            errorDiv.style.display = 'block';
            
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 5000);
        }
        
        function updateProgressSteps(activeStep) {
            const steps = document.querySelectorAll('.step');
            steps.forEach((step, index) => {
                const stepNumber = index + 1;
                if (stepNumber < activeStep) {
                    step.classList.remove('active');
                    step.classList.add('completed');
                } else if (stepNumber === activeStep) {
                    step.classList.remove('completed');
                    step.classList.add('active');
                } else {
                    step.classList.remove('active', 'completed');
                }
            });
        }
        
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
        
        // Character counter for description
        const detailTextarea = document.getElementById('detail');
        const charCounter = document.createElement('div');
        charCounter.style.cssText = 'text-align: right; font-size: 0.8rem; color: #6b7280; margin-top: 0.5rem;';
        detailTextarea.parentElement.appendChild(charCounter);
        
        detailTextarea.addEventListener('input', function() {
            const length = this.value.length;
            charCounter.textContent = `${length} characters`;
            
            if (length < 20) {
                charCounter.style.color = 'var(--danger-color)';
            } else if (length < 50) {
                charCounter.style.color = 'var(--warning-color)';
            } else {
                charCounter.style.color = 'var(--success-color)';
            }
        });
        
        // Trigger initial character count
        detailTextarea.dispatchEvent(new Event('input'));
    </script>
</body>
=======

echo "<h1 class='d-flex justify-content-center bg-white p-3 mt-3'>Hello {$_SESSION['user_name']}</h1>";


echo "<h1 class='d-flex justify-content-center '>Create job Post </h1>";

?>


<!DOCTYPE html> <html lang="en"> 
<head>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<link rel="stylesheet" href="creatjob.css">
</head>

<body>

<div class="d-flex justify-content-center align-items-center vh-80">
<form class="w-50 bg-warning p-3" action="upload.php" method="post" enctype="multipart/form-data">

<input class="form-control mt-3" type="text" placeholder="Job / Work name" name="jobTitle" required>
<input class="form-control mt-2" type="number" placeholder="Budget / Expected Wage you pay " name="salary" required>
<textarea class="form-control mt-2" name="detail" cols="30" rows="5" placeholder="Job Detail Description :" reqired></textarea> 
<input class="form-control mt-3" type="text" placeholder="City in which you used to live" name="city" reqired>
<input class="form-control mt-3" type="text" placeholder="Location:describe proper location" name="location" reqired>
<input class="form-control mt-2" type="file" accept=" .jpg, .png, .jpeg" name="pdtimg" reqired>
<div class="mt-3 text-center">

<button class="btn btn-success"> Create Post</button>

</div>
</form>
</div>


<script src="creatjob.js"></script>
</body>

>>>>>>> 39578cd55d61ac8c691bf23cfd350dd7248f990a
</html>

?>
