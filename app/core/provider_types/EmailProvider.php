<?php
/**
 * Email Provider Types
 */
class EmailProvider
{
    const MAILCOW = 'mailcow';
    const EXCHANGE = 'exchange';
    const IMAP = 'imap';

    // API Versions
    const MAILCOW_VERSION = '1.0'; // Latest stable
    const EXCHANGE_VERSION = '2021';
    const IMAP_VERSION = 'RFC3501';

    public static function getAll(): array
    {
        return [self::MAILCOW, self::EXCHANGE, self::IMAP];
    }

    public static function getName(string $provider): string
    {
        $names = [
            self::MAILCOW => 'Mailcow',
            self::EXCHANGE => 'Microsoft Exchange',
            self::IMAP => 'IMAP',
        ];
        return $names[$provider] ?? ucfirst($provider);
    }

    public static function getVersion(string $provider): string
    {
        $versions = [
            self::MAILCOW => self::MAILCOW_VERSION,
            self::EXCHANGE => self::EXCHANGE_VERSION,
            self::IMAP => self::IMAP_VERSION,
        ];
        return $versions[$provider] ?? '1.0';
    }
}