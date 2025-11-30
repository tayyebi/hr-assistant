

import React, { useState, useEffect } from 'react';
import Layout from './components/Layout';
import Dashboard from './pages/Dashboard';
import Employees from './pages/Employees';
import Teams from './pages/Teams';
import Assets from './pages/Assets';
import Settings from './pages/Settings';
import Messages from './pages/Messages';
import Login from './pages/Login';
import Jobs from './pages/Jobs';
import SystemAdmin from './pages/SystemAdmin';
import { Employee, Team, ChatMessage, AppConfig, User, UserRole, Tenant, UnassignedMessage, SystemJob } from './types';
import { getTenantData, saveTenantData, saveMessage, getTenants, createTenant } from './services/storage';
import { RefreshCw } from 'lucide-react';

function App() {
  const [currentUser, setCurrentUser] = useState<User | null>(null);
  const [currentTenant, setCurrentTenant] = useState<Tenant | null>(null);
  const [isLoading, setIsLoading] = useState(true);

  // Tenant-specific state
  const [employees, setEmployees] = useState<Employee[]>([]);
  const [teams, setTeams] = useState<Team[]>([]);
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [unassignedMessages, setUnassignedMessages] = useState<UnassignedMessage[]>([]);
  const [jobs, setJobs] = useState<SystemJob[]>([]);
  const [appConfig, setAppConfig] = useState<AppConfig | null>(null);

  // Load user from session storage to persist login across reloads
  useEffect(() => {
    const storedUser = sessionStorage.getItem('currentUser');
    if (storedUser) {
      const user: User = JSON.parse(storedUser);
      handleLogin(user); // Re-login
    } else {
      setIsLoading(false);
    }
  }, []);

  const handleLogin = (user: User) => {
    setIsLoading(true);
    setCurrentUser(user);
    sessionStorage.setItem('currentUser', JSON.stringify(user));

    if (user.role === UserRole.TENANT_ADMIN && user.tenantId) {
      const tenantData = getTenantData(user.tenantId);
      const allTenants = getTenants();
      const tenantInfo = allTenants.find(t => t.id === user.tenantId);

      setEmployees(tenantData.employees);
      setTeams(tenantData.teams);
      setMessages(tenantData.messages);
      setUnassignedMessages(tenantData.unassignedMessages);
      setJobs(tenantData.jobs || []);
      setAppConfig(tenantData.config);
      setCurrentTenant(tenantInfo || null);
    }
    setIsLoading(false);
  };

  const handleLogout = () => {
    setCurrentUser(null);
    setCurrentTenant(null);
    sessionStorage.removeItem('currentUser');
    // Reset state
    setEmployees([]);
    setTeams([]);
    setMessages([]);
    setUnassignedMessages([]);
    setJobs([]);
    setAppConfig(null);
  };

  // Persistence effect for tenant data
  useEffect(() => {
    if (currentUser?.role === UserRole.TENANT_ADMIN && currentUser.tenantId && !isLoading) {
      saveTenantData(currentUser.tenantId, {
        employees,
        teams,
        messages,
        unassignedMessages,
        jobs, // Note: Jobs are also updated directly by jobQueue, this is for sync
        config: appConfig!,
      });
    }
  }, [employees, teams, messages, unassignedMessages, jobs, appConfig, isLoading, currentUser]);

  if (isLoading) {
    return (
      <div className="w-full h-screen flex items-center justify-center bg-gray-50">
        <div className="flex items-center gap-3 text-gray-500">
          <RefreshCw className="animate-spin" />
          <p>Loading Application...</p>
        </div>
      </div>
    );
  }

  if (!currentUser) {
    return <Login onLogin={handleLogin} />;
  }

  if (currentUser.role === UserRole.SYSTEM_ADMIN) {
    return <SystemAdmin user={currentUser} onLogout={handleLogout} />;
  }
  
  if (currentUser.role === UserRole.TENANT_ADMIN && currentTenant && appConfig) {
    const tenantId = currentUser.tenantId!;

    const handleSendMessage = (msg: ChatMessage) => {
      const updatedMessages = saveMessage(tenantId, msg);
      setMessages([...updatedMessages]);
    };

    const updateEmployees = (newEmployees: Employee[]) => setEmployees(newEmployees);
    const updateTeams = (newTeams: Team[]) => setTeams(newTeams);
    const updateConfig = (newConfig: AppConfig) => setAppConfig(newConfig);

    return (
      <TenantApp
        user={currentUser}
        tenant={currentTenant}
        onLogout={handleLogout}
        employees={employees}
        teams={teams}
        messages={messages}
        unassigned={unassignedMessages}
        config={appConfig}
        setEmployees={updateEmployees}
        setTeams={updateTeams}
        onSendMessage={handleSendMessage}
        onConfigChange={updateConfig}
        setUnassigned={setUnassignedMessages}
      />
    );
  }

  return <div>Error: Invalid user state. Please log out and try again.</div>;
}

// Tenant-facing application component
const TenantApp = (props: any) => {
  const [activeTab, setActiveTab] = useState('dashboard');
  const { user, tenant, onLogout, employees, teams, messages, unassigned, config, setEmployees, setTeams, onSendMessage, onConfigChange, setUnassigned } = props;

  const renderContent = () => {
    switch (activeTab) {
      case 'dashboard':
        return <Dashboard employees={employees} />;
      case 'employees':
        return <Employees employees={employees} setEmployees={setEmployees} tenantId={tenant.id} />;
      case 'teams':
        return <Teams teams={teams} employees={employees} setTeams={setTeams} setEmployees={setEmployees} tenantId={tenant.id} />;
      case 'messages':
        return <Messages employees={employees} messages={messages} unassigned={unassigned} setUnassigned={setUnassigned} onSendMessage={onSendMessage} config={config} tenantId={tenant.id} />;
      case 'assets':
        return <Assets employees={employees} config={config} tenantId={tenant.id} />;
      case 'jobs':
        return <Jobs tenantId={tenant.id} config={config} />;
      case 'settings':
        return <Settings config={config} onConfigChange={onConfigChange} />;
      default:
        return <Dashboard employees={employees} />;
    }
  };

  return (
    <Layout user={user} tenant={tenant} onLogout={onLogout} activeTab={activeTab} setActiveTab={setActiveTab}>
      {renderContent()}
    </Layout>
  );
};

export default App;