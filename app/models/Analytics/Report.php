<?php

namespace HRAssistant\Models\Analytics;

/**
 * Analytics Report Model
 * Example of PSR-4 namespaced model
 */
class Report
{
    private $data = [];
    
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }
    
    public function generate(): array
    {
        return [
            'class' => __CLASS__,
            'namespace' => __NAMESPACE__,
            'file' => __FILE__,
            'data' => $this->data,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    public static function getType(): string
    {
        return 'analytics_report';
    }
}