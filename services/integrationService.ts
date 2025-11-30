

import { Employee, ServiceType, AppConfig } from "../types";

// Polymorphic Interface for all Org Services
export interface IServiceAdapter {
    serviceType: ServiceType;
    provisionUser(employee: Employee, config: AppConfig): Promise<{ success: boolean; accountId?: string; message: string }>;
    resetCredential(accountId: string, config: AppConfig): Promise<{ success: boolean; newSecret?: string; message: string }>;
    deactivateUser?(accountId: string, config: AppConfig): Promise<{ success: boolean; message: string }>;
    getProjectList?(config: AppConfig): Promise<string[]>; // Optional, specific to GitLab/Jira
    assignToProject?(accountId: string, projectId: string, config: AppConfig): Promise<boolean>;
}

// Mailcow Implementation
export class MailcowAdapter implements IServiceAdapter {
    serviceType = ServiceType.MAILCOW;

    async provisionUser(employee: Employee, config: AppConfig) {
        if (!config.mailcow.apiKey) return { success: false, message: "Mail Service API Key missing in Settings" };
        
        console.log(`[Mail Service] Check if mailbox exists for ${employee.email}...`);
        // Simulate Check. If exists, ensure active.
        
        console.log(`[Mail Service] Creating/Activating mailbox for ${employee.email}...`);
        // Simulate API call
        await new Promise(r => setTimeout(r, 1000));
        
        return { success: true, accountId: employee.email, message: "Mailbox active" };
    }

    async resetCredential(email: string, config: AppConfig) {
        console.log(`[Mail Service] Resetting password for ${email}...`);
        await new Promise(r => setTimeout(r, 800));
        const newPass = Math.random().toString(36).slice(-10) + "!A1";
        return { success: true, newSecret: newPass, message: "Password reset. Sending to user..." };
    }

    async deactivateUser(email: string, config: AppConfig) {
        // SAFE CODING: NEVER DELETE. ONLY UPDATE ATTRIBUTES.
        console.log(`[Mail Service] Setting active=0 for ${email}`);
        await new Promise(r => setTimeout(r, 800));
        return { success: true, message: "Mailbox deactivated (Suspended)." };
    }
    
    async createAlias(targetEmail: string, alias: string, config: AppConfig) {
        console.log(`[Mail Service] Creating alias ${alias} -> ${targetEmail}`);
        await new Promise(r => setTimeout(r, 500));
        return true;
    }
}

// GitLab Implementation
export class GitlabAdapter implements IServiceAdapter {
    serviceType = ServiceType.GITLAB;

    async provisionUser(employee: Employee, config: AppConfig) {
        if (!config.gitlab.token) return { success: false, message: "Git Service Token missing" };
        
        const username = employee.email.split('@')[0];
        console.log(`[Git Service] Creating user ${username}...`);
        await new Promise(r => setTimeout(r, 1000));
        
        return { success: true, accountId: username, message: "Git Service user created" };
    }

    async resetCredential(username: string, config: AppConfig) {
         // GitLab usually handles its own resets via email, but admin can force reset
         return { success: true, message: "Triggered password reset email to user." };
    }

    async getProjectList(config: AppConfig) {
        return ['core-api', 'frontend-app', 'data-pipeline', 'hr-assistant-tool'];
    }

    async assignToProject(username: string, projectId: string, config: AppConfig) {
        console.log(`[Git Service] Adding ${username} to project ${projectId} (Developer Role)...`);
        await new Promise(r => setTimeout(r, 800));
        return true;
    }
}

// Keycloak Implementation
export class KeycloakAdapter implements IServiceAdapter {
    serviceType = ServiceType.KEYCLOAK;

    async provisionUser(employee: Employee, config: AppConfig) {
        if (!config.keycloak.clientSecret) return { success: false, message: "Keycloak Client Secret missing" };
        
        const username = employee.email.split('@')[0];
        console.log(`[Keycloak] Authenticating as Client ${config.keycloak.clientId}...`);
        console.log(`[Keycloak] POST /admin/realms/${config.keycloak.realm}/users { username: ${username}, email: ${employee.email} }`);
        
        await new Promise(r => setTimeout(r, 1200));
        
        return { success: true, accountId: username, message: "User federated in Keycloak" };
    }

    async resetCredential(username: string, config: AppConfig) {
        console.log(`[Keycloak] PUT /admin/realms/${config.keycloak.realm}/users/${username}/execute-actions-email { actions: ['UPDATE_PASSWORD'] }`);
        await new Promise(r => setTimeout(r, 800));
        return { success: true, message: "Sent Update Password email to user via Keycloak." };
    }

    async deactivateUser(username: string, config: AppConfig) {
        console.log(`[Keycloak] PUT /admin/realms/${config.keycloak.realm}/users/${username} { enabled: false }`);
        await new Promise(r => setTimeout(r, 800));
        return { success: true, message: "User disabled in Realm." };
    }
}

// Factory to get adapter
export const getServiceAdapter = (type: ServiceType): IServiceAdapter => {
    switch (type) {
        case ServiceType.MAILCOW: return new MailcowAdapter();
        case ServiceType.GITLAB: return new GitlabAdapter();
        case ServiceType.KEYCLOAK: return new KeycloakAdapter();
        default: throw new Error(`Service ${type} not implemented`);
    }
};