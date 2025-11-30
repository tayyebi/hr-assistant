

import { AppConfig, UnassignedMessage } from "../types";

// In a Browser-based environment (SPA), we cannot access raw TCP ports for IMAP (993) or SMTP (587).
// This service acts as an abstraction layer.
// In a real production deployment, this would call your Backend API (the Webhook URL) 
// which would perform the actual IMAP polling.

export const pollImapServer = async (imapConfig: AppConfig['emailService']['imap'], tenantId: string): Promise<UnassignedMessage[]> => {
    console.log(`[IMAP Service] Connecting to ${imapConfig.host}:${imapConfig.port} as ${imapConfig.user} (TLS: ${imapConfig.tls})...`);
    // Simulate latency
    await new Promise(r => setTimeout(r, 2000));
    
    // Randomly generate an incoming email 30% of the time for demo purposes
    if (Math.random() > 0.7) {
        const id = Date.now().toString();
        // FIX: Add tenantId to the UnassignedMessage object to conform to the type.
        return [{
            id: `email_${id}`,
            tenantId,
            channel: 'email',
            sourceId: `candidate_${id.slice(-4)}@example.com`,
            senderName: `Candidate ${id.slice(-4)}`,
            subject: 'Question about HR Policy',
            text: 'Hello, I wanted to ask about the remote work policy mentioned in the handbook. Thanks.',
            timestamp: new Date().toISOString()
        }];
    }
    
    return [];
};

export const sendEmail = async (to: string, subject: string, body: string, smtpConfig: AppConfig['emailService']['smtp']): Promise<boolean> => {
    console.log(`[SMTP Service] Connecting to ${smtpConfig.host}:${smtpConfig.port} (TLS: ${smtpConfig.tls})...`);
    console.log(`[SMTP Service] Sending to: ${to}`);
    console.log(`[SMTP Service] Subject: ${subject}`);
    
    await new Promise(r => setTimeout(r, 1500));
    return true;
};