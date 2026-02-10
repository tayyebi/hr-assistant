<?php

namespace HRAssistant\Controllers;

use HRAssistant\Models\{User, Message, Employee};
use HRAssistant\Core\View;

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
        $tenant = Tenant::getCurrentTenant();
        $user = User::getCurrentUser();
        
        $employees = Employee::getAll($tenantId);
        $reachableEmployees = array_filter($employees, fn($e) => !empty($e['telegram_chat_id']) || !empty($e['email']));
        
        $selectedEmpId = $_GET['employee'] ?? null;
        $messages = $selectedEmpId ? Message::getByEmployee($tenantId, $selectedEmpId) : [];
        $selectedEmployee = $selectedEmpId ? Employee::find($tenantId, $selectedEmpId) : null;
        
        $unassigned = Message::getUnassigned($tenantId);
        
        // Get delivery jobs for the selected employee
        $deliveryJobs = [];
        if ($selectedEmpId) {
            $allJobs = Job::getAll($tenantId);
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
            'employees' => $employees,
            'reachableEmployees' => array_values($reachableEmployees),
            'selectedEmployee' => $selectedEmployee,
            'messages' => $messages,
            'unassigned' => $unassigned,
            'deliveryJobs' => array_values($deliveryJobs),
            'view' => $view,
            'flashMessage' => $message,
            'activeTab' => 'messages'
        ]);
    }

    public function send(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $employeeId = $_POST['employee_id'] ?? '';
        $text = $_POST['text'] ?? '';
        $channel = $_POST['channel'] ?? 'both'; // email, telegram, or both
        $subject = $_POST['subject'] ?? '';
        
        if ($employeeId && $text) {
            $employee = Employee::find($tenantId, $employeeId);
            
            if ($employee) {
                // Create jobs for message delivery with retry support
                $jobsCreated = [];
                
                // Send via email if requested and employee has email
                if (($channel === 'email' || $channel === 'both') && !empty($employee['email'])) {
                    $jobsCreated[] = Job::create($tenantId, [
                        'service' => 'email',
                        'action' => 'send',
                        'target_name' => $employee['email'],
                        'metadata' => [
                            'to' => $employee['email'],
                            'subject' => $subject ?: 'Message from HR',
                            'body' => $text,
                            'employee_id' => $employeeId,
                            'retry_count' => 0
                        ]
                    ]);
                }
                
                // Send via telegram if requested and employee has chat_id
                if (($channel === 'telegram' || $channel === 'both') && !empty($employee['telegram_chat_id'])) {
                    $messageText = $subject ? "{$subject}\n\n{$text}" : $text;
                    $jobsCreated[] = Job::create($tenantId, [
                        'service' => 'telegram',
                        'action' => 'send',
                        'target_name' => "Chat:{$employee['telegram_chat_id']}",
                        'metadata' => [
                            'chat_id' => $employee['telegram_chat_id'],
                            'text' => $messageText,
                            'employee_id' => $employeeId,
                            'retry_count' => 0
                        ]
                    ]);
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
            $job = Job::find($tenantId, $jobId);
            
            if ($job && $job['status'] === Job::STATUS_FAILED) {
                // Reset retry count and requeue
                $metadata = $job['metadata'] ?? [];
                $metadata['retry_count'] = 0;
                $metadata['manual_retry'] = true;
                $metadata['retried_at'] = date('c');
                
                Job::update($tenantId, $jobId, [
                    'status' => Job::STATUS_PENDING,
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
        $messageId = $_POST['message_id'] ?? '';
        $employeeId = $_POST['employee_id'] ?? '';
        
        if ($messageId && $employeeId) {
            if (Message::assignToEmployee($tenantId, $messageId, $employeeId)) {
                $_SESSION['flash_message'] = 'Message assigned successfully.';
            } else {
                $_SESSION['flash_message'] = 'Failed to assign message.';
            }
        }
        
        View::redirect(View::workspaceUrl('/messages?view=inbox'));
    }
}
