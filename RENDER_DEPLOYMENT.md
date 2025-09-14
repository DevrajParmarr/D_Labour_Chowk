# 🚀 D Labour Chowk - Render Deployment Guide

## Overview
This guide will help you deploy D Labour Chowk to **Render** - a modern cloud platform with excellent PHP support and free tier.

## ✅ What's Already Prepared

- ✅ **Database Configuration** - Updated to support PostgreSQL and MySQL
- ✅ **Environment Variables** - Configured for Render's `DATABASE_URL`
- ✅ **File Structure** - Organized for production deployment
- ✅ **Security** - HTTPS enforcement and proper headers
- ✅ **Dependencies** - Composer configuration ready

## 🛠️ Step-by-Step Render Deployment

### Option 1: Blueprint Deployment (Recommended)

#### Step 1: Create Render Account
```bash
# Go to https://render.com
# Sign up with GitHub account
# Verify your email
```

#### Step 2: Connect GitHub Repository
```bash
# In Render dashboard:
# Click "New" → "Blueprint"
# Choose "Connect GitHub"
# Authorize Render to access your repositories
# Select your "D_Labour_Chowk" repository
```

#### Step 3: Deploy Blueprint
```bash
# Click "Create Blueprint"
# Render will automatically:
# - Read render.yaml configuration
# - Create PostgreSQL database
# - Create web service with Docker
# - Link database to web service
# - Deploy the application
```

### Option 2: Manual Deployment

#### Step 1: Create Render Account
```bash
# Go to https://render.com
# Sign up with GitHub account
# Verify your email
```

#### Step 2: Connect GitHub Repository
```bash
# In Render dashboard:
# Click "New" → "Web Service"
# Choose "Connect GitHub"
# Authorize Render to access your repositories
# Select your "D_Labour_Chowk" repository
```

#### Step 3: Configure Web Service
```yaml
# Service Configuration:
Name: dlabour-chowk
Runtime: Docker
Dockerfile Path: ./Dockerfile
```

#### Step 4: Create PostgreSQL Database
```bash
# In Render dashboard:
# Click "New" → "PostgreSQL"
# Name: dlabour-db
# Plan: Free
# Region: Any (closest to your users)
```

#### Step 5: Link Database to Web Service
```bash
# In your web service settings:
# Go to "Environment"
# Add DATABASE_URL from PostgreSQL service
```

#### Step 6: Deploy
```bash
# Click "Create Web Service"
# Render will automatically build and deploy
```

## 📊 Database Setup

### PostgreSQL Schema (Automatic)
The `render.yaml` blueprint automatically creates a PostgreSQL database and the schema is defined in `database/d_labour_postgres.sql`.

### Manual Database Setup
If deploying manually, after creating the PostgreSQL database:

1. **Connect to Database**: Use Render's database connection details
2. **Run Schema**: Execute the SQL from `database/d_labour_postgres.sql`
3. **Verify Connection**: Check that the web app can connect

### Schema Conversion Notes
- `AUTO_INCREMENT` → `SERIAL`
- `ENGINE=InnoDB` → Removed
- Backticks `` ` `` → Double quotes `"`
- `ENUM` → `VARCHAR` with `CHECK` constraint
- Foreign keys maintained with proper references

## 🔧 Environment Variables

Render automatically provides:
```bash
DATABASE_URL=postgresql://user:password@host:port/database
RENDER_EXTERNAL_URL=https://your-app.onrender.com
PORT=10000 (or assigned port)
```

## 📁 File Structure for Render

```
D_Labour_Chowk/
├── 📄 index.php              # Main entry point
├── 📄 index.html             # Landing page
├── 📁 config/                # Configuration files
├── 📁 src/Controllers/       # PHP controllers
├── 📁 public/                # Static assets
├── 📁 Shared/                # Shared resources
├── 📄 composer.json          # PHP dependencies
├── 📄 .htaccess              # Apache configuration
└── 📄 .gitignore             # Git ignore rules
```

## 🚀 Post-Deployment Checklist

- [ ] **Test Homepage**: `https://your-app.onrender.com/`
- [ ] **Test Login**: `https://your-app.onrender.com/Shared/login_form.php`
- [ ] **Test Registration**: `https://your-app.onrender.com/Shared/signup_form.php`
- [ ] **Test Database**: Check if user registration works
- [ ] **Test File Uploads**: Verify image uploads work
- [ ] **Test Email**: Check if password reset emails work

## 🔧 Troubleshooting

### Common Issues:

1. **Build Fails**
   ```bash
   # Check build logs in Render dashboard
   # Ensure composer.json is valid
   # Verify PHP version compatibility
   ```

2. **Database Connection Error**
   ```bash
   # Check DATABASE_URL in environment variables
   # Verify PostgreSQL database is running
   # Check database credentials
   ```

3. **File Not Found Errors**
   ```bash
   # Ensure .htaccess is in root directory
   # Check file permissions
   # Verify file paths in code
   ```

4. **PHP Errors**
   ```bash
   # Check PHP error logs in Render dashboard
   # Enable error reporting temporarily
   # Verify PHP extensions are installed
   ```

## 💰 Render Pricing

- **Free Tier**: 750 hours/month, 1GB storage
- **Upgrades**: From $7/month for more resources
- **Database**: Free PostgreSQL included
- **SSL**: Automatic HTTPS included
- **Custom Domain**: Free with any plan

## 🎯 Why Render is Great for Your Project

1. **Native PHP Support** - No serverless complexity
2. **PostgreSQL Included** - Free managed database
3. **Auto-Scaling** - Handles traffic spikes
4. **Global CDN** - Fast worldwide loading
5. **GitHub Integration** - Automatic deployments
6. **Professional Logs** - Detailed monitoring

## 🚀 Advanced Configuration

### Custom Domain
```bash
# In Render dashboard:
# Go to your web service
# Click "Settings" → "Custom Domain"
# Add your domain
# Update DNS records
```

### Environment Variables
```bash
# Add custom environment variables:
# APP_ENV=production
# DEBUG=false
# SMTP_HOST=your-smtp-host
# SMTP_USER=your-email@domain.com
# SMTP_PASS=your-password
```

### Monitoring
```bash
# Enable monitoring in Render dashboard
# Set up alerts for downtime
# Monitor performance metrics
```

## 📞 Support

- **Render Docs**: https://docs.render.com/
- **Community**: https://community.render.com/
- **Status Page**: https://status.render.com/

## 🎉 Success!

Once deployed, your D Labour Chowk will be live at:
**`https://your-app.onrender.com/`**

**Features that will work:**
- ✅ User registration and login
- ✅ Job posting and browsing
- ✅ File uploads
- ✅ Database operations
- ✅ Email notifications
- ✅ Responsive design

**Enjoy your deployed application!** 🚀