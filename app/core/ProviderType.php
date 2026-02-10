<?php

namespace HRAssistant\Core;

/**
 * Email Provider Types
 */
class EmailProvider
{
    const MAILCOW = 'mailcow';
    const EXCHANGE = 'exchange';
    const IMAP = 'imap';

    public static function getAll(): array
    {
        return [self::MAILCOW, self::EXCHANGE, self::IMAP];
    }

    public static function getName(string $provider): string
    {
        $names = [
            self::MAILCOW => 'Mailcow',
            self::EXCHANGE => 'Microsoft Exchange',
            self::IMAP => 'IMAP',
        ];
        return $names[$provider] ?? ucfirst($provider);
    }
}

/**
 * Git Provider Types
 */
class GitProvider
{
    const GITLAB = 'gitlab';
    const GITEA = 'gitea';
    const GITHUB = 'github';

    public static function getAll(): array
    {
        return [self::GITLAB, self::GITEA, self::GITHUB];
    }

    public static function getName(string $provider): string
    {
        $names = [
            self::GITLAB => 'GitLab',
            self::GITEA => 'Gitea',
            self::GITHUB => 'GitHub',
        ];
        return $names[$provider] ?? ucfirst($provider);
    }
}

/**
 * Messenger Provider Types
 */
class MessengerProvider
{
    const TELEGRAM = 'telegram';
    const WHATSAPP = 'whatsapp';
    const SLACK = 'slack';
    const TEAMS = 'teams';

    public static function getAll(): array
    {
        return [self::TELEGRAM, self::WHATSAPP, self::SLACK, self::TEAMS];
    }

    public static function getName(string $provider): string
    {
        $names = [
            self::TELEGRAM => 'Telegram',
            self::WHATSAPP => 'WhatsApp',
            self::SLACK => 'Slack',
            self::TEAMS => 'Microsoft Teams',
        ];
        return $names[$provider] ?? ucfirst($provider);
    }
}

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
            self::AZURE_AD => 'Azure AD',
        ];
        return $names[$provider] ?? ucfirst($provider);
    }
}

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
     * Validate provider type
     */
    public static function isValid(string $provider): bool
    {
        return in_array($provider, self::getAll());
    }
}
