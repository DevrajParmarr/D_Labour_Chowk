-- Add authentication columns to user table
ALTER TABLE user
ADD COLUMN Verified TINYINT(1) DEFAULT 0 AFTER user_type,
ADD COLUMN `Verification Code` VARCHAR(64) DEFAULT NULL AFTER Verified,
ADD COLUMN reset_token VARCHAR(64) DEFAULT NULL AFTER `Verification Code`,
ADD COLUMN reset_expiry DATETIME DEFAULT NULL AFTER reset_token;