


import React, { useState, useEffect, useRef } from 'react';
import { Employee, ChatMessage, UnassignedMessage, Channel, AppConfig } from '../types';
import { Send, Wand2, Sparkles, MessageCircle, UserPlus, Inbox, Hash, Mail, RefreshCw, Radio } from 'lucide-react';
import { sendTelegramMessage, pollTelegramUpdates } from '../services/telegramService';
import { sendEmail, pollImapServer } from '../services/emailService';
import { getStoredData, assignSourceId, saveData } from '../services/storage';

interface MessagesProps {
    employees: Employee[];
    messages: ChatMessage[];
    onSendMessage: (msg: ChatMessage) => void;
    config: AppConfig; // Added config prop
}

const Messages: React.FC<MessagesProps> = ({ employees, messages, onSendMessage, config }) => {
    const [view, setView] = useState<'chats' | 'inbox'>('chats');
    const [selectedEmpId, setSelectedEmpId] = useState<string | null>(null);
    const [inputValue, setInputValue] = useState('');
    const [emailSubject, setEmailSubject] = useState(''); // Only for new emails
    // const [isEnriching, setIsEnriching] = useState(false); // Removed as Gemini is not used
    const [isPollingEmail, setIsPollingEmail] = useState(false);
    const [isPollingTelegram, setIsPollingTelegram] = useState(false);
    
    // Inbox State
    const [unassigned, setUnassigned] = useState<UnassignedMessage[]>([]);
    const [assignTarget, setAssignTarget] = useState<string>('');

    const messagesEndRef = useRef<HTMLDivElement>(null);
    
    // Filter employees who have either a Chat ID or an Email
    const reachableEmployees = employees.filter(e => e.telegramChatId || e.email);
    const currentMessages = messages.filter(m => m.employeeId === selectedEmpId);
    const selectedEmployee = employees.find(e => e.id === selectedEmpId);

    // Determine active channel based on last message or default to Telegram if available
    const lastMsg = currentMessages[currentMessages.length - 1];
    const activeChannel: Channel = lastMsg?.channel || (selectedEmployee?.telegramChatId ? 'telegram' : 'email');

    // Refresh data periodically or on mount
    useEffect(() => {
        const data = getStoredData();
        setUnassigned(data.unassignedMessages);
    }, [view]);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
    };

    useEffect(() => {
        scrollToBottom();
    }, [currentMessages, selectedEmpId]);

    const checkMails = async () => {
        setIsPollingEmail(true);
        const { imapHost, imapUser, imapPass } = config.emailService;
        
        if (!imapHost) {
            alert("IMAP not configured in settings.");
            setIsPollingEmail(false);
            return;
        }

        try {
            const newMails = await pollImapServer(imapHost, imapUser, imapPass);
            if (newMails.length > 0) {
                const currentData = getStoredData();
                currentData.unassignedMessages.push(...newMails);
                saveData(currentData); // Save to trigger persistence and update local cache
                setUnassigned([...currentData.unassignedMessages]);
                alert(`Fetched ${newMails.length} new emails.`);
            } else {
                alert("No new emails.");
            }
        } catch (e) {
            console.error("Error polling email:", e);
            alert("Failed to check email server. See console for details.");
        } finally {
            setIsPollingEmail(false);
        }
    };

    const checkTelegram = async () => {
        setIsPollingTelegram(true);
        const botToken = config.telegramBotToken;

        if (!botToken) {
            alert("Telegram Bot Token not configured in settings.");
            setIsPollingTelegram(false);
            return;
        }

        try {
            const newTelegramMessages = await pollTelegramUpdates(botToken);
            if (newTelegramMessages.length > 0) {
                const currentData = getStoredData();
                currentData.unassignedMessages.push(...newTelegramMessages);
                saveData(currentData); // Save to trigger persistence and update local cache
                setUnassigned([...currentData.unassignedMessages]);
                alert(`Fetched ${newTelegramMessages.length} new Telegram messages.`);
            } else {
                alert("No new Telegram messages.");
            }
        } catch (e) {
            console.error("Error polling Telegram:", e);
            alert("Failed to check Telegram updates. See console for details.");
        } finally {
            setIsPollingTelegram(false);
        }
    };


    const handleSend = async () => {
        if (!inputValue.trim() || !selectedEmpId) return;

        // Construct message
        const newMessage: ChatMessage = {
            id: `msg_${Date.now()}`,
            employeeId: selectedEmpId,
            sender: 'hr',
            channel: activeChannel,
            text: inputValue,
            subject: activeChannel === 'email' ? (emailSubject || `Re: Message`) : undefined,
            timestamp: new Date().toISOString()
        };

        onSendMessage(newMessage);
        setInputValue('');
        setEmailSubject('');

        // Dispatch to correct service
        if (activeChannel === 'telegram' && selectedEmployee?.telegramChatId) {
            await sendTelegramMessage(selectedEmployee.telegramChatId, newMessage.text);
        } else if (activeChannel === 'email' && selectedEmployee?.email) {
            const emailConfig = config.emailService;
            await sendEmail(selectedEmployee.email, newMessage.subject || "HR Update", newMessage.text, emailConfig);
        }
    };

    // Removed handleEnrich function as Gemini is not used
    // const handleEnrich = async () => {
    //     if (!inputValue.trim()) return;
    //     setIsEnriching(true);
    //     const polished = await enrichDraftMessage(inputValue, 'professional');
    //     setInputValue(polished);
    //     setIsEnriching(false);
    // };

    const handleAssign = (msgId: string) => {
        if (!assignTarget) return;
        const currentData = getStoredData();
        assignSourceId(msgId, assignTarget); // This modifies memCache directly then triggers saveData
        
        // After assignSourceId, the unassignedMessages in memCache is updated.
        // We need to re-fetch from getStoredData or simply update local state
        setUnassigned([...currentData.unassignedMessages]); // Refresh local state
        setAssignTarget('');
        alert("Source connected successfully!");
    };

    return (
        <div className="h-[calc(100vh-8rem)] flex flex-col md:flex-row gap-6">
            {/* Sidebar List */}
            <div className="w-full md:w-80 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
                <div className="flex border-b border-gray-100">
                    <button 
                        onClick={() => setView('chats')}
                        className={`flex-1 py-3 text-sm font-medium ${view === 'chats' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:bg-gray-50'}`}
                    >
                        Employees
                    </button>
                    <button 
                         onClick={() => setView('inbox')}
                         className={`flex-1 py-3 text-sm font-medium flex items-center justify-center gap-1 ${view === 'inbox' ? 'text-blue-600 border-b-2 border-blue-600' : 'text-gray-500 hover:bg-gray-50'}`}
                    >
                        Inbox
                        {unassigned.length > 0 && <span className="w-2 h-2 rounded-full bg-red-500"></span>}
                    </button>
                </div>

                <div className="flex-1 overflow-y-auto">
                    {view === 'chats' ? (
                        <>
                             {reachableEmployees.map(emp => (
                                <button
                                    key={emp.id}
                                    onClick={() => setSelectedEmpId(emp.id)}
                                    className={`w-full text-left p-4 hover:bg-blue-50 transition-colors border-b border-gray-50 flex items-center space-x-3
                                        ${selectedEmpId === emp.id ? 'bg-blue-50 border-l-4 border-l-blue-600' : ''}
                                    `}
                                >
                                    <div className="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center font-bold text-slate-600 flex-shrink-0 relative">
                                        {emp.fullName.charAt(0)}
                                        <div className="absolute -bottom-1 -right-1 bg-white rounded-full p-0.5 shadow">
                                            {emp.telegramChatId ? <Hash size={10} className="text-blue-500"/> : <Mail size={10} className="text-orange-500"/>}
                                        </div>
                                    </div>
                                    <div className="overflow-hidden">
                                        <p className={`font-medium truncate ${selectedEmpId === emp.id ? 'text-blue-900' : 'text-gray-900'}`}>
                                            {emp.fullName}
                                        </p>
                                        <p className="text-xs text-gray-500 truncate">{emp.position}</p>
                                    </div>
                                </button>
                            ))}
                        </>
                    ) : (
                        <div className="p-2 space-y-2">
                             <button 
                                onClick={checkMails}
                                disabled={isPollingEmail}
                                className="w-full mb-2 flex items-center justify-center gap-2 text-xs bg-gray-100 py-2 rounded hover:bg-gray-200 text-gray-600"
                            >
                                <RefreshCw size={12} className={isPollingEmail ? "animate-spin" : ""} /> Check Mail Server
                            </button>

                            {config.telegramMode === 'polling' && (
                                <button
                                    onClick={checkTelegram}
                                    disabled={isPollingTelegram}
                                    className="w-full mb-2 flex items-center justify-center gap-2 text-xs bg-gray-100 py-2 rounded hover:bg-gray-200 text-gray-600"
                                >
                                    <Radio size={12} className={isPollingTelegram ? "animate-spin" : ""} /> Check Telegram Updates
                                </button>
                            )}

                            {unassigned.length === 0 && <div className="text-center p-4 text-gray-400 text-sm">No new messages.</div>}
                            {unassigned.map(msg => (
                                <div key={msg.id} className="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                    <div className="flex justify-between items-start mb-2">
                                        <div className="flex items-center gap-1">
                                            {msg.channel === 'email' ? <Mail size={12} className="text-orange-500"/> : <Hash size={12} className="text-blue-500"/>}
                                            <h4 className="font-bold text-sm text-gray-800">{msg.senderName}</h4>
                                        </div>
                                        <span className="text-[10px] text-gray-500">{new Date(msg.timestamp).toLocaleDateString()}</span>
                                    </div>
                                    
                                    {msg.subject && <p className="text-xs font-semibold text-gray-700 mb-1">{msg.subject}</p>}
                                    <p className="text-xs text-gray-600 italic mb-2 line-clamp-2">"{msg.text}"</p>
                                    <div className="flex items-center gap-1 text-[10px] text-gray-400 mb-3">
                                        Source: {msg.sourceId}
                                    </div>
                                    
                                    <div className="space-y-2">
                                        <select 
                                            className="w-full text-xs p-1 border rounded"
                                            value={assignTarget}
                                            onChange={e => setAssignTarget(e.target.value)}
                                        >
                                            <option value="">Assign to Employee...</option>
                                            {employees.map(e => (
                                                <option key={e.id} value={e.id}>{e.fullName}</option>
                                            ))}
                                        </select>
                                        <button 
                                            onClick={() => handleAssign(msg.id)}
                                            className="w-full bg-blue-600 text-white text-xs py-1.5 rounded hover:bg-blue-700 flex justify-center items-center gap-1"
                                        >
                                            <UserPlus size={12}/> Link Profile
                                        </button>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </div>

            {/* Chat Area */}
            <div className="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
                {!selectedEmpId ? (
                    <div className="flex-1 flex flex-col items-center justify-center text-gray-400">
                        <MessageCircle size={48} className="mb-4 text-gray-200" />
                        <p>Select an employee to view conversation.</p>
                    </div>
                ) : (
                    <>
                        {/* Header */}
                        <div className="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                            <div>
                                <h3 className="font-bold text-gray-800">{selectedEmployee?.fullName}</h3>
                                <div className="flex gap-2">
                                    <p className="text-xs text-gray-500 flex items-center gap-1">
                                        Active Channel: <span className="uppercase font-bold">{activeChannel}</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Messages */}
                        <div className="flex-1 overflow-y-auto p-4 space-y-4 bg-slate-50">
                            {currentMessages.length === 0 && (
                                <p className="text-center text-xs text-gray-400 mt-10">No messages yet.</p>
                            )}
                            {currentMessages.map(msg => (
                                <div key={msg.id} className={`flex ${msg.sender === 'hr' ? 'justify-end' : 'justify-start'}`}>
                                    <div className={`
                                        max-w-[80%] rounded-2xl px-4 py-3 text-sm relative
                                        ${msg.sender === 'hr' 
                                            ? 'bg-blue-600 text-white rounded-tr-none' 
                                            : 'bg-white border border-gray-200 text-gray-800 rounded-tl-none shadow-sm'}
                                    `}>
                                        <div className="absolute -top-2 left-0 right-0 flex justify-center">
                                             {msg.channel === 'email' && <span className="bg-orange-100 text-orange-800 text-[9px] px-1 rounded">EMAIL</span>}
                                        </div>
                                        {msg.subject && <p className="font-bold border-b border-white/20 pb-1 mb-1">{msg.subject}</p>}
                                        <p>{msg.text}</p>
                                        <p className={`text-[10px] mt-1 text-right ${msg.sender === 'hr' ? 'text-blue-100' : 'text-gray-400'}`}>
                                            {new Date(msg.timestamp).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                        </p>
                                    </div>
                                </div>
                            ))}
                            <div ref={messagesEndRef} />
                        </div>

                        {/* Input */}
                        <div className="p-4 bg-white border-t border-gray-100">
                            {activeChannel === 'email' && (
                                <input 
                                    type="text" 
                                    placeholder="Subject..."
                                    className="w-full border border-gray-200 rounded-lg px-4 py-2 mb-2 text-sm focus:outline-none focus:border-blue-400"
                                    value={emailSubject}
                                    onChange={e => setEmailSubject(e.target.value)}
                                />
                            )}
                            <div className="relative">
                                <textarea
                                    className="w-full border border-gray-200 rounded-xl pl-4 pr-12 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none resize-none bg-gray-50 focus:bg-white transition-all"
                                    rows={2}
                                    placeholder={`Message via ${activeChannel}...`}
                                    value={inputValue}
                                    onChange={(e) => setInputValue(e.target.value)}
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter' && !e.shiftKey) {
                                            e.preventDefault();
                                            handleSend();
                                        }
                                    }}
                                />
                                
                                <div className="absolute right-3 bottom-3 flex items-center space-x-2">
                                    {/* Removed Magic Polish button */}
                                    {/* <button
                                        onClick={handleEnrich}
                                        disabled={!inputValue || isEnriching}
                                        className={`p-2 rounded-lg transition-colors group relative
                                            ${!inputValue ? 'text-gray-300' : 'text-purple-500 hover:bg-purple-50'}
                                        `}
                                        title="Magic Polish with Gemini"
                                    >
                                        {isEnriching ? <Sparkles size={18} className="animate-spin" /> : <Wand2 size={18} />}
                                    </button> */}
                                    <button
                                        onClick={handleSend}
                                        disabled={!inputValue}
                                        className={`p-2 rounded-lg transition-colors
                                            ${!inputValue ? 'text-gray-300' : 'bg-blue-600 text-white hover:bg-blue-700 shadow-md'}
                                        `}
                                    >
                                        <Send size={18} />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
};

export default Messages;