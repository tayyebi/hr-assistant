-- Drop assets table and remove email/telegram_chat_id from employees
-- These are replaced by provider instance accounts mappings

-- Drop the assets table
DROP TABLE IF EXISTS assets;

-- Remove email and telegram_chat_id columns from employees
-- Communication now uses the 'accounts' JSON field mapping to provider instances
ALTER TABLE employees
DROP COLUMN IF EXISTS email,
DROP COLUMN IF EXISTS telegram_chat_id;
