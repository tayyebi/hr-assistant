<?php

namespace App\Core;

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
            \App\Core\EmailProvider::getAll(),
            \App\Core\GitProvider::getAll(),
            \App\Core\MessengerProvider::getAll(),
            \App\Core\IamProvider::getAll()
        );
    }

    /**
     * Get providers grouped by asset type
     */
    public static function getByType(string $type): array
    {
        $providers = [
            self::TYPE_EMAIL => \App\Core\EmailProvider::getAll(),
            self::TYPE_GIT => \App\Core\GitProvider::getAll(),
            self::TYPE_MESSENGER => \App\Core\MessengerProvider::getAll(),
            self::TYPE_IAM => \App\Core\IamProvider::getAll(),
        ];

        return $providers[$type] ?? [];
    }

    /**
     * Get asset type for a provider
     */
    public static function getAssetType(string $provider): ?string
    {
        if (in_array($provider, \App\Core\EmailProvider::getAll())) {
            return self::TYPE_EMAIL;
        }
        if (in_array($provider, \App\Core\GitProvider::getAll())) {
            return self::TYPE_GIT;
        }
        if (in_array($provider, \App\Core\MessengerProvider::getAll())) {
            return self::TYPE_MESSENGER;
        }
        if (in_array($provider, \App\Core\IamProvider::getAll())) {
            return self::TYPE_IAM;
        }

        return null;
    }

    /**
     * Get provider display name
     */
    public static function getName(string $provider): string
    {
        if (in_array($provider, \App\Core\EmailProvider::getAll())) {
            return \App\Core\EmailProvider::getName($provider);
        }
        if (in_array($provider, \App\Core\GitProvider::getAll())) {
            return \App\Core\GitProvider::getName($provider);
        }
        if (in_array($provider, \App\Core\MessengerProvider::getAll())) {
            return \App\Core\MessengerProvider::getName($provider);
        }
        if (in_array($provider, \App\Core\IamProvider::getAll())) {
            return \App\Core\IamProvider::getName($provider);
        }

        return ucfirst($provider);
    }

    /**
     * Get display name for a provider type
     */
    public static function getTypeName(string $type): string
    {
        return match($type) {
            self::TYPE_EMAIL => 'Email Services',
            self::TYPE_GIT => 'Git & Code Services',
            self::TYPE_MESSENGER => 'Messaging Services',
            self::TYPE_IAM => 'Identity & Access Management',
            default => ucfirst($type) . ' Services'
        };
    }

    /**
     * Check if a provider type is valid
     */
    public static function isValid(string $provider): bool
    {
        return in_array($provider, self::getAll());
    }
}
