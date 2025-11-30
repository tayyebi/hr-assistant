<?php
/**
 * Settings Controller
 */
class SettingsController
{
    public function index(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $tenant = Tenant::getCurrentTenant();
        $user = User::getCurrentUser();
        
        $config = Config::get($tenantId);
        
        $message = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        
        View::render('settings', [
            'tenant' => $tenant,
            'user' => $user,
            'config' => $config,
            'message' => $message,
            'activeTab' => 'settings'
        ]);
    }

    public function save(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        
        $config = [
            'telegram_bot_token' => $_POST['telegram_bot_token'] ?? '',
            'telegram_mode' => $_POST['telegram_mode'] ?? 'webhook',
            'webhook_url' => $_POST['webhook_url'] ?? '',
            'mailcow_url' => $_POST['mailcow_url'] ?? '',
            'mailcow_api_key' => $_POST['mailcow_api_key'] ?? '',
            'gitlab_url' => $_POST['gitlab_url'] ?? '',
            'gitlab_token' => $_POST['gitlab_token'] ?? '',
            'keycloak_url' => $_POST['keycloak_url'] ?? '',
            'keycloak_realm' => $_POST['keycloak_realm'] ?? '',
            'keycloak_client_id' => $_POST['keycloak_client_id'] ?? '',
            'keycloak_client_secret' => $_POST['keycloak_client_secret'] ?? '',
            'imap_host' => $_POST['imap_host'] ?? '',
            'imap_port' => $_POST['imap_port'] ?? '',
            'imap_tls' => isset($_POST['imap_tls']) ? '1' : '0',
            'imap_user' => $_POST['imap_user'] ?? '',
            'imap_pass' => $_POST['imap_pass'] ?? '',
            'smtp_host' => $_POST['smtp_host'] ?? '',
            'smtp_port' => $_POST['smtp_port'] ?? '',
            'smtp_tls' => isset($_POST['smtp_tls']) ? '1' : '0',
            'smtp_user' => $_POST['smtp_user'] ?? '',
            'smtp_pass' => $_POST['smtp_pass'] ?? ''
        ];
        
        Config::save($tenantId, $config);
        
        $_SESSION['flash_message'] = 'Configuration saved successfully.';
        View::redirect('/settings');
    }
}
