# Dgital Labour_Chowk

---

# Labour Management Platform (D Labour Chowk)

A web-based platform that connects labourers, contractors, and clients to streamline profiles, job postings, applications, endorsements, and cross-device access. Built with PHP on the backend and a plain HTML/CSS/JS frontend, focusing on credibility, usability, and multi-role workflows.


## Table of Contents

- [Project Overview](#project-overview)
- [Key Features](#key-features)
- [User Roles & Profiles](#user-roles--profiles)
- [System Architecture](#system-architecture)
- [Tech Stack](#tech-stack)
- [Database](#database)
- [Getting Started](#getting-started)
- [Development & Testing](#development--testing)
- [Deployment](#deployment)
- [Contributing](#contributing)
- [Documentation](#documentation)
- [Licensing & Roadmap](#licensing--roadmap)

## Project Overview

The Labour Management Platform connects three stakeholder groups:
- Labourers
- Contractors
- Clients

 core capabilities:
- Create and manage profiles
- Post and browse jobs
- Submit applications, reviews, and endorsements
- Messaging and notifications
- Portfolio/gallery for past work
- Access across devices via a responsive frontend

The system emphasizes credibility (endorsements/skill validations) and accessibility.

## Key Features

- Networking and connections between users
- Job recommendations and alerts
- Portfolio and project showcases
- Profiles management for Labourers and Contractors
- Job posting, search, and filtering
- Applications, reviews, and ratings
- Messaging and notifications
- Endorsements and skill validations
- Cross-device responsive UI

## User Roles & Profiles

### Labourers
- Create and manage profiles
- Browse and apply for jobs
- Manage availability and skills
- View and interact with other profiles

### Contractors
- Create and manage profiles
- Post job opportunities
- Review applications
- Communicate with labourers and clients

### Clients
- Create and manage profiles
- Post jobs
- Monitor progress and interact with labourers/contractors

## System Architecture

- Frontend: Static/dynamic HTML, CSS, and JavaScript
- Backend: PHP-based API and server-side rendering as needed
- Database: SQL-based (DQL on your server) with relational schemas for users, profiles, jobs, applications, messages, endorsements, and portfolios
- Authentication: Session-based ,Role Based and token-based (adjust to PHP stack)
- Messaging/Notifications: Server-side events or polling implemented in PHP
- Admin Dashboard: Light-weight admin UI for management and monitoring

## Tech Stack

- Frontend: HTML, CSS, JavaScript (no framework required; can progressively enhance with vanilla JS)
- Backend: PHP
- Database: SQL Server (as indicated by DQL server; ensure compatibility with PHP PDO or mysqli)
- Hosting: Your preferred PHP hosting environment

## Database

- Core entities: User, Profile (Labourer, Contractor, Client), Job, Application, Message, Endorsement, Rating, Portfolio, Notification
- Use relational tables with proper indexes for search and filtering
- Implement RBAC (role-based access) at the application layer

Note: Adapt schema details to match the SRS table of contents and specific field requirements.


## Development & Testing

- Linting and style checks: optional for vanilla HTML/CSS/JS; you can integrate ESLint/Prettier for scripts
- Unit tests: PHP Unit (optional)
- Functional tests: manual testing for flows (profiles, posting, applying, messaging)
- Database migrations: write SQL scripts to create tables; version them and apply with a simple migration runner if desired

## Deployment

- Deploy to your chosen PHP hosting provider
- Set environment variables for DB connection
- Ensure proper security measures:
  - Use prepared statements to prevent SQL injection
  - Enable HTTPS
  - Implement input validation and output escaping

## Contributing

Contributions are welcome. Please follow these steps:
1. Fork the repository
2. Create a feature branch: git checkout -b feature/your-feature
3. Commit with a clear message
4. Open a Pull Request with a descriptive title and details

Code of Conduct: Maintain a respectful, collaborative environment.

## Documentation

- This repo is aligned with the SRS: Software Requirement Specification for D Labour Chowk Platform.
- API or data model documentation can be added under docs/ as you evolve.
- Consider adding a data dictionary, ERD, and deployment guides as you grow.

## Licensing & Roadmap

- License: MIT (or your chosen license)
- Roadmap highlights:
  - Enhanced search and filtering
  - Real-time messaging (WebSocket/polling)
  - Access controls and security hardening
  - Administrative reporting

---

## Screenshhots


(appled_job_post.png) (client_home_page.png) (hired_labour.png) (labour_home_page.png) (labour_post.png) (landng_page.png) (login_page_screenshot.png) (<Screenshot 2025-08-24 120317.png>) (sign_up_page_screenshot.png)
---

## Setup Instructions

### Prerequisites
- **XAMPP**: Download and install from [XAMPP Official Website](https://www.apachefriends.org/index.html).

### Steps to Run the Project

1. **Install XAMPP**:
   - Download and install XAMPP on your system.

2. **Start Apache and MySQL Servers**:
   - Open the XAMPP Control Panel.
   - Click **Start** next to **Apache** and **MySQL**.

3. **Set Up Project Files**:
   - Copy the project folder to the `htdocs` directory:
     ```
     C:\xampp\htdocs\D_Labour_Chowk
     ```

4. **Configure the Database**:
   - Open `http://localhost/phpmyadmin/` in your browser.
   - Create a new database.
   - Import the SQL file from the project folder.

5. **Configure the Project**:
   - Open the project’s configuration file (e.g., `config.php`).
   - Update the database settings:
     ```php
     $servername = "localhost";
     $username = "root";
     $password = "";
     $dbname = "d_labour";
     ```

6. **Run the Project**:
   - Open your browser and navigate to:
     ```
     http://localhost/D_Labour_Chowk/Shared/sign_up
     ```

### Troubleshooting
- **Ports in Use**: Ensure ports 80 (Apache) and 3306 (MySQL) are not in use by other applications.
- **Database Configuration**: Verify that your database credentials in the config file are correct.

## Overview
--- 
