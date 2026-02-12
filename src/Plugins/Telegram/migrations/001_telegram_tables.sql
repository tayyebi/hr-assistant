CREATE TABLE IF NOT EXISTS telegram_chats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    employee_id INT NULL,
    chat_id VARCHAR(64) NOT NULL,
    username VARCHAR(128) NULL,
    first_name VARCHAR(128) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_tg_tenant (tenant_id),
    UNIQUE KEY uq_tg_chat (tenant_id, chat_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS telegram_messages (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    chat_id VARCHAR(64) NOT NULL,
    direction ENUM('inbound', 'outbound') NOT NULL,
    body TEXT NOT NULL,
    telegram_message_id VARCHAR(64) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_tgm_tenant (tenant_id),
    KEY idx_tgm_chat (tenant_id, chat_id),
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
