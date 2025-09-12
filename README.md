# 🏗️ D Labour Chowk - Digital Labour Marketplace

> **Transform the way skilled workers connect with employers across India** 🇮🇳
>
> A **production-ready, enterprise-grade digital platform** that modernizes the traditional labour marketplace with cutting-edge technology, security, and user experience.

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-blue.svg)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-orange.svg)](https://mysql.com)
[![Railway](https://img.shields.io/badge/Deployed%20on-Railway-0B0D17.svg)](https://railway.app)
[![Version](https://img.shields.io/badge/Version-2.2-red.svg)](#version-22-latest---september-2025)
[![License](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

---

## 📋 Table of Contents

- [🎯 Mission](#-mission)
- [🌟 Key Features](#-key-features)
- [👥 User Roles](#-user-roles)
- [🏗️ Project Structure](#️-project-structure)
- [🚀 Quick Start](#-quick-start)
- [📦 Tech Stack](#-tech-stack)
- [🔧 Configuration](#-configuration)
- [🚀 Deployment](#-deployment)
- [📚 Documentation](#-documentation)
- [🤝 Contributing](#-contributing)
- [📄 License](#-license)

---

## 🎯 Mission

**Empowering India's workforce through technology** - D Labour Chowk bridges the gap between skilled workers and employers, creating opportunities and driving economic growth in the digital age.

### 🌟 Why D Labour Chowk?

- 🇮🇳 **Built for India** - Designed specifically for the Indian labour market
- 📱 **Mobile First** - Optimized for smartphone users across India
- 🔒 **Secure & Trusted** - Enterprise-grade security for user protection
- ⚡ **Lightning Fast** - Optimized for slow internet connections
- 🎨 **User Friendly** - Intuitive design for users of all technical levels
- 📊 **Data Driven** - Analytics and insights for better decision making
- 💬 **Real-time Communication** - Instant messaging between clients and workers
- 📍 **Location Intelligence** - GPS-based smart matching and search

---

## 🌟 Key Features

### 🔐 Enterprise-Grade Security
- Advanced authentication with bcrypt password hashing
- CSRF protection with token-based validation
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- Session security with timeout handling

### 🎨 Modern User Experience
- Mobile-first responsive design
- Interactive dashboards with real-time data
- Advanced search with multi-filter capabilities
- Beautiful charts using Chart.js
- Toast notifications for real-time alerts

### ⚡ Performance Optimized
- Smart caching system with TTL
- Image optimization and lazy loading
- Database query optimization
- CDN-ready asset management

### 🏢 Business Features
- Multi-role system (Clients, Workers, Admins)
- Job management and application tracking
- Rating and review system
- Portfolio management for workers
- Analytics dashboards for all user types

---

## 👥 User Roles

### 🏢 **Clients/Employers**
- Post job requirements with detailed specifications
- Browse and hire workers with ratings and reviews
- Advanced search with location-based filtering
- Real-time messaging with hired workers
- Analytics dashboard with hiring insights

### 👷 **Workers/Labour**
- Create professional profiles with skills and experience
- Browse and apply to job opportunities
- Portfolio management with work samples
- Earnings tracking and payment history
- Real-time messaging with potential clients

### 🔧 **Administrators**
- User management and account oversight
- System analytics and performance monitoring
- Content moderation and quality control
- Security monitoring and threat detection

---

## 🏗️ Project Structure

```
D_Labour_Chowk/
├── 📄 index.php                 # Main application entry point
├── 📄 .htaccess                 # Apache configuration & URL rewriting
├── 📄 railway.json              # Railway deployment configuration
├── 📄 Dockerfile                # Docker container configuration
├── 📄 composer.json             # PHP dependencies
├── 📄 .gitignore               # Git ignore rules
│
├── 📁 config/                  # Configuration files
│   ├── config.php              # Database & app configuration
│   └── sqlconnection.php       # Database connection utilities
│
├── 📁 src/                     # Source code (MVC structure)
│   ├── Controllers/            # Business logic controllers
│   │   ├── Client/            # Client/Employer controllers
│   │   ├── Labour/            # Worker/Labour controllers
│   │   ├── Admin/             # Administrator controllers
│   │   └── *.php              # Shared controllers
│   ├── Models/                # Data models
│   ├── Views/                 # Presentation templates
│   │   ├── Client/            # Client views
│   │   ├── Labour/            # Labour views
│   │   └── *.html             # Shared views
│   ├── Services/              # Business services
│   │   ├── PHPMailer/         # Email service
│   │   └── PerformanceOptimizer.php
│   └── Utils/                 # Utility functions
│
├── 📁 public/                  # Public web assets
│   ├── css/                   # Stylesheets
│   ├── js/                    # JavaScript files
│   ├── images/                # Image assets
│   └── uploads/               # User uploaded files
│
├── 📁 database/               # Database files
│   ├── migrations/            # Database schema files
│   └── seeds/                 # Sample data
│
├── 📁 docs/                   # Documentation
│   ├── README.md              # Project documentation
│   ├── API_DOCUMENTATION.md   # API reference
│   ├── DEPLOYMENT_GUIDE.md    # Deployment instructions
│   └── SETUP_GUIDE.md         # Setup instructions
│
├── 📁 tests/                  # Test files
│   └── TestRunner.php         # Test runner
│
└── 📁 cache/                  # Application cache
```

---

## 🚀 Quick Start

### Prerequisites
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- Git

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/DevrajParmarr/D_Labour_Chowk.git
   cd D_Labour_Chowk
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Database setup**
   ```bash
   # Import database schema
   mysql -u username -p database_name < database/migrations/d_labour.sql
   ```

4. **Configuration**
   ```php
   # Edit config/config.php with your database credentials
   define('DB_HOST', 'localhost');
   define('DB_USERNAME', 'your_username');
   define('DB_PASSWORD', 'your_password');
   define('DB_NAME', 'd_labour');
   ```

5. **Start the application**
   ```bash
   # For local development with XAMPP
   # Copy to htdocs and access via localhost

   # For production deployment
   # Deploy to Railway or your preferred hosting
   ```

---

## 📦 Tech Stack

### Backend
- **PHP 8.0+** - Server-side scripting
- **MySQL 8.0+** - Database management
- **PHPMailer** - Email functionality

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Styling and animations
- **JavaScript** - Client-side interactivity
- **Bootstrap 5** - Responsive framework
- **Chart.js** - Data visualization

### Infrastructure
- **Railway** - Cloud deployment platform
- **Docker** - Containerization
- **Apache** - Web server
- **Git** - Version control

---

## 🔧 Configuration

### Environment Variables
```bash
# Database Configuration
DB_HOST=localhost
DB_USERNAME=your_username
DB_PASSWORD=your_password
DB_NAME=d_labour

# Application Settings
APP_NAME="D Labour Chowk"
APP_URL="https://your-domain.com"
APP_VERSION="2.2"

# Security Settings
DEVELOPMENT_MODE=false
BCRYPT_COST=12
```

### File Permissions
```bash
# Set proper permissions for web server
chmod 755 cache/
chmod 755 public/uploads/
chmod 755 public/images/
```

---

## 🚀 Deployment

### Railway (Recommended - Free)
1. **Create Railway account** at [railway.app](https://railway.app)
2. **Connect your GitHub repository**
3. **Railway auto-deploys** using the provided configuration
4. **Add MySQL database** (free tier included)
5. **Import database schema**
6. **Access your live app** at `https://your-project.railway.app`

### Manual Deployment
```bash
# Upload files to your web server
# Configure database credentials
# Set proper file permissions
# Access via your domain
```

---

## 📚 Documentation

- **[📖 Full Documentation](docs/README.md)** - Complete project documentation
- **[🚀 Deployment Guide](docs/DEPLOYMENT_GUIDE.md)** - Step-by-step deployment instructions
- **[🔧 Setup Guide](docs/SETUP_GUIDE.md)** - Local development setup
- **[📡 API Documentation](docs/API_DOCUMENTATION.md)** - API reference and endpoints

---

## 🤝 Contributing

We welcome contributions! Please follow these steps:

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/AmazingFeature`)
3. **Commit** your changes (`git commit -m 'Add AmazingFeature'`)
4. **Push** to the branch (`git push origin feature/AmazingFeature`)
5. **Open** a Pull Request

### Development Guidelines
- Follow PSR-12 coding standards
- Write comprehensive PHPDoc comments
- Add unit tests for new features
- Update documentation as needed

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- **Bootstrap** - Modern UI framework
- **Chart.js** - Beautiful charts and analytics
- **Font Awesome** - Icon library
- **PHP Community** - Excellent documentation
- **Railway** - Amazing deployment platform

---

## 📞 Support

- 📧 **Email**: devrajparmar232@gmail.com
- 🐛 **Issues**: [GitHub Issues](https://github.com/DevrajParmarr/D_Labour_Chowk/issues)
- 📚 **Documentation**: [Project Wiki](https://github.com/DevrajParmarr/D_Labour_Chowk/wiki)

---

<div align="center">

**Made with ❤️ for the Digital India Initiative**

*Connecting skilled workers with opportunities across India* 🇮🇳

**Version 2.2 - Enterprise Grade Digital Labour Marketplace**

</div>