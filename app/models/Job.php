<?php
/**
 * Job Model for System Jobs
 */
class Job
{
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    private static array $headers = ['id', 'tenant_id', 'service', 'action', 'target_name', 'status', 'result', 'created_at', 'updated_at', 'metadata'];

    public static function getAll(string $tenantId): array
    {
        try {
            $rows = Database::fetchAll('SELECT * FROM jobs WHERE tenant_id = ? ORDER BY created_at DESC', [$tenantId]);
            foreach ($rows as &$job) {
                $job['metadata'] = $job['metadata'] ? json_decode($job['metadata'], true) : [];
            }
            return $rows;
        } catch (\Exception $e) {
            return [];
        }
    }

    public static function find(string $tenantId, string $id): ?array
    {
        try {
            $row = Database::fetchOne('SELECT * FROM jobs WHERE tenant_id = ? AND id = ? LIMIT 1', [$tenantId, $id]);
            if ($row) {
                $row['metadata'] = $row['metadata'] ? json_decode($row['metadata'], true) : [];
                return $row;
            }
        } catch (\Exception $e) {
            return null;
        }

        $jobs = self::getAll($tenantId);
        foreach ($jobs as $job) {
            if ($job['id'] === $id) {
                return $job;
            }
        }
        return null;
    }

    public static function create(string $tenantId, array $data): array
    {
        $job = [
            'id' => 'job_' . time() . '_' . mt_rand(1000, 9999),
            'tenant_id' => $tenantId,
            'service' => $data['service'] ?? '',
            'action' => $data['action'] ?? '',
            'target_name' => $data['target_name'] ?? '',
            'status' => self::STATUS_PENDING,
            'result' => '',
            'created_at' => date('c'),
            'updated_at' => date('c'),
            'metadata' => json_encode($data['metadata'] ?? [])
        ];

        try {
            Database::execute('INSERT INTO jobs (id, tenant_id, service, action, target_name, status, result, created_at, updated_at, metadata) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', [
                $job['id'],
                $job['tenant_id'],
                $job['service'],
                $job['action'],
                $job['target_name'],
                $job['status'],
                $job['result'],
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s'),
                $job['metadata']
            ]);
            self::processJob($tenantId, $job['id']);
            return $job;
        } catch (\Exception $e) {
            // DB error: still process locally
            self::processJob($tenantId, $job['id']);
            return $job;
        }
    }

    public static function update(string $tenantId, string $id, array $data): bool
    {
        if (isset($data['metadata']) && is_array($data['metadata'])) {
            $data['metadata'] = json_encode($data['metadata']);
        }
        
        $data['updated_at'] = date('c');
        try {
            $setParts = [];
            $params = [];
            foreach ($data as $k => $v) {
                $setParts[] = "`$k` = ?";
                $params[] = $v;
            }
            $params[] = $tenantId;
            $params[] = $id;
            $sql = 'UPDATE jobs SET ' . implode(', ', $setParts) . ' WHERE tenant_id = ? AND id = ?';
            Database::execute($sql, $params);
            return true;
        } catch (\Exception $e) {
            return false;
        }
        }
    }

    public static function retry(string $tenantId, string $id): bool
    {
        $job = self::find($tenantId, $id);
        
        if (!$job || $job['status'] !== self::STATUS_FAILED) {
            return false;
        }
        
        self::update($tenantId, $id, [
            'status' => self::STATUS_PENDING,
            'result' => ''
        ]);
        
        // Re-process the job
        self::processJob($tenantId, $id);
        
        return true;
    }

    /**
     * Simulate job processing
     * In a real app, this would be handled by a background worker/queue
     */
    private static function processJob(string $tenantId, string $jobId): void
    {
        // Update to processing
        self::update($tenantId, $jobId, ['status' => self::STATUS_PROCESSING]);
        
        // Simulate processing time and random success/failure
        // In production, this would actually call the service APIs
        $success = mt_rand(0, 100) > 20; // 80% success rate
        
        if ($success) {
            self::update($tenantId, $jobId, [
                'status' => self::STATUS_COMPLETED,
                'result' => 'Job completed successfully (simulated)'
            ]);
        } else {
            self::update($tenantId, $jobId, [
                'status' => self::STATUS_FAILED,
                'result' => 'Service connection failed (simulated)'
            ]);
        }
    }
}
