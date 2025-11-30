
import { ChatMessage, TelegramUpdate, UnassignedMessage } from "../types";

// Mock Service for Telegram API interactions
// In a real application, these would use fetch() to hit the Telegram Bot API endpoints

// Simulate the last update ID received for polling
let lastUpdateId = 0;

export const setTelegramWebhook = async (webhookUrl: string, botToken: string): Promise<boolean> => {
    console.log(`[Telegram API] Setting webhook to: ${webhookUrl}`);
    // Simulate API latency
    await new Promise(resolve => setTimeout(resolve, 1500));
    
    if (!webhookUrl.startsWith("https://")) {
        throw new Error("Webhook URL must start with https://");
    }

    // Mock successful response
    return true;
};

export const deleteTelegramWebhook = async (botToken: string): Promise<boolean> => {
    console.log(`[Telegram API] Deleting previous webhook...`);
    await new Promise(resolve => setTimeout(resolve, 800));
    return true;
};

export const sendTelegramMessage = async (chatId: string, text: string): Promise<boolean> => {
    console.log(`[Telegram API] Sending message to ${chatId}: ${text}`);
    await new Promise(resolve => setTimeout(resolve, 600));
    return true;
};

/**
 * Simulates polling for new Telegram updates.
 * In a real scenario, this would involve a GET request to `https://api.telegram.org/bot<token>/getUpdates?offset=<offset>`.
 */
export const pollTelegramUpdates = async (botToken: string): Promise<UnassignedMessage[]> => {
    console.log(`[Telegram API] Polling for updates with offset ${lastUpdateId + 1}...`);
    await new Promise(r => setTimeout(r, 2000)); // Simulate latency

    const newMessages: UnassignedMessage[] = [];

    // Simulate new update if conditions are met
    if (Math.random() > 0.6) { // 40% chance of a new message
        const currentTimestamp = Math.floor(Date.now() / 1000);
        const senderId = Math.floor(100000000 + Math.random() * 900000000);
        const mockUpdate: TelegramUpdate = {
            update_id: lastUpdateId + 1,
            message: {
                message_id: Math.floor(Math.random() * 100000),
                from: {
                    id: senderId,
                    is_bot: false,
                    first_name: `TelegramUser${senderId.toString().slice(-4)}`,
                    username: `user_${senderId.toString().slice(-4)}`
                },
                chat: {
                    id: senderId, // Chat ID is usually sender's ID for private chats
                    first_name: `TelegramUser${senderId.toString().slice(-4)}`,
                    type: 'private'
                },
                date: currentTimestamp,
                text: "Hello, I have a question about my vacation days."
            }
        };

        lastUpdateId = mockUpdate.update_id; // Update last processed ID
        
        newMessages.push({
            id: `telegram_${mockUpdate.update_id}`,
            channel: 'telegram',
            sourceId: mockUpdate.message.chat.id.toString(),
            senderName: mockUpdate.message.from.first_name + (mockUpdate.message.from.last_name ? ` ${mockUpdate.message.from.last_name}` : ''),
            text: mockUpdate.message.text,
            timestamp: new Date(mockUpdate.message.date * 1000).toISOString()
        });
    }

    console.log(`[Telegram API] Found ${newMessages.length} new updates.`);
    return newMessages;
};

// Orchestrator for the "Refresh Webhook" feature
export const refreshWebhookConfiguration = async (url: string, token: string) => {
    try {
        // Defensive: Always remove before setting to ensure clean state
        await deleteTelegramWebhook(token);
        await setTelegramWebhook(url, token);
        return { success: true, message: "Webhook updated successfully." };
    } catch (error: any) {
        return { success: false, message: error.message || "Failed to update webhook." };
    }
};