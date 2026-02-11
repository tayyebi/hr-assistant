<?php

namespace App\Core;

/**
 * Messenger Provider Types
 */
class MessengerProvider
{
    const TELEGRAM = 'telegram';
    const WHATSAPP = 'whatsapp';
    const SLACK = 'slack';
    const TEAMS = 'teams';

    public static function getAll(): array
    {
        return [self::TELEGRAM, self::WHATSAPP, self::SLACK, self::TEAMS];
    }

    public static function getName(string $provider): string
    {
        $names = [
            self::TELEGRAM => 'Telegram',
            self::WHATSAPP => 'WhatsApp',
            self::SLACK => 'Slack',
            self::TEAMS => 'Microsoft Teams',
        ];
        return $names[$provider] ?? ucfirst($provider);
    }
}