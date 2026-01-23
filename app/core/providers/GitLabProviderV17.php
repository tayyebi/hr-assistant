<?php
/**
 * GitLab Git Provider (Version 17.0)
 */
class GitLabProviderV17 extends HttpProvider
{
    public function __construct(string $tenantId, array $config)
    {
        // Map common keys to GitLab-specific keys
        if (isset($config['url']) && !isset($config['gitlab_url'])) {
            $config['gitlab_url'] = $config['url'];
        }
        if (isset($config['token']) && !isset($config['gitlab_token'])) {
            $config['gitlab_token'] = $config['token'];
        }
        
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

        $assetType = $data['asset_type'] ?? '';
        $identifier = $data['identifier'] ?? '';
        $employee = $data['employee'] ?? [];
        $password = $data['password'] ?? '';

        if ($assetType === 'repository') {
            // Grant access to repository
            // Find employee's GitLab user ID
            $employeeAssets = Asset::getByEmployee($this->tenantId, $employee['id'] ?? '');
            $gitlabUser = array_filter($employeeAssets, fn($a) => $a['provider'] === GitProvider::GITLAB && $a['asset_type'] === ProviderType::TYPE_GIT);
            if (empty($gitlabUser)) {
                throw new Exception('Employee must have a GitLab account assigned first');
            }
            $userId = json_decode($gitlabUser[0]['metadata'] ?? '{}', true)['gitlab_user_id'] ?? null;
            if (!$userId) {
                throw new Exception('GitLab user ID not found');
            }

            // Add user to project as maintainer
            $result = $this->post("projects/{$identifier}/members", [
                'user_id' => $userId,
                'access_level' => 40, // Maintainer
            ], $this->getGitLabHeaders());

            if (is_array($result) && !empty($result['id'])) {
                return [
                    'id' => 'gitlab_repo_' . $identifier . '_' . $userId,
                    'password' => null,
                    'metadata' => ['project_id' => $identifier, 'user_id' => $userId, 'access_level' => 40]
                ];
            }

            throw new Exception('Failed to grant GitLab repository access: ' . json_encode($result));
        } else {
            // Create user
            $username = $identifier;
            $email = $employee['email'] ?? '';
            $name = $employee['full_name'] ?? $username;

            $payload = [
                'username' => $username,
                'email' => $email,
                'password' => $password,
                'name' => $name,
                'skip_confirmation' => true,
            ];

            $result = $this->post('users', $payload, $this->getGitLabHeaders());

            if (is_array($result) && !empty($result['id'])) {
                return [
                    'id' => 'gitlab_' . $result['id'],
                    'password' => $password,
                    'metadata' => ['gitlab_user_id' => $result['id'], 'created_at' => date('c')]
                ];
            }

            throw new Exception('Failed to create GitLab user: ' . json_encode($result));
        }
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

    public function listAvailableAssets(): array
    {
        if (!$this->isConfigured()) {
            return [];
        }

        $assets = [];

        // Get users
        $users = $this->get('users?per_page=100', $this->getGitLabHeaders());
        if (is_array($users)) {
            foreach ($users as $user) {
                $assets[] = [
                    'id' => 'gitlab_user_' . $user['id'],
                    'type' => 'account',
                    'identifier' => $user['username'],
                    'name' => $user['name'] ?: $user['username'],
                    'metadata' => $user
                ];
            }
        }

        // Get projects/repositories
        $projects = $this->get('projects?per_page=100', $this->getGitLabHeaders());
        if (is_array($projects)) {
            foreach ($projects as $project) {
                $assets[] = [
                    'id' => 'gitlab_repo_' . $project['id'],
                    'type' => 'repository',
                    'identifier' => $project['id'],
                    'name' => $project['name_with_namespace'],
                    'metadata' => $project
                ];
            }
        }

        return $assets;
    }

    public function assignAsset(string $assetId, array $employee, array $options = []): array
    {
        if (!$this->isConfigured()) {
            throw new Exception('GitLab provider not configured');
        }

        if (str_starts_with($assetId, 'gitlab_user_')) {
            // Assign user account - set password
            $userId = substr($assetId, 12); // Remove 'gitlab_user_' prefix
            $password = $options['password'] ?? bin2hex(random_bytes(8));

            $result = $this->put('users/' . $userId, [
                'password' => $password,
                'email' => $employee['email'],
                'name' => $employee['full_name'] ?? ($employee['first_name'] . ' ' . $employee['last_name'])
            ], $this->getGitLabHeaders());

            if (!is_array($result) && $result !== true) {
                throw new Exception('Failed to update GitLab user: ' . json_encode($result));
            }

            return [
                'id' => $assetId,
                'password' => $password,
                'metadata' => ['gitlab_user_id' => $userId, 'assigned_at' => date('c')]
            ];
        } elseif (str_starts_with($assetId, 'gitlab_repo_')) {
            // Assign repository access
            $projectId = substr($assetId, 12); // Remove 'gitlab_repo_' prefix
            $accessLevel = $options['access_level'] ?? 30; // Developer by default

            // Find employee's GitLab user ID from their assigned assets
            $employeeAssets = Asset::getByEmployee($this->tenantId, $employee['id'] ?? '');
            $gitlabUser = array_filter($employeeAssets, fn($a) => $a['provider'] === GitProvider::GITLAB && str_starts_with($a['identifier'], 'gitlab_user_'));
            if (empty($gitlabUser)) {
                throw new Exception('Employee must have a GitLab account assigned first');
            }
            $userAsset = reset($gitlabUser);
            $userId = json_decode($userAsset['metadata'] ?? '{}', true)['gitlab_user_id'] ?? null;
            if (!$userId) {
                throw new Exception('GitLab user ID not found in employee assets');
            }

            $result = $this->post("projects/{$projectId}/members", [
                'user_id' => $userId,
                'access_level' => $accessLevel,
            ], $this->getGitLabHeaders());

            if (!is_array($result) && $result !== true) {
                throw new Exception('Failed to grant GitLab repository access: ' . json_encode($result));
            }

            return [
                'id' => $assetId,
                'password' => null,
                'metadata' => ['project_id' => $projectId, 'user_id' => $userId, 'access_level' => $accessLevel, 'assigned_at' => date('c')]
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

        $result = $this->get('user', $this->getGitLabHeaders());
        return is_array($result) && !empty($result['id']);
    }
}