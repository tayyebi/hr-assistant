<?php
/**
 * Notification Controller
 * Real-time notification system implementation
 */
class NotificationController
{
    public function index(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $tenant = Tenant::getCurrentTenant();
        $user = User::getCurrentUser();
        
        // Get notifications for current tenant
        $notifications = $this->getNotifications($tenantId, $user['id']);
        $unreadCount = $this->getUnreadCount($tenantId, $user['id']);
        
        View::render('notifications', [
            'tenant' => $tenant,
            'user' => $user,
            'activeTab' => 'notifications',
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }
    
    public function create(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $user = User::getCurrentUser();
        
        $data = [
            'tenant_id' => $tenantId,
            'from_user_id' => $user['id'],
            'title' => $_POST['title'] ?? '',
            'message' => $_POST['message'] ?? '',
            'type' => $_POST['type'] ?? 'announcement',
            'priority' => $_POST['priority'] ?? 'normal',
            'target_audience' => $_POST['target_audience'] ?? 'all'
        ];
        
        $this->createNotification($data);
        
        $_SESSION['flash_message'] = 'Notification sent successfully!';
        View::redirect(View::workspaceUrl('/notifications/'));
    }
    
    public function markRead(): void
    {
        AuthController::requireTenantAdmin();
        
        $notificationId = $_POST['notification_id'] ?? '';
        $userId = User::getCurrentUser()['id'];
        
        if ($notificationId) {
            $this->markAsRead($notificationId, $userId);
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }
    
    public function getUnread(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $userId = User::getCurrentUser()['id'];
        
        $notifications = $this->getUnreadNotifications($tenantId, $userId);
        $count = count($notifications);
        
        header('Content-Type: application/json');
        echo json_encode([
            'count' => $count,
            'notifications' => $notifications
        ]);
    }
    
    private function getNotifications(string $tenantId, string $userId): array
    {
        $db = Database::getInstance();
        
        $sql = "
            SELECT n.*, u.name as from_user_name,
                   nr.read_at, nr.read_at IS NOT NULL as is_read
            FROM notifications n 
            LEFT JOIN users u ON n.from_user_id = u.id
            LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
            WHERE n.tenant_id = ? 
            ORDER BY n.created_at DESC 
            LIMIT 50
        ";
        
        $result = $db->query($sql, [$userId, $tenantId]);
        return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    
    private function getUnreadNotifications(string $tenantId, string $userId): array
    {
        $db = Database::getInstance();
        
        $sql = "
            SELECT n.*, u.name as from_user_name
            FROM notifications n 
            LEFT JOIN users u ON n.from_user_id = u.id
            LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
            WHERE n.tenant_id = ? AND nr.read_at IS NULL
            ORDER BY n.created_at DESC
        ";
        
        $result = $db->query($sql, [$userId, $tenantId]);
        return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    
    private function getUnreadCount(string $tenantId, string $userId): int
    {
        $db = Database::getInstance();
        
        $sql = "
            SELECT COUNT(*) as count
            FROM notifications n 
            LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.user_id = ?
            WHERE n.tenant_id = ? AND nr.read_at IS NULL
        ";
        
        $result = $db->query($sql, [$userId, $tenantId]);
        $row = $result ? $result->fetch(PDO::FETCH_ASSOC) : null;
        return $row ? (int)$row['count'] : 0;
    }
    
    private function createNotification(array $data): void
    {
        $db = Database::getInstance();
        
        $sql = "
            INSERT INTO notifications (tenant_id, from_user_id, title, message, type, priority, target_audience, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ";
        
        $db->execute($sql, [
            $data['tenant_id'],
            $data['from_user_id'],
            $data['title'],
            $data['message'],
            $data['type'],
            $data['priority'],
            $data['target_audience']
        ]);
    }
    
    private function markAsRead(string $notificationId, string $userId): void
    {
        $db = Database::getInstance();
        
        $sql = "
            INSERT INTO notification_reads (notification_id, user_id, read_at)
            VALUES (?, ?, NOW())
            ON DUPLICATE KEY UPDATE read_at = NOW()
        ";
        
        $db->execute($sql, [$notificationId, $userId]);
    }
}