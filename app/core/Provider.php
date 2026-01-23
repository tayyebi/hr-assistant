<?php
/**
 * Provider Interface
 * All provider implementations must implement this interface
 */
interface IProvider
{
    /**
     * Get provider type
     */
    public function getType(): string;

    /**
     * Get provider name
     */
    public function getName(): string;

    /**
     * Get asset type this provider manages
     */
    public function getAssetType(): string;

    /**
     * Check if provider is configured
     */
    public function isConfigured(): bool;

    /**
     * Get configuration keys required for this provider
     */
    public function getConfigKeys(): array;

    /**
     * Create an asset (e.g., provision an email account)
     */
    public function createAsset(array $data): array;

    /**
     * Delete an asset
     */
    public function deleteAsset(string $assetId): bool;

    /**
     * Update an asset
     */
    public function updateAsset(string $assetId, array $data): bool;

    /**
     * Get asset details
     */
    public function getAsset(string $assetId): ?array;

    /**
     * List all assets for a tenant/user
     */
    public function listAssets(): array;

    /**
     * List available assets that can be assigned to employees
     * Returns array of assets with their types (account, repository, chat, mailbox, alias, etc.)
     */
    public function listAvailableAssets(): array;

    /**
     * Assign an existing asset to an employee
     * For accounts: may set/reset password
     * For repositories: grant access with specified level
     * For chats/mailboxes: assign for messaging/email use
     */
    public function assignAsset(string $assetId, array $employee, array $options = []): array;

    /**
     * Test connection to provider
     */
    public function testConnection(): bool;
}

/**
 * Abstract Provider Base Class
 * Provides common functionality for all providers
 */
abstract class AbstractProvider implements IProvider
{
    protected string $tenantId;
    protected array $config;

    public function __construct(string $tenantId, array $config)
    {
        $this->tenantId = $tenantId;
        $this->config = $config;
    }

    /**
     * Get provider type
     */
    abstract public function getType(): string;

    /**
     * Get provider name
     */
    public function getName(): string
    {
        return ProviderType::getName($this->getType());
    }

    /**
     * Get asset type this provider manages
     */
    abstract public function getAssetType(): string;

    /**
     * Default implementation: check if all required config keys are present
     */
    public function isConfigured(): bool
    {
        $keys = $this->getConfigKeys();
        foreach ($keys as $key) {
            if (empty($this->config[$key])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get configuration keys required for this provider
     */
    abstract public function getConfigKeys(): array;

    /**
     * Default test connection - override in subclasses
     */
    public function testConnection(): bool
    {
        return $this->isConfigured();
    }

    /**
     * Default implementation: return empty array
     * Subclasses should override to return available assets
     */
    public function listAvailableAssets(): array
    {
        return [];
    }

    /**
     * Default implementation: throw exception
     * Subclasses should override to implement asset assignment
     */
    public function assignAsset(string $assetId, array $employee, array $options = []): array
    {
        throw new Exception('Asset assignment not implemented for this provider');
    }
}
