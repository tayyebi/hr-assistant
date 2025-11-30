import { SystemJob, AppConfig, ServiceType, Employee } from '../types';
import { saveJob, updateJob, getTenantData } from './storage';
import { getServiceAdapter } from './integrationService';

export const createJob = async (
    tenantId: string,
    service: ServiceType,
    action: string,
    targetName: string,
    payload: any, // Employee object or Account ID or specific params
    config: AppConfig
) => {
    const job: SystemJob = {
        id: `job_${Date.now()}_${Math.random().toString(36).substr(2, 5)}`,
        tenantId,
        service,
        action,
        targetName,
        status: 'pending',
        createdAt: new Date().toISOString(),
        updatedAt: new Date().toISOString(),
        metadata: payload
    };

    saveJob(tenantId, job);
    
    // Trigger async processing (fire and forget from UI perspective)
    // In a real backend, this would be a message queue consumer
    setTimeout(() => processJob(tenantId, job.id, config), 100);
    
    return job;
};

export const retryJob = async (tenantId: string, jobId: string, config: AppConfig) => {
    updateJob(tenantId, jobId, { status: 'pending', updatedAt: new Date().toISOString(), result: undefined });
    setTimeout(() => processJob(tenantId, jobId, config), 500);
};

const processJob = async (tenantId: string, jobId: string, config: AppConfig) => {
    // 1. Mark as processing
    updateJob(tenantId, jobId, { status: 'processing', updatedAt: new Date().toISOString() });
    
    // Fetch fresh job data in case it changed (rare here)
    const tenantData = getTenantData(tenantId);
    const job = tenantData.jobs.find(j => j.id === jobId);
    if(!job) return;

    try {
        const adapter = getServiceAdapter(job.service);
        let result = { success: false, message: 'Unknown Action' };

        // Simulate a delay for "Background Task" feel (1.5 - 4 seconds)
        const delay = 1500 + Math.random() * 2500;
        await new Promise(resolve => setTimeout(resolve, delay));

        switch(job.action) {
            case 'PROVISION':
                // Payload is Employee
                result = await adapter.provisionUser(job.metadata as Employee, config);
                break;
            case 'RESET_CREDENTIAL':
                 // Payload is accountId string
                 result = await adapter.resetCredential(job.metadata as string, config);
                 break;
            case 'DEACTIVATE':
                 // Payload is accountId string
                 if (adapter.deactivateUser) {
                    result = await adapter.deactivateUser(job.metadata as string, config);
                 } else {
                     result = { success: false, message: 'Operation not supported by adapter' };
                 }
                 break;
             case 'ASSIGN_PROJECT':
                if (adapter.assignToProject) {
                     const { accountId, projectId } = job.metadata;
                     const success = await adapter.assignToProject(accountId, projectId, config);
                     result = { success, message: success ? 'Assigned to project' : 'Assignment failed' };
                } else {
                    result = { success: false, message: 'Operation not supported by adapter' };
                }
                break;
        }

        // 2. Mark as completed or failed
        updateJob(tenantId, jobId, {
            status: result.success ? 'completed' : 'failed',
            result: result.message,
            updatedAt: new Date().toISOString()
        });

    } catch (error: any) {
        // Handle unexpected errors
        console.error("Job Failed", error);
        updateJob(tenantId, jobId, {
            status: 'failed',
            result: error.message || 'Unexpected system error',
            updatedAt: new Date().toISOString()
        });
    }
};
