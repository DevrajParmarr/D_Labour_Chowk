-- Add location and messaging features to D Labour Chowk database

-- 1. User Location Tracking Table
CREATE TABLE IF NOT EXISTS user_location (
    location_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    location_name VARCHAR(255),
    city VARCHAR(100),
    state VARCHAR(100),
    country VARCHAR(100) DEFAULT 'India',
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES user(user_ID) ON DELETE CASCADE,
    INDEX idx_user_location (user_id),
    INDEX idx_location_coords (latitude, longitude),
    INDEX idx_location_city (city)
);

-- 2. Job Location Table (extends existing job_post)
ALTER TABLE job_post
ADD COLUMN latitude DECIMAL(10, 8),
ADD COLUMN longitude DECIMAL(11, 8),
ADD COLUMN location_radius INT DEFAULT 10, -- in kilometers
ADD COLUMN is_location_based BOOLEAN DEFAULT FALSE;

-- 3. Labour Profile Location Table (extends existing lab_post)
ALTER TABLE lab_post
ADD COLUMN latitude DECIMAL(10, 8),
ADD COLUMN longitude DECIMAL(11, 8),
ADD COLUMN availability_radius INT DEFAULT 15, -- in kilometers
ADD COLUMN is_available BOOLEAN DEFAULT TRUE,
ADD COLUMN last_location_update TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- 4. Messaging System Tables
CREATE TABLE IF NOT EXISTS conversations (
    conversation_id INT PRIMARY KEY AUTO_INCREMENT,
    participant_1 INT NOT NULL, -- client_id
    participant_2 INT NOT NULL, -- labour_id
    job_post_id INT, -- related job post
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (participant_1) REFERENCES user(user_ID) ON DELETE CASCADE,
    FOREIGN KEY (participant_2) REFERENCES user(user_ID) ON DELETE CASCADE,
    FOREIGN KEY (job_post_id) REFERENCES job_post(job_post_id) ON DELETE SET NULL,
    UNIQUE KEY unique_conversation (participant_1, participant_2, job_post_id),
    INDEX idx_participants (participant_1, participant_2),
    INDEX idx_last_message (last_message_at)
);

CREATE TABLE IF NOT EXISTS messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    message_text TEXT NOT NULL,
    message_type ENUM('text', 'image', 'file', 'location') DEFAULT 'text',
    attachment_url VARCHAR(500),
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(conversation_id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES user(user_ID) ON DELETE CASCADE,
    INDEX idx_conversation (conversation_id),
    INDEX idx_sender (sender_id),
    INDEX idx_sent_at (sent_at)
);

-- 5. Location-based Search History
CREATE TABLE IF NOT EXISTS location_search_history (
    search_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    search_lat DECIMAL(10, 8),
    search_lng DECIMAL(11, 8),
    search_radius INT DEFAULT 10,
    search_type ENUM('labour', 'jobs') NOT NULL,
    search_results_count INT DEFAULT 0,
    searched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(user_ID) ON DELETE CASCADE,
    INDEX idx_user_search (user_id),
    INDEX idx_search_time (searched_at)
);

-- 6. Add location permissions to user table
ALTER TABLE user
ADD COLUMN location_permission BOOLEAN DEFAULT FALSE,
ADD COLUMN location_sharing_enabled BOOLEAN DEFAULT TRUE;

-- 7. Insert sample location data for testing
INSERT INTO user_location (user_id, latitude, longitude, location_name, city, state) VALUES
(1, 23.2599, 77.4126, 'Bhopal Central', 'Bhopal', 'Madhya Pradesh'),
(2, 22.7196, 75.8577, 'Indore Main', 'Indore', 'Madhya Pradesh'),
(3, 19.0760, 72.8777, 'Mumbai Central', 'Mumbai', 'Maharashtra'),
(4, 28.7041, 77.1025, 'Delhi Center', 'Delhi', 'Delhi'),
(5, 13.0827, 80.2707, 'Chennai Central', 'Chennai', 'Tamil Nadu'),
(6, 22.5726, 88.3639, 'Kolkata Hub', 'Kolkata', 'West Bengal');

-- 8. Sample conversations for testing
INSERT INTO conversations (participant_1, participant_2, job_post_id) VALUES
(1, 6, 1), -- Client 1 chatting with Labour 6
(2, 6, 2), -- Client 2 chatting with Labour 6
(3, 6, NULL); -- General conversation

-- 9. Sample messages
INSERT INTO messages (conversation_id, sender_id, message_text, sent_at) VALUES
(1, 1, 'Hello! I saw your profile for plumbing work. Are you available this weekend?', '2024-01-15 10:30:00'),
(1, 6, 'Hi! Yes, I am available. What kind of plumbing work do you need?', '2024-01-15 10:35:00'),
(1, 1, 'I need bathroom fittings installed. Can you come for a site visit tomorrow?', '2024-01-15 10:40:00'),
(2, 2, 'I need electrical work done in my office. Are you experienced with commercial wiring?', '2024-01-15 11:00:00'),
(2, 6, 'Yes, I have experience with commercial electrical work. What is the scope of the project?', '2024-01-15 11:05:00');

-- 10. Update existing job posts with sample locations
UPDATE job_post SET
    latitude = CASE
        WHEN job_post_id = 1 THEN 23.2599
        WHEN job_post_id = 2 THEN 22.7196
        WHEN job_post_id = 3 THEN 19.0760
        ELSE 28.7041
    END,
    longitude = CASE
        WHEN job_post_id = 1 THEN 77.4126
        WHEN job_post_id = 2 THEN 75.8577
        WHEN job_post_id = 3 THEN 72.8777
        ELSE 77.1025
    END,
    is_location_based = TRUE,
    location_radius = 15;

-- 11. Update existing labour posts with sample locations
UPDATE lab_post SET
    latitude = CASE
        WHEN post_ID = 1 THEN 23.2599
        WHEN post_ID = 2 THEN 22.7196
        WHEN post_ID = 3 THEN 19.0760
        ELSE 28.7041
    END,
    longitude = CASE
        WHEN post_ID = 1 THEN 77.4126
        WHEN post_ID = 2 THEN 75.8577
        WHEN post_ID = 3 THEN 72.8777
        ELSE 77.1025
    END,
    is_available = TRUE,
    availability_radius = 20;

-- 12. Create indexes for better performance
CREATE INDEX idx_messages_unread ON messages(conversation_id, is_read);
CREATE INDEX idx_user_location_active ON user_location(user_id, is_active);
CREATE INDEX idx_lab_post_available ON lab_post(is_available, latitude, longitude);
CREATE INDEX idx_job_post_location ON job_post(is_location_based, latitude, longitude);