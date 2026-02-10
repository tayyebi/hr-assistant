<?php

namespace App\Controllers;

use App\Models\User;
use App\Core\{Database, View};

/**
 * API Controller for external integrations and mobile access
 * Provides RESTful endpoints for all major functionality
 */
class ApiController {
    private $apiKey;
    private $allowedOrigins = ['*']; // Configure as needed
    
    public function __construct() {
        $this->apiKey = $_ENV['API_KEY'] ?? 'hr-assistant-2024';
        
        // Set CORS headers for API access
        $this->setCorsHeaders();
    }
    
    /**
     * Set CORS headers for cross-origin requests
     */
    private function setCorsHeaders() {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');
        header('Content-Type: application/json');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }
    
    /**
     * Authenticate API request
     */
    private function authenticate() {
        $providedKey = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? null;
        
        if (!$providedKey || $providedKey !== $this->apiKey) {
            $this->respondError('Unauthorized - Invalid API key', 401);
        }
        
        // Log API access
        $this->logApiAccess();
    }
    
    /**
     * Log API access for audit trail
     */
    private function logApiAccess() {
        try {
            $data = [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'endpoint' => $_SERVER['REQUEST_URI'] ?? '',
                'method' => $_SERVER['REQUEST_METHOD'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'timestamp' => date('Y-m-d H:i:s'),
                'tenant_id' => $_SESSION['tenant_id'] ?? null
            ];
            
            Database::execute(
                "INSERT INTO api_access_logs (tenant_id, ip_address, endpoint, method, user_agent, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$data['tenant_id'], $data['ip'], $data['endpoint'], $data['method'], $data['user_agent'], $data['timestamp']]
            );
        } catch (Exception $e) {
            // Silent fail for logging - don't break API functionality
            error_log("API access logging failed: " . $e->getMessage());
        }
    }
    
    /**
     * Main API documentation page
     */
    public function index(): void {
        // Show API documentation instead of handling API requests
        $user = User::getCurrentUser();
        
        View::render('api-docs', [
            'user' => $user,
            'activeTab' => 'api'
        ]);
    }
    
    /**
     * API endpoint handler - processes REST API calls
     */
    public function api(): void {
        $this->authenticate();
        
        $method = $_SERVER['REQUEST_METHOD'];
        $path = trim($_GET['path'] ?? '', '/');
        $segments = array_filter(explode('/', $path));
        
        $endpoint = $segments[0] ?? 'status';
        
        switch ($endpoint) {
            case 'status':
                $this->getStatus();
                break;
                
            case 'employees':
                $this->handleEmployees($method, array_slice($segments, 1));
                break;
                
            case 'assets':
                $this->handleAssets($method, array_slice($segments, 1));
                break;
                
            case 'messages':
                $this->handleMessages($method, array_slice($segments, 1));
                break;
                
            case 'notifications':
                $this->handleNotifications($method, array_slice($segments, 1));
                break;
                
            case 'reports':
                $this->handleReports($method, array_slice($segments, 1));
                break;
                
            default:
                $this->respondError("Unknown endpoint: {$endpoint}", 404);
        }
    }
    
    /**
     * API Status endpoint
     */
    private function getStatus() {
        $this->respondSuccess([
            'status' => 'ok',
            'version' => '1.0.0',
            'timestamp' => date('c'),
            'endpoints' => [
                'employees' => '/api/employees',
                'assets' => '/api/assets',
                'messages' => '/api/messages',
                'notifications' => '/api/notifications',
                'reports' => '/api/reports'
            ]
        ]);
    }
    
    /**
     * Handle Employee API endpoints
     */
    private function handleEmployees($method, $segments) {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        
        switch ($method) {
            case 'GET':
                if (empty($segments)) {
                    // List all employees
                    $employees = Database::fetchAll(
                        "SELECT id, name, email, position, department, status, created_at 
                         FROM employees WHERE tenant_id = ? ORDER BY name",
                        [$tenantId]
                    );
                    $this->respondSuccess(['employees' => $employees]);
                } else {
                    // Get specific employee
                    $employee = Database::fetchOne(
                        "SELECT * FROM employees WHERE id = ? AND tenant_id = ?",
                        [$segments[0], $tenantId]
                    );
                    if ($employee) {
                        $this->respondSuccess(['employee' => $employee]);
                    } else {
                        $this->respondError('Employee not found', 404);
                    }
                }
                break;
                
            case 'POST':
                // Create new employee
                $data = json_decode(file_get_contents('php://input'), true);
                $required = ['name', 'email'];
                
                foreach ($required as $field) {
                    if (empty($data[$field])) {
                        $this->respondError("Missing required field: {$field}", 400);
                    }
                }
                
                try {
                    Database::execute(
                        "INSERT INTO employees (tenant_id, name, email, position, department, status) 
                         VALUES (?, ?, ?, ?, ?, ?)",
                        [
                            $tenantId,
                            $data['name'],
                            $data['email'],
                            $data['position'] ?? '',
                            $data['department'] ?? '',
                            $data['status'] ?? 'active'
                        ]
                    );
                    $this->respondSuccess(['message' => 'Employee created successfully'], 201);
                } catch (Exception $e) {
                    $this->respondError('Failed to create employee: ' . $e->getMessage(), 500);
                }
                break;
                
            default:
                $this->respondError('Method not allowed', 405);
        }
    }
    
    /**
     * Handle Asset API endpoints
     */
    private function handleAssets($method, $segments) {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        
        switch ($method) {
            case 'GET':
                $assets = Database::fetchAll(
                    "SELECT a.*, e.name as assignee_name 
                     FROM assets a 
                     LEFT JOIN employees e ON a.assignee_id = e.id 
                     WHERE a.tenant_id = ? 
                     ORDER BY a.created_at DESC",
                    [$tenantId]
                );
                $this->respondSuccess(['assets' => $assets]);
                break;
                
            default:
                $this->respondError('Method not allowed', 405);
        }
    }
    
    /**
     * Handle Message API endpoints
     */
    private function handleMessages($method, $segments) {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        
        switch ($method) {
            case 'GET':
                $messages = Database::fetchAll(
                    "SELECT m.*, e.name as sender_name 
                     FROM messages m 
                     LEFT JOIN employees e ON m.sender_id = e.id 
                     WHERE m.tenant_id = ? 
                     ORDER BY m.created_at DESC LIMIT 50",
                    [$tenantId]
                );
                $this->respondSuccess(['messages' => $messages]);
                break;
                
            case 'POST':
                // Send new message
                $data = json_decode(file_get_contents('php://input'), true);
                
                if (empty($data['content'])) {
                    $this->respondError('Missing message content', 400);
                }
                
                try {
                    Database::execute(
                        "INSERT INTO messages (tenant_id, sender_id, content, channel, created_at) 
                         VALUES (?, ?, ?, ?, NOW())",
                        [
                            $tenantId,
                            $data['sender_id'] ?? null,
                            $data['content'],
                            $data['channel'] ?? 'api'
                        ]
                    );
                    $this->respondSuccess(['message' => 'Message sent successfully'], 201);
                } catch (Exception $e) {
                    $this->respondError('Failed to send message: ' . $e->getMessage(), 500);
                }
                break;
                
            default:
                $this->respondError('Method not allowed', 405);
        }
    }
    
    /**
     * Handle Notification API endpoints
     */
    private function handleNotifications($method, $segments) {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        
        switch ($method) {
            case 'GET':
                $notifications = Database::fetchAll(
                    "SELECT n.*, 
                            CASE WHEN nr.id IS NOT NULL THEN 1 ELSE 0 END as is_read
                     FROM notifications n 
                     LEFT JOIN notification_reads nr ON n.id = nr.notification_id 
                     WHERE n.tenant_id = ? 
                     ORDER BY n.created_at DESC LIMIT 20",
                    [$tenantId]
                );
                $this->respondSuccess(['notifications' => $notifications]);
                break;
                
            default:
                $this->respondError('Method not allowed', 405);
        }
    }
    
    /**
     * Handle Reports API endpoints
     */
    private function handleReports($method, $segments) {
        $tenantId = $_SESSION['tenant_id'] ?? 1;
        
        switch ($method) {
            case 'GET':
                $reportType = $segments[0] ?? 'summary';
                
                switch ($reportType) {
                    case 'summary':
                        $data = $this->generateSummaryReport($tenantId);
                        break;
                    case 'employees':
                        $data = $this->generateEmployeeReport($tenantId);
                        break;
                    default:
                        $this->respondError('Unknown report type', 400);
                        return;
                }
                
                $this->respondSuccess(['report' => $data]);
                break;
                
            default:
                $this->respondError('Method not allowed', 405);
        }
    }
    
    /**
     * Generate summary report data
     */
    private function generateSummaryReport($tenantId) {
        return [
            'employees' => [
                'total' => Database::fetchOne("SELECT COUNT(*) as count FROM employees WHERE tenant_id = ?", [$tenantId])['count'],
                'active' => Database::fetchOne("SELECT COUNT(*) as count FROM employees WHERE tenant_id = ? AND status = 'active'", [$tenantId])['count']
            ],
            'assets' => [
                'total' => Database::fetchOne("SELECT COUNT(*) as count FROM assets WHERE tenant_id = ?", [$tenantId])['count'],
                'assigned' => Database::fetchOne("SELECT COUNT(*) as count FROM assets WHERE tenant_id = ? AND assignee_id IS NOT NULL", [$tenantId])['count']
            ],
            'messages' => [
                'total' => Database::fetchOne("SELECT COUNT(*) as count FROM messages WHERE tenant_id = ?", [$tenantId])['count'],
                'recent' => Database::fetchOne("SELECT COUNT(*) as count FROM messages WHERE tenant_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)", [$tenantId])['count']
            ]
        ];
    }
    
    /**
     * Generate employee report data
     */
    private function generateEmployeeReport($tenantId) {
        return [
            'by_department' => Database::fetchAll(
                "SELECT department, COUNT(*) as count 
                 FROM employees 
                 WHERE tenant_id = ? AND department != '' 
                 GROUP BY department",
                [$tenantId]
            ),
            'by_status' => Database::fetchAll(
                "SELECT status, COUNT(*) as count 
                 FROM employees 
                 WHERE tenant_id = ? 
                 GROUP BY status",
                [$tenantId]
            )
        ];
    }
    
    /**
     * Send successful JSON response
     */
    private function respondSuccess($data = [], $code = 200) {
        http_response_code($code);
        echo json_encode([
            'success' => true,
            'data' => $data,
            'timestamp' => date('c')
        ]);
        exit();
    }
    
    /**
     * Send error JSON response
     */
    private function respondError($message, $code = 400) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message,
            'timestamp' => date('c')
        ]);
        exit();
    }
}