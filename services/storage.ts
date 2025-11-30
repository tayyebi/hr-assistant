

import { Employee, Team, ChatMessage, UnassignedMessage, AppConfig, User, Tenant, UserRole, SystemJob } from '../types';

const STORAGE_KEY = 'hr_assistant_multitenant_db';

const DEFAULT_CONFIG: AppConfig = {
    telegramBotToken: '',
    telegramMode: 'webhook',
    webhookUrl: '',
    mailcow: { url: 'https://mail.example.com', apiKey: '' },
    gitlab: { url: 'https://gitlab.example.com', token: '' },
    keycloak: { url: 'https://auth.example.com', realm: 'hr-assistant', clientId: 'hr-assistant-client', clientSecret: '' },
    emailService: {
        imap: { host: 'imap.example.com', port: 993, tls: true, user: 'hr@example.com', pass: '' },
        smtp: { host: 'smtp.example.com', port: 465, tls: true, user: 'hr@example.com', pass: '' }
    }
};

interface TenantData {
    employees: Employee[];
    teams: Team[];
    messages: ChatMessage[];
    unassignedMessages: UnassignedMessage[];
    jobs: SystemJob[];
    config: AppConfig;
}

interface SystemData {
    users: User[];
    tenants: Tenant[];
}

interface FullStorage {
    system: SystemData;
    tenants: Record<string, TenantData>;
}

let memCache: FullStorage;

const persistLocal = () => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(memCache));
};

export const initializeStorage = (): void => {
    const local = localStorage.getItem(STORAGE_KEY);
    if (local) {
        memCache = JSON.parse(local);
        // Migration: Ensure jobs array exists for existing data
        Object.values(memCache.tenants).forEach(tenant => {
            if (!tenant.jobs) tenant.jobs = [];
        });
        return;
    }

    // Seed with initial data if no local storage is found
    const defaultTenantId = 'tenant_default_corp';
    memCache = {
        system: {
            users: [
                { id: 'user_sys_admin', email: 'sysadmin@corp.com', passwordHash: 'password', role: UserRole.SYSTEM_ADMIN },
                { id: 'user_tenant_admin_1', email: 'admin@defaultcorp.com', passwordHash: 'password', role: UserRole.TENANT_ADMIN, tenantId: defaultTenantId }
            ],
            tenants: [
                { id: defaultTenantId, name: 'Default Corp' }
            ]
        },
        tenants: {
            [defaultTenantId]: {
                employees: [],
                teams: [],
                messages: [],
                unassignedMessages: [],
                jobs: [],
                config: DEFAULT_CONFIG
            }
        }
    };
    persistLocal();
};

// --- Authentication & System Admin ---
export const authenticate = (email: string, pass: string): User | null => {
    const user = memCache.system.users.find(u => u.email.toLowerCase() === email.toLowerCase());
    // In a real app, compare hashed passwords
    if (user && user.passwordHash === pass) {
        return user;
    }
    return null;
};

export const getTenants = (): Tenant[] => memCache.system.tenants;

export const createTenant = (name: string): Tenant => {
    const newTenantId = `tenant_${Date.now()}`;
    const newTenant: Tenant = { id: newTenantId, name };
    memCache.system.tenants.push(newTenant);
    memCache.tenants[newTenantId] = {
        employees: [],
        teams: [],
        messages: [],
        unassignedMessages: [],
        jobs: [],
        config: DEFAULT_CONFIG
    };
    persistLocal();
    return newTenant;
};


// --- Tenant-Specific Data Access ---
export const getTenantData = (tenantId: string): TenantData => {
    if (!memCache.tenants[tenantId]) {
        throw new Error(`Tenant with ID ${tenantId} not found.`);
    }
    return memCache.tenants[tenantId];
};

export const saveTenantData = (tenantId: string, data: Partial<TenantData>): void => {
    if (!memCache.tenants[tenantId]) {
        throw new Error(`Tenant with ID ${tenantId} not found.`);
    }
    memCache.tenants[tenantId] = {
        ...memCache.tenants[tenantId],
        ...data
    };
    persistLocal();
};

export const saveMessage = (tenantId: string, msg: ChatMessage) => {
    const tenantData = getTenantData(tenantId);
    tenantData.messages.push(msg);
    saveTenantData(tenantId, { messages: tenantData.messages });
    return tenantData.messages;
};

// --- Job Management ---
export const saveJob = (tenantId: string, job: SystemJob) => {
    const tenantData = getTenantData(tenantId);
    if (!tenantData.jobs) tenantData.jobs = []; // Safety check
    tenantData.jobs.unshift(job); // Add to beginning
    saveTenantData(tenantId, { jobs: tenantData.jobs });
    return tenantData.jobs;
};

export const updateJob = (tenantId: string, jobId: string, updates: Partial<SystemJob>) => {
    const tenantData = getTenantData(tenantId);
    if (!tenantData.jobs) return;
    
    tenantData.jobs = tenantData.jobs.map(job => 
        job.id === jobId ? { ...job, ...updates } : job
    );
    saveTenantData(tenantId, { jobs: tenantData.jobs });
};


export const assignSourceId = (tenantId: string, unassignedMsgId: string, employeeId: string) => {
    const tenantData = getTenantData(tenantId);
    const msgIndex = tenantData.unassignedMessages.findIndex(m => m.id === unassignedMsgId);
    if (msgIndex === -1) return tenantData;

    const msg = tenantData.unassignedMessages[msgIndex];
    const employee = tenantData.employees.find(e => e.id === employeeId);
    
    if (employee) {
        if (msg.channel === 'telegram') {
            employee.telegramChatId = msg.sourceId;
        }
        
        tenantData.messages.push({
            id: `msg_conv_${Date.now()}`,
            tenantId: tenantId,
            employeeId: employee.id,
            sender: 'employee',
            channel: msg.channel,
            text: msg.text,
            subject: msg.subject,
            timestamp: msg.timestamp
        });

        tenantData.unassignedMessages.splice(msgIndex, 1);
        saveTenantData(tenantId, tenantData);
    }
    return tenantData;
};

// Initialize on load
initializeStorage();