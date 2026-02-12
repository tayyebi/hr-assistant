<?php
/**
 * Messaging channel contract.
 * Each messaging plugin (Telegram, Email, etc.) implements this independently.
 */

declare(strict_types=1);

namespace Src\Core\Messaging;

interface ChannelInterface
{
    public function identifier(): string;
    public function send(string $to, string $body, array $meta = []): bool;
    public function receive(): array;
    public function assignToEmployee(int $employeeId, string $channelAddress): void;
}
