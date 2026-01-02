<?php
/**
 * Excel Storage Layer with Consistency Handling
 * Uses PhpSpreadsheet for .xlsx file operations
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelStorage
{
    private static string $dataPath;
    private static array $lockHandles = [];

    public static function init(): void
    {
        self::$dataPath = __DIR__ . '/../../data/';
        
        if (!is_dir(self::$dataPath)) {
            mkdir(self::$dataPath, 0755, true);
        }
        
        // Initialize default data files if they don't exist
        self::initializeSystemData();
    }

    private static function initializeSystemData(): void
    {
        $systemFile = self::$dataPath . 'system.xlsx';
        
        if (!file_exists($systemFile)) {
            $spreadsheet = new Spreadsheet();
            
            // Users sheet
            $usersSheet = $spreadsheet->getActiveSheet();
            $usersSheet->setTitle('users');
            $usersSheet->fromArray([
                ['id', 'email', 'password_hash', 'role', 'tenant_id'],
                ['user_sys_admin', 'sysadmin@corp.com', 'password', 'system_admin', ''],
                ['user_tenant_admin_1', 'admin@defaultcorp.com', 'password', 'tenant_admin', 'tenant_default_corp']
            ]);
            
            // Tenants sheet
            $tenantsSheet = $spreadsheet->createSheet();
            $tenantsSheet->setTitle('tenants');
            $tenantsSheet->fromArray([
                ['id', 'name'],
                ['tenant_default_corp', 'Default Corp']
            ]);
            
            $writer = new Xlsx($spreadsheet);
            $writer->save($systemFile);
        }
        
        // Initialize default tenant data
        self::initializeTenantData('tenant_default_corp');
    }

    public static function initializeTenantData(string $tenantId): void
    {
        $tenantFile = self::$dataPath . "tenant_{$tenantId}.xlsx";
        
        if (!file_exists($tenantFile)) {
            $spreadsheet = new Spreadsheet();
            
            // Employees sheet
            $empSheet = $spreadsheet->getActiveSheet();
            $empSheet->setTitle('employees');
            $empSheet->fromArray([
                ['id', 'tenant_id', 'full_name', 'email', 'telegram_chat_id', 'birthday', 'hired_date', 'position', 'team_id', 'feelings_log', 'accounts']
            ]);
            
            // Teams sheet
            $teamsSheet = $spreadsheet->createSheet();
            $teamsSheet->setTitle('teams');
            $teamsSheet->fromArray([
                ['id', 'tenant_id', 'name', 'description', 'member_ids', 'email_aliases']
            ]);
            
            // Messages sheet
            $msgsSheet = $spreadsheet->createSheet();
            $msgsSheet->setTitle('messages');
            $msgsSheet->fromArray([
                ['id', 'tenant_id', 'employee_id', 'sender', 'channel', 'text', 'subject', 'timestamp']
            ]);
            
            // Unassigned messages sheet
            $unassignedSheet = $spreadsheet->createSheet();
            $unassignedSheet->setTitle('unassigned_messages');
            $unassignedSheet->fromArray([
                ['id', 'tenant_id', 'channel', 'source_id', 'sender_name', 'text', 'subject', 'timestamp']
            ]);
            
            // Jobs sheet
            $jobsSheet = $spreadsheet->createSheet();
            $jobsSheet->setTitle('jobs');
            $jobsSheet->fromArray([
                ['id', 'tenant_id', 'service', 'action', 'target_name', 'status', 'result', 'created_at', 'updated_at', 'metadata']
            ]);
            
            // Assets sheet
            $assetsSheet = $spreadsheet->createSheet();
            $assetsSheet->setTitle('assets');
            $assetsSheet->fromArray([
                ['id', 'tenant_id', 'employee_id', 'provider', 'asset_type', 'identifier', 'status', 'metadata', 'created_at', 'updated_at']
            ]);
            
            // Config sheet
            $configSheet = $spreadsheet->createSheet();
            $configSheet->setTitle('config');
            $configSheet->fromArray([
                ['key', 'value'],
                ['telegram_bot_token', ''],
                ['telegram_mode', 'webhook'],
                ['webhook_url', ''],
                ['mailcow_url', 'https://mail.example.com'],
                ['mailcow_api_key', ''],
                ['gitlab_url', 'https://gitlab.example.com'],
                ['gitlab_token', ''],
                ['keycloak_url', 'https://auth.example.com'],
                ['keycloak_realm', 'hr-assistant'],
                ['keycloak_client_id', 'hr-assistant-client'],
                ['keycloak_client_secret', ''],
                ['imap_host', 'imap.example.com'],
                ['imap_port', '993'],
                ['imap_tls', '1'],
                ['imap_user', 'hr@example.com'],
                ['imap_pass', ''],
                ['smtp_host', 'smtp.example.com'],
                ['smtp_port', '465'],
                ['smtp_tls', '1'],
                ['smtp_user', 'hr@example.com'],
                ['smtp_pass', '']
            ]);
            
            $writer = new Xlsx($spreadsheet);
            $writer->save($tenantFile);
        }
    }

    /**
     * Acquire an exclusive lock on a file for consistency
     * @param string $filePath Path to the file to lock
     * @param int $timeout Maximum time in seconds to wait for lock (default: 10)
     * @throws Exception if lock cannot be acquired within timeout
     */
    private static function acquireLock(string $filePath, int $timeout = 10): void
    {
        $lockFile = $filePath . '.lock';
        $handle = fopen($lockFile, 'c+');
        
        if (!$handle) {
            throw new Exception("Could not create lock file: {$lockFile}");
        }
        
        // Try to acquire lock with timeout
        $startTime = time();
        $acquired = false;
        
        while (!$acquired && (time() - $startTime) < $timeout) {
            // Try non-blocking lock first
            if (flock($handle, LOCK_EX | LOCK_NB)) {
                $acquired = true;
            } else {
                // Wait a short time before retrying
                usleep(100000); // 100ms
            }
        }
        
        if (!$acquired) {
            fclose($handle);
            throw new Exception("Could not acquire lock within {$timeout} seconds: {$lockFile}");
        }
        
        self::$lockHandles[$filePath] = $handle;
    }

    /**
     * Release the lock on a file
     */
    private static function releaseLock(string $filePath): void
    {
        if (isset(self::$lockHandles[$filePath])) {
            flock(self::$lockHandles[$filePath], LOCK_UN);
            fclose(self::$lockHandles[$filePath]);
            unset(self::$lockHandles[$filePath]);
        }
    }

    /**
     * Read data from a sheet with locking
     */
    public static function readSheet(string $file, string $sheetName): array
    {
        $filePath = self::$dataPath . $file;
        
        if (!file_exists($filePath)) {
            return [];
        }
        
        self::acquireLock($filePath);
        
        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getSheetByName($sheetName);
            
            if (!$sheet) {
                self::releaseLock($filePath);
                return [];
            }
            
            $data = $sheet->toArray();
            self::releaseLock($filePath);
            
            if (count($data) <= 1) {
                return [];
            }
            
            $headers = array_shift($data);
            $result = [];
            
            foreach ($data as $row) {
                $item = [];
                foreach ($headers as $i => $header) {
                    $item[$header] = $row[$i] ?? null;
                }
                $result[] = $item;
            }
            
            return $result;
        } catch (Exception $e) {
            self::releaseLock($filePath);
            throw $e;
        }
    }

    /**
     * Write data to a sheet with locking and consistency
     */
    public static function writeSheet(string $file, string $sheetName, array $data, array $headers): void
    {
        $filePath = self::$dataPath . $file;
        
        self::acquireLock($filePath);
        
        try {
            if (file_exists($filePath)) {
                $spreadsheet = IOFactory::load($filePath);
            } else {
                $spreadsheet = new Spreadsheet();
            }
            
            $sheet = $spreadsheet->getSheetByName($sheetName);
            
            if (!$sheet) {
                $sheet = $spreadsheet->createSheet();
                $sheet->setTitle($sheetName);
            }
            
            // Clear existing data
            $highestRow = $sheet->getHighestRow();
            $highestColumn = $sheet->getHighestColumn();
            $sheet->removeRow(1, $highestRow);
            
            // Write headers
            $sheet->fromArray([$headers], null, 'A1');
            
            // Write data
            $rows = [];
            foreach ($data as $item) {
                $row = [];
                foreach ($headers as $header) {
                    $value = $item[$header] ?? '';
                    // Handle arrays/objects by JSON encoding
                    if (is_array($value) || is_object($value)) {
                        $value = json_encode($value);
                    }
                    $row[] = $value;
                }
                $rows[] = $row;
            }
            
            if (!empty($rows)) {
                $sheet->fromArray($rows, null, 'A2');
            }
            
            $writer = new Xlsx($spreadsheet);
            $writer->save($filePath);
            
            self::releaseLock($filePath);
        } catch (Exception $e) {
            self::releaseLock($filePath);
            throw $e;
        }
    }

    /**
     * Append a row to a sheet with locking
     */
    public static function appendRow(string $file, string $sheetName, array $item, array $headers): void
    {
        $data = self::readSheet($file, $sheetName);
        $data[] = $item;
        self::writeSheet($file, $sheetName, $data, $headers);
    }

    /**
     * Update a row in a sheet with locking
     */
    public static function updateRow(string $file, string $sheetName, string $idField, string $id, array $updates, array $headers): bool
    {
        $data = self::readSheet($file, $sheetName);
        $found = false;
        
        foreach ($data as &$item) {
            if ($item[$idField] === $id) {
                foreach ($updates as $key => $value) {
                    $item[$key] = $value;
                }
                $found = true;
                break;
            }
        }
        
        if ($found) {
            self::writeSheet($file, $sheetName, $data, $headers);
        }
        
        return $found;
    }

    /**
     * Delete a row from a sheet with locking
     */
    public static function deleteRow(string $file, string $sheetName, string $idField, string $id, array $headers): bool
    {
        $data = self::readSheet($file, $sheetName);
        $initialCount = count($data);
        
        $data = array_filter($data, function($item) use ($idField, $id) {
            return $item[$idField] !== $id;
        });
        
        if (count($data) < $initialCount) {
            self::writeSheet($file, $sheetName, array_values($data), $headers);
            return true;
        }
        
        return false;
    }

    /**
     * Read config values for a tenant
     */
    public static function readConfig(string $tenantId): array
    {
        $data = self::readSheet("tenant_{$tenantId}.xlsx", 'config');
        $config = [];
        
        foreach ($data as $row) {
            $config[$row['key']] = $row['value'];
        }
        
        return $config;
    }

    /**
     * Write config values for a tenant
     */
    public static function writeConfig(string $tenantId, array $config): void
    {
        $data = [];
        foreach ($config as $key => $value) {
            $data[] = ['key' => $key, 'value' => $value];
        }
        
        self::writeSheet("tenant_{$tenantId}.xlsx", 'config', $data, ['key', 'value']);
    }
}

// Initialize storage on load
ExcelStorage::init();
