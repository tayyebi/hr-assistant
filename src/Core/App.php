<?php
/**
 * Application bootstrap.
 * Wires core framework, resolves tenant, loads plugins.
 */

declare(strict_types=1);

namespace Src\Core;

final class App
{
    private function __construct(
        private readonly Request $request,
        private readonly Response $response,
        private readonly Router $router,
        private readonly Database $db,
        private readonly Session $session,
    ) {
    }

    public static function fromGlobals(): self
    {
        require_once __DIR__ . '/Config.php';
        require_once __DIR__ . '/Request.php';
        require_once __DIR__ . '/Response.php';
        require_once __DIR__ . '/Database.php';
        require_once __DIR__ . '/Session.php';
        require_once __DIR__ . '/Tenant.php';
        require_once __DIR__ . '/Auth.php';
        require_once __DIR__ . '/AuditLog.php';
        require_once __DIR__ . '/View.php';
        require_once __DIR__ . '/PluginInterface.php';
        require_once __DIR__ . '/PluginManager.php';
        require_once __DIR__ . '/Router.php';
        require_once __DIR__ . '/Messaging/Message.php';
        require_once __DIR__ . '/Messaging/ChannelInterface.php';
        require_once __DIR__ . '/Messaging/ChannelManager.php';

        $request = Request::fromGlobals();
        $response = new Response();
        $db = Database::getInstance();
        $session = new Session();

        $tenant = new Tenant($db, $request);
        $tenant->resolve();

        $auth = new Auth($db, $session, $tenant);
        $view = new View($tenant, $auth);
        $router = new Router($request, $response, $tenant, $auth, $view, $db);
        $router->registerCoreRoutes();

        $pluginManager = new PluginManager($db, $router, $tenant);
        $pluginManager->loadAll();
        $GLOBALS['pluginManager'] = $pluginManager;

        return new self($request, $response, $router, $db, $session);
    }

    public function run(): void
    {
        $this->router->dispatch();
    }
}
