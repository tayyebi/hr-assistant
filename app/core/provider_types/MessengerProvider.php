<?php
/**
 * Messenger Provider Types
 */
class MessengerProvider
{
    const TELEGRAM = 'telegram';
    const WHATSAPP = 'whatsapp';
    const SLACK = 'slack';
    const TEAMS = 'teams';

    // API Versions
    const TELEGRAM_VERSION = '7.0';
    const WHATSAPP_VERSION = '2.35';
    const SLACK_VERSION = '1.0';
    const TEAMS_VERSION = '1.0';

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

    public static function getVersion(string $provider): string
    {
        $versions = [
            self::TELEGRAM => self::TELEGRAM_VERSION,
            self::WHATSAPP => self::WHATSAPP_VERSION,
            self::SLACK => self::SLACK_VERSION,
            self::TEAMS => self::TEAMS_VERSION,
        ];
        return $versions[$provider] ?? '1.0';
    }
}