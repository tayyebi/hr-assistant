

import React, { useState, useEffect } from 'react';
import { RefreshCcw, Save, MessageCircle, CheckCircle2, AlertTriangle, Server, GitBranch, Mail, Lock, Radio } from 'lucide-react';
import { refreshWebhookConfiguration, deleteTelegramWebhook } from '../services/telegramService';
import { AppConfig } from '../types';

interface SettingsProps {
    config: AppConfig;
    onConfigChange: (config: AppConfig) => void;
}

const Settings: React.FC<SettingsProps> = ({ config: initialConfig, onConfigChange }) => {
    const [config, setConfig] = useState(initialConfig);
    const [isUpdatingWebhook, setIsUpdatingWebhook] = useState(false);
    const [statusMsg, setStatusMsg] = useState<{type: 'success' | 'error', text: string} | null>(null);
    
    useEffect(() => {
        setConfig(initialConfig);
    }, [initialConfig]);

    const handleSaveConfig = () => {
        onConfigChange(config);
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

        try {
            new URL(config.webhookUrl);
        } catch (e) {
             setStatusMsg({ type: 'error', text: "Invalid Webhook URL." });
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
        const updatedConfig = { ...config, telegramMode: mode };
        setConfig(updatedConfig);
        onConfigChange(updatedConfig); // Persist immediately

        if (mode === 'polling') {
            if (config.telegramBotToken) {
                setStatusMsg({ type: 'success', text: "Switching to Polling. Deleting webhook..." });
                setIsUpdatingWebhook(true);
                try {
                    await deleteTelegramWebhook(config.telegramBotToken);
                    setStatusMsg({ type: 'success', text: "Webhook deleted. Now in Polling mode." });
                } catch (e: any) {
                    setStatusMsg({ type: 'error', text: "Failed to delete webhook: " + e.message });
                } finally {
                    setIsUpdatingWebhook(false);
                }
            } else {
                 setStatusMsg({ type: 'success', text: "Switched to Polling mode." });
            }
        } else {
            setStatusMsg({ type: 'success', text: "Switched to Webhook mode." });
        }
        setTimeout(() => setStatusMsg(null), 4000);
    };


    return (
        <div className="space-y-6 pb-10">
            <header className="flex justify-between items-center">
                 <div>
                    <h2 className="text-2xl font-bold text-gray-800">System Configuration</h2>
                    <p className="text-gray-500">Manage integrations, keys, and backend communication for this tenant.</p>
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
                
                <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 col-span-1 lg:col-span-2">
                    <div className="flex items-center space-x-3 mb-6 border-b border-gray-100 pb-4">
                        <div className="p-2 bg-blue-100 text-blue-600 rounded-lg">
                            <MessageCircle size={24} />
                        </div>
                        <div>
                            <h3 className="font-bold text-gray-800">Telegram & Backend</h3>
                            <p className="text-xs text-gray-500">Configure bot tokens and webhook settings.</p>
                        </div>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div className="space-y-4">
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
                                <label className="block text-sm font-medium text-gray-700">Telegram Mode</label>
                                <div className="flex items-center space-x-4">
                                    <label className="inline-flex items-center"><input type="radio" className="form-radio text-blue-600" name="telegramMode" value="webhook" checked={config.telegramMode === 'webhook'} onChange={() => handleTelegramModeChange('webhook')}/> <span className="ml-2 text-sm">Webhook</span></label>
                                    <label className="inline-flex items-center"><input type="radio" className="form-radio text-blue-600" name="telegramMode" value="polling" checked={config.telegramMode === 'polling'} onChange={() => handleTelegramModeChange('polling')}/> <span className="ml-2 text-sm">Polling</span></label>
                                </div>
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
                                <p className="text-xs text-gray-400 mt-1">Used for Telegram webhook and data sync.</p>
                            </div>
                            {config.telegramMode === 'webhook' && (
                                <button onClick={handleWebhookUpdate} disabled={isUpdatingWebhook} className={`w-full py-2 rounded-lg font-medium text-white flex justify-center items-center space-x-2 transition-all ${isUpdatingWebhook ? 'bg-slate-400' : 'bg-slate-800 hover:bg-slate-900'}`}>
                                    {isUpdatingWebhook ? <RefreshCcw size={18} className="animate-spin" /> : <RefreshCcw size={18} />}
                                    <span>Update Telegram Webhook</span>
                                </button>
                            )}
                        </div>
                    </div>
                </div>

                <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 col-span-1 lg:col-span-2">
                    <div className="flex items-center space-x-3 mb-4 border-b border-gray-100 pb-4">
                        <div className="p-2 bg-yellow-100 text-yellow-600 rounded-lg"><Mail size={24} /></div>
                        <div>
                            <h3 className="font-bold text-gray-800">Email Gateway</h3>
                            <p className="text-xs text-gray-500">IMAP/SMTP for Inbox & Outbox</p>
                        </div>
                    </div>
                    <div className="space-y-6">
                        <div>
                            <h4 className="font-semibold text-gray-700 border-b border-gray-200 pb-1 mb-3">IMAP (Incoming)</h4>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div className="grid grid-cols-3 gap-2 md:col-span-2">
                                    <div className="col-span-2"><label className="block text-sm font-medium text-gray-700 mb-1">Host</label><input type="text" className="w-full border border-gray-200 p-2 rounded-lg bg-gray-50" value={config.emailService.imap.host} onChange={(e) => setConfig({...config, emailService: { ...config.emailService, imap: { ...config.emailService.imap, host: e.target.value}}})} /></div>
                                    <div><label className="block text-sm font-medium text-gray-700 mb-1">Port</label><input type="number" className="w-full border border-gray-200 p-2 rounded-lg bg-gray-50" value={config.emailService.imap.port} onChange={(e) => setConfig({...config, emailService: { ...config.emailService, imap: { ...config.emailService.imap, port: parseInt(e.target.value, 10) || 0}}})} /></div>
                                </div>
                                <div><label className="block text-sm font-medium text-gray-700 mb-1">Username</label><input type="text" className="w-full border border-gray-200 p-2 rounded-lg bg-gray-50" value={config.emailService.imap.user} onChange={(e) => setConfig({...config, emailService: { ...config.emailService, imap: { ...config.emailService.imap, user: e.target.value}}})} /></div>
                                <div><label className="block text-sm font-medium text-gray-700 mb-1">Password</label><input type="password" className="w-full border border-gray-200 p-2 rounded-lg bg-gray-50" value={config.emailService.imap.pass} onChange={(e) => setConfig({...config, emailService: { ...config.emailService, imap: { ...config.emailService.imap, pass: e.target.value}}})} /></div>
                                <div className="md:col-span-2"><label className="flex items-center space-x-2 text-sm"><input type="checkbox" className="form-checkbox h-4 w-4 text-blue-600" checked={config.emailService.imap.tls} onChange={(e) => setConfig({...config, emailService: { ...config.emailService, imap: { ...config.emailService.imap, tls: e.target.checked}}})} /><span>Use TLS/SSL</span></label></div>
                            </div>
                        </div>
                        <div>
                            <h4 className="font-semibold text-gray-700 border-b border-gray-200 pb-1 mb-3">SMTP (Outgoing)</h4>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                               <div className="grid grid-cols-3 gap-2 md:col-span-2">
                                    <div className="col-span-2"><label className="block text-sm font-medium text-gray-700 mb-1">Host</label><input type="text" className="w-full border border-gray-200 p-2 rounded-lg bg-gray-50" value={config.emailService.smtp.host} onChange={(e) => setConfig({...config, emailService: { ...config.emailService, smtp: { ...config.emailService.smtp, host: e.target.value}}})} /></div>
                                    <div><label className="block text-sm font-medium text-gray-700 mb-1">Port</label><input type="number" className="w-full border border-gray-200 p-2 rounded-lg bg-gray-50" value={config.emailService.smtp.port} onChange={(e) => setConfig({...config, emailService: { ...config.emailService, smtp: { ...config.emailService.smtp, port: parseInt(e.target.value, 10) || 0}}})} /></div>
                                </div>
                                <div><label className="block text-sm font-medium text-gray-700 mb-1">Username</label><input type="text" className="w-full border border-gray-200 p-2 rounded-lg bg-gray-50" value={config.emailService.smtp.user} onChange={(e) => setConfig({...config, emailService: { ...config.emailService, smtp: { ...config.emailService.smtp, user: e.target.value}}})} /></div>
                                <div><label className="block text-sm font-medium text-gray-700 mb-1">Password</label><input type="password" className="w-full border border-gray-200 p-2 rounded-lg bg-gray-50" value={config.emailService.smtp.pass} onChange={(e) => setConfig({...config, emailService: { ...config.emailService, smtp: { ...config.emailService.smtp, pass: e.target.value}}})} /></div>
                                <div className="md:col-span-2"><label className="flex items-center space-x-2 text-sm"><input type="checkbox" className="form-checkbox h-4 w-4 text-blue-600" checked={config.emailService.smtp.tls} onChange={(e) => setConfig({...config, emailService: { ...config.emailService, smtp: { ...config.emailService.smtp, tls: e.target.checked}}})} /><span>Use TLS/SSL</span></label></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div className="flex items-center space-x-3 mb-6 border-b pb-4"><div className="p-2 bg-orange-100 text-orange-600 rounded-lg"><Server size={24} /></div><div><h3 className="font-bold">Mail Service API</h3><p className="text-xs text-gray-500">Admin Management</p></div></div>
                    <div className="space-y-4">
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">URL</label><input type="text" className="w-full border p-2.5 rounded-lg bg-gray-50" value={config.mailcow.url} onChange={(e) => setConfig({...config, mailcow: { ...config.mailcow, url: e.target.value}})} /></div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">API Key</label><input type="password" className="w-full border p-2.5 rounded-lg bg-gray-50" value={config.mailcow.apiKey} onChange={(e) => setConfig({...config, mailcow: { ...config.mailcow, apiKey: e.target.value}})} /></div>
                    </div>
                </div>

                <div className="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                     <div className="flex items-center space-x-3 mb-6 border-b pb-4"><div className="p-2 bg-indigo-100 text-indigo-600 rounded-lg"><Lock size={24} /></div><div><h3 className="font-bold">Keycloak IAM</h3><p className="text-xs text-gray-500">Auth Server API</p></div></div>
                    <div className="space-y-4">
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Base URL</label><input type="text" className="w-full border p-2.5 rounded-lg bg-gray-50" value={config.keycloak.url} onChange={(e) => setConfig({...config, keycloak: { ...config.keycloak, url: e.target.value}})} /></div>
                        <div className="grid grid-cols-2 gap-4">
                            <div><label className="block text-sm font-medium mb-1">Realm</label><input type="text" className="w-full border p-2.5 rounded-lg bg-gray-50" value={config.keycloak.realm} onChange={(e) => setConfig({...config, keycloak: { ...config.keycloak, realm: e.target.value}})} /></div>
                            <div><label className="block text-sm font-medium mb-1">Client ID</label><input type="text" className="w-full border p-2.5 rounded-lg bg-gray-50" value={config.keycloak.clientId} onChange={(e) => setConfig({...config, keycloak: { ...config.keycloak, clientId: e.target.value}})} /></div>
                        </div>
                        <div><label className="block text-sm font-medium text-gray-700 mb-1">Client Secret</label><input type="password" className="w-full border p-2.5 rounded-lg bg-gray-50" value={config.keycloak.clientSecret} onChange={(e) => setConfig({...config, keycloak: { ...config.keycloak, clientSecret: e.target.value}})} /></div>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Settings;