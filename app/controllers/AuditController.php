<?php

namespace App\Controllers;

use App\Models\User;
use App\Core\{Database, View};

/**
 * Audit Controller
 * Comprehensive audit logging implementation
 */
class AuditController
{
    public function index(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $tenant = Tenant::getCurrentTenant();
        $user = User::getCurrentUser();
        
        // Get audit log filters from request
        $filters = [
            'action' => $_GET['action'] ?? '',
            'user_id' => $_GET['user_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? date('Y-m-01'),
            'date_to' => $_GET['date_to'] ?? date('Y-m-d'),
            'limit' => min((int)($_GET['limit'] ?? 50), 100)
        ];
        
        $auditLogs = $this->getAuditLogs($tenantId, $filters);
        $activitySummary = $this->getActivitySummary($tenantId);
        
        View::render('audit', [
            'tenant' => $tenant,
            'user' => $user,
            'activeTab' => 'audit',
            'auditLogs' => $auditLogs,
            'activitySummary' => $activitySummary,
            'filters' => $filters
        ]);
    }
    
    public function search(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $query = $_GET['q'] ?? '';
        $filters = [
            'action' => $_GET['action'] ?? '',
            'user_id' => $_GET['user_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'limit' => min((int)($_GET['limit'] ?? 50), 100)
        ];
        
        $results = $this->searchAuditLogs($tenantId, $query, $filters);
        
        header('Content-Type: application/json');
        echo json_encode($results);
    }
    
    public function export(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $format = $_GET['format'] ?? 'csv';
        $filters = [
            'action' => $_GET['action'] ?? '',
            'user_id' => $_GET['user_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? date('Y-m-01'),
            'date_to' => $_GET['date_to'] ?? date('Y-m-d')
        ];
        
        $auditLogs = $this->getAuditLogs($tenantId, $filters, false); // No limit for export
        
        switch ($format) {
            case 'csv':
                $this->exportToCSV($auditLogs);
                break;
            case 'json':
                $this->exportToJSON($auditLogs);
                break;
            default:
                header('HTTP/1.1 400 Bad Request');
                echo 'Unsupported format';
        }
    }
    
    public function report(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $reportType = $_GET['type'] ?? 'compliance';
        $dateFrom = $_GET['date_from'] ?? date('Y-m-01');
        $dateTo = $_GET['date_to'] ?? date('Y-m-d');
        
        $report = $this->generateComplianceReport($tenantId, $reportType, $dateFrom, $dateTo);
        
        header('Content-Type: application/json');
        echo json_encode($report);
    }
    
    public static function logActivity(string $tenantId, string $userId, string $action, array $details = []): void
    {
        $db = Database::getInstance();
        
        $sql = "
            INSERT INTO audit_logs (tenant_id, user_id, action, details, ip_address, user_agent, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ";
        
        $db->execute($sql, [
            $tenantId,
            $userId,
            $action,
            json_encode($details),
            $_SERVER['REMOTE_ADDR'] ?? '',
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
    
    private function getAuditLogs(string $tenantId, array $filters, bool $withLimit = true): array
    {
        $db = Database::getInstance();
        
        $sql = "
            SELECT al.*, u.name as user_name, u.email as user_email
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE al.tenant_id = ?
        ";
        
        $params = [$tenantId];
        
        if (!empty($filters['action'])) {
            $sql .= " AND al.action LIKE ?";
            $params[] = '%' . $filters['action'] . '%';
        }
        
        if (!empty($filters['user_id'])) {
            $sql .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND al.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND al.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        $sql .= " ORDER BY al.created_at DESC";
        
        if ($withLimit && isset($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }
        
        $result = $db->query($sql, $params);
        return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    
    private function getActivitySummary(string $tenantId): array
    {
        $db = Database::getInstance();
        
        $sql = "
            SELECT 
                COUNT(*) as total_activities,
                COUNT(CASE WHEN created_at >= CURDATE() THEN 1 END) as today_activities,
                COUNT(CASE WHEN created_at >= CURDATE() - INTERVAL 7 DAY THEN 1 END) as week_activities,
                COUNT(DISTINCT user_id) as active_users
            FROM audit_logs 
            WHERE tenant_id = ?
        ";
        
        $result = $db->query($sql, [$tenantId]);
        return $result ? $result->fetch(PDO::FETCH_ASSOC) : [];
    }
    
    private function searchAuditLogs(string $tenantId, string $query, array $filters): array
    {
        $db = Database::getInstance();
        
        $sql = "
            SELECT al.*, u.name as user_name, u.email as user_email
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE al.tenant_id = ? AND (
                al.action LIKE ? OR 
                al.details LIKE ? OR 
                u.name LIKE ? OR 
                u.email LIKE ?
            )
        ";
        
        $searchTerm = '%' . $query . '%';
        $params = [$tenantId, $searchTerm, $searchTerm, $searchTerm, $searchTerm];
        
        // Apply additional filters
        if (!empty($filters['date_from'])) {
            $sql .= " AND al.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND al.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        
        $sql .= " ORDER BY al.created_at DESC LIMIT " . (int)($filters['limit'] ?? 50);
        
        $result = $db->query($sql, $params);
        return $result ? $result->fetchAll(PDO::FETCH_ASSOC) : [];
    }
    
    private function generateComplianceReport(string $tenantId, string $type, string $dateFrom, string $dateTo): array
    {
        $db = Database::getInstance();
        
        // Get activity breakdown
        $sql = "
            SELECT 
                action,
                COUNT(*) as count,
                COUNT(DISTINCT user_id) as unique_users
            FROM audit_logs 
            WHERE tenant_id = ? AND created_at BETWEEN ? AND ?
            GROUP BY action
            ORDER BY count DESC
        ";
        
        $result = $db->query($sql, [$tenantId, $dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);
        $activityBreakdown = $result ? $result->fetchAll(PDO::FETCH_ASSOC) : [];
        
        return [
            'type' => $type,
            'period' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ],
            'activity_breakdown' => $activityBreakdown,
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }
    
    private function exportToCSV(array $auditLogs): void
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="audit_log_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Date', 'User', 'Action', 'Details', 'IP Address']);
        
        foreach ($auditLogs as $log) {
            fputcsv($output, [
                $log['created_at'],
                $log['user_name'] ?? 'System',
                $log['action'],
                $log['details'],
                $log['ip_address']
            ]);
        }
        
        fclose($output);
    }
    
    private function exportToJSON(array $auditLogs): void
    {
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="audit_log_' . date('Y-m-d') . '.json"');
        
        echo json_encode([
            'export_date' => date('Y-m-d H:i:s'),
            'total_records' => count($auditLogs),
            'audit_logs' => $auditLogs
        ], JSON_PRETTY_PRINT);
    }
}