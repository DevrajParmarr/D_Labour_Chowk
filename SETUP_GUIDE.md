# 🚀 D Labour Chowk - Complete Setup Guide

This comprehensive guide will walk you through setting up the Digital Labour Chowk platform from scratch.

## 📋 Table of Contents

- [System Requirements](#-system-requirements)
- [Pre-installation Checklist](#-pre-installation-checklist)
- [Step-by-Step Installation](#-step-by-step-installation)
- [Database Configuration](#-database-configuration)
- [Security Configuration](#-security-configuration)
- [Performance Optimization](#-performance-optimization)
- [Testing Setup](#-testing-setup)
- [Troubleshooting](#-troubleshooting)
- [Production Deployment](#-production-deployment)

---

## 💻 System Requirements

### Minimum Requirements
- **OS**: Windows 10, macOS 10.14, Ubuntu 18.04+
- **PHP**: 8.0 or higher
- **MySQL**: 5.7 or higher / MariaDB 10.3+
- **Apache**: 2.4+
- **RAM**: 2GB minimum
- **Storage**: 1GB free space
- **Browser**: Chrome 80+, Firefox 75+, Safari 13+, Edge 80+

### Recommended Requirements
- **PHP**: 8.1+
- **MySQL**: 8.0+
- **RAM**: 4GB+
- **Storage**: 5GB+ free space
- **SSD**: For better performance

---

## ✅ Pre-installation Checklist

Before starting, ensure you have:

- [ ] Administrative privileges on your system
- [ ] Stable internet connection
- [ ] Antivirus temporarily disabled (if needed)
- [ ] Backup of existing web server (if any)
- [ ] Text editor (VS Code, Sublime Text, etc.)

---

## 🛠 Step-by-Step Installation

### Step 1: Download and Install XAMPP

1. **Download XAMPP**
   ```
   🌐 Visit: https://www.apachefriends.org/download.html
   📦 Choose: XAMPP for Windows/Mac/Linux
   ✅ Version: 8.0+ with PHP 8.0+
   ```

2. **Install XAMPP**
   ```bash
   # Windows
   - Run the installer as Administrator
   - Choose installation directory (default: C:\xampp)
   - Select components: Apache, MySQL, PHP, phpMyAdmin
   
   # macOS
   sudo installer -pkg xampp-*.pkg -target /
   
   # Linux
   chmod +x xampp-linux-*-installer.run
   sudo ./xampp-linux-*-installer.run
   ```

3. **Start Services**
   ```bash
   # Open XAMPP Control Panel
   # Start Apache and MySQL services
   # Verify: http://localhost should show XAMPP dashboard
   ```

### Step 2: Download Project Files

**Option A: Direct Download**
```bash
# Download ZIP from project repository
# Extract to: C:\xampp\htdocs\D_Labour_Chowk
```

**Option B: Git Clone**
```bash
cd C:\xampp\htdocs
git clone https://github.com/yourusername/D_Labour_Chowk.git
cd D_Labour_Chowk
```

### Step 3: Set File Permissions

**Windows:**
```batch
# Right-click project folder
# Properties → Security → Edit
# Give "Full Control" to your user account
```

**Unix/Linux:**
```bash
sudo chown -R www-data:www-data /opt/lampp/htdocs/D_Labour_Chowk
sudo chmod -R 755 /opt/lampp/htdocs/D_Labour_Chowk
sudo chmod -R 777 /opt/lampp/htdocs/D_Labour_Chowk/Shared/images
sudo chmod -R 777 /opt/lampp/htdocs/D_Labour_Chowk/cache
```

---

## 🗄️ Database Configuration

### Step 1: Create Database

1. **Access phpMyAdmin**
   ```
   🌐 http://localhost/phpmyadmin
   👤 Username: root
   🔑 Password: (leave empty)
   ```

2. **Create Database**
   ```sql
   CREATE DATABASE d_labour CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Import Schema**
   ```bash
   # In phpMyAdmin:
   # 1. Select 'd_labour' database
   # 2. Click 'Import' tab
   # 3. Choose file: d_labour.sql
   # 4. Click 'Go'
   ```

### Step 2: Verify Database Structure

Expected tables after import:
```
✅ user                 (User accounts)
✅ job_post             (Job postings)
✅ lab_post             (Worker profiles)
✅ hires                (Hiring records)
✅ job_applications     (Job applications)
✅ ratings              (Reviews & ratings)
✅ work_posts           (Portfolio posts)
```

### Step 3: Create Sample Data (Optional)

```sql
-- Create test users
INSERT INTO user (user_name, email_id, mobile_no, password, user_type) VALUES 
('Test Client', 'client@test.com', '9999999999', '$2y$12$example_hash', 'User'),
('Test Worker', 'worker@test.com', '8888888888', '$2y$12$example_hash', 'Labour');
```

---

## ⚙️ Application Configuration

### Step 1: Database Configuration

Edit `Shared/config.php`:
```php
<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');  // Default XAMPP password is empty
define('DB_NAME', 'd_labour');

// Application Configuration
define('APP_NAME', 'D Labour Chowk');
define('APP_URL', 'http://localhost/D_Labour_Chowk');
define('APP_VERSION', '2.0');

// Enable development mode for testing
define('DEVELOPMENT_MODE', true);
?>
```

### Step 2: Performance Configuration

```php
// File Upload Configuration
define('UPLOAD_PATH', 'Shared/images/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'pdf']);

// Session Configuration
define('SESSION_TIMEOUT', 30 * 60); // 30 minutes

// Security Configuration
define('BCRYPT_COST', 12);
define('CSRF_TOKEN_LENGTH', 32);

// Performance Configuration
define('CACHE_TTL', 3600); // 1 hour
define('MAX_IMAGE_WIDTH', 1200);
define('MAX_IMAGE_HEIGHT', 800);
define('IMAGE_QUALITY', 85);
```

### Step 3: Create Required Directories

```bash
# Create cache directory
mkdir cache
chmod 777 cache

# Create optimized images directory
mkdir Shared/images/optimized
chmod 777 Shared/images/optimized

# Create logs directory
mkdir logs
chmod 777 logs
```

---

## 🔐 Security Configuration

### Step 1: Enable Security Headers

Add to `.htaccess` in project root:
```apache
# Security Headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
Header always set Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; img-src 'self' data:;"

# Hide PHP Version
Header unset X-Powered-By
ServerTokens Prod

# Prevent access to sensitive files
<Files "config.php">
    Require all denied
</Files>

<Files "*.log">
    Require all denied
</Files>

# Enable HTTPS redirect (for production)
# RewriteEngine On
# RewriteCond %{HTTPS} off
# RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### Step 2: Secure File Uploads

```php
// In config.php - Add upload security
define('UPLOAD_SECURITY', [
    'max_size' => 5 * 1024 * 1024, // 5MB
    'allowed_types' => ['image/jpeg', 'image/png', 'image/gif'],
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif'],
    'scan_viruses' => false, // Set to true if antivirus available
    'watermark' => true
]);
```

---

## ⚡ Performance Optimization

### Step 1: Enable PHP OpCache

Edit `php.ini`:
```ini
; Enable OpCache
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=7963
opcache.revalidate_freq=60
opcache.fast_shutdown=1

; Increase memory limits
memory_limit=512M
max_execution_time=300
max_input_vars=3000
post_max_size=50M
upload_max_filesize=50M
```

### Step 2: Configure MySQL

Edit `my.cnf` or `my.ini`:
```ini
[mysql]
# Increase buffer sizes
innodb_buffer_pool_size=512M
innodb_log_file_size=256M
query_cache_size=64M
query_cache_limit=2M

# Optimize connections
max_connections=200
thread_cache_size=16
table_open_cache=4096
```

### Step 3: Enable Application Caching

```php
// Initialize performance optimizer in your main files
require_once 'Shared/PerformanceOptimizer.php';

$optimizer = PerformanceOptimizer::getInstance();
$optimizer->enableCompression();
$optimizer->setCacheHeaders(3600); // 1 hour cache
```

---

## 🧪 Testing Setup

### Step 1: Verify Installation

```bash
# Access the test runner
http://localhost/D_Labour_Chowk/tests/TestRunner.php
```

### Step 2: Run System Tests

Expected test results:
```
✅ Database Connection
✅ Database Tables Exist
✅ Configuration Constants
✅ Helper Functions
✅ User Authentication Functions
✅ Database CRUD Operations
✅ Security Measures
✅ File Structure
✅ Configuration Values
✅ Basic Performance
✅ Edge Cases
```

### Step 3: Manual Testing Checklist

- [ ] Homepage loads correctly
- [ ] User registration works
- [ ] User login works
- [ ] Dashboard displays properly
- [ ] Image upload functions
- [ ] Search functionality works
- [ ] Database queries execute
- [ ] Security headers present
- [ ] Mobile responsive design

---

## 🌐 First Run & Verification

### Step 1: Access the Application

```bash
# Homepage
http://localhost/D_Labour_Chowk/Shared/

# Direct login
http://localhost/D_Labour_Chowk/Shared/login_form.php

# Registration
http://localhost/D_Labour_Chowk/Shared/signup_form.php
```

### Step 2: Create Test Accounts

1. **Create Client Account**
   - Username: Test Client
   - Email: client@test.com
   - Mobile: 9999999999
   - Type: Client

2. **Create Worker Account**
   - Username: Test Worker
   - Email: worker@test.com
   - Mobile: 8888888888
   - Type: Worker

### Step 3: Test Core Features

**Client Dashboard:**
- [ ] View statistics
- [ ] Post a job
- [ ] Browse workers
- [ ] Advanced search
- [ ] View analytics

**Worker Dashboard:**
- [ ] Create profile
- [ ] Browse jobs
- [ ] Apply to jobs
- [ ] Upload work samples
- [ ] View applications

---

## 🐛 Troubleshooting

### Common Issues & Solutions

**Database Connection Error**
```
Error: Database connection failed
Solution:
1. Verify MySQL service is running
2. Check credentials in config.php
3. Ensure database 'd_labour' exists
4. Test connection: http://localhost/phpmyadmin
```

**File Upload Errors**
```
Error: Failed to upload file
Solution:
1. Check directory permissions (777 for uploads)
2. Verify PHP upload settings in php.ini
3. Check file size limits
4. Ensure proper file extensions
```

**Session Issues**
```
Error: User not logged in / Session expired
Solution:
1. Clear browser cache and cookies
2. Check session configuration
3. Verify session directory permissions
4. Restart Apache service
```

**Performance Issues**
```
Error: Slow loading times
Solution:
1. Enable caching in config
2. Optimize images
3. Check database queries
4. Enable compression
5. Increase PHP memory limit
```

**CSS/JS Not Loading**
```
Error: Styling/functionality missing
Solution:
1. Check file paths
2. Verify .htaccess rules
3. Clear browser cache
4. Check console for errors
5. Verify CDN connections
```

### Debug Mode

Enable detailed error reporting:
```php
// In config.php
define('DEVELOPMENT_MODE', true);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

### Log Files

Check these locations for errors:
```bash
# XAMPP Error Logs
C:\xampp\apache\logs\error.log
C:\xampp\mysql\data\mysql_error.log

# PHP Errors
C:\xampp\php\logs\php_error_log

# Application Logs
/D_Labour_Chowk/logs/application.log
```

---

## 🚀 Production Deployment

### Pre-deployment Checklist

- [ ] Disable DEVELOPMENT_MODE
- [ ] Update database credentials
- [ ] Enable HTTPS
- [ ] Configure proper file permissions
- [ ] Set up backup system
- [ ] Configure monitoring
- [ ] Test all functionality
- [ ] Performance optimization enabled

### Security Hardening

```php
// Production config.php
define('DEVELOPMENT_MODE', false);
define('FORCE_HTTPS', true);
define('SESSION_SECURE', true);
define('SESSION_HTTPONLY', true);
define('CSRF_PROTECTION', true);
```

### Server Configuration

**Apache Virtual Host:**
```apache
<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /var/www/html/D_Labour_Chowk
    
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    
    <Directory /var/www/html/D_Labour_Chowk>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

---

## ✅ Setup Complete!

If you've followed this guide successfully, you should now have:

- ✅ Fully functional D Labour Chowk platform
- ✅ Secure authentication system
- ✅ Modern responsive design
- ✅ Performance optimizations enabled
- ✅ Comprehensive testing suite
- ✅ Production-ready configuration

### Next Steps

1. **Customize branding** - Update logos, colors, and content
2. **Add content** - Create initial job categories and sample data
3. **Configure email** - Set up SMTP for notifications
4. **Set up backup** - Implement regular database backups
5. **Monitor performance** - Set up analytics and monitoring

### Support

If you encounter any issues:
- 📧 Email: support@labourchowd.com
- 🐛 Issues: GitHub Issues
- 📚 Documentation: Project Wiki

---

**🎉 Congratulations! Your Digital Labour Chowk platform is ready to connect workers with opportunities!** 🚀
