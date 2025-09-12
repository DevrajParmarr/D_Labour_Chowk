<?php
/**
 * Modern UI Components for D Labour Chowk
 * Shared CSS and JavaScript components for consistent design
 */

function getModernUIStyles($userType = 'User') {
    $primaryColor = $userType === 'Labour' ? '#28a745' : '#667eea';
    $secondaryColor = $userType === 'Labour' ? '#20c997' : '#764ba2';
    $gradientPrimary = $userType === 'Labour' ? 
        'linear-gradient(135deg, #28a745 0%, #20c997 100%)' : 
        'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
    
    return "
    <style>
        :root {
            --primary-color: {$primaryColor};
            --secondary-color: {$secondaryColor};
            --accent-color: #f59e0b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
            --gradient-primary: {$gradientPrimary};
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

        /* Modern Header */
        .modern-header {
            background: var(--gradient-primary);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .modern-header::before {
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

        /* Modern Cards */
        .modern-card {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .modern-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        .modern-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-accent);
        }

        /* Modern Buttons */
        .btn-modern {
            background: var(--gradient-primary);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1rem;
        }

        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(var(--primary-color), 0.4);
            color: white;
        }

        .btn-modern.secondary {
            background: #6c757d;
        }

        .btn-modern.secondary:hover {
            box-shadow: 0 10px 25px rgba(108, 117, 125, 0.4);
        }

        /* Modern Form Controls */
        .form-control-modern {
            width: 100%;
            padding: 1rem 1.25rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #fafbfc;
        }

        .form-control-modern:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-color), 0.1);
            background: white;
            transform: translateY(-1px);
        }

        .form-label-modern {
            display: block;
            margin-bottom: 0.75rem;
            font-weight: 600;
            color: #374151;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Grid Layouts */
        .modern-grid {
            display: grid;
            gap: 2rem;
        }

        .modern-grid.cols-1 { grid-template-columns: 1fr; }
        .modern-grid.cols-2 { grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); }
        .modern-grid.cols-3 { grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); }
        .modern-grid.cols-4 { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); }

        /* Responsive Design */
        @media (max-width: 768px) {
            .modern-grid.cols-2,
            .modern-grid.cols-3,
            .modern-grid.cols-4 {
                grid-template-columns: 1fr;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease forwards;
        }

        /* Loading States */
        .loading {
            display: inline-block;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>";
}

function getModernUIScripts() {
    return "
    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js'></script>
    <script src='https://unpkg.com/aos@2.3.1/dist/aos.js'></script>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true
        });

        // Add loading states to buttons
        document.querySelectorAll('.btn-modern').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (this.type === 'submit' || this.href) {
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class=\"fas fa-spinner fa-spin me-2\"></i>Loading...';
                    this.disabled = true;
                    
                    // Reset after navigation delay
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.disabled = false;
                    }, 3000);
                }
            });
        });

        // Enhanced hover effects for cards
        document.querySelectorAll('.modern-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Form validation enhancements
        document.querySelectorAll('.form-control-modern').forEach(control => {
            control.addEventListener('focus', function() {
                this.parentElement.style.transform = 'translateY(-2px)';
                this.parentElement.style.transition = 'transform 0.3s ease';
            });
            
            control.addEventListener('blur', function() {
                this.parentElement.style.transform = 'translateY(0)';
            });
        });
    </script>";
}

function renderModernHeader($title, $subtitle, $userType = 'User', $welcomeText = null) {
    $userName = $_SESSION['user_name'] ?? 'User';
    $welcome = $welcomeText ?: "Welcome back, <strong>" . htmlspecialchars($userName) . "</strong>!";
    
    return "
    <div class='modern-header'>
        <div class='header-content'>
            <div class='welcome-text' data-aos='fade-down'>
                {$welcome}
            </div>
            <h1 class='main-title' data-aos='fade-up' data-aos-delay='200'>
                {$title}
            </h1>
            <p class='main-subtitle' data-aos='fade-up' data-aos-delay='400'>
                {$subtitle}
            </p>
        </div>
    </div>";
}

function renderStatCard($number, $label, $icon, $change = null) {
    $changeHtml = $change ? "<div class='stat-change'>{$change}</div>" : '';
    
    return "
    <div class='modern-card'>
        {$changeHtml}
        <div class='stat-icon'>
            <i class='{$icon}'></i>
        </div>
        <div class='stat-number'>{$number}</div>
        <div class='stat-label'>{$label}</div>
    </div>";
}
?>