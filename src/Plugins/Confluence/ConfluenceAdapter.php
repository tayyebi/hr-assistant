<?php
/**
 * Confluence Cloud / Data Center REST API adapter.
 * Uses Basic Auth (email + API token) for Cloud, Bearer for DC.
 */

declare(strict_types=1);

namespace Src\Plugins\Confluence;

final class ConfluenceAdapter
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $email,
        private readonly string $token,
    ) {
    }

    public function listSpaces(int $start = 0, int $limit = 50): array
    {
        return $this->get('/rest/api/space?start=' . $start . '&limit=' . $limit);
    }

    public function getSpace(string $key): array
    {
        return $this->get('/rest/api/space/' . urlencode($key) . '?expand=description.plain,homepage');
    }

    public function searchContent(string $cql, int $limit = 25): array
    {
        return $this->get('/rest/api/content/search?cql=' . urlencode($cql) . '&limit=' . $limit);
    }

    public function getSpacePermissions(string $spaceKey): array
    {
        return $this->get('/rest/api/space/' . urlencode($spaceKey) . '/permission');
    }

    public function addSpacePermission(string $spaceKey, string $accountId, string $operation, string $target = 'space'): array
    {
        return $this->post('/rest/api/space/' . urlencode($spaceKey) . '/permission', [
            'subject' => ['type' => 'user', 'identifier' => $accountId],
            'operation' => ['key' => $operation, 'target' => $target],
        ]);
    }

    public function removeSpacePermission(string $spaceKey, int $permissionId): bool
    {
        return $this->delete('/rest/api/space/' . urlencode($spaceKey) . '/permission/' . $permissionId);
    }

    public function searchUsers(string $query): array
    {
        return $this->get('/rest/api/search/user?cql=' . urlencode('user.fullname~"' . $query . '"'));
    }

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
            'Authorization: Basic ' . base64_encode($this->email . ':' . $this->token),
            'Content-Type: application/json',
            'Accept: application/json',
        ];
    }
}
