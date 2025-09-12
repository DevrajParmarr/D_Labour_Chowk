<?php
include "menu.html";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Workers - D Labour Chowk</title>
    <meta name="description" content="Search and hire skilled workers in your area. Find carpenters, plumbers, electricians, and more.">
    
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .main-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 2rem 0;
        }
        
        .search-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 3rem;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .search-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        
        .search-title {
            font-family: 'Poppins', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        .search-subtitle {
            color: #6b7280;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        
        .search-stats {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        
        .stat-item {
            text-align: center;
            background: rgba(102, 126, 234, 0.1);
            padding: 1rem 1.5rem;
            border-radius: 12px;
            min-width: 120px;
        }
        
        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        
        .search-form {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
            transform: translateY(-1px);
        }
        
        .form-control::placeholder {
            color: #9ca3af;
        }
        
        .btn-search {
            width: 100%;
            background: var(--gradient-primary);
            border: none;
            color: white;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 1rem;
        }
        
        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4);
            color: white;
        }
        
        .btn-search:active {
            transform: translateY(0);
        }
        
        .quick-categories {
            margin-top: 2rem;
            text-align: center;
        }
        
        .categories-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .category-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
        }
        
        .category-tag {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
        }
        
        .category-tag:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
            color: white;
        }
        
        .back-link {
            position: fixed;
            top: 2rem;
            left: 2rem;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            color: #374151;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            z-index: 1000;
        }
        
        .back-link:hover {
            background: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color: var(--primary-color);
        }
        
        /* Advanced Search Toggle */
        .advanced-toggle {
            text-align: center;
            margin: 1.5rem 0;
        }
        
        .advanced-btn {
            background: none;
            border: none;
            color: var(--primary-color);
            font-weight: 500;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .advanced-btn:hover {
            background: rgba(37, 99, 235, 0.1);
        }
        
        .advanced-fields {
            display: none;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
        }
        
        .advanced-fields.show {
            display: block;
            animation: fadeInUp 0.3s ease;
        }
        
        .row {
            display: flex;
            gap: 1rem;
            margin: 0 -0.5rem;
        }
        
        .col {
            flex: 1;
            padding: 0 0.5rem;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .search-card {
                margin: 1rem;
                padding: 2rem;
            }
            
            .search-title {
                font-size: 2rem;
            }
            
            .search-stats {
                gap: 1rem;
            }
            
            .stat-item {
                min-width: 100px;
                padding: 0.75rem 1rem;
            }
            
            .back-link {
                position: static;
                display: inline-block;
                margin-bottom: 1rem;
            }
            
            .row {
                flex-direction: column;
                gap: 0;
            }
        }
        
        /* Loading Animation */
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
    </style>
</head>
<body>
    <a href="../Shared/index.html" class="back-link">
        <i class="fas fa-arrow-left me-2"></i>Back to Home
    </a>
    
    <div class="main-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="search-card" data-aos="fade-up">
                        <div class="search-header">
                            <h1 class="search-title">
                                <i class="fas fa-search me-3"></i>Find Workers
                            </h1>
                            <p class="search-subtitle">
                                Connect with skilled professionals in your area. Quality work guaranteed.
                            </p>
                            
                            <div class="search-stats">
                                <div class="stat-item">
                                    <div class="stat-number">10K+</div>
                                    <div class="stat-label">Active Workers</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">500+</div>
                                    <div class="stat-label">Cities Covered</div>
                                </div>
                                <div class="stat-item">
                                    <div class="stat-number">50K+</div>
                                    <div class="stat-label">Jobs Completed</div>
                                </div>
                            </div>
                        </div>
                        
                        <form action="search.php" method="get" class="search-form" id="searchForm">
                            <div class="form-group">
                                <label for="workType" class="form-label">
                                    <i class="fas fa-tools me-2"></i>Work Type
                                </label>
                                <input type="text" 
                                       id="workType" 
                                       name="workType" 
                                       class="form-control" 
                                       placeholder="e.g., Carpenter, Plumber, Electrician" 
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="city" class="form-label">
                                    <i class="fas fa-map-marker-alt me-2"></i>City
                                </label>
                                <input type="text" 
                                       id="city" 
                                       name="city" 
                                       class="form-control" 
                                       placeholder="e.g., Mumbai, Delhi, Bangalore" 
                                       required>
                            </div>
                            
                            <div class="advanced-toggle">
                                <button type="button" class="advanced-btn" onclick="toggleAdvanced()">
                                    <i class="fas fa-sliders-h me-2"></i>Advanced Search
                                    <i class="fas fa-chevron-down ms-2" id="advancedIcon"></i>
                                </button>
                            </div>
                            
                            <div class="advanced-fields" id="advancedFields">
                                <div class="row">
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="minSalary" class="form-label">
                                                <i class="fas fa-rupee-sign me-2"></i>Min Salary
                                            </label>
                                            <input type="number" 
                                                   id="minSalary" 
                                                   name="minSalary" 
                                                   class="form-control" 
                                                   placeholder="e.g., 15000">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="form-group">
                                            <label for="experience" class="form-label">
                                                <i class="fas fa-star me-2"></i>Experience
                                            </label>
                                            <select id="experience" name="experience" class="form-control">
                                                <option value="">Any Experience</option>
                                                <option value="0-2">0-2 years</option>
                                                <option value="3-5">3-5 years</option>
                                                <option value="5+">5+ years</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn-search">
                                <i class="fas fa-search me-2"></i>Search Workers
                                <i class="fas fa-spinner loading" id="loadingIcon"></i>
                            </button>
                        </form>
                        
                        <div class="quick-categories">
                            <div class="categories-title">
                                <i class="fas fa-fire me-2"></i>Popular Categories
                            </div>
                            <div class="category-tags">
                                <a href="#" class="category-tag" onclick="quickSearch('Carpenter')">
                                    <i class="fas fa-hammer me-2"></i>Carpenter
                                </a>
                                <a href="#" class="category-tag" onclick="quickSearch('Plumber')">
                                    <i class="fas fa-wrench me-2"></i>Plumber
                                </a>
                                <a href="#" class="category-tag" onclick="quickSearch('Electrician')">
                                    <i class="fas fa-bolt me-2"></i>Electrician
                                </a>
                                <a href="#" class="category-tag" onclick="quickSearch('Painter')">
                                    <i class="fas fa-paint-roller me-2"></i>Painter
                                </a>
                                <a href="#" class="category-tag" onclick="quickSearch('Mason')">
                                    <i class="fas fa-cube me-2"></i>Mason
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 600,
            easing: 'ease-in-out',
            once: true
        });
        
        // Toggle advanced search
        function toggleAdvanced() {
            const fields = document.getElementById('advancedFields');
            const icon = document.getElementById('advancedIcon');
            
            if (fields.classList.contains('show')) {
                fields.classList.remove('show');
                icon.className = 'fas fa-chevron-down ms-2';
            } else {
                fields.classList.add('show');
                icon.className = 'fas fa-chevron-up ms-2';
            }
        }
        
        // Quick search functionality
        function quickSearch(workType) {
            document.getElementById('workType').value = workType;
            document.getElementById('city').focus();
        }
        
        // Form submission with loading animation
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            const submitBtn = document.querySelector('.btn-search');
            const loadingIcon = document.getElementById('loadingIcon');
            
            // Show loading animation
            loadingIcon.classList.add('show');
            submitBtn.disabled = true;
            
            // Optional: Add a slight delay to show the loading animation
            setTimeout(() => {
                // The form will submit naturally
            }, 500);
        });
        
        // Auto-complete suggestions (basic implementation)
        const cities = ['Mumbai', 'Delhi', 'Bangalore', 'Chennai', 'Kolkata', 'Pune', 'Hyderabad', 'Ahmedabad', 'Indore', 'Bhopal'];
        const workTypes = ['Carpenter', 'Plumber', 'Electrician', 'Painter', 'Mason', 'Welder', 'Driver', 'Cook', 'Cleaner', 'Guard'];
        
        function addAutoComplete(inputId, suggestions) {
            const input = document.getElementById(inputId);
            
            input.addEventListener('input', function() {
                const value = this.value.toLowerCase();
                // Simple auto-complete logic can be added here
                // For now, just adding placeholder functionality
            });
        }
        
        // Initialize auto-complete
        addAutoComplete('city', cities);
        addAutoComplete('workType', workTypes);
        
        // Add input focus effects
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>
