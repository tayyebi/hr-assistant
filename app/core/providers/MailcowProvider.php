<?php
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

        $assetType = $data['asset_type'] ?? '';
        $identifier = $data['identifier'] ?? '';
        $employee = $data['employee'] ?? [];
        $password = $data['password'] ?? '';

        if ($assetType === 'alias') {
            // Create alias pointing to employee's mailbox
            $employeeAssets = Asset::getByEmployee($this->tenantId, $employee['id'] ?? '');
            $mailboxAsset = array_filter($employeeAssets, fn($a) => $a['provider'] === EmailProvider::MAILCOW && $a['asset_type'] === ProviderType::TYPE_EMAIL);
            if (empty($mailboxAsset)) {
                throw new Exception('Employee must have a mailbox assigned first');
            }
            $mailboxEmail = $mailboxAsset[0]['identifier'] ?? '';
            if (!$mailboxEmail) {
                throw new Exception('Mailbox email not found');
            }

            $domain = substr(strrchr($identifier, "@"), 1);
            $payload = [
                'address' => $identifier,
                'goto' => $mailboxEmail,
                'active' => 1,
            ];

            $result = $this->post('add/alias', $payload, $this->getMailcowHeaders());

            if ($result && is_array($result) && !empty($result[0]['type']) && $result[0]['type'] === 'success') {
                return [
                    'id' => 'mailcow_alias_' . md5($identifier),
                    'password' => null,
                    'metadata' => ['alias' => $identifier, 'goto' => $mailboxEmail, 'created_at' => date('c')]
                ];
            }

            throw new Exception('Failed to create alias: ' . json_encode($result));
        } else {
            // Create mailbox
            $email = $identifier;
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
                return [
                    'id' => 'mailcow_' . md5($email),
                    'password' => $password,
                    'metadata' => ['mailbox_created' => date('c')]
                ];
            }

            throw new Exception('Failed to create mailbox: ' . json_encode($result));
        }
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

        $result = $this->get('get/domain/all', $this->getMailcowHeaders());
        return is_array($result);
    }
}