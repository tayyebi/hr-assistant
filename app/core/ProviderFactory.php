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
        self::$providers = [
            EmailProvider::MAILCOW => MailcowProvider::class,
            GitProvider::GITLAB => GitLabProvider::class,
            MessengerProvider::TELEGRAM => TelegramProvider::class,
            IamProvider::KEYCLOAK => KeycloakProvider::class,
        ];
    }

    /**
     * Create a provider instance
     */
    public static function create(string $tenantId, string $providerType, array $config): IProvider
    {
        if (empty(self::$providers)) {
            self::register();
        }

        if (!isset(self::$providers[$providerType])) {
            throw new Exception("Provider '{$providerType}' not found");
        }

        $className = self::$providers[$providerType];
        return new $className($tenantId, $config);
    }

    /**
     * Get or create a singleton provider instance
     */
    public static function getInstance(string $tenantId, string $providerType, array $config): IProvider
    {
        $key = "{$tenantId}_{$providerType}";

        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = self::create($tenantId, $providerType, $config);
        }

        return self::$instances[$key];
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
                $providers[$providerType] = self::create($tenantId, $providerType, $config);
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
