<?php

namespace App\Core;

/**
 * Provider Settings Configuration
 * Defines configuration fields required for each provider type
 */

class ProviderSettings
{
    /**
     * Get configuration fields for a provider
     * Returns array of field definitions with type, label, required, etc.
     */
    public static function getFields(string $provider): array
    {
        return match($provider) {
            // Email Providers
            EmailProvider::MAILCOW => self::getMailcowFields(),
            EmailProvider::EXCHANGE => self::getExchangeFields(),
            EmailProvider::IMAP => self::getImapFields(),

            // Git Providers
            GitProvider::GITLAB => self::getGitLabFields(),
            GitProvider::GITEA => self::getGiteaFields(),
            GitProvider::GITHUB => self::getGithubFields(),

            // Messenger Providers
            MessengerProvider::TELEGRAM => self::getTelegramFields(),
            MessengerProvider::WHATSAPP => self::getWhatsappFields(),
            MessengerProvider::SLACK => self::getSlackFields(),
            MessengerProvider::TEAMS => self::getTeamsFields(),

            // IAM Providers
            IamProvider::KEYCLOAK => self::getKeycloakFields(),
            IamProvider::OKTA => self::getOktaFields(),
            IamProvider::AZURE_AD => self::getAzureAdFields(),

            // Secrets Providers
            SecretsProvider::PASSBOLT => self::getPassboltFields(),
            SecretsProvider::BITWARDEN => self::getBitwardenFields(),
            SecretsProvider::ONEPWD => self::getOnePasswordFields(),
            SecretsProvider::VAULT => self::getVaultFields(),

            default => []
        };
    }

    /**
     * Get all available providers with their metadata
     */
    public static function getProvidersMetadata(): array
    {
        return [
            // Email
            EmailProvider::MAILCOW => [
                'name' => 'Mailcow',
                'type' => ProviderType::TYPE_EMAIL,
                'icon' => 'mail',
                'color' => '#fed7aa',
                'description' => 'Email hosting and management',
                'configurable' => true
            ],
            EmailProvider::EXCHANGE => [
                'name' => 'Microsoft Exchange',
                'type' => ProviderType::TYPE_EMAIL,
                'icon' => 'mail',
                'color' => '#dbeafe',
                'description' => 'Office 365 Exchange Online',
                'configurable' => true
            ],
            EmailProvider::IMAP => [
                'name' => 'IMAP',
                'type' => ProviderType::TYPE_EMAIL,
                'icon' => 'mail',
                'color' => '#fef3c7',
                'description' => 'Generic IMAP/SMTP',
                'configurable' => true
            ],

            // Git
            GitProvider::GITLAB => [
                'name' => 'GitLab',
                'type' => ProviderType::TYPE_GIT,
                'icon' => 'git-branch',
                'color' => '#fecaca',
                'description' => 'GitLab repository management',
                'configurable' => true
            ],
            GitProvider::GITEA => [
                'name' => 'Gitea',
                'type' => ProviderType::TYPE_GIT,
                'icon' => 'git-branch',
                'color' => '#fed7aa',
                'description' => 'Gitea self-hosted git service',
                'configurable' => true
            ],
            GitProvider::GITHUB => [
                'name' => 'GitHub',
                'type' => ProviderType::TYPE_GIT,
                'icon' => 'git-branch',
                'color' => '#d1d5db',
                'description' => 'GitHub.com or Enterprise',
                'configurable' => true
            ],

            // Messenger
            MessengerProvider::TELEGRAM => [
                'name' => 'Telegram',
                'type' => ProviderType::TYPE_MESSENGER,
                'icon' => 'messages',
                'color' => '#bfdbfe',
                'description' => 'Telegram Bot API',
                'configurable' => true
            ],
            MessengerProvider::WHATSAPP => [
                'name' => 'WhatsApp',
                'type' => ProviderType::TYPE_MESSENGER,
                'icon' => 'messages',
                'color' => '#bbf7d0',
                'description' => 'WhatsApp Business API',
                'configurable' => true
            ],
            MessengerProvider::SLACK => [
                'name' => 'Slack',
                'type' => ProviderType::TYPE_MESSENGER,
                'icon' => 'messages',
                'color' => '#e9d5ff',
                'description' => 'Slack Workspace API',
                'configurable' => true
            ],
            MessengerProvider::TEAMS => [
                'name' => 'Microsoft Teams',
                'type' => ProviderType::TYPE_MESSENGER,
                'icon' => 'messages',
                'color' => '#dbeafe',
                'description' => 'Teams Bot Framework',
                'configurable' => true
            ],

            // IAM
            IamProvider::KEYCLOAK => [
                'name' => 'Keycloak',
                'type' => ProviderType::TYPE_IAM,
                'icon' => 'lock',
                'color' => '#c7d2fe',
                'description' => 'Keycloak identity provider',
                'configurable' => true
            ],
            IamProvider::OKTA => [
                'name' => 'Okta',
                'type' => ProviderType::TYPE_IAM,
                'icon' => 'lock',
                'color' => '#f3e8ff',
                'description' => 'Okta identity management',
                'configurable' => true
            ],
            IamProvider::AZURE_AD => [
                'name' => 'Azure AD',
                'type' => ProviderType::TYPE_IAM,
                'icon' => 'lock',
                'color' => '#dbeafe',
                'description' => 'Microsoft Azure Active Directory',
                'configurable' => true
            ],

            // Secrets
            SecretsProvider::PASSBOLT => [
                'name' => 'Passbolt',
                'type' => ProviderType::TYPE_SECRETS,
                'icon' => 'key',
                'color' => '#fecdd3',
                'description' => 'Open source team password manager',
                'configurable' => true
            ],
            SecretsProvider::BITWARDEN => [
                'name' => 'Bitwarden',
                'type' => ProviderType::TYPE_SECRETS,
                'icon' => 'key',
                'color' => '#bfdbfe',
                'description' => 'Open source password management',
                'configurable' => true
            ],
            SecretsProvider::ONEPWD => [
                'name' => '1Password',
                'type' => ProviderType::TYPE_SECRETS,
                'icon' => 'key',
                'color' => '#d1d5db',
                'description' => 'Enterprise password manager',
                'configurable' => true
            ],
            SecretsProvider::VAULT => [
                'name' => 'HashiCorp Vault',
                'type' => ProviderType::TYPE_SECRETS,
                'icon' => 'key',
                'color' => '#fef3c7',
                'description' => 'Secrets management platform',
                'configurable' => true
            ],
        ];
    }

    // ============================================
    // EMAIL PROVIDER FIELDS
    // ============================================

    private static function getMailcowFields(): array
    {
        return [
            'mailcow_url' => [
                'label' => 'Mailcow URL',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'https://mail.company.com',
                'description' => 'Base URL of your Mailcow installation'
            ],
            'mailcow_api_key' => [
                'label' => 'API Key',
                'type' => 'password',
                'required' => true,
                'description' => 'Admin API key from Mailcow'
            ]
        ];
    }

    private static function getExchangeFields(): array
    {
        return [
            'exchange_url' => [
                'label' => 'Exchange Server URL',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'https://outlook.office365.com/EWS/Exchange.asmx',
                'description' => 'Exchange Server or Office 365 endpoint'
            ],
            'exchange_username' => [
                'label' => 'Username',
                'type' => 'text',
                'required' => true,
                'description' => 'Service account email'
            ],
            'exchange_password' => [
                'label' => 'Password',
                'type' => 'password',
                'required' => true,
                'description' => 'Service account password'
            ]
        ];
    }

    private static function getImapFields(): array
    {
        return [
            'imap_host' => [
                'label' => 'IMAP Host',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'imap.company.com'
            ],
            'imap_port' => [
                'label' => 'IMAP Port',
                'type' => 'number',
                'required' => false,
                'value' => '993'
            ],
            'imap_tls' => [
                'label' => 'Use TLS/SSL',
                'type' => 'checkbox',
                'required' => false
            ],
            'imap_user' => [
                'label' => 'Username',
                'type' => 'text',
                'required' => true
            ],
            'imap_pass' => [
                'label' => 'Password',
                'type' => 'password',
                'required' => true
            ],
            'smtp_host' => [
                'label' => 'SMTP Host',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'smtp.company.com'
            ],
            'smtp_port' => [
                'label' => 'SMTP Port',
                'type' => 'number',
                'required' => false,
                'value' => '465'
            ],
            'smtp_tls' => [
                'label' => 'Use TLS/SSL',
                'type' => 'checkbox',
                'required' => false
            ],
            'smtp_user' => [
                'label' => 'Username',
                'type' => 'text',
                'required' => true
            ],
            'smtp_pass' => [
                'label' => 'Password',
                'type' => 'password',
                'required' => true
            ]
        ];
    }

    // ============================================
    // GIT PROVIDER FIELDS
    // ============================================

    private static function getGitLabFields(): array
    {
        return [
            'gitlab_url' => [
                'label' => 'GitLab URL',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'https://gitlab.company.com',
                'description' => 'Base URL of your GitLab instance'
            ],
            'gitlab_token' => [
                'label' => 'Personal Access Token',
                'type' => 'password',
                'required' => true,
                'description' => 'Token with admin access'
            ]
        ];
    }

    private static function getGiteaFields(): array
    {
        return [
            'gitea_url' => [
                'label' => 'Gitea URL',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'https://git.company.com',
                'description' => 'Base URL of your Gitea instance'
            ],
            'gitea_token' => [
                'label' => 'Access Token',
                'type' => 'password',
                'required' => true,
                'description' => 'Admin API token'
            ]
        ];
    }

    private static function getGithubFields(): array
    {
        return [
            'github_token' => [
                'label' => 'Personal Access Token',
                'type' => 'password',
                'required' => true,
                'description' => 'GitHub or GitHub Enterprise token'
            ],
            'github_org' => [
                'label' => 'Organization/Owner',
                'type' => 'text',
                'required' => true,
                'description' => 'Default organization for user creation'
            ],
            'github_api_url' => [
                'label' => 'API URL',
                'type' => 'text',
                'required' => false,
                'placeholder' => 'https://api.github.com',
                'description' => 'GitHub API endpoint (for Enterprise)'
            ]
        ];
    }

    // ============================================
    // MESSENGER PROVIDER FIELDS
    // ============================================

    private static function getTelegramFields(): array
    {
        return [
            'telegram_bot_token' => [
                'label' => 'Bot Token',
                'type' => 'password',
                'required' => true,
                'description' => 'Telegram Bot API token from @BotFather'
            ],
            'telegram_mode' => [
                'label' => 'Update Mode',
                'type' => 'radio',
                'required' => false,
                'options' => [
                    'webhook' => 'Webhook',
                    'polling' => 'Polling'
                ],
                'value' => 'webhook'
            ],
            'telegram_webhook_url' => [
                'label' => 'Webhook URL',
                'type' => 'text',
                'required' => false,
                'placeholder' => 'https://hr-assistant.company.com/webhook/telegram',
                'description' => 'Only required for webhook mode'
            ]
        ];
    }

    private static function getWhatsappFields(): array
    {
        return [
            'whatsapp_api_url' => [
                'label' => 'API URL',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'https://api.whatsapp.com',
                'description' => 'WhatsApp Business API endpoint'
            ],
            'whatsapp_access_token' => [
                'label' => 'Access Token',
                'type' => 'password',
                'required' => true,
                'description' => 'WhatsApp Business API access token'
            ],
            'whatsapp_phone_id' => [
                'label' => 'Phone Number ID',
                'type' => 'text',
                'required' => true,
                'description' => 'Phone number ID from Business Account'
            ]
        ];
    }

    private static function getSlackFields(): array
    {
        return [
            'slack_bot_token' => [
                'label' => 'Bot Token',
                'type' => 'password',
                'required' => true,
                'placeholder' => 'xxxx-xxxx-xxxx-xxxx-xxxx',
                'description' => 'Slack Bot User OAuth Token'
            ],
            'slack_signing_secret' => [
                'label' => 'Signing Secret',
                'type' => 'password',
                'required' => false,
                'description' => 'For request verification'
            ]
        ];
    }

    private static function getTeamsFields(): array
    {
        return [
            'teams_bot_id' => [
                'label' => 'Bot ID',
                'type' => 'text',
                'required' => true,
                'description' => 'Microsoft Teams Bot ID (App ID)'
            ],
            'teams_bot_password' => [
                'label' => 'Bot Password',
                'type' => 'password',
                'required' => true,
                'description' => 'Bot password/secret'
            ],
            'teams_service_url' => [
                'label' => 'Service URL',
                'type' => 'text',
                'required' => false,
                'placeholder' => 'https://smba.trafficmanager.net/emea/',
                'description' => 'Teams service URL (if different)'
            ]
        ];
    }

    // ============================================
    // IAM PROVIDER FIELDS
    // ============================================

    private static function getKeycloakFields(): array
    {
        return [
            'keycloak_url' => [
                'label' => 'Keycloak URL',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'https://keycloak.company.com/auth',
                'description' => 'Base URL of your Keycloak instance'
            ],
            'keycloak_realm' => [
                'label' => 'Realm',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'master',
                'description' => 'Keycloak realm name'
            ],
            'keycloak_client_id' => [
                'label' => 'Client ID',
                'type' => 'text',
                'required' => true,
                'description' => 'OAuth Client ID'
            ],
            'keycloak_client_secret' => [
                'label' => 'Client Secret',
                'type' => 'password',
                'required' => true,
                'description' => 'OAuth Client Secret'
            ]
        ];
    }

    private static function getOktaFields(): array
    {
        return [
            'okta_org_url' => [
                'label' => 'Organization URL',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'https://company.okta.com',
                'description' => 'Your Okta organization URL'
            ],
            'okta_api_token' => [
                'label' => 'API Token',
                'type' => 'password',
                'required' => true,
                'description' => 'Okta API token'
            ]
        ];
    }

    private static function getAzureAdFields(): array
    {
        return [
            'azure_tenant_id' => [
                'label' => 'Tenant ID',
                'type' => 'text',
                'required' => true,
                'placeholder' => '12345678-1234-5678-9abc-123456789abc',
                'description' => 'Azure AD Tenant ID'
            ],
            'azure_client_id' => [
                'label' => 'Client ID',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'abcdef12-3456-7890-abcd-ef1234567890',
                'description' => 'Application (Client) ID'
            ],
            'azure_client_secret' => [
                'label' => 'Client Secret',
                'type' => 'password',
                'required' => true,
                'placeholder' => 'Enter application client secret',
                'description' => 'Application Client Secret'
            ]
        ];
    }

    // ============================================
    // SECRETS PROVIDER FIELDS
    // ============================================

    private static function getPassboltFields(): array
    {
        return [
            'passbolt_url' => [
                'label' => 'Passbolt URL',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'https://passbolt.company.com',
                'description' => 'Base URL of your Passbolt instance'
            ],
            'passbolt_server_key' => [
                'label' => 'Server Public Key',
                'type' => 'textarea',
                'required' => true,
                'description' => 'Passbolt server GPG public key'
            ],
            'passbolt_user_key' => [
                'label' => 'User Private Key',
                'type' => 'textarea',
                'required' => true,
                'description' => 'GPG private key for API authentication'
            ],
            'passbolt_user_passphrase' => [
                'label' => 'Key Passphrase',
                'type' => 'password',
                'required' => true,
                'description' => 'Passphrase for the private key'
            ]
        ];
    }

    private static function getBitwardenFields(): array
    {
        return [
            'bitwarden_url' => [
                'label' => 'Bitwarden URL',
                'type' => 'text',
                'required' => false,
                'placeholder' => 'https://vault.bitwarden.com',
                'description' => 'Bitwarden server URL (leave empty for cloud)'
            ],
            'bitwarden_client_id' => [
                'label' => 'Client ID',
                'type' => 'text',
                'required' => true,
                'description' => 'API client ID from organization settings'
            ],
            'bitwarden_client_secret' => [
                'label' => 'Client Secret',
                'type' => 'password',
                'required' => true,
                'description' => 'API client secret'
            ],
            'bitwarden_org_id' => [
                'label' => 'Organization ID',
                'type' => 'text',
                'required' => true,
                'description' => 'Bitwarden organization ID'
            ]
        ];
    }

    private static function getOnePasswordFields(): array
    {
        return [
            'onepassword_url' => [
                'label' => '1Password Connect URL',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'https://connect.1password.com',
                'description' => '1Password Connect server URL'
            ],
            'onepassword_token' => [
                'label' => 'Connect Token',
                'type' => 'password',
                'required' => true,
                'description' => '1Password Connect access token'
            ],
            'onepassword_vault_id' => [
                'label' => 'Default Vault ID',
                'type' => 'text',
                'required' => false,
                'description' => 'Default vault for operations'
            ]
        ];
    }

    private static function getVaultFields(): array
    {
        return [
            'vault_url' => [
                'label' => 'Vault URL',
                'type' => 'text',
                'required' => true,
                'placeholder' => 'https://vault.company.com',
                'description' => 'HashiCorp Vault server URL'
            ],
            'vault_token' => [
                'label' => 'Access Token',
                'type' => 'password',
                'required' => true,
                'description' => 'Vault access token or root token'
            ],
            'vault_namespace' => [
                'label' => 'Namespace',
                'type' => 'text',
                'required' => false,
                'placeholder' => 'admin',
                'description' => 'Vault namespace (Enterprise only)'
            ],
            'vault_mount' => [
                'label' => 'Secrets Mount',
                'type' => 'text',
                'required' => false,
                'placeholder' => 'secret',
                'description' => 'KV secrets engine mount path'
            ]
        ];
    }

    /**
     * Get all configuration fields grouped by asset type
     */
    public static function getAllFields(): array
    {
        $fields = [];
        foreach (ProviderType::getAll() as $provider) {
            $fields[$provider] = self::getFields($provider);
        }
        return $fields;
    }

    /**
     * Validate configuration for a provider
     */
    public static function validateConfig(string $provider, array $config): array
    {
        $errors = [];
        $fields = self::getFields($provider);

        foreach ($fields as $key => $field) {
            if ($field['required'] ?? false) {
                if (empty($config[$key])) {
                    $errors[$key] = "{$field['label']} is required";
                }
            }
        }

        return $errors;
    }
}
