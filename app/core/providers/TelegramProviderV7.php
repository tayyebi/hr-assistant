<?php
/**
 * Telegram Messenger Provider (Version 7.0)
 */
class TelegramProviderV7 extends HttpProvider
{
    const TELEGRAM_API_BASE = 'https://api.telegram.org/bot';

    public function __construct(string $tenantId, array $config)
    {
        parent::__construct($tenantId, $config);
        $this->baseUrl = self::TELEGRAM_API_BASE . ($config['telegram_bot_token'] ?? '') . '/';
    }

    public function getType(): string
    {
        return MessengerProvider::TELEGRAM;
    }

    public function getAssetType(): string
    {
        return ProviderType::TYPE_MESSENGER;
    }

    public function getConfigKeys(): array
    {
        return ['telegram_bot_token'];
    }

    public function createAsset(array $data): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('Telegram provider not configured');
        }

        $chatId = $data['identifier'] ?? '';
        $employee = $data['employee'] ?? [];

        // Verify chat exists by trying to get chat info
        $chatInfo = $this->get('getChat?chat_id=' . $chatId);

        if (!is_array($chatInfo) || !isset($chatInfo['ok']) || !$chatInfo['ok']) {
            throw new Exception('Invalid Telegram chat ID or chat not accessible');
        }

        return [
            'id' => 'telegram_' . abs($chatId),
            'password' => null,
            'metadata' => ['chat_id' => $chatId, 'assigned_at' => date('c')]
        ];
    }

    public function deleteAsset(string $assetId): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        // Telegram doesn't allow deleting chats, so we mark as inactive
        // This is handled at the Asset model level
        return true;
    }

    public function updateAsset(string $assetId, array $data): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $chatId = $data['chat_id'] ?? '';

        if (!$chatId) {
            return true;
        }

        // Verify chat still exists
        $chatInfo = $this->get('getChat?chat_id=' . $chatId);
        return is_array($chatInfo) && isset($chatInfo['ok']) && $chatInfo['ok'];
    }

    public function getAsset(string $assetId): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        // Extract chat ID from asset ID
        $chatId = '-' . substr($assetId, 9); // Remove 'telegram_' prefix

        $chatInfo = $this->get('getChat?chat_id=' . $chatId);

        if (is_array($chatInfo) && isset($chatInfo['ok']) && $chatInfo['ok']) {
            $chat = $chatInfo['result'] ?? [];
            return $this->formatAsset(
                $assetId,
                $chatId,
                'active',
                $chat
            );
        }

        return null;
    }

    public function listAssets(): array
    {
        // Telegram Bot API doesn't provide a list of chats the bot is in
        // This would need to be tracked at the application level
        // Return empty for now - application should maintain chat list separately
        return [];
    }

    public function testConnection(): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $result = $this->get('getMe');
        return is_array($result) && isset($result['ok']) && $result['ok'];
    }
}