CREATE TABLE IF NOT EXISTS keycloak_instances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    label VARCHAR(128) NOT NULL,
    base_url VARCHAR(512) NOT NULL,
    realm VARCHAR(128) NOT NULL DEFAULT 'master',
    client_id VARCHAR(128) NOT NULL,
    client_secret VARCHAR(512) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_ki_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS keycloak_user_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    instance_id INT NOT NULL,
    employee_id INT NOT NULL,
    keycloak_user_id VARCHAR(128) NOT NULL,
    username VARCHAR(255) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    linked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_kul_tenant (tenant_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (instance_id) REFERENCES keycloak_instances(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
