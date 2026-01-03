<?php
/**
 * Provider Factory
 * Creates and manages provider instances
 */
class ProviderFactory
{
    private static array $providers = [];
    private static array $instances = [];

    /**
     * Register available providers
     */
    public static function register(): void
    {
        // Require base classes first
        require_once __DIR__ . '/Provider.php';
        require_once __DIR__ . '/HttpProvider.php';

        // Require provider files
        require_once __DIR__ . '/providers/MailcowProvider.php';
        require_once __DIR__ . '/providers/GitLabProviderV17.php';
        require_once __DIR__ . '/providers/TelegramProviderV7.php';
        require_once __DIR__ . '/providers/KeycloakProviderV25.php';

        self::$providers = [
            EmailProvider::MAILCOW => MailcowProvider::class,
            GitProvider::GITLAB => GitLabProviderV17::class,
            MessengerProvider::TELEGRAM => TelegramProviderV7::class,
            IamProvider::KEYCLOAK => KeycloakProviderV25::class,
        ];
    }

    /**
     * Get the class name for a provider version
     */
    private static function getClassForVersion(string $providerType, string $version): string
    {
        // Extract major version (before first dot) for class name
        $majorVersion = explode('.', $version)[0];

        // Map provider to base class name
        $baseClasses = [
            GitProvider::GITLAB => 'GitLabProvider',
            MessengerProvider::TELEGRAM => 'TelegramProvider',
            IamProvider::KEYCLOAK => 'KeycloakProvider',
            EmailProvider::MAILCOW => 'MailcowProvider',
        ];

        if (!isset($baseClasses[$providerType])) {
            throw new Exception("No base class defined for provider '{$providerType}'");
        }

        $baseClass = $baseClasses[$providerType];
        $versionedClass = $baseClass . 'V' . $majorVersion;

        // Check if the versioned class exists, otherwise fall back to base
        if (class_exists($versionedClass)) {
            return $versionedClass;
        }

        // For now, return the versioned class assuming it exists
        // In production, you might want to check file existence or have a default
        return $versionedClass;
    }

    /**
     * Get or create a singleton provider instance
     */
    public static function getInstance(string $tenantId, string $providerType, array $config, string $version = '1.0'): IProvider
    {
        $key = "{$tenantId}_{$providerType}_{$version}";

        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = self::create($tenantId, $providerType, $config, $version);
        }

        return self::$instances[$key];
    }

    /**
     * Create a provider instance
     */
    public static function create(string $tenantId, string $providerType, array $config, string $version = '1.0'): IProvider
    {
        if (empty(self::$providers)) {
            self::register();
        }

        if (!isset(self::$providers[$providerType])) {
            throw new Exception("Provider '{$providerType}' is not registered");
        }

        $className = self::getClassForVersion($providerType, $version);

        if (!class_exists($className)) {
            throw new Exception("Provider class '{$className}' does not exist");
        }

        return new $className($tenantId, $config);
    }

    /**
     * Create providers for all asset types
     */
    public static function createForAssetType(string $tenantId, string $assetType, array $config): array
    {
        $providerTypes = ProviderType::getByType($assetType);
        $providers = [];

        foreach ($providerTypes as $providerType) {
            try {
                $providers[$providerType] = self::create($tenantId, $providerType, $config, '1.0');
            } catch (Exception $e) {
                // Provider not yet implemented, skip
            }
        }

        return $providers;
    }

    /**
     * Get all registered provider types
     */
    public static function getRegistered(): array
    {
        if (empty(self::$providers)) {
            self::register();
        }

        return array_keys(self::$providers);
    }

    /**
     * Clear cached instances (useful for testing)
     */
    public static function clearInstances(): void
    {
        self::$instances = [];
    }
}

// Auto-register providers when the factory is loaded
ProviderFactory::register();
