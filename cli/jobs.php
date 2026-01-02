<?php
/**
 * CLI Jobs Script - Background Job Processing
 * 
 * Processes pending jobs including:
 * - Message delivery (Email, Telegram) with retry
 * - Third-party service sync
 * 
 * Usage:
 *   php jobs.php process [tenant]  - Process pending jobs
 *   php jobs.php retry [tenant]    - Retry failed jobs
 *   php jobs.php list [tenant]     - List all jobs
 *   php jobs.php stats [tenant]    - Show job statistics
 * 
 * Environment Variables:
 *   SIMULATE_MODE=1  - Enable simulation mode with random failures (for testing)
 */

// Bootstrap application
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Employee.php';
require_once __DIR__ . '/../app/models/Tenant.php';
require_once __DIR__ . '/../app/models/Config.php';
require_once __DIR__ . '/../app/models/Job.php';
require_once __DIR__ . '/../app/models/Message.php';

// Ensure CLI context
if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

// Check if simulation mode is enabled (for testing)
define('SIMULATE_MODE', getenv('SIMULATE_MODE') === '1');

/**
 * Job Processor - Handles actual job execution
 */
class JobProcessor
{
    private array $config;
    private string $tenantId;
    private int $maxRetries = 3;
    
    public function __construct(string $tenantId)
    {
        $this->tenantId = $tenantId;
        $this->config = Config::get($tenantId);
    }
    
    /**
     * Process a single job
     */
    public function process(array $job): array
    {
        $service = $job['service'];
        $action = $job['action'];
        
        echo "  Processing job {$job['id']}: {$service}/{$action}\n";
        
        // Update status to processing
        Job::update($this->tenantId, $job['id'], ['status' => Job::STATUS_PROCESSING]);
        
        try {
            $result = match ($service) {
                'email' => $this->processEmail($job),
                'telegram' => $this->processTelegram($job),
                'mailcow' => $this->processMailcow($job),
                'gitlab' => $this->processGitlab($job),
                default => throw new Exception("Unknown service: {$service}")
            };
            
            Job::update($this->tenantId, $job['id'], [
                'status' => Job::STATUS_COMPLETED,
                'result' => json_encode($result)
            ]);
            
            echo "    [SUCCESS] {$job['id']}\n";
            return ['success' => true, 'result' => $result];
            
        } catch (Exception $e) {
            $metadata = $job['metadata'] ?? [];
            $retryCount = ($metadata['retry_count'] ?? 0) + 1;
            
            if ($retryCount < $this->maxRetries) {
                // Schedule for retry
                $metadata['retry_count'] = $retryCount;
                $metadata['last_error'] = $e->getMessage();
                
                Job::update($this->tenantId, $job['id'], [
                    'status' => Job::STATUS_PENDING,
                    'result' => "Retry {$retryCount}/{$this->maxRetries}: " . $e->getMessage(),
                    'metadata' => $metadata
                ]);
                
                echo "    [RETRY] {$job['id']} - attempt {$retryCount}/{$this->maxRetries}\n";
            } else {
                // Max retries exceeded
                Job::update($this->tenantId, $job['id'], [
                    'status' => Job::STATUS_FAILED,
                    'result' => "Failed after {$this->maxRetries} attempts: " . $e->getMessage()
                ]);
                
                echo "    [FAILED] {$job['id']} - max retries exceeded\n";
            }
            
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Process email delivery job
     */
    private function processEmail(array $job): array
    {
        $metadata = $job['metadata'] ?? [];
        $to = $metadata['to'] ?? '';
        $subject = $metadata['subject'] ?? '';
        $body = $metadata['body'] ?? '';
        
        if (empty($to)) {
            throw new Exception("No recipient specified");
        }
        
        // Check SMTP configuration
        $smtpHost = $this->config['smtp_host'] ?? '';
        $smtpPort = $this->config['smtp_port'] ?? '465';
        $smtpUser = $this->config['smtp_user'] ?? '';
        $smtpPass = $this->config['smtp_pass'] ?? '';
        
        if (empty($smtpHost) || empty($smtpUser) || empty($smtpPass)) {
            throw new Exception("SMTP not configured");
        }
        
        // In production, this would use PHPMailer or similar
        // Simulation mode for testing retry functionality
        if (SIMULATE_MODE && mt_rand(1, 10) > 8) { // 20% failure rate in simulation mode
            throw new Exception("SMTP connection timeout (simulated)");
        }
        
        // TODO: Implement actual email sending with PHPMailer
        // For now, mark as sent (assumes SMTP config is valid)
        
        return [
            'sent' => true,
            'to' => $to,
            'subject' => $subject,
            'timestamp' => date('c')
        ];
    }
    
    /**
     * Process Telegram message delivery job
     */
    private function processTelegram(array $job): array
    {
        $metadata = $job['metadata'] ?? [];
        $chatId = $metadata['chat_id'] ?? '';
        $text = $metadata['text'] ?? '';
        
        if (empty($chatId)) {
            throw new Exception("No chat_id specified");
        }
        
        $botToken = $this->config['telegram_bot_token'] ?? '';
        
        if (empty($botToken)) {
            throw new Exception("Telegram bot not configured");
        }
        
        // In production, this would call the Telegram Bot API
        // POST https://api.telegram.org/bot{token}/sendMessage
        
        // Simulation mode for testing retry functionality
        if (SIMULATE_MODE && mt_rand(1, 10) > 9) { // 10% failure rate in simulation mode
            throw new Exception("Telegram API error (simulated)");
        }
        
        // TODO: Implement actual Telegram API call
        // For now, mark as sent (assumes bot token is valid)
        
        return [
            'sent' => true,
            'chat_id' => $chatId,
            'message_id' => mt_rand(10000, 99999),
            'timestamp' => date('c')
        ];
    }
    
    /**
     * Process Mailcow API job
     */
    private function processMailcow(array $job): array
    {
        $action = $job['action'];
        $metadata = $job['metadata'] ?? [];
        
        $mailcowUrl = $this->config['mailcow_url'] ?? '';
        $apiKey = $this->config['mailcow_api_key'] ?? '';
        
        if (empty($mailcowUrl) || empty($apiKey)) {
            throw new Exception("Mailcow not configured");
        }
        
        return match ($action) {
            'create_mailbox' => $this->mailcowCreateMailbox($job['target_name'], $metadata),
            'deactivate_mailbox' => $this->mailcowDeactivateMailbox($job['target_name']),
            default => throw new Exception("Unknown Mailcow action: {$action}")
        };
    }
    
    private function mailcowCreateMailbox(string $email, array $metadata): array
    {
        // In production: POST {mailcow_url}/api/v1/add/mailbox
        return ['created' => true, 'email' => $email];
    }
    
    private function mailcowDeactivateMailbox(string $email): array
    {
        // In production: POST {mailcow_url}/api/v1/edit/mailbox
        // Note: We deactivate, not delete (soft-delete policy)
        return ['deactivated' => true, 'email' => $email];
    }
    
    /**
     * Process GitLab API job
     */
    private function processGitlab(array $job): array
    {
        $action = $job['action'];
        $metadata = $job['metadata'] ?? [];
        
        $gitlabUrl = $this->config['gitlab_url'] ?? '';
        $token = $this->config['gitlab_token'] ?? '';
        
        if (empty($gitlabUrl) || empty($token)) {
            throw new Exception("GitLab not configured");
        }
        
        return match ($action) {
            'create_user' => $this->gitlabCreateUser($job['target_name'], $metadata),
            'block_user' => $this->gitlabBlockUser($job['target_name']),
            default => throw new Exception("Unknown GitLab action: {$action}")
        };
    }
    
    private function gitlabCreateUser(string $email, array $metadata): array
    {
        // In production: POST {gitlab_url}/api/v4/users
        return ['created' => true, 'email' => $email];
    }
    
    private function gitlabBlockUser(string $email): array
    {
        // In production: POST {gitlab_url}/api/v4/users/{id}/block
        // Note: We block, not delete (soft-delete policy)
        return ['blocked' => true, 'email' => $email];
    }
}

/**
 * Message Delivery Service
 * Creates jobs for sending messages via email and telegram
 */
class MessageDeliveryService
{
    /**
     * Send a direct message to an employee
     * Creates jobs for both email and telegram if configured
     */
    public static function send(string $tenantId, string $employeeId, string $subject, string $body): array
    {
        $employee = Employee::find($tenantId, $employeeId);
        
        if (!$employee) {
            throw new Exception("Employee not found: {$employeeId}");
        }
        
        $jobs = [];
        
        // Create email job if employee has email
        if (!empty($employee['email'])) {
            $jobs[] = Job::create($tenantId, [
                'service' => 'email',
                'action' => 'send',
                'target_name' => $employee['email'],
                'metadata' => [
                    'to' => $employee['email'],
                    'subject' => $subject,
                    'body' => $body,
                    'employee_id' => $employeeId
                ]
            ]);
        }
        
        // Create telegram job if employee has telegram
        if (!empty($employee['telegram_chat_id'])) {
            $jobs[] = Job::create($tenantId, [
                'service' => 'telegram',
                'action' => 'send',
                'target_name' => "Chat:{$employee['telegram_chat_id']}",
                'metadata' => [
                    'chat_id' => $employee['telegram_chat_id'],
                    'text' => "{$subject}\n\n{$body}",
                    'employee_id' => $employeeId
                ]
            ]);
        }
        
        // Log the message in the conversation
        Message::create($tenantId, [
            'employee_id' => $employeeId,
            'sender' => 'hr',
            'channel' => 'multiple',
            'text' => $body,
            'subject' => $subject
        ]);
        
        return $jobs;
    }
    
    /**
     * Retry a specific message delivery
     * Creates new jobs for failed deliveries
     */
    public static function retry(string $tenantId, string $jobId): ?array
    {
        $job = Job::find($tenantId, $jobId);
        
        if (!$job) {
            return null;
        }
        
        // Reset retry count and requeue
        $metadata = $job['metadata'] ?? [];
        $metadata['retry_count'] = 0;
        $metadata['manual_retry'] = true;
        
        Job::update($tenantId, $jobId, [
            'status' => Job::STATUS_PENDING,
            'metadata' => $metadata
        ]);
        
        return $job;
    }
}

/**
 * Main job runner
 */
function processPendingJobs(string $tenantFilter = 'all'): void
{
    $tenants = Tenant::getAll();
    
    foreach ($tenants as $tenant) {
        $tenantId = $tenant['id'];
        
        if ($tenantFilter !== 'all' && $tenantId !== $tenantFilter) {
            continue;
        }
        
        echo "\n=== Processing jobs for: {$tenant['name']} ===\n";
        
        $processor = new JobProcessor($tenantId);
        $jobs = Job::getAll($tenantId);
        
        $pending = array_filter($jobs, fn($j) => $j['status'] === Job::STATUS_PENDING);
        
        echo "Found " . count($pending) . " pending job(s)\n";
        
        foreach ($pending as $job) {
            $processor->process($job);
        }
    }
}

/**
 * Retry failed jobs
 */
function retryFailedJobs(string $tenantFilter = 'all'): void
{
    $tenants = Tenant::getAll();
    
    foreach ($tenants as $tenant) {
        $tenantId = $tenant['id'];
        
        if ($tenantFilter !== 'all' && $tenantId !== $tenantFilter) {
            continue;
        }
        
        echo "\n=== Retrying failed jobs for: {$tenant['name']} ===\n";
        
        $jobs = Job::getAll($tenantId);
        $failed = array_filter($jobs, fn($j) => $j['status'] === Job::STATUS_FAILED);
        
        echo "Found " . count($failed) . " failed job(s)\n";
        
        foreach ($failed as $job) {
            MessageDeliveryService::retry($tenantId, $job['id']);
            echo "  Requeued: {$job['id']}\n";
        }
    }
    
    // Process the requeued jobs
    processPendingJobs($tenantFilter);
}

/**
 * List all jobs
 */
function listJobs(string $tenantFilter = 'all'): void
{
    $tenants = Tenant::getAll();
    
    foreach ($tenants as $tenant) {
        $tenantId = $tenant['id'];
        
        if ($tenantFilter !== 'all' && $tenantId !== $tenantFilter) {
            continue;
        }
        
        echo "\n=== Jobs for: {$tenant['name']} ===\n";
        
        $jobs = Job::getAll($tenantId);
        
        if (empty($jobs)) {
            echo "No jobs found.\n";
            continue;
        }
        
        printf("%-30s %-15s %-15s %-20s %s\n", 'ID', 'Service', 'Status', 'Target', 'Created');
        printf("%s\n", str_repeat('-', 100));
        
        foreach ($jobs as $job) {
            printf("%-30s %-15s %-15s %-20s %s\n",
                substr($job['id'], 0, 28),
                $job['service'],
                $job['status'],
                substr($job['target_name'], 0, 18),
                $job['created_at']
            );
        }
    }
}

/**
 * Show job statistics
 */
function showStats(string $tenantFilter = 'all'): void
{
    $tenants = Tenant::getAll();
    
    foreach ($tenants as $tenant) {
        $tenantId = $tenant['id'];
        
        if ($tenantFilter !== 'all' && $tenantId !== $tenantFilter) {
            continue;
        }
        
        echo "\n=== Statistics for: {$tenant['name']} ===\n";
        
        $jobs = Job::getAll($tenantId);
        
        $stats = [
            Job::STATUS_PENDING => 0,
            Job::STATUS_PROCESSING => 0,
            Job::STATUS_COMPLETED => 0,
            Job::STATUS_FAILED => 0
        ];
        
        $byService = [];
        
        foreach ($jobs as $job) {
            $stats[$job['status']]++;
            $byService[$job['service']] = ($byService[$job['service']] ?? 0) + 1;
        }
        
        echo "\nBy Status:\n";
        foreach ($stats as $status => $count) {
            echo "  {$status}: {$count}\n";
        }
        
        echo "\nBy Service:\n";
        foreach ($byService as $service => $count) {
            echo "  {$service}: {$count}\n";
        }
        
        echo "\nTotal: " . count($jobs) . "\n";
    }
}

// Main execution
$command = $argv[1] ?? 'process';
$tenant = $argv[2] ?? 'all';

switch ($command) {
    case 'process':
        processPendingJobs($tenant);
        break;
        
    case 'retry':
        retryFailedJobs($tenant);
        break;
        
    case 'list':
        listJobs($tenant);
        break;
        
    case 'stats':
        showStats($tenant);
        break;
        
    default:
        echo "Usage: php jobs.php <process|retry|list|stats> [tenant]\n";
        exit(1);
}
