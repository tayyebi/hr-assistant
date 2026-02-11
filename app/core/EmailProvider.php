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
    const SMTP = 'smtp';

    public static function getAll(): array
    {
        return [self::MAILCOW, self::EXCHANGE, self::IMAP, self::SMTP];
    }

    public static function getName(string $provider): string
    {
        $names = [
            self::MAILCOW => 'Mailcow',
            self::EXCHANGE => 'Microsoft Exchange',
            self::IMAP => 'IMAP',
            self::SMTP => 'SMTP',
        ];
        return $names[$provider] ?? ucfirst($provider);
    }
}