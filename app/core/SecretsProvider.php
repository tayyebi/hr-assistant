<?php

namespace App\Core;

/**
 * Secrets/Password Manager Provider Constants
 */
class SecretsProvider
{
    // Provider constants
    const PASSBOLT = 'passbolt';
    const BITWARDEN = 'bitwarden';
    const ONEPWD = '1password';
    const VAULT = 'hashicorp_vault';

    /**
     * Get all secrets providers
     */
    public static function getAll(): array
    {
        return [
            self::PASSBOLT,
            self::BITWARDEN,
            self::ONEPWD,
            self::VAULT,
        ];
    }

    /**
     * Get provider display name
     */
    public static function getName(string $provider): string
    {
        return match($provider) {
            self::PASSBOLT => 'Passbolt',
            self::BITWARDEN => 'Bitwarden',
            self::ONEPWD => '1Password',
            self::VAULT => 'HashiCorp Vault',
            default => ucfirst($provider)
        };
    }

    /**
     * Get provider description
     */
    public static function getDescription(string $provider): string
    {
        return match($provider) {
            self::PASSBOLT => 'Open source team password manager',
            self::BITWARDEN => 'Open source password management',
            self::ONEPWD => 'Enterprise password manager',
            self::VAULT => 'Secrets management platform',
            default => 'Password management service'
        };
    }
}
