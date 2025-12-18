-- Add password column to users table to store actual passwords
ALTER TABLE `users` ADD COLUMN `password` varchar(255) DEFAULT NULL AFTER `password_hash`;

-- Update existing users to have password (if needed)
-- UPDATE users SET password = 'your_password' WHERE password IS NULL;
