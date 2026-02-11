<?php

namespace App\Controllers;

use App\Models\{User, Message, Employee, ProviderInstance};
use App\Core\{View, ProviderType};

/**
 * Message Controller
 * Handles direct messaging with job-based delivery (email + telegram with retry)
 */
class MessageController
{
    public function index(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $tenant = \App\Models\Tenant::getCurrentTenant();
        $user = User::getCurrentUser();
        
        // Check if any messaging providers are configured (email or messenger)
        $allInstances = ProviderInstance::getAll($tenantId);
        $messagingInstances = array_filter($allInstances, function($instance) {
            $type = ProviderType::getAssetType($instance['provider']);
            return $type === ProviderType::TYPE_EMAIL || $type === ProviderType::TYPE_MESSENGER;
        });
        
        // Get employees with their available channels
        $employees = Employee::getAllWithChannels($tenantId);
        
        $selectedEmpId = $_GET['employee'] ?? null;
        $selectedChannel = $_GET['channel'] ?? 'all';
        
        $messages = [];
        $selectedEmployee = null;
        $availableChannels = [];
        
        if ($selectedEmpId) {
            $selectedEmployee = Employee::find($tenantId, $selectedEmpId);
            
            if ($selectedChannel === 'all') {
                $messages = Message::getByEmployee($tenantId, $selectedEmpId);
            } else {
                $messages = Message::getByEmployeeChannel($tenantId, $selectedEmpId, $selectedChannel);
            }
            
            // Get available channels for this employee
            $availableChannels = Employee::getAvailableChannels($tenantId, $selectedEmpId);
        }
        
        $unassigned = Message::getUnassigned($tenantId);
        
        // Get delivery jobs for the selected employee
        $deliveryJobs = [];
        if ($selectedEmpId) {
            $allJobs = \App\Models\Job::getAll($tenantId);
            $deliveryJobs = array_filter($allJobs, function($job) use ($selectedEmpId) {
                $metadata = $job['metadata'] ?? [];
                return ($job['service'] === 'email' || $job['service'] === 'telegram') 
                    && ($metadata['employee_id'] ?? '') === $selectedEmpId;
            });
        }
        
        $view = $_GET['view'] ?? 'chats';
        
        $message = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        
        View::render('messages', [
            'tenant' => $tenant,
            'user' => $user,
            'employees' => array_values($employees),
            'selectedEmployee' => $selectedEmployee,
            'selectedChannel' => $selectedChannel,
            'availableChannels' => $availableChannels,
            'messages' => $messages,
            'unassigned' => $unassigned,
            'deliveryJobs' => array_values($deliveryJobs),
            'view' => $view,
            'flashMessage' => $message,
            'activeTab' => 'messages',
            'messagingInstances' => array_values($messagingInstances)
        ]);
    }

    public function send(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $employeeId = $_POST['employee_id'] ?? '';
        $text = $_POST['text'] ?? '';
        $channel = $_POST['channel'] ?? 'all'; // email, telegram, slack, or specific provider
        $subject = $_POST['subject'] ?? '';
        
        if ($employeeId && $text) {
            $employee = Employee::find($tenantId, $employeeId);
            
            if ($employee) {
                // Get provider instances and employee accounts
                $providerInstances = \App\Models\ProviderInstance::getAll($tenantId);
                $providerMap = [];
                foreach ($providerInstances as $pi) {
                    $providerMap[$pi['id']] = $pi;
                }
                
                $accounts = $employee['accounts'] ?? [];
                $jobsCreated = [];
                
                // Create jobs for message delivery based on employee accounts
                foreach ($accounts as $providerInstanceId => $identifier) {
                    if (empty($identifier)) continue;
                    
                    $instance = $providerMap[$providerInstanceId] ?? null;
                    if (!$instance) continue;
                    
                    $providerType = \App\Core\ProviderType::getAssetType($instance['provider']);
                    
                    // Send via email providers
                    if ($providerType === \App\Core\ProviderType::TYPE_EMAIL && ($channel === 'all' || $channel === 'email')) {
                        $jobsCreated[] = \App\Models\Job::create($tenantId, [
                            'service' => 'email',
                            'action' => 'send',
                            'target_name' => $identifier,
                            'metadata' => [
                                'to' => $identifier,
                                'subject' => $subject ?: 'Message from HR',
                                'body' => $text,
                                'employee_id' => $employeeId,
                                'provider_instance_id' => $providerInstanceId,
                                'retry_count' => 0
                            ]
                        ]);
                    }
                    
                    // Send via messenger providers (telegram, slack, etc.)
                    if ($providerType === \App\Core\ProviderType::TYPE_MESSENGER && ($channel === 'all' || $channel === $instance['provider'])) {
                        $messageText = $subject ? "{$subject}\n\n{$text}" : $text;
                        $jobsCreated[] = \App\Models\Job::create($tenantId, [
                            'service' => $instance['provider'],
                            'action' => 'send',
                            'target_name' => "Chat:{$identifier}",
                            'metadata' => [
                                'chat_id' => $identifier,
                                'text' => $messageText,
                                'employee_id' => $employeeId,
                                'provider_instance_id' => $providerInstanceId,
                                'retry_count' => 0
                            ]
                        ]);
                    }
                }
                
                // Store message in conversation history
                Message::create($tenantId, [
                    'employee_id' => $employeeId,
                    'sender' => 'hr',
                    'channel' => $channel,
                    'text' => $text,
                    'subject' => $subject
                ]);
                
                $jobCount = count($jobsCreated);
                $_SESSION['flash_message'] = "Message queued for delivery via {$jobCount} channel(s). Jobs will retry automatically on failure.";
            } else {
                $_SESSION['flash_message'] = 'Employee not found.';
            }
        }
        
        View::redirect('/messages?employee=' . urlencode($employeeId));
    }

    /**
     * Retry a failed delivery job
     */
    public function retryDelivery(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $jobId = $_POST['job_id'] ?? '';
        
        if ($jobId) {
            $job = \App\Models\Job::find($tenantId, $jobId);
            
            if ($job && $job['status'] === \App\Models\Job::STATUS_FAILED) {
                // Reset retry count and requeue
                $metadata = $job['metadata'] ?? [];
                $metadata['retry_count'] = 0;
                $metadata['manual_retry'] = true;
                $metadata['retried_at'] = date('c');
                
                \App\Models\Job::update($tenantId, $jobId, [
                    'status' => \App\Models\Job::STATUS_PENDING,
                    'result' => 'Manually retried',
                    'metadata' => $metadata
                ]);
                
                $_SESSION['flash_message'] = 'Delivery job queued for retry.';
            } else {
                $_SESSION['flash_message'] = 'Cannot retry this job.';
            }
        }
        
        $employeeId = $_POST['employee_id'] ?? '';
        View::redirect(View::workspaceUrl('/messages?employee=' . urlencode($employeeId)));
    }

    public function assign(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $unassignedId = $_POST['unassigned_id'] ?? '';
        $employeeId = $_POST['employee_id'] ?? '';
        $channel = $_POST['channel'] ?? null; // Optional channel override
        
        if ($unassignedId && $employeeId) {
            $success = Message::assignToEmployee($tenantId, $unassignedId, $employeeId, $channel);
            if ($success) {
                $_SESSION['flash_message'] = 'Message assigned successfully.';
            } else {
                $_SESSION['flash_message'] = 'Failed to assign message.';
            }
        } else {
            $_SESSION['flash_message'] = 'Employee and message are required.';
        }
        
        View::redirect(View::workspaceUrl('/messages'));
    }
}
