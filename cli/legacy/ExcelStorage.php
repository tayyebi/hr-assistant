<?php
// DEPRECATED STUB
// The legacy Excel implementation has been archived to `archive/legacy_excel/`.
// This file is intentionally a stub and should not be used. Any attempt to
// use it will result in an exception to avoid accidental runtime usage.

throw new \Exception("Excel support removed; legacy files archived under archive/legacy_excel/");


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
// Excel storage initialization removed as part of cleanup; use DB-backed storage.
