# 📚 D Labour Chowk - API Documentation

> Complete API reference for the Digital Labour Chowk platform

## 🚀 Overview

The Digital Labour Chowk platform provides a comprehensive set of endpoints for managing workers, jobs, applications, and analytics. All APIs are RESTful and return JSON responses.

### Base URL
```
http://localhost/D_Labour_Chowk/
```

### Authentication
Most endpoints require user authentication via session cookies. CSRF tokens are required for state-changing operations.

---

## 🔐 Authentication APIs

### User Registration
**Endpoint:** `POST /Shared/sign_up.php`

**Description:** Register a new user account

**Request Body:**
```json
{
    "username": "John Doe",
    "email": "john@example.com",
    "mobile": "1234567890",
    "password": "SecurePass123",
    "usertype": "User|Labour",
    "csrf_token": "generated_token"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Account created successfully",
    "redirect": "/Shared/login_form.php"
}
```

**Error Response:**
```json
{
    "status": "error",
    "message": "Email already exists"
}
```

---

### User Login
**Endpoint:** `POST /Shared/login.php`

**Description:** Authenticate user and create session

**Request Body:**
```json
{
    "mobile_no": "1234567890",
    "password": "SecurePass123",
    "csrf_token": "generated_token"
}
```

**Response:**
```json
{
    "status": "success",
    "user": {
        "id": 123,
        "name": "John Doe",
        "type": "User",
        "email": "john@example.com"
    },
    "redirect": "/client_/dashboard.php"
}
```

---

### Logout
**Endpoint:** `GET /Shared/logout.php`

**Description:** End user session

**Response:**
```json
{
    "status": "success",
    "message": "Logged out successfully",
    "redirect": "/Shared/login_form.php"
}
```

---

## 👔 Client/Employer APIs

### Get Dashboard Data
**Endpoint:** `GET /client_/dashboard.php`

**Description:** Retrieve client dashboard statistics

**Authentication:** Required (Client)

**Response:**
```json
{
    "stats": {
        "total_jobs": 15,
        "total_applications": 45,
        "total_hires": 8,
        "active_jobs": 12
    },
    "recent_jobs": [
        {
            "id": 1,
            "title": "Carpenter needed",
            "salary": 5000,
            "city": "Mumbai",
            "applications": 12
        }
    ],
    "hired_workers": [
        {
            "id": 1,
            "name": "Ram Kumar",
            "work_type": "Carpenter",
            "hire_date": "2024-01-15",
            "status": "active"
        }
    ]
}
```

---

### Post New Job
**Endpoint:** `POST /client_/creatjob.php`

**Description:** Create a new job posting

**Authentication:** Required (Client)

**Request Body:**
```json
{
    "jobTitle": "Carpenter",
    "salary": 5000,
    "detail": "Need experienced carpenter for furniture work",
    "city": "Mumbai",
    "location": "Andheri West",
    "csrf_token": "token"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Job posted successfully",
    "job_id": 123
}
```

---

### Search Workers
**Endpoint:** `GET /client_/advanced_search.php`

**Description:** Search for workers with filters

**Authentication:** Required (Client)

**Query Parameters:**
- `work_type` (string): Type of work
- `city` (string): City name
- `min_salary` (integer): Minimum salary
- `max_salary` (integer): Maximum salary
- `experience` (string): Experience level
- `sort_by` (string): Sort field (salary, experience, etc.)
- `sort_order` (string): ASC or DESC

**Example:**
```
GET /client_/advanced_search.php?work_type=carpenter&city=mumbai&min_salary=3000&max_salary=8000
```

**Response:**
```json
{
    "total": 25,
    "workers": [
        {
            "id": 1,
            "name": "Ram Kumar",
            "work_type": "Carpenter",
            "experience": "5 years",
            "salary": 5000,
            "city": "Mumbai",
            "rating": 4.5,
            "reviews": 12,
            "mobile": "9876543210"
        }
    ]
}
```

---

### Hire Worker
**Endpoint:** `POST /client_/hire_labor.php`

**Description:** Hire a specific worker

**Authentication:** Required (Client)

**Request Body:**
```json
{
    "labour_id": 123,
    "csrf_token": "token"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Worker hired successfully",
    "hire_id": 456
}
```

---

### Get Analytics
**Endpoint:** `GET /client_/analytics.php`

**Description:** Get hiring analytics and insights

**Authentication:** Required (Client)

**Response:**
```json
{
    "job_stats": {
        "total_jobs": 15,
        "active_jobs": 12
    },
    "application_stats": {
        "total_applications": 45,
        "pending": 20,
        "accepted": 15,
        "rejected": 10
    },
    "hire_stats": {
        "total_hires": 8,
        "active": 6,
        "completed": 2
    },
    "charts": {
        "applications_by_status": {
            "pending": 20,
            "accepted": 15,
            "rejected": 10
        },
        "jobs_by_type": [
            {"type": "Carpenter", "count": 8},
            {"type": "Plumber", "count": 5},
            {"type": "Electrician", "count": 2}
        ]
    }
}
```

---

## 👷 Worker/Labour APIs

### Get Worker Dashboard
**Endpoint:** `GET /Labour/dashboard.php`

**Description:** Retrieve worker dashboard data

**Authentication:** Required (Worker)

**Response:**
```json
{
    "stats": {
        "total_applications": 25,
        "accepted_applications": 8,
        "total_hires": 5,
        "work_posts": 12
    },
    "profile": {
        "id": 1,
        "work_type": "Carpenter",
        "experience": "5 years",
        "salary": 5000,
        "city": "Mumbai",
        "rating": 4.5
    },
    "recent_applications": [
        {
            "id": 1,
            "job_title": "Furniture Work",
            "client": "ABC Company",
            "salary": 5000,
            "status": "pending",
            "applied_at": "2024-01-15"
        }
    ],
    "work_portfolio": [
        {
            "id": 1,
            "title": "Custom Wardrobe",
            "image": "work1.jpg",
            "description": "Made custom wardrobe for client",
            "date": "2024-01-10"
        }
    ]
}
```

---

### Create Worker Profile
**Endpoint:** `POST /Labour/creatLpost.php`

**Description:** Create or update worker profile

**Authentication:** Required (Worker)

**Request Body:**
```json
{
    "workType": "Carpenter",
    "experience": "5 years",
    "salary": "5000",
    "location": "Andheri West, Mumbai",
    "city": "Mumbai",
    "csrf_token": "token"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Profile created successfully",
    "profile_id": 123
}
```

---

### Apply for Job
**Endpoint:** `POST /Labour/apply_job.php`

**Description:** Apply for a specific job

**Authentication:** Required (Worker)

**Request Body:**
```json
{
    "job_id": 123,
    "csrf_token": "token"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Application submitted successfully",
    "application_id": 456
}
```

---

### Add Work Post
**Endpoint:** `POST /Labour/post_work.php`

**Description:** Add work sample to portfolio

**Authentication:** Required (Worker)

**Request Body (Form Data):**
```
title: "Custom Furniture Work"
description: "Created custom dining table and chairs"
image: (file upload)
csrf_token: "token"
```

**Response:**
```json
{
    "status": "success",
    "message": "Work post added successfully",
    "post_id": 789
}
```

---

### Get Available Jobs
**Endpoint:** `GET /Labour/postL.php`

**Description:** Get list of available jobs

**Authentication:** Required (Worker)

**Query Parameters:**
- `work_type` (string): Filter by work type
- `city` (string): Filter by city
- `min_salary` (integer): Minimum salary filter

**Response:**
```json
{
    "total": 50,
    "jobs": [
        {
            "id": 1,
            "title": "Carpenter needed",
            "company": "ABC Construction",
            "salary": 5000,
            "city": "Mumbai",
            "location": "Andheri West",
            "description": "Need experienced carpenter",
            "posted_date": "2024-01-15",
            "applications": 12
        }
    ]
}
```

---

## 📊 Analytics & Reporting APIs

### Get System Analytics (Admin)
**Endpoint:** `GET /admin/analytics.php`

**Description:** System-wide analytics for administrators

**Authentication:** Required (Admin)

**Response:**
```json
{
    "users": {
        "total": 1000,
        "clients": 400,
        "workers": 600,
        "new_this_month": 50
    },
    "jobs": {
        "total": 500,
        "active": 200,
        "completed": 300
    },
    "applications": {
        "total": 2000,
        "success_rate": 45.5
    },
    "popular_categories": [
        {"category": "Carpenter", "jobs": 150},
        {"category": "Plumber", "jobs": 120},
        {"category": "Electrician", "jobs": 80}
    ],
    "city_distribution": [
        {"city": "Mumbai", "users": 300},
        {"city": "Delhi", "users": 250},
        {"city": "Bangalore", "users": 200}
    ]
}
```

---

## 🔍 Search & Filter APIs

### Global Search
**Endpoint:** `GET /search.php`

**Description:** Search across jobs, workers, and categories

**Query Parameters:**
- `q` (string): Search query
- `type` (string): Search type (jobs, workers, all)
- `limit` (integer): Results limit

**Example:**
```
GET /search.php?q=carpenter&type=workers&limit=20
```

**Response:**
```json
{
    "query": "carpenter",
    "results": {
        "workers": [
            {
                "id": 1,
                "name": "Ram Kumar",
                "work_type": "Carpenter",
                "city": "Mumbai",
                "rating": 4.5
            }
        ],
        "jobs": [
            {
                "id": 1,
                "title": "Carpenter needed",
                "company": "ABC Construction",
                "city": "Mumbai"
            }
        ]
    },
    "total": 25
}
```

---

## 📝 Rating & Review APIs

### Add Rating
**Endpoint:** `POST /client_/rate_labour.php`

**Description:** Rate and review a worker

**Authentication:** Required (Client)

**Request Body:**
```json
{
    "hire_id": 123,
    "labour_id": 456,
    "rating": 5,
    "review": "Excellent work quality and punctual",
    "csrf_token": "token"
}
```

**Response:**
```json
{
    "status": "success",
    "message": "Rating submitted successfully",
    "rating_id": 789
}
```

---

### Get Worker Ratings
**Endpoint:** `GET /get_ratings.php?labour_id=123`

**Description:** Get all ratings for a specific worker

**Response:**
```json
{
    "worker_id": 123,
    "average_rating": 4.5,
    "total_reviews": 20,
    "ratings": [
        {
            "id": 1,
            "client_name": "John Doe",
            "rating": 5,
            "review": "Excellent work",
            "date": "2024-01-15"
        }
    ]
}
```

---

## 🗂️ File Upload APIs

### Upload Profile Image
**Endpoint:** `POST /upload_profile_image.php`

**Description:** Upload user profile image

**Authentication:** Required

**Request Body (Form Data):**
```
image: (file upload)
csrf_token: "token"
```

**Response:**
```json
{
    "status": "success",
    "message": "Image uploaded successfully",
    "image_url": "/images/profile_123.jpg",
    "optimized_url": "/images/profile_123_optimized.jpg"
}
```

---

## 🚨 Error Codes

### HTTP Status Codes
- `200` - Success
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Internal Server Error

### Application Error Codes
```json
{
    "AUTH_001": "Invalid credentials",
    "AUTH_002": "Session expired",
    "AUTH_003": "CSRF token invalid",
    "VALID_001": "Required field missing",
    "VALID_002": "Invalid email format",
    "VALID_003": "Password too weak",
    "DB_001": "Database connection failed",
    "DB_002": "Record not found",
    "FILE_001": "File upload failed",
    "FILE_002": "Invalid file type",
    "PERM_001": "Insufficient permissions"
}
```

---

## 🔧 Development & Testing

### Enable Debug Mode
Add to any PHP file:
```php
define('DEVELOPMENT_MODE', true);
```

### Test Endpoints
Use the built-in test runner:
```
GET /tests/TestRunner.php
```

### Performance Monitoring
Performance metrics are automatically logged in development mode:
```html
<!-- Performance Metrics:
Memory Usage: 2.5 MB
Peak Memory: 3.1 MB
Execution Time: 45.67ms
Cache Files: 12
Cache Size: 1.2 MB
-->
```

---

## 🛡️ Security Considerations

### CSRF Protection
All state-changing operations require CSRF tokens:
```javascript
// Get CSRF token from meta tag or form
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

// Include in requests
fetch('/api/endpoint', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrfToken
    },
    body: JSON.stringify(data)
});
```

### Input Validation
All inputs are validated server-side:
- Email format validation
- Password strength requirements
- File type and size restrictions
- SQL injection prevention
- XSS protection

### Rate Limiting
API endpoints have built-in rate limiting:
- Authentication: 5 attempts per minute
- File uploads: 10 files per hour
- Search: 60 requests per minute

---

## 📱 SDK & Examples

### JavaScript Example
```javascript
class LabourChowkAPI {
    constructor(baseURL) {
        this.baseURL = baseURL;
        this.csrfToken = this.getCSRFToken();
    }
    
    async login(mobile, password) {
        const response = await fetch(`${this.baseURL}/Shared/login.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                mobile_no: mobile,
                password: password,
                csrf_token: this.csrfToken
            })
        });
        return response.json();
    }
    
    async searchWorkers(filters) {
        const params = new URLSearchParams(filters);
        const response = await fetch(`${this.baseURL}/client_/advanced_search.php?${params}`);
        return response.json();
    }
}

// Usage
const api = new LabourChowkAPI('http://localhost/D_Labour_Chowk');
const result = await api.searchWorkers({
    work_type: 'carpenter',
    city: 'mumbai',
    min_salary: 3000
});
```

### PHP Example
```php
// Client code example
class LabourChowkClient {
    private $baseURL;
    private $session;
    
    public function __construct($baseURL) {
        $this->baseURL = $baseURL;
    }
    
    public function searchWorkers($filters) {
        $url = $this->baseURL . '/client_/advanced_search.php?' . http_build_query($filters);
        return json_decode(file_get_contents($url), true);
    }
}
```

---

## 🔄 Webhooks (Future Feature)

### Job Application Webhook
```json
{
    "event": "job.application.created",
    "data": {
        "application_id": 123,
        "job_id": 456,
        "worker_id": 789,
        "timestamp": "2024-01-15T10:30:00Z"
    }
}
```

### Worker Hired Webhook
```json
{
    "event": "worker.hired",
    "data": {
        "hire_id": 123,
        "client_id": 456,
        "worker_id": 789,
        "timestamp": "2024-01-15T10:30:00Z"
    }
}
```

---

## 📞 Support & Resources

### Documentation Links
- **Setup Guide**: `/SETUP_GUIDE.md`
- **README**: `/README.md`
- **Test Suite**: `/tests/TestRunner.php`

### Support Channels
- 📧 **Email**: api-support@labourchowd.com
- 🐛 **Issues**: GitHub Issues
- 📚 **Wiki**: Project Wiki
- 💬 **Discord**: Development Community

---

**📈 API Version**: 2.0  
**🗓️ Last Updated**: January 2024  
**🔄 Status**: Active Development

*This API documentation is automatically updated with each release.*
