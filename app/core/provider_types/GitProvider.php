<?php
/**
 * Git Provider Types
 */
class GitProvider
{
    const GITLAB = 'gitlab';
    const GITEA = 'gitea';
    const GITHUB = 'github';

    // API Versions
    const GITLAB_VERSION = '17.0';
    const GITEA_VERSION = '1.21';
    const GITHUB_VERSION = '2022-11-28';

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

    public static function getVersion(string $provider): string
    {
        $versions = [
            self::GITLAB => self::GITLAB_VERSION,
            self::GITEA => self::GITEA_VERSION,
            self::GITHUB => self::GITHUB_VERSION,
        ];
        return $versions[$provider] ?? '1.0';
    }
}