-- Create provider_instances table
CREATE TABLE IF NOT EXISTS provider_instances (
  id VARCHAR(64) PRIMARY KEY,
  tenant_id VARCHAR(64) NOT NULL,
  type VARCHAR(64) NOT NULL,
  provider VARCHAR(64) NOT NULL,
  name VARCHAR(255) NOT NULL,
  settings JSON DEFAULT (JSON_OBJECT()),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY tenant_provider_name (tenant_id, provider, name),
  FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
