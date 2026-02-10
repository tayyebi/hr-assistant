<?php

namespace HRAssistant\Models;

use HRAssistant\Core\Database;

/**
 * Config Model for Tenant Configuration
 */
class Config
{
    public static function get(string $tenantId): array
    {
        // Try database-backed provider settings first
        $config = [];
        try {
            $rows = Database::fetchAll('SELECT provider, settings FROM provider_settings WHERE tenant_id = ?', [$tenantId]);
            foreach ($rows as $row) {
                $settings = json_decode($row['settings'], true) ?: [];
                // Merge into flat config
                foreach ($settings as $k => $v) {
                    $config[$k] = $v;
                }
            }
        } catch (\Exception $e) {
            // fallback to Excel
        }

        return $config;
    }

    public static function save(string $tenantId, array $config): void
    {
        // Split config by provider using ProviderSettings definitions
        try {
            $allFields = ProviderSettings::getAllFields();
            $byProvider = [];

            // Map each config key to a provider
            foreach ($config as $k => $v) {
                $found = false;
                foreach ($allFields as $provider => $fields) {
                    if (array_key_exists($k, $fields)) {
                        $byProvider[$provider][$k] = $v;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $byProvider['__global__'][$k] = $v;
                }
            }

            // Upsert provider_settings rows
            foreach ($byProvider as $provider => $settings) {
                $settingsJson = json_encode($settings);
                $exists = Database::fetchOne('SELECT 1 FROM provider_settings WHERE tenant_id = ? AND provider = ? LIMIT 1', [$tenantId, $provider]);
                if ($exists) {
                    Database::execute('UPDATE provider_settings SET settings = ? WHERE tenant_id = ? AND provider = ?', [$settingsJson, $tenantId, $provider]);
                } else {
                    Database::execute('INSERT INTO provider_settings (tenant_id, provider, settings) VALUES (?, ?, ?)', [$tenantId, $provider, $settingsJson]);
                }
            }

            return;
        } catch (\Exception $e) {
            // DB error - do not persist
            return;
        }
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
