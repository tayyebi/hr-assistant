<?php
/**
 * Keycloak Admin REST API adapter.
 * Uses client-credentials grant (service account) to obtain access tokens.
 */

declare(strict_types=1);

namespace Src\Plugins\Keycloak;

final class KeycloakAdapter
{
    private ?string $accessToken = null;

    public function __construct(
        private readonly string $baseUrl,
        private readonly string $realm,
        private readonly string $clientId,
        private readonly string $clientSecret,
    ) {
    }

    /* ---- Users ---- */

    public function listUsers(int $first = 0, int $max = 50): array
    {
        return $this->get("/admin/realms/{$this->realm}/users?first={$first}&max={$max}");
    }

    public function searchUsers(string $search): array
    {
        return $this->get("/admin/realms/{$this->realm}/users?search=" . urlencode($search));
    }

    public function getUser(string $userId): array
    {
        return $this->get("/admin/realms/{$this->realm}/users/{$userId}");
    }

    public function createUser(array $payload): string
    {
        /* Returns location header containing user id */
        $ch = curl_init($this->baseUrl . "/admin/realms/{$this->realm}/users");
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => $this->headers(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $resp = curl_exec($ch); curl_close($ch);
        if (preg_match('/Location:.*\/([a-f0-9-]+)\s/i', $resp, $m)) {
            return $m[1];
        }
        return '';
    }

    public function enableUser(string $userId): void { $this->put("/admin/realms/{$this->realm}/users/{$userId}", ['enabled' => true]); }
    public function disableUser(string $userId): void { $this->put("/admin/realms/{$this->realm}/users/{$userId}", ['enabled' => false]); }

    /* ---- Roles ---- */

    public function listRealmRoles(): array
    {
        return $this->get("/admin/realms/{$this->realm}/roles");
    }

    public function getUserRealmRoles(string $userId): array
    {
        return $this->get("/admin/realms/{$this->realm}/users/{$userId}/role-mappings/realm");
    }

    public function assignRealmRole(string $userId, array $roles): void
    {
        $this->postRaw("/admin/realms/{$this->realm}/users/{$userId}/role-mappings/realm", $roles);
    }

    public function removeRealmRole(string $userId, array $roles): void
    {
        $this->deleteWithBody("/admin/realms/{$this->realm}/users/{$userId}/role-mappings/realm", $roles);
    }

    /* ---- Groups ---- */

    public function listGroups(): array
    {
        return $this->get("/admin/realms/{$this->realm}/groups");
    }

    public function addUserToGroup(string $userId, string $groupId): void
    {
        $this->put("/admin/realms/{$this->realm}/users/{$userId}/groups/{$groupId}", []);
    }

    public function removeUserFromGroup(string $userId, string $groupId): void
    {
        $this->delete("/admin/realms/{$this->realm}/users/{$userId}/groups/{$groupId}");
    }

    /* ---- Token ---- */

    private function ensureToken(): void
    {
        if ($this->accessToken !== null) { return; }
        $ch = curl_init($this->baseUrl . "/realms/{$this->realm}/protocol/openid-connect/token");
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query([
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $r = curl_exec($ch); curl_close($ch);
        $data = json_decode($r ?: '', true) ?: [];
        $this->accessToken = $data['access_token'] ?? '';
    }

    /* ---- HTTP helpers ---- */

    private function get(string $path): array
    {
        $this->ensureToken();
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER     => $this->headers(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $r = curl_exec($ch); curl_close($ch);
        return json_decode($r ?: '[]', true) ?: [];
    }

    private function postRaw(string $path, array $data): void
    {
        $this->ensureToken();
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => $this->headers(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        curl_exec($ch); curl_close($ch);
    }

    private function put(string $path, array $data): void
    {
        $this->ensureToken();
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => $this->headers(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        curl_exec($ch); curl_close($ch);
    }

    private function delete(string $path): void
    {
        $this->ensureToken();
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => $this->headers(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        curl_exec($ch); curl_close($ch);
    }

    private function deleteWithBody(string $path, array $data): void
    {
        $this->ensureToken();
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => $this->headers(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        curl_exec($ch); curl_close($ch);
    }

    private function headers(): array
    {
        return [
            'Authorization: Bearer ' . ($this->accessToken ?? ''),
            'Content-Type: application/json',
            'Accept: application/json',
        ];
    }
}
