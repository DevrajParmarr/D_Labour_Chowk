-- Add Admin User to D Labour Chowk Database
-- Run this script to create an admin user

INSERT INTO user (user_name, email_id, mobile_no, password, user_type, date_created, Verified) VALUES
('System Admin', 'admin@dlabourchowk.com', '9999999999', '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewdBPj8ZJcKvqQG', 'Admin', NOW(), 1);

-- Password: admin123 (hashed with bcrypt)