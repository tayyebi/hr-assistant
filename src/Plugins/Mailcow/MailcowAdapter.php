<?php
/**
 * Mailcow API adapter.
 * Mailcow API v1 for mailbox provisioning.
 */

declare(strict_types=1);

namespace Src\Plugins\Mailcow;

final class MailcowAdapter
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $apiKey,
    ) {
    }

    public function listMailboxes(): array
    {
        return $this->get('/api/v1/get/mailbox/all');
    }

    public function listDomains(): array
    {
        return $this->get('/api/v1/get/domain/all');
    }

    public function createMailbox(string $localPart, string $domain, string $name, string $password, int $quota = 1024): array
    {
        return $this->post('/api/v1/add/mailbox', [
            'local_part' => $localPart,
            'domain'     => $domain,
            'name'       => $name,
            'password'   => $password,
            'password2'  => $password,
            'quota'      => $quota,
            'active'     => '1',
        ]);
    }

    public function deleteMailbox(string $username): array
    {
        return $this->post('/api/v1/delete/mailbox', [$username]);
    }

    public function toggleMailbox(string $username, bool $active): array
    {
        return $this->post('/api/v1/edit/mailbox', [
            'items' => [$username],
            'attr'  => ['active' => $active ? '1' : '0'],
        ]);
    }

    public function getMailbox(string $username): array
    {
        return $this->get('/api/v1/get/mailbox/' . urlencode($username));
    }

    private function get(string $path): array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER     => ['X-API-Key: ' . $this->apiKey, 'Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $r = curl_exec($ch);
        curl_close($ch);
        return json_decode($r ?: '[]', true) ?: [];
    }

    private function post(string $path, array $data): array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => ['X-API-Key: ' . $this->apiKey, 'Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $r = curl_exec($ch);
        curl_close($ch);
        return json_decode($r ?: '[]', true) ?: [];
    }
}
