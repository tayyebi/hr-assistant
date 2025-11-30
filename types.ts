

export enum UserRole {
  SYSTEM_ADMIN = 'system_admin',
  TENANT_ADMIN = 'tenant_admin',
}

export interface User {
  id: string;
  email: string;
  passwordHash: string; // In a real app, this would be a proper hash.
  role: UserRole;
  tenantId?: string; // Only for tenant_admin
}

export interface Tenant {
  id: string;
  name: string;
}

export enum Feeling {
  SAD = 'sad',
  NEUTRAL = 'neutral',
  HAPPY = 'happy',
}

export enum ServiceType {
  MAILCOW = 'mailcow',
  GITLAB = 'gitlab',
  JIRA = 'jira',
  KEYCLOAK = 'keycloak',
}

export type Channel = 'telegram' | 'email';

export interface ServiceAccount {
  service: ServiceType;
  accountId: string; // e.g., email or username
  status: 'active' | 'suspended';
  metadata?: Record<string, any>; 
}

export interface Employee {
  id: string;
  tenantId: string;
  fullName: string;
  email: string; // Personal or work email
  telegramChatId?: string;
  birthday: string; // ISO Date
  hiredDate: string; // ISO Date
  position: string;
  teamId?: string;
  feelingsLog: { date: string; feeling: Feeling }[];
  accounts: ServiceAccount[]; 
}

export interface Team {
  id: string;
  tenantId: string;
  name: string;
  description: string;
  memberIds: string[];
  emailAliases: string[];
}

export interface ChatMessage {
  id: string;
  tenantId: string;
  employeeId: string;
  sender: 'hr' | 'employee';
  channel: Channel;
  text: string;
  subject?: string; // For emails
  timestamp: string;
}

// Messages from unknown sources
export interface UnassignedMessage {
    id: string;
    tenantId: string;
    channel: Channel;
    sourceId: string; // Telegram ChatID OR Email Address
    senderName: string;
    text: string;
    subject?: string;
    timestamp: string;
}

export interface TelegramUpdate {
    update_id: number;
    message: {
        message_id: number;
        from: {
            id: number;
            is_bot: boolean;
            first_name: string;
            last_name?: string;
            username?: string;
        };
        chat: {
            id: number;
            first_name: string;
            last_name?: string;
            username?: string;
            type: string;
        };
        date: number; // Unix timestamp
        text: string;
    };
}


export interface AppConfig {
  telegramBotToken: string;
  telegramMode: 'webhook' | 'polling';
  webhookUrl: string;
  mailcow: {
      url: string;
      apiKey: string;
  };
  gitlab: {
      url: string;
      token: string;
  };
  keycloak: {
      url: string;
      realm: string;
      clientId: string;
      clientSecret: string;
  };
  emailService: {
      imap: {
          host: string;
          port: number;
          tls: boolean;
          user: string;
          pass: string;
      };
      smtp: {
          host: string;
          port: number;
          tls: boolean;
          user: string;
          pass: string;
      };
  };
}

export interface SystemJob {
    id: string;
    tenantId: string;
    service: ServiceType;
    action: string; // 'PROVISION', 'DEACTIVATE', 'RESET_CREDENTIAL', etc.
    targetName: string; // Employee Name or Account ID for display
    status: 'pending' | 'processing' | 'completed' | 'failed';
    result?: string;
    createdAt: string;
    updatedAt: string;
    metadata?: any; // Payload required to retry/run the job
}