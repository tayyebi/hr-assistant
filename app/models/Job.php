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
        $jobs = ExcelStorage::readSheet("tenant_{$tenantId}.xlsx", 'jobs');
        
        // Parse JSON metadata
        foreach ($jobs as &$job) {
            $job['metadata'] = $job['metadata'] ? json_decode($job['metadata'], true) : [];
        }
        
        // Sort by created_at descending
        usort($jobs, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
        
        return $jobs;
    }

    public static function find(string $tenantId, string $id): ?array
    {
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
        
        ExcelStorage::appendRow("tenant_{$tenantId}.xlsx", 'jobs', $job, self::$headers);
        
        // Simulate job processing (in a real app, this would be a background worker)
        self::processJob($tenantId, $job['id']);
        
        return $job;
    }

    public static function update(string $tenantId, string $id, array $data): bool
    {
        if (isset($data['metadata']) && is_array($data['metadata'])) {
            $data['metadata'] = json_encode($data['metadata']);
        }
        
        $data['updated_at'] = date('c');
        
        return ExcelStorage::updateRow(
            "tenant_{$tenantId}.xlsx",
            'jobs',
            'id',
            $id,
            $data,
            self::$headers
        );
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
