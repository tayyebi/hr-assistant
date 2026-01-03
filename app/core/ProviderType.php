<?php
require_once __DIR__ . '/provider_types/EmailProvider.php';
require_once __DIR__ . '/provider_types/GitProvider.php';
require_once __DIR__ . '/provider_types/MessengerProvider.php';
require_once __DIR__ . '/provider_types/IamProvider.php';

/**
 * Provider Type Enumeration
 * Defines all supported asset provider types and asset categories
 */
class ProviderType
{
    // Asset Type Categories
    const TYPE_EMAIL = 'email';
    const TYPE_GIT = 'git';
    const TYPE_MESSENGER = 'messenger';
    const TYPE_IAM = 'iam';

    /**
     * Get all available provider types
     */
    public static function getAll(): array
    {
        return array_merge(
            EmailProvider::getAll(),
            GitProvider::getAll(),
            MessengerProvider::getAll(),
            IamProvider::getAll()
        );
    }

    /**
     * Get providers grouped by asset type
     */
    public static function getByType(string $type): array
    {
        $providers = [
            self::TYPE_EMAIL => EmailProvider::getAll(),
            self::TYPE_GIT => GitProvider::getAll(),
            self::TYPE_MESSENGER => MessengerProvider::getAll(),
            self::TYPE_IAM => IamProvider::getAll(),
        ];

        return $providers[$type] ?? [];
    }

    /**
     * Get the asset type category for a provider
     */
    public static function getAssetType(string $provider): ?string
    {
        if (in_array($provider, EmailProvider::getAll())) {
            return self::TYPE_EMAIL;
        }
        if (in_array($provider, GitProvider::getAll())) {
            return self::TYPE_GIT;
        }
        if (in_array($provider, MessengerProvider::getAll())) {
            return self::TYPE_MESSENGER;
        }
        if (in_array($provider, IamProvider::getAll())) {
            return self::TYPE_IAM;
        }
        return null;
    }

    /**
     * Get human-readable name for a provider
     */
    public static function getName(string $provider): string
    {
        if (in_array($provider, EmailProvider::getAll())) {
            return EmailProvider::getName($provider);
        }
        if (in_array($provider, GitProvider::getAll())) {
            return GitProvider::getName($provider);
        }
        if (in_array($provider, MessengerProvider::getAll())) {
            return MessengerProvider::getName($provider);
        }
        if (in_array($provider, IamProvider::getAll())) {
            return IamProvider::getName($provider);
        }
        return ucfirst($provider);
    }

    /**
     * Get API version for a provider
     */
    public static function getVersion(string $provider): string
    {
        if (in_array($provider, EmailProvider::getAll())) {
            return EmailProvider::getVersion($provider);
        }
        if (in_array($provider, GitProvider::getAll())) {
            return GitProvider::getVersion($provider);
        }
        if (in_array($provider, MessengerProvider::getAll())) {
            return MessengerProvider::getVersion($provider);
        }
        if (in_array($provider, IamProvider::getAll())) {
            return IamProvider::getVersion($provider);
        }
        return '1.0';
    }

    /**
     * Validate provider type
     */
    public static function isValid(string $provider): bool
    {
        return in_array($provider, self::getAll());
    }
}
