# 🚂 D Labour Chowk - Railway Deployment Guide (Simplified)

## Overview
This guide will help you deploy D Labour Chowk to **Railway** using their simplified PHP deployment approach.

## ✅ What's Already Prepared

- ✅ **Database Configuration** - Updated for Railway's MySQL environment variables
- ✅ **Simplified Configuration** - Removed FrankenPHP complexity
- ✅ **Standard PHP Setup** - Using Railway's built-in PHP support
- ✅ **File Structure** - Optimized for Railway deployment
- ✅ **Environment Variables** - Configured for Railway MySQL

## 🛠️ Step-by-Step Railway Deployment

### Step 1: Create Railway Account
```bash
# Go to https://railway.app
# Sign up with GitHub account
# Verify your email
```

### Step 2: Connect GitHub Repository
```bash
# In Railway Dashboard:
# 1. Click "New Project"
# 2. Choose "Deploy from GitHub repo"
# 3. Connect your GitHub account
# 4. Search for "D_Labour_Chowk"
# 5. Select your repository
```

### Step 3: Configure Project
Railway will automatically detect your `railway.json` and configure:
- ✅ PHP 8.2 runtime
- ✅ Composer dependencies installation
- ✅ PHP built-in server
- ✅ Environment variables

### Step 4: Add MySQL Database
```bash
# In your Railway project:
# 1. Click "Add Plugin"
# 2. Search for "MySQL"
# 3. Choose "MySQL" (Free tier)
# 4. Railway automatically links it to your app
```

### Step 5: Deploy
```bash
# Click "Deploy" in Railway dashboard
# Railway will:
# - Clone your repository
# - Install PHP dependencies
# - Set up MySQL database
# - Start your application
```

## 📊 Database Setup

### Automatic Setup (Recommended)
Railway automatically provides these environment variables:
```bash
MYSQLHOST=your-mysql-host
MYSQLUSER=your-mysql-username
MYSQLPASSWORD=your-mysql-password
MYSQLDATABASE=your-database-name
MYSQLPORT=3306
```

### Manual Database Import
```bash
# After deployment, connect to your MySQL database:
# 1. Go to MySQL plugin in Railway dashboard
# 2. Click "Connect"
# 3. Use the provided credentials
# 4. Import your d_labour.sql file
```

## 🔧 Configuration Files

### railway.json (Already Created)
```json
{
  "build": {
    "builder": "NIXPACKS",
    "buildCommand": "composer install --no-dev --optimize-autoloader"
  },
  "deploy": {
    "startCommand": "php -S 0.0.0.0:$PORT -t .",
    "healthcheckPath": "/",
    "healthcheckTimeout": 300,
    "restartPolicyType": "ON_FAILURE",
    "restartPolicyMaxRetries": 10
  },
  "variables": {
    "NIXPACKS_PHP_VERSION": "8.2",
    "PHP_MEMORY_LIMIT": "256M",
    "PHP_MAX_EXECUTION_TIME": "300"
  }
}
```

### .htaccess (Already Optimized)
- ✅ Simplified for PHP built-in server
- ✅ Security headers included
- ✅ HTTPS enforcement
- ✅ File upload configuration

## 📁 File Structure for Railway

```
D_Labour_Chowk/
├── 📄 index.php              # Main entry point
├── 📄 index.html             # Landing page
├── 📁 config/                # Configuration files
├── 📁 src/Controllers/       # PHP controllers
├── 📁 public/                # Static assets
├── 📁 Shared/                # Shared resources
├── 📄 composer.json          # PHP dependencies
├── 📄 railway.json           # Railway configuration
├── 📄 .htaccess              # Apache configuration
└── 📄 .gitignore             # Git ignore rules
```

## 🚀 Post-Deployment Checklist

- [ ] **Test Homepage**: `https://your-app.railway.app/`
- [ ] **Test Login**: `https://your-app.railway.app/Shared/login_form.php`
- [ ] **Test Registration**: `https://your-app.railway.app/Shared/signup_form.php`
- [ ] **Test Database**: Check if user registration works
- [ ] **Test File Uploads**: Verify image uploads work
- [ ] **Test Email**: Check if password reset emails work

## 🔧 Troubleshooting

### Common Issues:

1. **Build Fails**
   ```bash
   # Check Railway build logs
   # Ensure composer.json is valid
   # Verify PHP version compatibility
   ```

2. **Database Connection Error**
   ```bash
   # Check environment variables in Railway dashboard
   # Verify MySQL plugin is attached
   # Check database credentials
   ```

3. **File Not Found Errors**
   ```bash
   # Check .htaccess configuration
   # Verify file paths in code
   # Ensure files are committed to Git
   ```

4. **PHP Errors**
   ```bash
   # Check PHP error logs in Railway
   # Enable error reporting temporarily
   # Verify PHP extensions
   ```

## 💰 Railway Pricing

- **Free Tier**: 512MB RAM, 100 hours/month
- **MySQL**: Free with 1GB storage
- **Custom Domain**: Paid feature ($10/month)
- **SSL**: Automatic and free
- **Scaling**: Automatic based on usage

## 🎯 Why This Approach Works

### ✅ Advantages:
- **Simplified Setup** - No complex Docker/Caddy configuration
- **Railway Native** - Uses Railway's built-in PHP support
- **Automatic Scaling** - Handles traffic spikes
- **Easy Debugging** - Clear logs and error messages
- **Fast Deployment** - Quick setup and deployment

### ✅ What We Removed:
- ❌ FrankenPHP complexity
- ❌ Custom Docker configuration
- ❌ Complex Caddyfile routing
- ❌ Manual server setup

## 🚀 Quick Start Commands

```bash
# 1. Push to GitHub (already done)
git add .
git commit -m "Railway deployment - simplified"
git push origin main

# 2. Deploy on Railway
# - Go to railway.app
# - New Project → GitHub repo
# - Select D_Labour_Chowk
# - Add MySQL plugin
# - Deploy!
```

## 📞 Support

- **Railway Docs**: https://docs.railway.app/
- **Community**: Railway Discord/Community
- **Logs**: Check deployment logs in Railway dashboard

## 🎉 Success!

Once deployed, your D Labour Chowk will be live at:
**`https://your-project-name.railway.app/`**

**Features that will work:**
- ✅ User registration and login
- ✅ Job posting and browsing
- ✅ File uploads
- ✅ Database operations
- ✅ Email notifications
- ✅ Responsive design

**This simplified approach should work much better than the previous FrankenPHP setup!** 🚀