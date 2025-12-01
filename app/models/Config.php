<?php
/**
 * Config Model for Tenant Configuration
 */
class Config
{
    public static function get(string $tenantId): array
    {
        return ExcelStorage::readConfig($tenantId);
    }

    public static function save(string $tenantId, array $config): void
    {
        ExcelStorage::writeConfig($tenantId, $config);
    }

    public static function getValue(string $tenantId, string $key, $default = ''): string
    {
        $config = self::get($tenantId);
        return $config[$key] ?? $default;
    }

    public static function setValue(string $tenantId, string $key, string $value): void
    {
        $config = self::get($tenantId);
        $config[$key] = $value;
        self::save($tenantId, $config);
    }

    public static function getDefault(): array
    {
        return [
            'telegram_bot_token' => '',
            'telegram_mode' => 'webhook',
            'webhook_url' => '',
            'mailcow_url' => 'https://mail.example.com',
            'mailcow_api_key' => '',
            'gitlab_url' => 'https://gitlab.example.com',
            'gitlab_token' => '',
            'keycloak_url' => 'https://auth.example.com',
            'keycloak_realm' => 'hr-assistant',
            'keycloak_client_id' => 'hr-assistant-client',
            'keycloak_client_secret' => '',
            'imap_host' => 'imap.example.com',
            'imap_port' => '993',
            'imap_tls' => '1',
            'imap_user' => 'hr@example.com',
            'imap_pass' => '',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => '465',
            'smtp_tls' => '1',
            'smtp_user' => 'hr@example.com',
            'smtp_pass' => ''
        ];
    }
}
