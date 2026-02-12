<?php
/**
 * Jira Cloud / Data Center REST API adapter.
 * Uses Basic Auth (email + API token) for Cloud, Bearer for DC.
 */

declare(strict_types=1);

namespace Src\Plugins\Jira;

final class JiraAdapter
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $email,
        private readonly string $token,
    ) {
    }

    public function listProjects(): array
    {
        return $this->get('/rest/api/2/project');
    }

    public function getProject(string $key): array
    {
        return $this->get('/rest/api/2/project/' . urlencode($key));
    }

    public function searchUsers(string $query): array
    {
        return $this->get('/rest/api/2/user/search?query=' . urlencode($query));
    }

    public function getProjectRoles(string $projectKey): array
    {
        return $this->get('/rest/api/2/project/' . urlencode($projectKey) . '/role');
    }

    public function addUserToProjectRole(string $projectKey, int $roleId, string $accountId): array
    {
        return $this->post('/rest/api/2/project/' . urlencode($projectKey) . '/role/' . $roleId, [
            'user' => [$accountId],
        ]);
    }

    public function removeUserFromProjectRole(string $projectKey, int $roleId, string $accountId): bool
    {
        $path = '/rest/api/2/project/' . urlencode($projectKey) . '/role/' . $roleId . '?user=' . urlencode($accountId);
        return $this->delete($path);
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
