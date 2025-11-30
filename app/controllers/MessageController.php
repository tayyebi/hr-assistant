<?php
/**
 * Message Controller
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
        $channel = $_POST['channel'] ?? 'email';
        $subject = $_POST['subject'] ?? '';
        
        if ($employeeId && $text) {
            Message::create($tenantId, [
                'employee_id' => $employeeId,
                'sender' => 'hr',
                'channel' => $channel,
                'text' => $text,
                'subject' => $subject
            ]);
            
            $_SESSION['flash_message'] = 'Message sent successfully (simulated).';
        }
        
        View::redirect('/messages?employee=' . urlencode($employeeId));
    }
}
