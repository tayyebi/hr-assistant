<?php
/**
 * CLI Sync Script - Third-party Service Synchronization
 * 
 * Provides diff functionality to find orphan data between HR system 
 * and external services (mailcow, gitlab, telegram).
 * 
 * Usage:
 *   php sync.php diff [service]              - Show differences
 *   php sync.php push [service] [--dry-run]  - Push local changes
 *   php sync.php pull [service] [--dry-run]  - Pull external changes
 * 
 * Services: mailcow, gitlab, telegram, all
 */

// Bootstrap application
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/core/ExcelStorage.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Employee.php';
require_once __DIR__ . '/../app/models/Tenant.php';
require_once __DIR__ . '/../app/models/Config.php';
require_once __DIR__ . '/../app/models/Job.php';

// Ensure CLI context
if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line.\n");
}

/**
 * Service Sync Base Interface
 */
interface SyncService
{
    public function getName(): string;
    public function getLocalEntities(string $tenantId): array;
    public function getRemoteEntities(string $tenantId, array $config): array;
    public function diff(array $local, array $remote): array;
    public function push(string $tenantId, array $diff, array $config, bool $dryRun): array;
    public function pull(string $tenantId, array $diff, array $config, bool $dryRun): array;
}

/**
 * Mailcow Sync Service
 * Syncs email accounts with mailcow mail server
 */
class MailcowSyncService implements SyncService
{
    public function getName(): string
    {
        return 'mailcow';
    }
    
    public function getLocalEntities(string $tenantId): array
    {
        $employees = Employee::getAll($tenantId);
        $entities = [];
        
        foreach ($employees as $emp) {
            if (!empty($emp['email'])) {
                $entities[$emp['email']] = [
                    'id' => $emp['id'],
                    'email' => $emp['email'],
                    'name' => $emp['full_name'],
                    'active' => true, // All local employees are considered active
                    'source' => 'local'
                ];
            }
        }
        
        return $entities;
    }
    
    public function getRemoteEntities(string $tenantId, array $config): array
    {
        $entities = [];
        
        // In production, this would call the Mailcow API
        // GET {mailcow_url}/api/v1/get/mailbox/all
        
        $mailcowUrl = $config['mailcow_url'] ?? '';
        $apiKey = $config['mailcow_api_key'] ?? '';
        
        if (empty($mailcowUrl) || empty($apiKey)) {
            echo "  [WARN] Mailcow not configured - skipping remote fetch\n";
            return [];
        }
        
        // Simulated API call (would be real in production)
        // $response = $this->apiCall($mailcowUrl . '/api/v1/get/mailbox/all', $apiKey);
        
        // For demo, return empty (no mock data)
        return $entities;
    }
    
    public function diff(array $local, array $remote): array
    {
        $diff = [
            'only_local' => [],    // Exists in HR, not in mailcow (orphan remote)
            'only_remote' => [],   // Exists in mailcow, not in HR (orphan local)
            'both' => [],          // Exists in both
            'conflicts' => []      // Different data between systems
        ];
        
        // Find entities only in local
        foreach ($local as $email => $entity) {
            if (!isset($remote[$email])) {
                $diff['only_local'][$email] = $entity;
            } else {
                // Check for conflicts
                if ($entity['name'] !== ($remote[$email]['name'] ?? '')) {
                    $diff['conflicts'][$email] = [
                        'local' => $entity,
                        'remote' => $remote[$email]
                    ];
                } else {
                    $diff['both'][$email] = $entity;
                }
            }
        }
        
        // Find entities only in remote
        foreach ($remote as $email => $entity) {
            if (!isset($local[$email])) {
                $diff['only_remote'][$email] = $entity;
            }
        }
        
        return $diff;
    }
    
    public function push(string $tenantId, array $diff, array $config, bool $dryRun): array
    {
        $results = ['created' => 0, 'deactivated' => 0, 'errors' => []];
        
        // Create mailboxes for local-only entities
        foreach ($diff['only_local'] as $email => $entity) {
            if ($dryRun) {
                echo "  [DRY-RUN] Would create mailbox: {$email}\n";
            } else {
                // Would call Mailcow API to create mailbox
                // Note: We don't delete, we deactivate (soft-delete policy)
                echo "  [CREATE] Creating mailbox: {$email}\n";
                
                // Create job for async processing
                Job::create($tenantId, [
                    'service' => 'mailcow',
                    'action' => 'create_mailbox',
                    'target_name' => $email,
                    'metadata' => ['name' => $entity['name']]
                ]);
            }
            $results['created']++;
        }
        
        // We don't delete remote orphans - just report them
        // User must manually handle orphan accounts in third-party systems
        if (!empty($diff['only_remote'])) {
            echo "  [INFO] Found " . count($diff['only_remote']) . " orphan mailbox(es) in Mailcow:\n";
            foreach ($diff['only_remote'] as $email => $entity) {
                echo "    - {$email} (not in HR system, consider deactivating in Mailcow)\n";
            }
        }
        
        return $results;
    }
    
    public function pull(string $tenantId, array $diff, array $config, bool $dryRun): array
    {
        $results = ['imported' => 0, 'errors' => []];
        
        // Import remote-only entities into HR (deactivated/pending review)
        foreach ($diff['only_remote'] as $email => $entity) {
            if ($dryRun) {
                echo "  [DRY-RUN] Would flag for review: {$email}\n";
            } else {
                echo "  [INFO] Remote orphan: {$email} - manual import required\n";
            }
        }
        
        return $results;
    }
}

/**
 * GitLab Sync Service
 * Syncs user accounts with GitLab instance
 */
class GitLabSyncService implements SyncService
{
    public function getName(): string
    {
        return 'gitlab';
    }
    
    public function getLocalEntities(string $tenantId): array
    {
        $employees = Employee::getAll($tenantId);
        $entities = [];
        
        foreach ($employees as $emp) {
            if (!empty($emp['email'])) {
                // Extract gitlab account if stored
                $gitlabUsername = null;
                if (!empty($emp['accounts'])) {
                    try {
                        $accounts = is_string($emp['accounts']) 
                            ? json_decode($emp['accounts'], true, 512, JSON_THROW_ON_ERROR) 
                            : $emp['accounts'];
                        $gitlabUsername = $accounts['gitlab'] ?? null;
                    } catch (JsonException $e) {
                        // Log error and continue with null username
                        error_log("Failed to decode accounts JSON for employee {$emp['id']}: " . $e->getMessage());
                    }
                }
                
                $entities[$emp['email']] = [
                    'id' => $emp['id'],
                    'email' => $emp['email'],
                    'name' => $emp['full_name'],
                    'username' => $gitlabUsername,
                    'active' => true,
                    'source' => 'local'
                ];
            }
        }
        
        return $entities;
    }
    
    public function getRemoteEntities(string $tenantId, array $config): array
    {
        $entities = [];
        
        $gitlabUrl = $config['gitlab_url'] ?? '';
        $token = $config['gitlab_token'] ?? '';
        
        if (empty($gitlabUrl) || empty($token)) {
            echo "  [WARN] GitLab not configured - skipping remote fetch\n";
            return [];
        }
        
        // In production, this would call the GitLab API
        // GET {gitlab_url}/api/v4/users
        
        return $entities;
    }
    
    public function diff(array $local, array $remote): array
    {
        $diff = [
            'only_local' => [],
            'only_remote' => [],
            'both' => [],
            'conflicts' => []
        ];
        
        foreach ($local as $email => $entity) {
            if (!isset($remote[$email])) {
                $diff['only_local'][$email] = $entity;
            } else {
                $diff['both'][$email] = $entity;
            }
        }
        
        foreach ($remote as $email => $entity) {
            if (!isset($local[$email])) {
                $diff['only_remote'][$email] = $entity;
            }
        }
        
        return $diff;
    }
    
    public function push(string $tenantId, array $diff, array $config, bool $dryRun): array
    {
        $results = ['created' => 0, 'errors' => []];
        
        foreach ($diff['only_local'] as $email => $entity) {
            if ($dryRun) {
                echo "  [DRY-RUN] Would create GitLab user: {$email}\n";
            } else {
                echo "  [CREATE] Creating GitLab user: {$email}\n";
                
                Job::create($tenantId, [
                    'service' => 'gitlab',
                    'action' => 'create_user',
                    'target_name' => $email,
                    'metadata' => ['name' => $entity['name']]
                ]);
            }
            $results['created']++;
        }
        
        if (!empty($diff['only_remote'])) {
            echo "  [INFO] Found " . count($diff['only_remote']) . " orphan user(s) in GitLab:\n";
            foreach ($diff['only_remote'] as $email => $entity) {
                echo "    - {$email} (not in HR system, consider blocking in GitLab)\n";
            }
        }
        
        return $results;
    }
    
    public function pull(string $tenantId, array $diff, array $config, bool $dryRun): array
    {
        return ['imported' => 0, 'errors' => []];
    }
}

/**
 * Telegram Sync Service
 * Syncs chat IDs with Telegram bot
 */
class TelegramSyncService implements SyncService
{
    public function getName(): string
    {
        return 'telegram';
    }
    
    public function getLocalEntities(string $tenantId): array
    {
        $employees = Employee::getAll($tenantId);
        $entities = [];
        
        foreach ($employees as $emp) {
            if (!empty($emp['telegram_chat_id'])) {
                $entities[$emp['telegram_chat_id']] = [
                    'id' => $emp['id'],
                    'chat_id' => $emp['telegram_chat_id'],
                    'name' => $emp['full_name'],
                    'email' => $emp['email'],
                    'source' => 'local'
                ];
            }
        }
        
        return $entities;
    }
    
    public function getRemoteEntities(string $tenantId, array $config): array
    {
        // Telegram doesn't have a list API - we only know about users who message us
        // This would be populated from webhook/polling history
        return [];
    }
    
    public function diff(array $local, array $remote): array
    {
        return [
            'only_local' => $local,
            'only_remote' => $remote,
            'both' => [],
            'conflicts' => []
        ];
    }
    
    public function push(string $tenantId, array $diff, array $config, bool $dryRun): array
    {
        // Can't push to Telegram - users must initiate contact
        echo "  [INFO] Telegram sync is pull-only (users must message the bot first)\n";
        return ['created' => 0, 'errors' => []];
    }
    
    public function pull(string $tenantId, array $diff, array $config, bool $dryRun): array
    {
        // Pull would process incoming messages and match to employees
        return ['imported' => 0, 'errors' => []];
    }
}

/**
 * Main sync orchestrator
 */
class SyncOrchestrator
{
    private array $services = [];
    
    public function __construct()
    {
        $this->services = [
            'mailcow' => new MailcowSyncService(),
            'gitlab' => new GitLabSyncService(),
            'telegram' => new TelegramSyncService()
        ];
    }
    
    public function getServices(string $serviceFilter = 'all'): array
    {
        if ($serviceFilter === 'all') {
            return $this->services;
        }
        
        if (isset($this->services[$serviceFilter])) {
            return [$serviceFilter => $this->services[$serviceFilter]];
        }
        
        return [];
    }
    
    public function diff(string $serviceFilter = 'all'): void
    {
        $tenants = ExcelStorage::readSheet('system.xlsx', 'tenants');
        
        foreach ($tenants as $tenant) {
            $tenantId = $tenant['id'];
            $tenantName = $tenant['name'];
            
            echo "\n=== Tenant: {$tenantName} ({$tenantId}) ===\n";
            
            $config = ExcelStorage::readConfig($tenantId);
            
            foreach ($this->getServices($serviceFilter) as $name => $service) {
                echo "\n--- {$name} ---\n";
                
                $local = $service->getLocalEntities($tenantId);
                $remote = $service->getRemoteEntities($tenantId, $config);
                $diff = $service->diff($local, $remote);
                
                $this->printDiff($diff);
            }
        }
    }
    
    public function push(string $serviceFilter = 'all', bool $dryRun = false): void
    {
        $tenants = ExcelStorage::readSheet('system.xlsx', 'tenants');
        
        foreach ($tenants as $tenant) {
            $tenantId = $tenant['id'];
            $tenantName = $tenant['name'];
            
            echo "\n=== Tenant: {$tenantName} ({$tenantId}) ===\n";
            
            $config = ExcelStorage::readConfig($tenantId);
            
            foreach ($this->getServices($serviceFilter) as $name => $service) {
                echo "\n--- Pushing to {$name} ---\n";
                
                $local = $service->getLocalEntities($tenantId);
                $remote = $service->getRemoteEntities($tenantId, $config);
                $diff = $service->diff($local, $remote);
                
                $results = $service->push($tenantId, $diff, $config, $dryRun);
                
                echo "  Results: {$results['created']} created\n";
            }
        }
    }
    
    public function pull(string $serviceFilter = 'all', bool $dryRun = false): void
    {
        $tenants = ExcelStorage::readSheet('system.xlsx', 'tenants');
        
        foreach ($tenants as $tenant) {
            $tenantId = $tenant['id'];
            $tenantName = $tenant['name'];
            
            echo "\n=== Tenant: {$tenantName} ({$tenantId}) ===\n";
            
            $config = ExcelStorage::readConfig($tenantId);
            
            foreach ($this->getServices($serviceFilter) as $name => $service) {
                echo "\n--- Pulling from {$name} ---\n";
                
                $local = $service->getLocalEntities($tenantId);
                $remote = $service->getRemoteEntities($tenantId, $config);
                $diff = $service->diff($local, $remote);
                
                $results = $service->pull($tenantId, $diff, $config, $dryRun);
                
                echo "  Results: {$results['imported']} imported\n";
            }
        }
    }
    
    private function printDiff(array $diff): void
    {
        echo "  Local only (orphans on remote side): " . count($diff['only_local']) . "\n";
        foreach ($diff['only_local'] as $key => $entity) {
            echo "    + {$key}\n";
        }
        
        echo "  Remote only (orphans in HR): " . count($diff['only_remote']) . "\n";
        foreach ($diff['only_remote'] as $key => $entity) {
            echo "    - {$key}\n";
        }
        
        echo "  Synced: " . count($diff['both']) . "\n";
        
        if (!empty($diff['conflicts'])) {
            echo "  Conflicts: " . count($diff['conflicts']) . "\n";
            foreach ($diff['conflicts'] as $key => $data) {
                echo "    ! {$key}\n";
            }
        }
    }
}

// Main execution
$command = $argv[1] ?? 'diff';
$service = $argv[2] ?? 'all';
$dryRun = in_array('--dry-run', $argv);

$orchestrator = new SyncOrchestrator();

switch ($command) {
    case 'diff':
        $orchestrator->diff($service);
        break;
        
    case 'push':
        $orchestrator->push($service, $dryRun);
        break;
        
    case 'pull':
        $orchestrator->pull($service, $dryRun);
        break;
        
    default:
        echo "Usage: php sync.php <diff|push|pull> [service] [--dry-run]\n";
        echo "Services: mailcow, gitlab, telegram, all\n";
        exit(1);
}
