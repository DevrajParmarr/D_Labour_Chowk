# 🚀 D Labour Chowk - Railway Deployment Guide

This guide will help you deploy the D Labour Chowk application to Railway for free.

## 📋 Prerequisites

- GitHub account
- Railway account (https://railway.app)
- Git installed locally

## 🛠️ Step-by-Step Deployment

### Step 1: Prepare Your Code

✅ **Already Done:**
- Updated `config.php` to use environment variables
- Created `.htaccess` for URL rewriting
- Created `Dockerfile` for containerization
- Created `railway.json` for Railway configuration
- Updated hardcoded localhost URLs
- Created `.gitignore` file

### Step 2: Create GitHub Repository

1. Go to [GitHub.com](https://github.com) and create a new repository
2. Name it `D_Labour_Chowk` or similar
3. **Don't initialize with README** (we already have one)
4. Copy the repository URL

### Step 3: Push Code to GitHub

```bash
# Initialize git if not already done
git init

# Add all files
git add .

# Commit changes
git commit -m "Initial commit for Railway deployment"

# Add remote repository
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git

# Push to GitHub
git push -u origin main
```

### Step 4: Deploy to Railway

1. **Sign up/Login to Railway**
   - Go to [railway.app](https://railway.app)
   - Sign up with GitHub account

2. **Create New Project**
   - Click "New Project"
   - Choose "Deploy from GitHub repo"
   - Connect your GitHub account
   - Select your `D_Labour_Chowk` repository

3. **Add MySQL Database**
   - In your Railway project dashboard
   - Click "Add Plugin"
   - Search for "MySQL" and add it
   - Railway will automatically create environment variables

4. **Configure Environment Variables**
   - Go to your web service settings
   - Add these variables:
     ```
     DEVELOPMENT_MODE=false
     ```

5. **Deploy**
   - Railway will automatically detect your Dockerfile
   - Click "Deploy" to start deployment
   - Wait for build and deployment to complete

### Step 5: Import Database Schema

1. **Access MySQL Database**
   - In Railway dashboard, go to your MySQL plugin
   - Click "Connect" to get connection details
   - Use the provided credentials

2. **Import Schema**
   ```bash
   # Use Railway's database connection details
   mysql -h [MYSQLHOST] -u [MYSQLUSER] -p [MYSQLDATABASE] < d_labour.sql
   ```

### Step 6: Access Your Application

- Once deployment is complete, Railway will provide a URL
- Your app will be available at: `https://your-project-name.railway.app`
- The landing page is at: `https://your-project-name.railway.app/Shared/`

## 🔧 Environment Variables

Railway automatically provides these MySQL variables:
- `MYSQLHOST` - Database host
- `MYSQLUSER` - Database username
- `MYSQLPASSWORD` - Database password
- `MYSQLDATABASE` - Database name
- `MYSQLPORT` - Database port

## 🐛 Troubleshooting

### Common Issues:

1. **Build Fails**
   - Check Railway build logs
   - Ensure Dockerfile is correct
   - Verify PHP extensions are properly installed

2. **Database Connection Error**
   - Verify environment variables are set
   - Check MySQL plugin is properly attached
   - Ensure database schema is imported

3. **File Upload Issues**
   - Check file permissions in Railway
   - Verify upload directories exist
   - Check PHP upload settings

4. **404 Errors**
   - Ensure `.htaccess` is working
   - Check Apache mod_rewrite is enabled
   - Verify file paths are correct

### Debug Mode:

Enable debug logging by setting:
```
DEVELOPMENT_MODE=true
```

## 📊 Monitoring

- **Railway Dashboard**: Monitor usage, logs, and performance
- **Database**: Monitor queries and connections
- **Logs**: Check application logs for errors

## 🔒 Security Notes

- Railway provides SSL certificates automatically
- Database connections are encrypted
- Environment variables are secure
- File uploads are restricted by type and size

## 💰 Cost Information

- **Free Tier**: 512MB RAM, 1GB storage, 100 hours/month
- **MySQL**: Included in free tier
- **Custom Domain**: Optional (paid feature)

## 📞 Support

- Railway Documentation: https://docs.railway.app/
- GitHub Issues: Report bugs in the repository
- Railway Support: Contact through dashboard

---

**🎉 Your D Labour Chowk application is now live on Railway!**

Access it at: `https://your-project-name.railway.app/Shared/`