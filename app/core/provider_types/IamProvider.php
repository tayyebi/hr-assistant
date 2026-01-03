<?php
/**
 * IAM Provider Types
 */
class IamProvider
{
    const KEYCLOAK = 'keycloak';
    const OKTA = 'okta';
    const AZURE_AD = 'azure_ad';

    // API Versions
    const KEYCLOAK_VERSION = '25.0';
    const OKTA_VERSION = '2024-07-01';
    const AZURE_AD_VERSION = '1.0';

    public static function getAll(): array
    {
        return [self::KEYCLOAK, self::OKTA, self::AZURE_AD];
    }

    public static function getName(string $provider): string
    {
        $names = [
            self::KEYCLOAK => 'Keycloak',
            self::OKTA => 'Okta',
            self::AZURE_AD => 'Azure AD',
        ];
        return $names[$provider] ?? ucfirst($provider);
    }

    public static function getVersion(string $provider): string
    {
        $versions = [
            self::KEYCLOAK => self::KEYCLOAK_VERSION,
            self::OKTA => self::OKTA_VERSION,
            self::AZURE_AD => self::AZURE_AD_VERSION,
        ];
        return $versions[$provider] ?? '1.0';
    }
}