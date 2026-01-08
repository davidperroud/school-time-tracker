-- Add is_admin column to existing users table
ALTER TABLE users ADD COLUMN is_admin INTEGER DEFAULT 0;

-- Update existing admin user to have admin privileges
UPDATE users SET is_admin = 1 WHERE username = 'admin';