<?php

namespace App\Controllers;

use App\Models\{User, Job};
use App\Core\View;

/**
 * Job Controller
 */
class JobController
{
    public function index(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $tenant = \App\Models\Tenant::getCurrentTenant();
        $user = User::getCurrentUser();
        
        $jobs = Job::getAll($tenantId);
        
        $message = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);
        
        View::render('jobs', [
            'tenant' => $tenant,
            'user' => $user,
            'jobs' => $jobs,
            'message' => $message,
            'activeTab' => 'jobs'
        ]);
    }

    public function retry(): void
    {
        AuthController::requireTenantAdmin();
        
        $tenantId = User::getTenantId();
        $jobId = $_POST['job_id'] ?? '';
        
        if (Job::retry($tenantId, $jobId)) {
            $_SESSION['flash_message'] = 'Job queued for retry.';
        } else {
            $_SESSION['flash_message'] = 'Could not retry job.';
        }
        
        View::redirect(View::workspaceUrl('/jobs'));
    }
}
