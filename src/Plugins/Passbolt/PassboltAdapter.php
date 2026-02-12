<?php
/**
 * Passbolt REST API adapter.
 * Uses API key (X-Passbolt-Api-Key) header for authentication.
 */

declare(strict_types=1);

namespace Src\Plugins\Passbolt;

final class PassboltAdapter
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
    ) {
    }

    /* ---- Users ---- */

    public function listUsers(): array
    {
        $resp = $this->get('/users.json');
        return $resp['body'] ?? $resp;
    }

    public function getUser(string $id): array
    {
        $resp = $this->get('/users/' . urlencode($id) . '.json');
        return $resp['body'] ?? $resp;
    }

    public function createUser(string $username, string $firstName, string $lastName, string $roleId = ''): array
    {
        $payload = [
            'username' => $username,
            'profile'  => ['first_name' => $firstName, 'last_name' => $lastName],
        ];
        if ($roleId) { $payload['role_id'] = $roleId; }
        $resp = $this->post('/users.json', $payload);
        return $resp['body'] ?? $resp;
    }

    public function deleteUser(string $id): bool
    {
        return $this->delete('/users/' . urlencode($id) . '.json');
    }

    /* ---- Groups ---- */

    public function listGroups(): array
    {
        $resp = $this->get('/groups.json');
        return $resp['body'] ?? $resp;
    }

    public function addUserToGroup(string $groupId, string $userId, bool $isAdmin = false): array
    {
        $group = $this->get('/groups/' . urlencode($groupId) . '.json');
        $groupData = $group['body'] ?? $group;
        $members = $groupData['groups_users'] ?? [];
        $members[] = ['user_id' => $userId, 'is_admin' => $isAdmin];
        $resp = $this->put('/groups/' . urlencode($groupId) . '.json', ['groups_users' => $members]);
        return $resp['body'] ?? $resp;
    }

    /* ---- Resources ---- */

    public function listResources(): array
    {
        $resp = $this->get('/resources.json');
        return $resp['body'] ?? $resp;
    }

    /* ---- HTTP helpers ---- */

    private function get(string $path): array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER     => $this->headers(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $r = curl_exec($ch); curl_close($ch);
        return json_decode($r ?: '[]', true) ?: [];
    }

    private function post(string $path, array $data): array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => $this->headers(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $r = curl_exec($ch); curl_close($ch);
        return json_decode($r ?: '[]', true) ?: [];
    }

    private function put(string $path, array $data): array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => $this->headers(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $r = curl_exec($ch); curl_close($ch);
        return json_decode($r ?: '[]', true) ?: [];
    }

    private function delete(string $path): bool
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => $this->headers(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code >= 200 && $code < 300;
    }

    private function headers(): array
    {
        return [
            'X-Passbolt-Api-Key: ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ];
    }
}
