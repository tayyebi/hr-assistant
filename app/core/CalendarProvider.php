<?php

namespace App\Core;

/**
 * Calendar Provider Types
 */
class CalendarProvider
{
    const GOOGLE_CALENDAR = 'google_calendar';
    const OUTLOOK_CALENDAR = 'outlook_calendar';
    const CALDAV = 'caldav';

    public static function getAll(): array
    {
        return [self::GOOGLE_CALENDAR, self::OUTLOOK_CALENDAR, self::CALDAV];
    }

    public static function getName(string $provider): string
    {
        $names = [
            self::GOOGLE_CALENDAR => 'Google Calendar',
            self::OUTLOOK_CALENDAR => 'Outlook Calendar',
            self::CALDAV => 'CalDAV',
        ];
        return $names[$provider] ?? ucfirst($provider);
    }
}
