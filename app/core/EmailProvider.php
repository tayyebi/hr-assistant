<?php

namespace App\Core;

/**
 * Email Provider Types
 */
class EmailProvider
{
    const MAILCOW = 'mailcow';
    const EXCHANGE = 'exchange';
    const IMAP = 'imap';

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
}