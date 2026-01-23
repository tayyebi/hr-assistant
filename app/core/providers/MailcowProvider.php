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

    public function listAvailableAssets(): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $assets = [];

        // Get mailboxes
        $mailboxes = $this->get('get/mailbox/all', $this->getMailcowHeaders());
        if (is_array($mailboxes)) {
            foreach ($mailboxes as $mailbox) {
                $assets[] = [
                    'id' => 'mailcow_mailbox_' . md5($mailbox['username']),
                    'type' => 'mailbox',
                    'identifier' => $mailbox['username'],
                    'name' => $mailbox['name'] ?: $mailbox['username'],
                    'metadata' => $mailbox
                ];
            }
        }

        // Get aliases
        $aliases = $this->get('get/alias/all', $this->getMailcowHeaders());
        if (is_array($aliases)) {
            foreach ($aliases as $alias) {
                $assets[] = [
                    'id' => 'mailcow_alias_' . md5($alias['address']),
                    'type' => 'alias',
                    'identifier' => $alias['address'],
                    'name' => $alias['address'],
                    'metadata' => $alias
                ];
            }
        }

        return $assets;
    }

    public function assignAsset(string $assetId, array $employee, array $options = []): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('Mailcow provider not configured');
        }

        if (str_starts_with($assetId, 'mailcow_mailbox_')) {
            // Assign mailbox - set password
            $emailMd5 = substr($assetId, 16); // Remove 'mailcow_mailbox_' prefix
            $password = $options['password'] ?? bin2hex(random_bytes(8));

            // Find the mailbox
            $mailboxes = $this->get('get/mailbox/all', $this->getMailcowHeaders());
            $mailbox = null;
            foreach ($mailboxes as $mb) {
                if (md5($mb['username']) === $emailMd5) {
                    $mailbox = $mb;
                    break;
                }
            }

            if (!$mailbox) {
                throw new Exception('Mailbox not found');
            }

            $result = $this->post('edit/mailbox', [
                $mailbox['username'] => [
                    'password' => $password,
                    'password2' => $password,
                    'active' => 1
                ]
            ], $this->getMailcowHeaders());

            if (!is_array($result) || empty($result[0]['type']) || $result[0]['type'] !== 'success') {
                throw new Exception('Failed to update mailbox password: ' . json_encode($result));
            }

            return [
                'id' => $assetId,
                'password' => $password,
                'metadata' => ['mailbox' => $mailbox['username'], 'assigned_at' => date('c')]
            ];
        } elseif (str_starts_with($assetId, 'mailcow_alias_')) {
            // Assign alias - point to employee's mailbox
            $aliasMd5 = substr($assetId, 14); // Remove 'mailcow_alias_' prefix

            // Find employee's mailbox
            $employeeAssets = Asset::getByEmployee($this->tenantId, $employee['id'] ?? '');
            $mailboxAsset = array_filter($employeeAssets, fn($a) => $a['provider'] === EmailProvider::MAILCOW && str_starts_with($a['identifier'], 'mailcow_mailbox_'));
            if (empty($mailboxAsset)) {
                throw new Exception('Employee must have a mailbox assigned first');
            }
            $mailboxAsset = reset($mailboxAsset);
            $mailboxEmail = json_decode($mailboxAsset['metadata'] ?? '{}', true)['mailbox'] ?? '';
            if (!$mailboxEmail) {
                throw new Exception('Mailbox email not found in employee assets');
            }

            // Find the alias
            $aliases = $this->get('get/alias/all', $this->getMailcowHeaders());
            $alias = null;
            foreach ($aliases as $al) {
                if (md5($al['address']) === $aliasMd5) {
                    $alias = $al;
                    break;
                }
            }

            if (!$alias) {
                throw new Exception('Alias not found');
            }

            $result = $this->post('edit/alias', [
                $alias['address'] => [
                    'goto' => $mailboxEmail,
                    'active' => 1
                ]
            ], $this->getMailcowHeaders());

            if (!is_array($result) || empty($result[0]['type']) || $result[0]['type'] !== 'success') {
                throw new Exception('Failed to update alias: ' . json_encode($result));
            }

            return [
                'id' => $assetId,
                'password' => null,
                'metadata' => ['alias' => $alias['address'], 'goto' => $mailboxEmail, 'assigned_at' => date('c')]
            ];
        } else {
            throw new Exception('Invalid asset ID format');
        }
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