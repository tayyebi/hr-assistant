<?php

namespace App\Core;

/**
 * Git Provider Types
 */
class GitProvider
{
    const GITLAB = 'gitlab';
    const GITEA = 'gitea';
    const GITHUB = 'github';

    public static function getAll(): array
    {
        return [self::GITLAB, self::GITEA, self::GITHUB];
    }

    public static function getName(string $provider): string
    {
        $names = [
            self::GITLAB => 'GitLab',
            self::GITEA => 'Gitea',
            self::GITHUB => 'GitHub',
        ];
        return $names[$provider] ?? ucfirst($provider);
    }
}