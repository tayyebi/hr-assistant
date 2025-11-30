

import { UnassignedMessage } from "../types";

// In a Browser-based environment (SPA), we cannot access raw TCP ports for IMAP (993) or SMTP (587).
// This service acts as an abstraction layer.
// In a real production deployment, this would call your Backend API (the Webhook URL) 
// which would perform the actual IMAP polling.

export const pollImapServer = async (host: string, user: string, pass: string): Promise<UnassignedMessage[]> => {
    console.log(`[IMAP Service] Connecting to ${host} as ${user}...`);
    // Simulate latency
    await new Promise(r => setTimeout(r, 2000));
    
    // Randomly generate an incoming email 30% of the time for demo purposes
    if (Math.random() > 0.7) {
        const id = Date.now().toString();
        return [{
            id: `email_${id}`,
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

export const sendEmail = async (to: string, subject: string, body: string, config: any): Promise<boolean> => {
    console.log(`[SMTP Service] Connecting to ${config.smtpHost}...`);
    console.log(`[SMTP Service] Sending to: ${to}`);
    console.log(`[SMTP Service] Subject: ${subject}`);
    
    await new Promise(r => setTimeout(r, 1500));
    return true;
};