<?php
/**
 * Keycloak IAM Provider (Version 25.0)
 */
class KeycloakProviderV25 extends HttpProvider
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

        $username = $data['identifier'] ?? '';
        $employee = $data['employee'] ?? [];
        $email = $employee['email'] ?? '';
        $firstName = $employee['first_name'] ?? '';
        $lastName = $employee['last_name'] ?? '';
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

                return [
                    'id' => 'keycloak_' . $userId,
                    'password' => $password,
                    'metadata' => ['keycloak_user_id' => $userId, 'created_at' => date('c')]
                ];
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