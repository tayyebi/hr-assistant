CREATE TABLE IF NOT EXISTS calendar_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    location VARCHAR(255) NULL,
    start_at DATETIME NOT NULL,
    end_at DATETIME NOT NULL,
    all_day TINYINT(1) NOT NULL DEFAULT 0,
    type ENUM('meeting','holiday','birthday','event','reminder') NOT NULL DEFAULT 'event',
    color VARCHAR(7) NOT NULL DEFAULT '#3498db',
    created_by INT NULL,
    employee_id INT NULL COMMENT 'if event is for specific employee',
    is_public TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'visible to all workspace members',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_ce_tenant (tenant_id),
    KEY idx_ce_dates (tenant_id, start_at, end_at),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
