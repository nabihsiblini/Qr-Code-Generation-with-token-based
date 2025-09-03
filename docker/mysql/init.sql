-- Initial database setup
-- This file will be executed when the MySQL container is first created

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS qrcode_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant privileges
GRANT ALL PRIVILEGES ON qrcode_db.* TO 'qrcode_user'@'%';
FLUSH PRIVILEGES;