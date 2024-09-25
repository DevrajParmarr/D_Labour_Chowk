# D_Labour_Chowk


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
