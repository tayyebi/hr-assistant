<?php

namespace App\Core;

// Import the separate provider classes
use App\Core\{EmailProvider, GitProvider, MessengerProvider, IamProvider};

/**
 * Provider Type Constants and Utilities
 */
class ProviderType
{
    // Asset type constants
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
     * Get asset type for a provider
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
     * Get provider display name
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
     * Check if a provider type is valid
     */
    public static function isValid(string $provider): bool
    {
        return in_array($provider, self::getAll());
    }
}