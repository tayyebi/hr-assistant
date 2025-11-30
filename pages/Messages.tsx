

import React, { useState, useEffect, useRef } from 'react';
import { Employee, ChatMessage, UnassignedMessage, Channel, AppConfig } from '../types';
import { Send, MessageCircle, UserPlus, Inbox, Hash, Mail, RefreshCw, Radio, Search, X } from 'lucide-react';
import { sendTelegramMessage, pollTelegramUpdates } from '../services/telegramService';
import { sendEmail, pollImapServer } from '../services/emailService';
import { assignSourceId } from '../services/storage';

interface MessagesProps {
    employees: Employee[];
    messages: ChatMessage[];
    unassigned: UnassignedMessage[];
    setUnassigned: (messages: UnassignedMessage[]) => void;
    onSendMessage: (msg: ChatMessage) => void;
    config: AppConfig;
    tenantId: string;
}

const Messages: React.FC<MessagesProps> = ({ employees, messages, unassigned, setUnassigned, onSendMessage, config, tenantId }) => {
    const [view, setView] = useState<'chats' | 'inbox'>('chats');
    const [selectedEmpId, setSelectedEmpId] = useState<string | null>(null);
    const [inputValue, setInputValue] = useState('');
    const [emailSubject, setEmailSubject] = useState('');
    const [isPollingEmail, setIsPollingEmail] = useState(false);
    const [isPollingTelegram, setIsPollingTelegram] = useState(false);
    const [isComposeModalOpen, setIsComposeModalOpen] = useState(false);
    const [composeSearchTerm, setComposeSearchTerm] = useState('');
    const [assignTarget, setAssignTarget] = useState<string>('');
    const messagesEndRef = useRef<HTMLDivElement>(null);
    
    const reachableEmployees = employees.filter(e => e.telegramChatId || e.email);
    const currentMessages = messages.filter(m => m.employeeId === selectedEmpId);
    const selectedEmployee = employees.find(e => e.id === selectedEmpId);

    const lastMsg = currentMessages[currentMessages.length - 1];
    const activeChannel: Channel = lastMsg?.channel || (selectedEmployee?.telegramChatId ? 'telegram' : 'email');

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
    };

    useEffect(scrollToBottom, [currentMessages, selectedEmpId]);

    const handleCheckMails = async () => {
        setIsPollingEmail(true);
        if (!config.emailService.imap.host) {
            alert("IMAP not configured.");
            setIsPollingEmail(false);
            return;
        }
        try {
            // FIX: Pass tenantId to the service function.
            const newMails = await pollImapServer(config.emailService.imap, tenantId);
            if (newMails.length > 0) {
                // FIX: No need to map and add tenantId, it's already present.
                setUnassigned([...unassigned, ...newMails]);
                alert(`Fetched ${newMails.length} new emails.`);
            } else {
                alert("No new emails.");
            }
        } finally {
            setIsPollingEmail(false);
        }
    };

    const handleCheckTelegram = async () => {
        setIsPollingTelegram(true);
        if (!config.telegramBotToken) {
            alert("Telegram Bot Token not configured.");
            setIsPollingTelegram(false);
            return;
        }
        try {
            // FIX: Pass tenantId to the service function.
            const newTgMsgs = await pollTelegramUpdates(config.telegramBotToken, tenantId);
            if (newTgMsgs.length > 0) {
                // FIX: No need to map and add tenantId, it's already present.
                setUnassigned([...unassigned, ...newTgMsgs]);
                alert(`Fetched ${newTgMsgs.length} new Telegram messages.`);
            } else {
                alert("No new Telegram messages.");
            }
        } finally {
            setIsPollingTelegram(false);
        }
    };

    const handleSend = async () => {
        if (!inputValue.trim() || !selectedEmployee) return;

        let success = false;
        if (activeChannel === 'telegram' && selectedEmployee.telegramChatId) {
            success = await sendTelegramMessage(selectedEmployee.telegramChatId, inputValue);
        } else if (activeChannel === 'email') {
            const subject = emailSubject || `Re: Conversation with ${selectedEmployee.fullName}`;
            success = await sendEmail(selectedEmployee.email, subject, inputValue, config.emailService.smtp);
        }

        if (success) {
            onSendMessage({
                id: `msg_${Date.now()}`,
                tenantId,
                employeeId: selectedEmployee.id,
                sender: 'hr',
                channel: activeChannel,
                text: inputValue,
                subject: activeChannel === 'email' ? emailSubject : undefined,
                timestamp: new Date().toISOString()
            });
            setInputValue('');
            setEmailSubject('');
        } else {
            alert(`Failed to send ${activeChannel} message. Ensure user has a valid contact ID and settings are correct.`);
        }
    };

    const handleAssign = (msgId: string, empId: string) => {
        const { employees, teams, messages, unassignedMessages } = assignSourceId(tenantId, msgId, empId);
        // This is a bit of a hack. Ideally, App.tsx would update all states.
        // For now, we only update unassigned messages locally.
        setUnassigned(unassignedMessages);
        alert('Source assigned. The message is now in their chat history.');
    };

    const openComposeModal = () => {
        setComposeSearchTerm('');
        setIsComposeModalOpen(true);
    };

    const selectEmployeeFromCompose = (empId: string) => {
        setSelectedEmpId(empId);
        setIsComposeModalOpen(false);
    };
    
    const filteredComposeEmployees = reachableEmployees.filter(e => 
        e.fullName.toLowerCase().includes(composeSearchTerm.toLowerCase())
    );

    return (
        <div className="flex h-[calc(100vh-4rem)] bg-white rounded-xl shadow-sm border border-gray-100">
            {/* Sidebar */}
            <div className="w-1/3 border-r border-gray-100 flex flex-col">
                <div className="p-4 border-b border-gray-100">
                    <h2 className="text-xl font-bold text-gray-800">Conversations</h2>
                    <div className="flex bg-gray-100 rounded-lg p-1 mt-3">
                        <button onClick={() => setView('chats')} className={`flex-1 text-sm py-1.5 rounded-md flex justify-center items-center gap-2 ${view === 'chats' ? 'bg-white shadow-sm' : 'text-gray-500'}`}><MessageCircle size={14}/> Chats</button>
                        <button onClick={() => setView('inbox')} className={`flex-1 text-sm py-1.5 rounded-md flex justify-center items-center gap-2 ${view === 'inbox' ? 'bg-white shadow-sm' : 'text-gray-500'}`}><Inbox size={14}/> Inbox ({unassigned.length})</button>
                    </div>
                </div>

                {view === 'chats' && (
                    <div className="overflow-y-auto">
                        {reachableEmployees.map(emp => (
                            <button key={emp.id} onClick={() => setSelectedEmpId(emp.id)} className={`w-full text-left flex items-center p-4 space-x-3 transition-colors ${selectedEmpId === emp.id ? 'bg-blue-50' : 'hover:bg-gray-50'}`}>
                                <div className="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center font-bold text-slate-600">{emp.fullName[0]}</div>
                                <div>
                                    <p className="font-semibold text-gray-900">{emp.fullName}</p>
                                    <p className="text-xs text-gray-500">{emp.position}</p>
                                </div>
                            </button>
                        ))}
                    </div>
                )}

                {view === 'inbox' && (
                    <div className="flex-1 flex flex-col">
                        <div className="p-4 flex gap-2 border-b border-gray-100">
                            <button onClick={handleCheckMails} disabled={isPollingEmail} className="flex-1 text-xs flex items-center justify-center gap-1.5 bg-yellow-500 text-white py-2 rounded-lg disabled:opacity-50">
                                {isPollingEmail ? <RefreshCw size={14} className="animate-spin"/> : <Mail size={14}/>} Check Mail
                            </button>
                            <button onClick={handleCheckTelegram} disabled={isPollingTelegram} className="flex-1 text-xs flex items-center justify-center gap-1.5 bg-blue-500 text-white py-2 rounded-lg disabled:opacity-50">
                                {isPollingTelegram ? <RefreshCw size={14} className="animate-spin"/> : <Radio size={14}/>} Poll Telegram
                            </button>
                        </div>
                        <div className="overflow-y-auto p-2 space-y-2 flex-1 bg-gray-50">
                            {unassigned.length === 0 && <p className="text-center text-sm text-gray-400 p-8">Inbox is empty.</p>}
                            {unassigned.map(msg => (
                                <div key={msg.id} className="bg-white p-3 rounded-lg border border-gray-200">
                                    <div className="flex justify-between items-center mb-2">
                                        <p className="font-bold text-sm flex items-center gap-2">
                                            {msg.channel === 'email' ? <Mail size={12} className="text-yellow-600"/> : <Hash size={12} className="text-blue-600"/>}
                                            {msg.senderName}
                                        </p>
                                        <p className="text-xs text-gray-400">{new Date(msg.timestamp).toLocaleTimeString()}</p>
                                    </div>
                                    <p className="text-sm text-gray-600 mb-3 line-clamp-2">{msg.subject && <strong>{msg.subject}: </strong>}{msg.text}</p>
                                    <div className="flex gap-2">
                                        <select onChange={(e) => setAssignTarget(e.target.value)} className="flex-1 text-xs border rounded p-1" defaultValue="">
                                            <option value="" disabled>Assign to...</option>
                                            {employees.map(e => <option key={e.id} value={e.id}>{e.fullName}</option>)}
                                        </select>
                                        <button onClick={() => handleAssign(msg.id, assignTarget)} disabled={!assignTarget} className="text-xs bg-slate-700 text-white px-3 rounded hover:bg-slate-800 disabled:opacity-50">Assign</button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>

            {/* Main Chat Area */}
            <div className="w-2/3 flex flex-col">
                {selectedEmployee ? (
                    <>
                        <div className="p-4 border-b border-gray-100 flex justify-between items-center">
                            <div>
                                <h3 className="font-bold text-lg text-gray-800">{selectedEmployee.fullName}</h3>
                                <div className="flex items-center gap-2 text-xs text-gray-500 mt-1">
                                    <span className={`capitalize px-2 py-0.5 rounded-full text-white ${activeChannel === 'email' ? 'bg-yellow-500' : 'bg-blue-500'}`}>{activeChannel}</span>
                                    <span>{activeChannel === 'email' ? selectedEmployee.email : `@${selectedEmployee.telegramChatId}`}</span>
                                </div>
                            </div>
                        </div>

                        <div className="flex-1 overflow-y-auto p-6 space-y-4 bg-slate-50">
                            {currentMessages.map(msg => (
                                <div key={msg.id} className={`flex ${msg.sender === 'hr' ? 'justify-end' : 'justify-start'}`}>
                                    <div className={`max-w-md p-3 rounded-xl ${msg.sender === 'hr' ? 'bg-blue-600 text-white' : 'bg-white border'}`}>
                                        {msg.subject && <p className="font-bold text-sm mb-1">{msg.subject}</p>}
                                        <p className="text-sm">{msg.text}</p>
                                        <p className="text-xs mt-2 opacity-70">{new Date(msg.timestamp).toLocaleString()}</p>
                                    </div>
                                </div>
                            ))}
                            <div ref={messagesEndRef} />
                        </div>
                        
                        <div className="p-4 border-t border-gray-200 bg-white">
                            {activeChannel === 'email' && (
                                <input 
                                    type="text"
                                    value={emailSubject}
                                    onChange={e => setEmailSubject(e.target.value)}
                                    placeholder="Subject (Optional for new thread)"
                                    className="w-full p-2 text-sm border-b mb-2 focus:outline-none"
                                />
                            )}
                            <div className="relative">
                                <textarea
                                    value={inputValue}
                                    onChange={(e) => setInputValue(e.target.value)}
                                    onKeyDown={(e) => { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); handleSend(); } }}
                                    placeholder={`Send a ${activeChannel} message...`}
                                    className="w-full p-3 pr-20 border border-gray-200 rounded-lg resize-none focus:ring-2 focus:ring-blue-500 outline-none"
                                    rows={2}
                                />
                                <div className="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-2">
                                    <button onClick={handleSend} className="bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700">
                                        <Send size={18}/>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </>
                ) : (
                    <div className="flex flex-col items-center justify-center h-full text-center text-gray-500 bg-gray-50">
                        <MessageCircle size={64} className="text-gray-300 mb-4" />
                        <h3 className="text-xl font-semibold text-gray-700">Select a conversation</h3>
                        <p className="mt-1">or</p>
                        <button onClick={openComposeModal} className="mt-4 flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition shadow-lg shadow-blue-500/30">
                            <UserPlus size={18}/> Compose New Message
                        </button>
                    </div>
                )}
            </div>

            {/* Compose Modal */}
            {isComposeModalOpen && (
                 <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
                    <div className="bg-white rounded-xl max-w-md w-full max-h-[70vh] flex flex-col">
                        <div className="p-4 border-b border-gray-100 flex justify-between items-center">
                            <h3 className="text-lg font-bold">Compose New Message</h3>
                            <button onClick={() => setIsComposeModalOpen(false)}><X size={20}/></button>
                        </div>
                        <div className="p-4">
                            <div className="relative">
                                <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" size={18} />
                                <input 
                                    type="text"
                                    placeholder="Search employees..."
                                    value={composeSearchTerm}
                                    onChange={e => setComposeSearchTerm(e.target.value)}
                                    className="w-full pl-10 pr-3 py-2 border rounded-lg"
                                />
                            </div>
                        </div>
                        <div className="flex-1 overflow-y-auto">
                           {filteredComposeEmployees.map(emp => (
                                <button key={emp.id} onClick={() => selectEmployeeFromCompose(emp.id)} className="w-full text-left flex items-center p-4 space-x-3 hover:bg-gray-50">
                                    <div className="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center font-bold text-slate-600">{emp.fullName[0]}</div>
                                    <div>
                                        <p className="font-semibold text-gray-900">{emp.fullName}</p>
                                        <p className="text-xs text-gray-500">{emp.position}</p>
                                    </div>
                                </button>
                           ))}
                           {filteredComposeEmployees.length === 0 && <p className="text-center text-sm text-gray-400 p-6">No matching employees found.</p>}
                        </div>
                    </div>
                 </div>
            )}
        </div>
    );
};

export default Messages;