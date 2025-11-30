


import React, { useState, useEffect } from 'react';
import { RefreshCcw, Save, MessageCircle, CheckCircle2, AlertTriangle, Server, GitBranch, Mail, Lock, Radio } from 'lucide-react';
import { refreshWebhookConfiguration, deleteTelegramWebhook } from '../services/telegramService';
import { getStoredData, saveData } from '../services/storage';
import { AppConfig } from '../types';

interface SettingsProps {
    onConfigChange: (config: AppConfig) => void;
}

const Settings: React.FC<SettingsProps> = ({ onConfigChange }) => {
    const [config, setConfig] = useState(getStoredData().config);
    const [isUpdatingWebhook, setIsUpdatingWebhook] = useState(false);
    const [statusMsg, setStatusMsg] = useState<{type: 'success' | 'error', text: string} | null>(null);
    
    // Initial load
    useEffect(() => {
        setConfig(getStoredData().config);
    }, []);

    const handleSaveConfig = () => {
        const currentData = getStoredData();
        const updatedConfig = { ...currentData.config, ...config }; // Ensure all config fields are updated
        saveData({ ...currentData, config: updatedConfig });
        onConfigChange(updatedConfig); // Notify parent component (App.tsx) about config change
        setStatusMsg({ type: 'success', text: "Configuration saved." });
        setTimeout(() => setStatusMsg(null), 3000);
    };

    const handleWebhookUpdate = async () => {
        if (!config.webhookUrl || !config.telegramBotToken) {
            setStatusMsg({ type: 'error', text: "Webhook URL and Telegram Bot Token are required." });
            return;
        }
        
        setIsUpdatingWebhook(true);
        setStatusMsg(null);

        // Strict Validation for Real URLs
        try {
            const urlObj = new URL(config.webhookUrl);
            if (urlObj.protocol !== 'https:') {
                 throw new Error("Security Alert: Webhook URL must use HTTPS.");
            }
        } catch (e: any) {
             setStatusMsg({ type: 'error', text: "Invalid Webhook URL: " + e.message });
             setIsUpdatingWebhook(false);
             return;
        }

        const result = await refreshWebhookConfiguration(config.webhookUrl, config.telegramBotToken);
        
        setStatusMsg({
            type: result.success ? 'success' : 'error',
            text: result.message
        });
        setIsUpdatingWebhook(false);
    };

    const handleTelegramModeChange = async (mode: 'webhook' | 'polling') => {
        const currentData = getStoredData();
        const updatedConfig = { ...config, telegramMode: mode };
        setConfig(updatedConfig);
        saveData({ ...currentData, config: updatedConfig }); // Persist immediately
        onConfigChange(updatedConfig); // Notify App.tsx

        if (mode === 'polling') {
            // If switching to polling, ensure any active webhook is deleted
            if (config.telegramBotToken) {
                setStatusMsg({ type: 'success', text: "Switching to Telegram Polling mode. Attempting to delete webhook..." });
                setIsUpdatingWebhook(true); // Reuse loading state
                try {
                    await deleteTelegramWebhook(config.telegramBotToken);
                    setStatusMsg({ type: 'success', text: "Telegram webhook deleted. Now in Polling mode." });
                } catch (e: any) {
                    setStatusMsg({ type: 'error', text: "Failed to delete old webhook: " + e.message });
                } finally {
                    setIsUpdatingWebhook(false);
                }
            } else {
                 setStatusMsg({ type: 'success', text: "Switched to Telegram Polling mode." });
            }
        } else {
            setStatusMsg({ type: 'success', text: "Switched to Telegram Webhook mode." });
        }
        setTimeout(() => setStatusMsg(null), 3000);
    };


    return (
        <div className="space-y-6 pb-10">
            <header className="flex justify-between items-center">
                 <div>
                    <h2 className="text-2xl font-bold text-gray-800">System Configuration</h2>
                    <p className="text-gray-500">Manage integrations, keys, and backend communication.</p>
                 </div>
                 <button 
                    onClick={handleSaveConfig}
                    className="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 shadow-lg shadow-blue-500/30"
                 >
                     <Save size={18} />
                     <span>Save All Changes</span>
                 </button>
            </header>

            {statusMsg && (
                <div className={`p-4 rounded-lg flex items-center gap-2 ${statusMsg.type === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                    {statusMsg.type === 'success' ? <CheckCircle2/> : <AlertTriangle/>}
                    {statusMsg.text}
                </div>
            )}

            <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                
                {/* Telegram Configuration */}
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 col-span-1 lg:col-span-2">
                    <div className="flex items-center space-x-3 mb-6 border-b border-gray-100 pb-4">
                        <div className="p-2 bg-blue-100 text-blue-600 rounded-lg">
                            <MessageCircle size={24} />
                        </div>
                        <div>
                            <h3 className="font-bold text-gray-800">Telegram Configuration</h3>
                            <p className="text-xs text-gray-500">Configure Telegram Bot Token and webhook settings.</p>
                        </div>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div className="space-y-4">
                            {/* Removed Gemini API Key input */}
                            {/* <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Gemini API Key</label>
                                <input 
                                    type="password" 
                                    className="w-full border border-gray-200 p-2.5 rounded-lg bg-gray-50 focus:bg-white outline-none"
                                    value={config.geminiApiKey}
                                    onChange={(e) => setConfig({...config, geminiApiKey: e.target.value})}
                                    placeholder="AI..."
                                />
                            </div> */}
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Telegram Bot API Token</label>
                                <input 
                                    type="password" 
                                    className="w-full border border-gray-200 p-2.5 rounded-lg bg-gray-50 focus:bg-white outline-none"
                                    value={config.telegramBotToken}
                                    onChange={(e) => setConfig({...config, telegramBotToken: e.target.value})}
                                />
                            </div>
                            <div className="space-y-2">
                                <label className="block text-sm font-medium text-gray-700">Telegram Integration Mode</label>
                                <div className="flex items-center space-x-4">
                                    <label className="inline-flex items-center">
                                        <input
                                            type="radio"
                                            className="form-radio text-blue-600"
                                            name="telegramMode"
                                            value="webhook"
                                            checked={config.telegramMode === 'webhook'}
                                            onChange={() => handleTelegramModeChange('webhook')}
                                        />
                                        <span className="ml-2 text-sm text-gray-700">Webhook</span>
                                    </label>
                                    <label className="inline-flex items-center">
                                        <input
                                            type="radio"
                                            className="form-radio text-blue-600"
                                            name="telegramMode"
                                            value="polling"
                                            checked={config.telegramMode === 'polling'}
                                            onChange={() => handleTelegramModeChange('polling')}
                                        />
                                        <span className="ml-2 text-sm text-gray-700">Polling</span>
                                    </label>
                                </div>
                                <p className="text-xs text-gray-400 mt-1">
                                    Webhook mode requires a publicly accessible URL for Telegram to send updates. Polling mode retrieves updates directly.
                                </p>
                            </div>
                        </div>
                        <div className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Backend API / Webhook URL</label>
                                <input 
                                    type="text" 
                                    className="w-full border border-gray-200 p-2.5 rounded-lg bg-gray-50 focus:bg-white outline-none font-mono text-sm"
                                    value={config.webhookUrl}
                                    onChange={(e) => setConfig({...config, webhookUrl: e.target.value})}
                                    placeholder="https://api.your-backend.com"
                                />
                                <p className="text-xs text-gray-400 mt-1">This URL is used for Telegram webhook (if enabled) and general data synchronization.</p>
                            </div>
                            {config.telegramMode === 'webhook' && (
                                <button 
                                    onClick={handleWebhookUpdate}
                                    disabled={isUpdatingWebhook}
                                    className={`w-full py-2 rounded-lg font-medium text-white flex justify-center items-center space-x-2 transition-all
                                        ${isUpdatingWebhook ? 'bg-slate-400' : 'bg-slate-800 hover:bg-slate-900'}
                                    `}
                                >
                                    {isUpdatingWebhook ? <RefreshCcw size={18} className="animate-spin" /> : <RefreshCcw size={18} />}
                                    <span>Update Telegram Webhook</span>
                                </button>
                            )}
                        </div>
                    </div>
                </div>

                {/* Email Service (IMAP/SMTP) */}
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div className="flex items-center space-x-3 mb-6 border-b border-gray-100 pb-4">
                        <div className="p-2 bg-yellow-100 text-yellow-600 rounded-lg">
                            <Mail size={24} />
                        </div>
                        <div>
                            <h3 className="font-bold text-gray-800">Email Gateway</h3>
                            <p className="text-xs text-gray-500">IMAP/SMTP Configuration for Inbox</p>
                        </div>
                    </div>

                    <div className="space-y-4 grid grid-cols-2 gap-4">
                         <div className="col-span-2">
                            <label className="block text-sm font-medium text-gray-700 mb-1">IMAP Host</label>
                            <input 
                                type="text" 
                                className="w-full border border-gray-200 p-2.5 rounded-lg bg-gray-50 focus:bg-white outline-none"
                                value={config.emailService.imapHost}
                                onChange={(e) => setConfig({...config, emailService: { ...config.emailService, imapHost: e.target.value}})}
                                placeholder="imap.example.com"
                            />
                        </div>
                        <div>
                             <label className="block text-sm font-medium text-gray-700 mb-1">Username</label>
                             <input 
                                type="text" 
                                className="w-full border border-gray-200 p-2.5 rounded-lg bg-gray-50 focus:bg-white outline-none"
                                value={config.emailService.imapUser}
                                onChange={(e) => setConfig({...config, emailService: { ...config.emailService, imapUser: e.target.value}})}
                                placeholder="hr-assistant@example.com"
                            />
                        </div>
                         <div>
                             <label className="block text-sm font-medium text-gray-700 mb-1">Password</label>
                             <input 
                                type="password" 
                                className="w-full border border-gray-200 p-2.5 rounded-lg bg-gray-50 focus:bg-white outline-none"
                                value={config.emailService.imapPass}
                                onChange={(e) => setConfig({...config, emailService: { ...config.emailService, imapPass: e.target.value}})}
                            />
                        </div>
                         <div className="col-span-2">
                             <label className="block text-sm font-medium text-gray-700 mb-1">SMTP Host</label>
                             <input 
                                type="text" 
                                className="w-full border border-gray-200 p-2.5 rounded-lg bg-gray-50 focus:bg-white outline-none"
                                value={config.emailService.smtpHost}
                                onChange={(e) => setConfig({...config, emailService: { ...config.emailService, smtpHost: e.target.value}})}
                                placeholder="smtp.example.com"
                            />
                        </div>
                    </div>
                </div>

                {/* Mailcow Config */}
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div className="flex items-center space-x-3 mb-6 border-b border-gray-100 pb-4">
                        <div className="p-2 bg-orange-100 text-orange-600 rounded-lg">
                            <Server size={24} />
                        </div>
                        <div>
                            <h3 className="font-bold text-gray-800">Mail Service API</h3>
                            <p className="text-xs text-gray-500">Admin Management</p>
                        </div>
                    </div>

                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Mail Service UI URL</label>
                            <input 
                                type="text" 
                                className="w-full border border-gray-200 p-2.5 rounded-lg bg-gray-50 focus:bg-white outline-none"
                                value={config.mailcow.url}
                                onChange={(e) => setConfig({...config, mailcow: { ...config.mailcow, url: e.target.value}})}
                                placeholder="https://mail.example.com"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">API Key</label>
                            <input 
                                type="password" 
                                className="w-full border border-gray-200 p-2.5 rounded-lg bg-gray-50 focus:bg-white outline-none"
                                value={config.mailcow.apiKey}
                                onChange={(e) => setConfig({...config, mailcow: { ...config.mailcow, apiKey: e.target.value}})}
                            />
                        </div>
                    </div>
                </div>

                {/* Keycloak Config */}
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div className="flex items-center space-x-3 mb-6 border-b border-gray-100 pb-4">
                        <div className="p-2 bg-indigo-100 text-indigo-600 rounded-lg">
                            <Lock size={24} />
                        </div>
                        <div>
                            <h3 className="font-bold text-gray-800">Keycloak IAM</h3>
                            <p className="text-xs text-gray-500">Auth Server API</p>
                        </div>
                    </div>

                    <div className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Keycloak Base URL</label>
                            <input 
                                type="text" 
                                className="w-full border border-gray-200 p-2.5 rounded-lg bg-gray-50 focus:bg-white outline-none"
                                value={config.keycloak.url}
                                onChange={(e) => setConfig({...config, keycloak: { ...config.keycloak, url: e.target.value}})}
                                placeholder="https://auth.example.com"
                            />
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Realm</label>
                                <input 
                                    type="text" 
                                    className="w-full border border-gray-200 p-2.5 rounded-lg bg-gray-50 focus:bg-white outline-none"
                                    value={config.keycloak.realm}
                                    onChange={(e) => setConfig({...config, keycloak: { ...config.keycloak, realm: e.target.value}})}
                                    placeholder="hr-assistant-realm"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">Client ID</label>
                                <input 
                                    type="text" 
                                    className="w-full border border-gray-200 p-2.5 rounded-lg bg-gray-50 focus:bg-white outline-none"
                                    value={config.keycloak.clientId}
                                    onChange={(e) => setConfig({...config, keycloak: { ...config.keycloak, clientId: e.target.value}})}
                                    placeholder="hr-assistant-client"
                                />
                            </div>
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">Client Secret</label>
                            <input 
                                type="password" 
                                className="w-full border border-gray-200 p-2.5 rounded-lg bg-gray-50 focus:bg-white outline-none"
                                value={config.keycloak.clientSecret}
                                onChange={(e) => setConfig({...config, keycloak: { ...config.keycloak, clientSecret: e.target.value}})}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Settings;