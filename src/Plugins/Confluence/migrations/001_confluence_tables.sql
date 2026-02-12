CREATE TABLE IF NOT EXISTS confluence_instances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    label VARCHAR(128) NOT NULL,
    base_url VARCHAR(512) NOT NULL,
    admin_email VARCHAR(255) NOT NULL,
    api_token VARCHAR(512) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_ci_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS confluence_space_grants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    instance_id INT NOT NULL,
    employee_id INT NOT NULL,
    confluence_account_id VARCHAR(128) NULL,
    space_key VARCHAR(64) NOT NULL,
    space_name VARCHAR(255) NULL,
    permission_type VARCHAR(64) NOT NULL DEFAULT 'read',
    granted_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    revoked_at DATETIME NULL,
    KEY idx_csg_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (instance_id) REFERENCES confluence_instances(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
