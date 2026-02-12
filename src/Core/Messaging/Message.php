<?php
/**
 * Message DTO.
 * Represents a single message in any channel.
 */

declare(strict_types=1);

namespace Src\Core\Messaging;

final class Message
{
    public function __construct(
        public readonly string $channel,
        public readonly string $direction,
        public readonly string $from,
        public readonly string $to,
        public readonly string $body,
        public readonly string $timestamp,
        public readonly array $meta = [],
    ) {
    }
}
