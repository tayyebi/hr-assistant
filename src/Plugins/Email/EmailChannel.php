<?php
/**
 * Email channel adapter.
 * Uses IMAP for receiving and SMTP (via fsockopen) for sending.
 * RFC 5321 (SMTP), RFC 3501 (IMAP4rev1).
 */

declare(strict_types=1);

namespace Src\Plugins\Email;

use Src\Core\Database;
use Src\Core\Messaging\ChannelInterface;

final class EmailChannel implements ChannelInterface
{
    public function __construct(
        private readonly Database $db,
        private readonly int $tenantId,
        private readonly int $accountId,
    ) {
    }

    public function identifier(): string
    {
        return 'email';
    }

    public function send(string $to, string $body, array $meta = []): bool
    {
        $account = $this->getAccount();
        if ($account === null) {
            return false;
        }

        $subject = $meta['subject'] ?? '(No Subject)';
        $from = $account['from_address'] ?: $account['username'];
        $fromName = $account['from_name'] ?: 'HCMS';

        $success = $this->smtpSend($account, $from, $fromName, $to, $subject, $body);

        if ($success) {
            $this->db->query(
                'INSERT INTO emails (tenant_id, account_id, direction, from_address, to_address, subject, body) '
                . 'VALUES (?, ?, ?, ?, ?, ?, ?)',
                [$this->tenantId, $this->accountId, 'outbound', $from, $to, $subject, $body],
            );
        }

        return $success;
    }

    public function receive(): array
    {
        $account = $this->getAccount();
        if ($account === null) {
            return [];
        }
        return $this->imapFetch($account);
    }

    public function assignToEmployee(int $employeeId, string $channelAddress): void
    {
        $this->db->query(
            'UPDATE emails SET employee_id = ? WHERE tenant_id = ? AND (from_address = ? OR to_address = ?)',
            [$employeeId, $this->tenantId, $channelAddress, $channelAddress],
        );
    }

    private function getAccount(): ?array
    {
        $row = $this->db->fetchOne(
            'SELECT * FROM email_accounts WHERE id = ? AND tenant_id = ? AND is_active = 1',
            [$this->accountId, $this->tenantId],
        );
        return $row ?: null;
    }

    private function smtpSend(array $account, string $from, string $fromName, string $to, string $subject, string $body): bool
    {
        $host = $account['smtp_host'];
        $port = (int)$account['smtp_port'];
        $user = $account['username'];
        $pass = $account['password'];

        $socket = @fsockopen('tls://' . $host, $port, $errno, $errstr, 10);
        if (!$socket) {
            $socket = @fsockopen($host, $port, $errno, $errstr, 10);
        }
        if (!$socket) {
            return false;
        }

        $this->smtpRead($socket);
        $this->smtpWrite($socket, "EHLO hcms\r\n");
        $this->smtpRead($socket);

        $this->smtpWrite($socket, "AUTH LOGIN\r\n");
        $this->smtpRead($socket);
        $this->smtpWrite($socket, base64_encode($user) . "\r\n");
        $this->smtpRead($socket);
        $this->smtpWrite($socket, base64_encode($pass) . "\r\n");
        $resp = $this->smtpRead($socket);

        if (!str_starts_with($resp, '235')) {
            fclose($socket);
            return false;
        }

        $this->smtpWrite($socket, "MAIL FROM:<{$from}>\r\n");
        $this->smtpRead($socket);
        $this->smtpWrite($socket, "RCPT TO:<{$to}>\r\n");
        $this->smtpRead($socket);
        $this->smtpWrite($socket, "DATA\r\n");
        $this->smtpRead($socket);

        $headers = "From: {$fromName} <{$from}>\r\n"
            . "To: {$to}\r\n"
            . "Subject: {$subject}\r\n"
            . "MIME-Version: 1.0\r\n"
            . "Content-Type: text/plain; charset=utf-8\r\n"
            . "\r\n";

        $this->smtpWrite($socket, $headers . $body . "\r\n.\r\n");
        $resp = $this->smtpRead($socket);
        $this->smtpWrite($socket, "QUIT\r\n");
        fclose($socket);

        return str_starts_with($resp, '250');
    }

    private function smtpWrite($socket, string $data): void
    {
        fwrite($socket, $data);
    }

    private function smtpRead($socket): string
    {
        $response = '';
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $response;
    }

    private function imapFetch(array $account): array
    {
        $host = $account['imap_host'];
        $port = (int)$account['imap_port'];
        $user = $account['username'];
        $pass = $account['password'];

        $mailbox = '{' . $host . ':' . $port . '/imap/ssl}INBOX';
        $connection = @imap_open($mailbox, $user, $pass);
        if ($connection === false) {
            return [];
        }

        $emails = [];
        $check = imap_check($connection);
        $count = min($check->Nmsgs, 50);

        if ($count > 0) {
            $range = max(1, $check->Nmsgs - $count + 1) . ':' . $check->Nmsgs;
            $messages = imap_fetch_overview($connection, $range, 0);

            foreach ($messages as $msg) {
                $msgId = (string)($msg->message_id ?? '');
                $existing = $this->db->fetchOne(
                    'SELECT id FROM emails WHERE tenant_id = ? AND account_id = ? AND message_id = ?',
                    [$this->tenantId, $this->accountId, $msgId],
                );
                if ($existing) {
                    continue;
                }

                $body = imap_fetchbody($connection, $msg->msgno, '1');
                $fromAddr = (string)($msg->from ?? '');
                $toAddr = (string)($msg->to ?? '');
                $subject = isset($msg->subject) ? imap_utf8($msg->subject) : '';

                $this->db->query(
                    'INSERT INTO emails (tenant_id, account_id, message_id, direction, from_address, to_address, subject, body) '
                    . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                    [$this->tenantId, $this->accountId, $msgId, 'inbound', $fromAddr, $toAddr, $subject, $body],
                );

                $emails[] = [
                    'message_id' => $msgId,
                    'from'       => $fromAddr,
                    'to'         => $toAddr,
                    'subject'    => $subject,
                    'body'       => $body,
                ];
            }
        }

        imap_close($connection);
        return $emails;
    }
}
