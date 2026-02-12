<?php
/**
 * Calendar plugin.
 * Workspace events, holidays, birthdays, meetings.
 * Month-view rendered server-side (no JS).
 */

declare(strict_types=1);

namespace Src\Plugins\Calendar;

use Src\Core\AuditLog;
use Src\Core\Database;
use Src\Core\PluginInterface;
use Src\Core\Router;
use Src\Core\Tenant;

final class Plugin implements PluginInterface
{
    public function name(): string { return 'Calendar'; }
    public function requires(): array { return ['Core']; }
    public function sidebarItem(): ?array { return ['label' => 'Calendar', 'icon' => 'calendar', 'route' => '/calendar']; }

    public function register(Router $router, Tenant $tenant, Database $db): void
    {
        /* ---- Month view ---- */
        $router->get('/calendar', function () use ($router, $db): void {
            $router->auth()->requireLogin($router->response());
            $tid = $router->tenant()->id();
            $year = (int)($_GET['year'] ?? date('Y'));
            $month = (int)($_GET['month'] ?? date('n'));
            if ($month < 1) { $month = 12; $year--; }
            if ($month > 12) { $month = 1; $year++; }
            $firstDay = sprintf('%04d-%02d-01', $year, $month);
            $lastDay = date('Y-m-t', strtotime($firstDay));
            $events = $db->fetchAll(
                'SELECT * FROM calendar_events WHERE tenant_id = ? AND start_at <= ? AND end_at >= ? ORDER BY start_at',
                [$tid, $lastDay . ' 23:59:59', $firstDay . ' 00:00:00'],
            );
            $isHR = $router->auth()->hasRole('workspace_admin') || $router->auth()->hasRole('hr_specialist');
            $router->response()->html($router->view()->render('plugins/calendar/index', [
                'title' => 'Calendar', 'layout' => 'app',
                'events' => $events, 'year' => $year, 'month' => $month, 'isHR' => $isHR,
            ]));
        });

        /* ---- Create event ---- */
        $router->post('/calendar/event', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $db->query(
                'INSERT INTO calendar_events (tenant_id, title, description, location, start_at, end_at, all_day, type, color, created_by, employee_id, is_public) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)',
                [
                    $tid, trim((string)($_POST['title'] ?? '')), trim((string)($_POST['description'] ?? '')),
                    trim((string)($_POST['location'] ?? '')),
                    trim((string)($_POST['start_at'] ?? '')), trim((string)($_POST['end_at'] ?? '')),
                    isset($_POST['all_day']) ? 1 : 0, trim((string)($_POST['type'] ?? 'event')),
                    trim((string)($_POST['color'] ?? '#3498db')), $router->auth()->userId(),
                    ($_POST['employee_id'] ?? '') !== '' ? (int)$_POST['employee_id'] : null,
                    isset($_POST['is_public']) ? 1 : 0,
                ],
            );
            AuditLog::record('calendar.event.created');
            $router->response()->redirect($router->tenant()->pathPrefix() . '/calendar');
        });

        /* ---- Delete event ---- */
        $router->post('/calendar/event/{id}/delete', function (array $p) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $db->query('DELETE FROM calendar_events WHERE id = ? AND tenant_id = ?', [(int)$p['id'], $router->tenant()->id()]);
            AuditLog::record('calendar.event.deleted', 'calendar_event', (int)$p['id']);
            $router->response()->redirect($router->tenant()->pathPrefix() . '/calendar');
        });

        $router->get('/calendar/settings', function () use ($router): void {
            $router->response()->redirect($router->tenant()->pathPrefix() . '/calendar');
        });
    }
}
