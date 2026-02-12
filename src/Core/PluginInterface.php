<?php
/**
 * Plugin contract.
 * Every plugin in src/Plugins/{Name}/ must have a Plugin.php that implements this.
 */

declare(strict_types=1);

namespace Src\Core;

interface PluginInterface
{
    public function name(): string;
    public function requires(): array;
    public function register(Router $router, Tenant $tenant, Database $db): void;
    public function sidebarItem(): ?array;
}
