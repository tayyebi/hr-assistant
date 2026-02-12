<?php
/**
 * Nextcloud OCS + WebDAV adapter.
 * Uses Basic Auth with admin credentials.
 * Provides user provisioning (OCS) and file management (WebDAV).
 */

declare(strict_types=1);

namespace Src\Plugins\Nextcloud;

final class NextcloudAdapter
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $user,
        private readonly string $password,
    ) {
    }

    /* ---- OCS User Provisioning ---- */

    public function listUsers(): array
    {
        $resp = $this->ocsGet('/ocs/v1.php/cloud/users');
        return $resp['ocs']['data']['users'] ?? [];
    }

    public function getUser(string $userId): array
    {
        $resp = $this->ocsGet('/ocs/v1.php/cloud/users/' . urlencode($userId));
        return $resp['ocs']['data'] ?? [];
    }

    public function createUser(string $userId, string $password, string $displayName = '', array $groups = []): bool
    {
        $data = ['userid' => $userId, 'password' => $password];
        if ($displayName) { $data['displayName'] = $displayName; }
        if ($groups) { $data['groups'] = $groups; }
        $resp = $this->ocsPost('/ocs/v1.php/cloud/users', $data);
        return ($resp['ocs']['meta']['statuscode'] ?? 999) === 100;
    }

    public function enableUser(string $userId): bool
    {
        return $this->ocsPut('/ocs/v1.php/cloud/users/' . urlencode($userId) . '/enable');
    }

    public function disableUser(string $userId): bool
    {
        return $this->ocsPut('/ocs/v1.php/cloud/users/' . urlencode($userId) . '/disable');
    }

    public function deleteUser(string $userId): bool
    {
        return $this->ocsDelete('/ocs/v1.php/cloud/users/' . urlencode($userId));
    }

    /* ---- OCS Groups ---- */

    public function listGroups(): array
    {
        $resp = $this->ocsGet('/ocs/v1.php/cloud/groups');
        return $resp['ocs']['data']['groups'] ?? [];
    }

    public function addUserToGroup(string $userId, string $groupId): bool
    {
        $resp = $this->ocsPost('/ocs/v1.php/cloud/users/' . urlencode($userId) . '/groups', ['groupid' => $groupId]);
        return ($resp['ocs']['meta']['statuscode'] ?? 999) === 100;
    }

    public function removeUserFromGroup(string $userId, string $groupId): bool
    {
        return $this->ocsDelete('/ocs/v1.php/cloud/users/' . urlencode($userId) . '/groups?groupid=' . urlencode($groupId));
    }

    /* ---- WebDAV File Listing ---- */

    public function listFolder(string $userId, string $path = '/'): array
    {
        $url = $this->baseUrl . '/remote.php/dav/files/' . urlencode($userId) . '/' . ltrim($path, '/');
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'PROPFIND',
            CURLOPT_HTTPHEADER     => [
                'Authorization: Basic ' . base64_encode($this->user . ':' . $this->password),
                'Depth: 1',
                'Content-Type: application/xml',
            ],
            CURLOPT_POSTFIELDS     => '<?xml version="1.0"?><d:propfind xmlns:d="DAV:"><d:prop><d:displayname/><d:getcontentlength/><d:getlastmodified/><d:resourcetype/></d:prop></d:propfind>',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $r = curl_exec($ch); curl_close($ch);
        return $this->parsePropfind($r ?: '');
    }

    public function createFolder(string $userId, string $path): bool
    {
        $url = $this->baseUrl . '/remote.php/dav/files/' . urlencode($userId) . '/' . ltrim($path, '/');
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'MKCOL',
            CURLOPT_HTTPHEADER     => [
                'Authorization: Basic ' . base64_encode($this->user . ':' . $this->password),
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code >= 200 && $code < 300;
    }

    /* ---- Sharing ---- */

    public function shareFolder(string $path, string $shareWith, int $permissions = 1): array
    {
        $resp = $this->ocsPost('/ocs/v2.php/apps/files_sharing/api/v1/shares', [
            'path'        => $path,
            'shareType'   => 0,
            'shareWith'   => $shareWith,
            'permissions' => $permissions,
        ]);
        return $resp['ocs']['data'] ?? [];
    }

    /* ---- Internal helpers ---- */

    private function ocsGet(string $path): array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER     => $this->ocsHeaders(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $r = curl_exec($ch); curl_close($ch);
        return json_decode($r ?: '[]', true) ?: [];
    }

    private function ocsPost(string $path, array $data): array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => $this->ocsHeaders(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $r = curl_exec($ch); curl_close($ch);
        return json_decode($r ?: '[]', true) ?: [];
    }

    private function ocsPut(string $path): bool
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_HTTPHEADER     => $this->ocsHeaders(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code >= 200 && $code < 300;
    }

    private function ocsDelete(string $path): bool
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => $this->ocsHeaders(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        return $code >= 200 && $code < 300;
    }

    private function ocsHeaders(): array
    {
        return [
            'Authorization: Basic ' . base64_encode($this->user . ':' . $this->password),
            'OCS-APIRequest: true',
            'Content-Type: application/json',
            'Accept: application/json',
        ];
    }

    private function parsePropfind(string $xml): array
    {
        if (!$xml) { return []; }
        $items = [];
        libxml_use_internal_errors(true);
        $doc = simplexml_load_string($xml);
        if (!$doc) { return []; }
        $doc->registerXPathNamespace('d', 'DAV:');
        foreach ($doc->xpath('//d:response') as $resp) {
            $href = (string)$resp->xpath('d:href')[0] ?? '';
            $name = (string)($resp->xpath('d:propstat/d:prop/d:displayname')[0] ?? basename($href));
            $isDir = !empty($resp->xpath('d:propstat/d:prop/d:resourcetype/d:collection'));
            $size = (int)($resp->xpath('d:propstat/d:prop/d:getcontentlength')[0] ?? 0);
            $items[] = ['href' => $href, 'name' => $name, 'is_dir' => $isDir, 'size' => $size];
        }
        return $items;
    }
}
