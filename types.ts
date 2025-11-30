

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
  name: string;
  description: string;
  memberIds: string[];
  emailAliases: string[];
}

export interface ChatMessage {
  id: string;
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
  telegramMode: 'webhook' | 'polling'; // New: Telegram interaction mode
  webhookUrl: string; // The URL of your deployed backend function
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
      imapHost: string;
      imapUser: string;
      imapPass: string;
      smtpHost: string;
  };
}