

import React, { useState, useEffect } from 'react';
import Layout from './components/Layout';
import Dashboard from './pages/Dashboard';
import Employees from './pages/Employees';
import Teams from './pages/Teams';
import Assets from './pages/Assets';
import Settings from './pages/Settings';
import Messages from './pages/Messages';
import { Employee, Team, ChatMessage, AppConfig } from './types';
import { initializeStorage, getStoredData, saveData, saveMessage } from './services/storage';
import { RefreshCw } from 'lucide-react';

function App() {
  const [activeTab, setActiveTab] = useState('dashboard');
  const [employees, setEmployees] = useState<Employee[]>([]);
  const [teams, setTeams] = useState<Team[]>([]);
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [appConfig, setAppConfig] = useState<AppConfig | null>(null); // New state for config
  const [isLoading, setIsLoading] = useState(true);

  // Load initial data
  useEffect(() => {
    const init = async () => {
        await initializeStorage();
        const data = getStoredData();
        setEmployees(data.employees);
        setTeams(data.teams);
        setMessages(data.messages || []);
        setAppConfig(data.config); // Set config
        setIsLoading(false);
    };
    init();
  }, []);

  // Persistence effect for structural data
  useEffect(() => {
    if (!isLoading && employees.length > 0 && appConfig) { // Ensure appConfig is loaded before saving
      const currentData = getStoredData();
      saveData({ 
        ...currentData,
        employees, 
        teams, 
        messages,
        config: appConfig // Also persist appConfig changes from settings
      });
    }
  }, [employees, teams, messages, appConfig, isLoading]);

  const handleSendMessage = (msg: ChatMessage) => {
      const updatedMessages = saveMessage(msg);
      setMessages([...updatedMessages]);
  };

  const renderContent = () => {
    if (isLoading || !appConfig) { // Ensure config is loaded
        return (
            <div className="flex items-center justify-center h-full">
                <div className="flex items-center gap-3 text-gray-500">
                    <RefreshCw className="animate-spin" />
                    <p>Syncing Data...</p>
                </div>
            </div>
        );
    }

    switch (activeTab) {
      case 'dashboard':
        return <Dashboard employees={employees} />;
      case 'employees':
        return <Employees employees={employees} setEmployees={setEmployees} />;
      case 'teams':
        return <Teams teams={teams} employees={employees} setTeams={setTeams} setEmployees={setEmployees} />;
      case 'messages':
        return <Messages employees={employees} messages={messages} onSendMessage={handleSendMessage} config={appConfig} />;
      case 'assets':
        return <Assets employees={employees} />;
      case 'settings':
        return <Settings onConfigChange={setAppConfig} />; {/* Pass config update handler */}
      default:
        return <Dashboard employees={employees} />;
    }
  };

  return (
    <Layout activeTab={activeTab} setActiveTab={setActiveTab}>
      {renderContent()}
    </Layout>
  );
}

export default App;