<?php
/**
 * GitLab REST API v4 adapter.
 * Uses system-level access tokens per instance.
 */

declare(strict_types=1);

namespace Src\Plugins\GitLab;

final class GitLabAdapter
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $token,
    ) {
    }

    public function listProjects(int $page = 1, int $perPage = 50): array
    {
        return $this->get("/api/v4/projects?page={$page}&per_page={$perPage}&membership=false");
    }

    public function listGroups(int $page = 1, int $perPage = 50): array
    {
        return $this->get("/api/v4/groups?page={$page}&per_page={$perPage}");
    }

    public function listUsers(int $page = 1, int $perPage = 50): array
    {
        return $this->get("/api/v4/users?page={$page}&per_page={$perPage}");
    }

    public function searchUsers(string $query): array
    {
        return $this->get("/api/v4/users?search=" . urlencode($query));
    }

    public function getProjectMembers(int $projectId): array
    {
        return $this->get("/api/v4/projects/{$projectId}/members");
    }

    public function getGroupMembers(int $groupId): array
    {
        return $this->get("/api/v4/groups/{$groupId}/members");
    }

    public function addProjectMember(int $projectId, int $userId, int $accessLevel = 30): array
    {
        return $this->post("/api/v4/projects/{$projectId}/members", [
            'user_id'      => $userId,
            'access_level' => $accessLevel,
        ]);
    }

    public function addGroupMember(int $groupId, int $userId, int $accessLevel = 30): array
    {
        return $this->post("/api/v4/groups/{$groupId}/members", [
            'user_id'      => $userId,
            'access_level' => $accessLevel,
        ]);
    }

    public function removeProjectMember(int $projectId, int $userId): bool
    {
        return $this->delete("/api/v4/projects/{$projectId}/members/{$userId}");
    }

    public function removeGroupMember(int $groupId, int $userId): bool
    {
        return $this->delete("/api/v4/groups/{$groupId}/members/{$userId}");
    }

    private function get(string $path): array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER     => ['PRIVATE-TOKEN: ' . $this->token],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result ?: '[]', true) ?: [];
    }

    private function post(string $path, array $data): array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => ['PRIVATE-TOKEN: ' . $this->token, 'Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result ?: '[]', true) ?: [];
    }

    private function delete(string $path): bool
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => ['PRIVATE-TOKEN: ' . $this->token],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code >= 200 && $code < 300;
    }
}
