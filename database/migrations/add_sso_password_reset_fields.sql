-- Migration: Add is_first_login and password_reset_at to users table
-- Created: 2026-01-01

ALTER TABLE users ADD COLUMN is_first_login BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE users ADD COLUMN password_reset_at TIMESTAMP NULL;

COMMENT ON COLUMN users.is_first_login IS 'Flag to indicate if SSO user needs to reset password on first login';
COMMENT ON COLUMN users.password_reset_at IS 'Timestamp when user last reset their password';
