<?php

namespace App\Core;

/**
 * IAM Provider Types
 */
class IamProvider
{
    const KEYCLOAK = 'keycloak';
    const OKTA = 'okta';
    const AZURE_AD = 'azure_ad';

    public static function getAll(): array
    {
        return [self::KEYCLOAK, self::OKTA, self::AZURE_AD];
    }

    public static function getName(string $provider): string
    {
        $names = [
            self::KEYCLOAK => 'Keycloak',
            self::OKTA => 'Okta',
            self::AZURE_AD => 'Azure Active Directory',
        ];
        return $names[$provider] ?? ucfirst($provider);
    }
}