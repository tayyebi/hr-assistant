<?php
/**
 * Channel registry.
 * Plugins register their channel implementations here.
 */

declare(strict_types=1);

namespace Src\Core\Messaging;

final class ChannelManager
{
    private static ?self $instance = null;
    private array $channels = [];

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function register(ChannelInterface $channel): void
    {
        $this->channels[$channel->identifier()] = $channel;
    }

    public function get(string $identifier): ?ChannelInterface
    {
        return $this->channels[$identifier] ?? null;
    }

    public function all(): array
    {
        return $this->channels;
    }

    public function sendVia(string $identifier, string $to, string $body, array $meta = []): bool
    {
        $channel = $this->get($identifier);
        if ($channel === null) {
            return false;
        }
        return $channel->send($to, $body, $meta);
    }
}
