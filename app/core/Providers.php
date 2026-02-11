<?php

namespace App\Core;

// Import provider type classes
use App\Core\{EmailProvider, GitProvider, MessengerProvider, IamProvider};

/**
 * Mailcow Email Provider
 */
class MailcowProvider extends HttpProvider
{
    public function __construct(string $tenantId, array $config)
    {
        parent::__construct($tenantId, $config);
        $this->baseUrl = rtrim($config['mailcow_url'] ?? '', '/') . '/api/v1/';
    }

    public function getType(): string
    {
        return EmailProvider::MAILCOW;
    }

    public function getAssetType(): string
    {
        return ProviderType::TYPE_EMAIL;
    }

    public function getConfigKeys(): array
    {
        return ['mailcow_url', 'mailcow_api_key'];
    }

    /**
     * Get Mailcow API headers with authentication
     */
    private function getMailcowHeaders(): array
    {
        return array_merge($this->getDefaultHeaders(), [
            'X-API-Key' => $this->config['mailcow_api_key'],
        ]);
    }

    public function createAsset(array $data): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('Mailcow provider not configured');
        }

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $domain = substr(strrchr($email, "@"), 1);

        $payload = [
            'local_part' => substr($email, 0, strpos($email, '@')),
            'domain' => $domain,
            'password' => $password,
            'password2' => $password,
            'quota' => $data['quota'] ?? 3072,
        ];

        $result = $this->post('add/mailbox', $payload, $this->getMailcowHeaders());

        if ($result && is_array($result) && !empty($result[0]['type']) && $result[0]['type'] === 'success') {
            return $this->formatAsset(
                'mailcow_' . md5($email),
                $email,
                'active',
                ['mailbox_created' => date('c')]
            );
        }

        throw new Exception('Failed to create mailbox: ' . json_encode($result));
    }

    public function deleteAsset(string $assetId): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        // Get the email identifier from asset metadata
        // In a real scenario, this would be retrieved from Asset model
        // For now, we'll delete by email pattern
        $asset = $this->getAsset($assetId);
        if (!$asset) {
            return false;
        }

        $email = $asset['identifier'];
        $result = $this->post('delete/mailbox', [$email], $this->getMailcowHeaders());

        return is_array($result) && !empty($result[0]['type']) && $result[0]['type'] === 'success';
    }

    public function updateAsset(string $assetId, array $data): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $email = $data['email'] ?? '';
        $payload = [];

        if (isset($data['password'])) {
            $payload['password'] = $data['password'];
            $payload['password2'] = $data['password'];
        }

        if (isset($data['quota'])) {
            $payload['quota'] = $data['quota'];
        }

        if (empty($payload)) {
            return true;
        }

        $result = $this->post('edit/mailbox', [$email => $payload], $this->getMailcowHeaders());
        return is_array($result) && !empty($result[0]['type']) && $result[0]['type'] === 'success';
    }

    public function getAsset(string $assetId): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $mailboxes = $this->get('get/mailbox/all', $this->getMailcowHeaders());

        if (!is_array($mailboxes)) {
            return null;
        }

        // Find mailbox by ID pattern (mailcow_{email_md5})
        foreach ($mailboxes as $mailbox) {
            if (md5($mailbox['username'] ?? '') === substr($assetId, 8)) {
                return $this->formatAsset(
                    'mailcow_' . md5($mailbox['username']),
                    $mailbox['username'],
                    'active',
                    $mailbox
                );
            }
        }

        return null;
    }

    public function listAssets(): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $mailboxes = $this->get('get/mailbox/all', $this->getMailcowHeaders());

        if (!is_array($mailboxes)) {
            return [];
        }

        return array_map(function ($mailbox) {
            return $this->formatAsset(
                'mailcow_' . md5($mailbox['username']),
                $mailbox['username'],
                'active',
                $mailbox
            );
        }, $mailboxes);
    }

    public function testConnection(): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        // Test API with a simple get request
        $result = $this->get('get/status/hostname', $this->getMailcowHeaders());
        return $result !== null && !empty($result);
    }
}

/**
 * GitLab Git Provider
 */
class GitLabProvider extends HttpProvider
{
    public function __construct(string $tenantId, array $config)
    {
        parent::__construct($tenantId, $config);
        $this->baseUrl = rtrim($config['gitlab_url'] ?? '', '/') . '/api/v4/';
    }

    public function getType(): string
    {
        return GitProvider::GITLAB;
    }

    public function getAssetType(): string
    {
        return ProviderType::TYPE_GIT;
    }

    public function getConfigKeys(): array
    {
        return ['gitlab_url', 'gitlab_token'];
    }

    /**
     * Get GitLab API headers with authentication
     */
    private function getGitLabHeaders(): array
    {
        return array_merge($this->getDefaultHeaders(), [
            'PRIVATE-TOKEN' => $this->config['gitlab_token'],
        ]);
    }

    public function createAsset(array $data): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('GitLab provider not configured');
        }

        $username = $data['username'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $name = $data['name'] ?? $username;

        $payload = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'name' => $name,
            'skip_confirmation' => true,
        ];

        $result = $this->post('users', $payload, $this->getGitLabHeaders());

        if (is_array($result) && !empty($result['id'])) {
            return $this->formatAsset(
                'gitlab_' . $result['id'],
                $username,
                'active',
                ['gitlab_user_id' => $result['id'], 'created_at' => date('c')]
            );
        }

        throw new Exception('Failed to create GitLab user: ' . json_encode($result));
    }

    public function deleteAsset(string $assetId): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        // Extract GitLab user ID from asset ID
        $userId = substr($assetId, 7); // Remove 'gitlab_' prefix

        return $this->delete('users/' . $userId, $this->getGitLabHeaders());
    }

    public function updateAsset(string $assetId, array $data): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $userId = substr($assetId, 7);
        $payload = [];

        if (isset($data['password'])) {
            $payload['password'] = $data['password'];
        }
        if (isset($data['email'])) {
            $payload['email'] = $data['email'];
        }
        if (isset($data['name'])) {
            $payload['name'] = $data['name'];
        }

        if (empty($payload)) {
            return true;
        }

        $result = $this->put('users/' . $userId, $payload, $this->getGitLabHeaders());
        return is_array($result) || $result === true;
    }

    public function getAsset(string $assetId): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $userId = substr($assetId, 7);
        $user = $this->get('users/' . $userId, $this->getGitLabHeaders());

        if (is_array($user) && !empty($user['id'])) {
            return $this->formatAsset(
                'gitlab_' . $user['id'],
                $user['username'],
                'active',
                $user
            );
        }

        return null;
    }

    public function listAssets(): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $users = $this->get('users?per_page=100', $this->getGitLabHeaders());

        if (!is_array($users)) {
            return [];
        }

        return array_map(function ($user) {
            return $this->formatAsset(
                'gitlab_' . $user['id'],
                $user['username'],
                'active',
                $user
            );
        }, $users);
    }

    public function testConnection(): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $result = $this->get('user', $this->getGitLabHeaders());
        return is_array($result) && !empty($result['id']);
    }
}

/**
 * Telegram Messenger Provider
 */
class TelegramProvider extends HttpProvider
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

        $chatId = $data['chat_id'] ?? '';
        $username = $data['username'] ?? '';

        // Verify chat exists by trying to get chat info
        $chatInfo = $this->get('getChat?chat_id=' . $chatId);

        if (!is_array($chatInfo) || !isset($chatInfo['ok']) || !$chatInfo['ok']) {
            throw new Exception('Invalid Telegram chat ID or chat not accessible');
        }

        return $this->formatAsset(
            'telegram_' . abs($chatId),
            $chatId,
            'active',
            ['username' => $username, 'chat_created' => date('c')]
        );
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

/**
 * Keycloak IAM Provider
 */
class KeycloakProvider extends HttpProvider
{
    private $accessToken = null;

    public function __construct(string $tenantId, array $config)
    {
        parent::__construct($tenantId, $config);
        $this->baseUrl = rtrim($config['keycloak_url'] ?? '', '/') . '/admin/realms/' . ($config['keycloak_realm'] ?? '') . '/';
    }

    public function getType(): string
    {
        return IamProvider::KEYCLOAK;
    }

    public function getAssetType(): string
    {
        return ProviderType::TYPE_IAM;
    }

    public function getConfigKeys(): array
    {
        return ['keycloak_url', 'keycloak_realm', 'keycloak_client_id', 'keycloak_client_secret'];
    }

    /**
     * Obtain OAuth2 access token
     */
    private function getAccessToken(): ?string
    {
        if ($this->accessToken) {
            return $this->accessToken;
        }

        $tokenUrl = rtrim($this->config['keycloak_url'] ?? '', '/') . '/realms/' . $this->config['keycloak_realm'] . '/protocol/openid-connect/token';

        $payload = [
            'grant_type' => 'client_credentials',
            'client_id' => $this->config['keycloak_client_id'],
            'client_secret' => $this->config['keycloak_client_secret'],
        ];

        try {
            $response = $this->httpClient->post($tokenUrl, [
                'form_params' => $payload,
                'verify' => true,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $this->accessToken = $data['access_token'] ?? null;
            return $this->accessToken;
        } catch (\Exception $e) {
            $this->logError('POST', $tokenUrl, $e->getMessage());
            return null;
        }
    }

    /**
     * Get headers with Bearer token authentication
     */
    private function getKeycloakHeaders(): array
    {
        $token = $this->getAccessToken();
        if (!$token) {
            return [];
        }

        return array_merge($this->getDefaultHeaders(), [
            'Authorization' => 'Bearer ' . $token,
        ]);
    }

    public function createAsset(array $data): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('Keycloak provider not configured');
        }

        $username = $data['username'] ?? '';
        $email = $data['email'] ?? '';
        $firstName = $data['first_name'] ?? '';
        $lastName = $data['last_name'] ?? '';
        $password = $data['password'] ?? bin2hex(random_bytes(8));

        $payload = [
            'username' => $username,
            'email' => $email,
            'firstName' => $firstName,
            'lastName' => $lastName,
            'enabled' => true,
        ];

        $result = $this->post('users', $payload, $this->getKeycloakHeaders());

        if (!is_array($result) && $result === true) {
            // User created, now set password
            $users = $this->get('users?username=' . urlencode($username), $this->getKeycloakHeaders());

            if (is_array($users) && !empty($users[0]['id'])) {
                $userId = $users[0]['id'];
                $this->post(
                    'users/' . $userId . '/reset-password',
                    ['type' => 'password', 'value' => $password, 'temporary' => false],
                    $this->getKeycloakHeaders()
                );

                return $this->formatAsset(
                    'keycloak_' . $userId,
                    $username,
                    'active',
                    ['keycloak_user_id' => $userId, 'created_at' => date('c')]
                );
            }
        }

        throw new Exception('Failed to create Keycloak user: ' . json_encode($result));
    }

    public function deleteAsset(string $assetId): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $userId = substr($assetId, 10); // Remove 'keycloak_' prefix

        return $this->delete('users/' . $userId, $this->getKeycloakHeaders());
    }

    public function updateAsset(string $assetId, array $data): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $userId = substr($assetId, 10);
        $payload = [];

        if (isset($data['email'])) {
            $payload['email'] = $data['email'];
        }
        if (isset($data['first_name'])) {
            $payload['firstName'] = $data['first_name'];
        }
        if (isset($data['last_name'])) {
            $payload['lastName'] = $data['last_name'];
        }

        if (empty($payload)) {
            return true;
        }

        $result = $this->put('users/' . $userId, $payload, $this->getKeycloakHeaders());
        return is_array($result) || $result === true;
    }

    public function getAsset(string $assetId): ?array
    {
        if (!$this->isConfigured()) {
            return null;
        }

        $userId = substr($assetId, 10);
        $user = $this->get('users/' . $userId, $this->getKeycloakHeaders());

        if (is_array($user) && !empty($user['id'])) {
            return $this->formatAsset(
                'keycloak_' . $user['id'],
                $user['username'],
                'active',
                $user
            );
        }

        return null;
    }

    public function listAssets(): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $users = $this->get('users?max=100', $this->getKeycloakHeaders());

        if (!is_array($users)) {
            return [];
        }

        return array_map(function ($user) {
            return $this->formatAsset(
                'keycloak_' . $user['id'],
                $user['username'],
                'active',
                $user
            );
        }, $users);
    }

    public function testConnection(): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $token = $this->getAccessToken();
        return $token !== null;
    }
}
