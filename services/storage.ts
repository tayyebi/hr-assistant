

import { Employee, Team, ChatMessage, Feeling, ServiceType, UnassignedMessage, AppConfig } from '../types';

// Local Fallback / Cache
const STORAGE_KEY = 'hr_assistant_db_cache';

const DEFAULT_CONFIG: AppConfig = {
    telegramBotToken: '',
    telegramMode: 'webhook', // New: Default to webhook
    webhookUrl: '', // This will now also serve as the backend API for data storage
    mailcow: { url: 'https://mail.example.com', apiKey: '' },
    gitlab: { url: 'https://gitlab.example.com', token: '' },
    keycloak: { url: 'https://auth.example.com', realm: 'hr-assistant-realm', clientId: 'hr-assistant-client', clientSecret: '' },
    emailService: { imapHost: 'imap.example.com', imapUser: 'hr-assistant@example.com', imapPass: '', smtpHost: 'smtp.example.com' }
};

interface StorageData {
    employees: Employee[];
    teams: Team[];
    messages: ChatMessage[];
    unassignedMessages: UnassignedMessage[];
    config: AppConfig;
}

// In-memory cache
let memCache: StorageData = {
    employees: [],
    teams: [],
    messages: [],
    unassignedMessages: [],
    config: DEFAULT_CONFIG
};

// Initialize: Load from LocalStorage first, then try Backend API if configured
export const initializeStorage = async (): Promise<StorageData> => {
    const local = localStorage.getItem(STORAGE_KEY);
    if (local) {
        memCache = JSON.parse(local);
        // Merge defaults in case of new config fields
        memCache.config = { ...DEFAULT_CONFIG, ...memCache.config };
    }

    // If we have a webhook URL, try to sync from the conceptual backend
    if (memCache.config.webhookUrl) {
        try {
            console.log("Syncing from Backend API...");
            const response = await fetch(`${memCache.config.webhookUrl}/data`);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const backendData: StorageData = await response.json();

            // Update cache if backend has data
            if (backendData.employees || backendData.teams || backendData.messages || backendData.unassignedMessages) {
                memCache.employees = backendData.employees || [];
                memCache.teams = backendData.teams || [];
                memCache.messages = backendData.messages || [];
                memCache.unassignedMessages = backendData.unassignedMessages || [];
                // config should not be overwritten from backend, it's client-managed
                persistLocal();
            }
            console.log("Synced from Backend API.");
        } catch (e) {
            console.error("Backend Sync Failed:", e);
            console.warn("Falling back to local storage data.");
        }
    }
    
    return memCache;
};

// Sync Accessor
export const getStoredData = (): StorageData => {
    return memCache;
};

// Save: Updates Local + Async Push to Backend API
export const saveData = async (data: StorageData) => {
    memCache = data;
    persistLocal();

    // Async Backend API Push
    if (memCache.config.webhookUrl) {
        try {
            const response = await fetch(`${memCache.config.webhookUrl}/data`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    employees: memCache.employees,
                    teams: memCache.teams,
                    messages: memCache.messages,
                    unassignedMessages: memCache.unassignedMessages,
                }), // Only send relevant data, not the whole config
            });
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            console.log("Synced to Backend API.");
        } catch (e) {
            console.error("Failed to sync to Backend API", e);
        }
    }
};

const persistLocal = () => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(memCache));
};

export const saveMessage = (msg: ChatMessage) => {
    memCache.messages.push(msg);
    saveData(memCache); // Triggers cloud sync
    return memCache.messages;
};

// Modified to handle both Telegram IDs and Emails
export const assignSourceId = (unassignedMsgId: string, employeeId: string) => {
    const msgIndex = memCache.unassignedMessages.findIndex(m => m.id === unassignedMsgId);
    if (msgIndex === -1) return;

    const msg = memCache.unassignedMessages[msgIndex];
    const employee = memCache.employees.find(e => e.id === employeeId);
    
    if (employee) {
        // Update contact info based on channel
        if (msg.channel === 'telegram') {
            employee.telegramChatId = msg.sourceId;
        } else if (msg.channel === 'email') {
            // Usually we don't overwrite email, but we might verify it here or add to secondary emails
            // For now, we assume this confirms the employee is the owner of this source
        }
        
        memCache.messages.push({
            id: `msg_conv_${Date.now()}`,
            employeeId: employee.id,
            sender: 'employee',
            channel: msg.channel,
            text: msg.text,
            subject: msg.subject,
            timestamp: msg.timestamp
        });

        memCache.unassignedMessages.splice(msgIndex, 1);
        saveData(memCache);
    }
    return memCache;
};