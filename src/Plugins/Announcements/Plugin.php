<?php
/**
 * Announcements plugin.
 * Tenant-wide broadcasts with read tracking, pinning, priority.
 */

declare(strict_types=1);

namespace Src\Plugins\Announcements;

use Src\Core\AuditLog;
use Src\Core\Database;
use Src\Core\PluginInterface;
use Src\Core\Router;
use Src\Core\Tenant;

final class Plugin implements PluginInterface
{
    public function name(): string { return 'Announcements'; }
    public function requires(): array { return ['Core']; }
    public function sidebarItem(): ?array { return ['label' => 'Announcements', 'icon' => 'megaphone', 'route' => '/announcements']; }

    public function register(Router $router, Tenant $tenant, Database $db): void
    {
        /* ---- List announcements ---- */
        $router->get('/announcements', function () use ($router, $db): void {
            $router->auth()->requireLogin($router->response());
            $tid = $router->tenant()->id();
            $userId = $router->auth()->userId();
            $announcements = $db->fetchAll(
                'SELECT a.*, u.username AS author, (SELECT COUNT(*) FROM announcement_reads ar WHERE ar.announcement_id = a.id) AS read_count, (SELECT 1 FROM announcement_reads ar2 WHERE ar2.announcement_id = a.id AND ar2.user_id = ?) AS is_read FROM announcements a LEFT JOIN users u ON u.id = a.created_by WHERE a.tenant_id = ? AND (a.published_at IS NULL OR a.published_at <= NOW()) AND (a.expires_at IS NULL OR a.expires_at >= NOW()) ORDER BY a.is_pinned DESC, a.created_at DESC',
                [$userId, $tid],
            );
            $isHR = $router->auth()->hasRole('workspace_admin') || $router->auth()->hasRole('hr_specialist');
            $router->response()->html($router->view()->render('plugins/announcements/index', [
                'title' => 'Announcements', 'layout' => 'app',
                'announcements' => $announcements, 'isHR' => $isHR,
            ]));
        });

        /* ---- View single ---- */
        $router->get('/announcements/{id}', function (array $p) use ($router, $db): void {
            $router->auth()->requireLogin($router->response());
            $tid = $router->tenant()->id();
            $ann = $db->fetchOne(
                'SELECT a.*, u.username AS author FROM announcements a LEFT JOIN users u ON u.id = a.created_by WHERE a.id = ? AND a.tenant_id = ?',
                [(int)$p['id'], $tid],
            );
            if (!$ann) { $router->response()->status(404)->html('<h1>Not found</h1>'); return; }
            /* mark read */
            $db->query('INSERT IGNORE INTO announcement_reads (announcement_id, user_id) VALUES (?,?)', [(int)$p['id'], $router->auth()->userId()]);
            $router->response()->html($router->view()->render('plugins/announcements/view', [
                'title' => $ann['title'], 'layout' => 'app', 'announcement' => $ann,
            ]));
        });

        /* ---- Create ---- */
        $router->post('/announcements', function () use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin', 'hr_specialist')) { return; }
            $tid = $router->tenant()->id();
            $publishedAt = trim((string)($_POST['published_at'] ?? ''));
            $expiresAt = trim((string)($_POST['expires_at'] ?? ''));
            $db->query(
                'INSERT INTO announcements (tenant_id, title, body, priority, published_at, expires_at, created_by, is_pinned) VALUES (?,?,?,?,?,?,?,?)',
                [
                    $tid, trim((string)($_POST['title'] ?? '')), trim((string)($_POST['body'] ?? '')),
                    trim((string)($_POST['priority'] ?? 'normal')),
                    $publishedAt ?: date('Y-m-d H:i:s'), $expiresAt ?: null,
                    $router->auth()->userId(), isset($_POST['is_pinned']) ? 1 : 0,
                ],
            );
            AuditLog::record('announcement.created', 'announcement', (int)$db->pdo()->lastInsertId());
            $router->response()->redirect($router->tenant()->pathPrefix() . '/announcements');
        });

        /* ---- Delete ---- */
        $router->post('/announcements/{id}/delete', function (array $p) use ($router, $db): void {
            if (!$router->auth()->requireRole($router->response(), 'workspace_admin')) { return; }
            $db->query('DELETE FROM announcements WHERE id = ? AND tenant_id = ?', [(int)$p['id'], $router->tenant()->id()]);
            AuditLog::record('announcement.deleted', 'announcement', (int)$p['id']);
            $router->response()->redirect($router->tenant()->pathPrefix() . '/announcements');
        });

        $router->get('/announcements/settings', function () use ($router): void {
            $router->response()->redirect($router->tenant()->pathPrefix() . '/announcements');
        });
    }
}
