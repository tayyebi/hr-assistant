-- Add status and timestamp columns to tenants table
ALTER TABLE tenants 
ADD COLUMN status VARCHAR(20) DEFAULT 'active' AFTER name,
ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP AFTER status,
ADD COLUMN updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at;

-- Update existing tenants to have active status
UPDATE tenants SET status = 'active' WHERE status IS NULL OR status = '';

-- Add index on status for filtering
CREATE INDEX idx_tenant_status ON tenants(status);
