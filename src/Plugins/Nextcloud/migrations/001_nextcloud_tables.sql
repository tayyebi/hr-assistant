CREATE TABLE IF NOT EXISTS nextcloud_instances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    label VARCHAR(128) NOT NULL,
    base_url VARCHAR(512) NOT NULL,
    admin_user VARCHAR(128) NOT NULL,
    admin_password VARCHAR(512) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_ni_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS nextcloud_user_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    instance_id INT NOT NULL,
    employee_id INT NOT NULL,
    nc_user_id VARCHAR(128) NOT NULL,
    nc_display_name VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    linked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_nul_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (instance_id) REFERENCES nextcloud_instances(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
