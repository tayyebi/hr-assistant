

import React from 'react';
import { LayoutDashboard, Users, UsersRound, Settings, Server, Menu, X, MessageCircle, LogOut, Building, Layers } from 'lucide-react';
import { User, Tenant } from '../types';

interface LayoutProps {
  children: React.ReactNode;
  activeTab: string;
  setActiveTab: (tab: string) => void;
  user: User;
  tenant: Tenant;
  onLogout: () => void;
}

const Layout: React.FC<LayoutProps> = ({ children, activeTab, setActiveTab, user, tenant, onLogout }) => {
  const [isSidebarOpen, setIsSidebarOpen] = React.useState(false);

  const navItems = [
    { id: 'dashboard', label: 'Dashboard', icon: LayoutDashboard },
    { id: 'employees', label: 'Employees', icon: Users },
    { id: 'teams', label: 'Teams', icon: UsersRound },
    { id: 'messages', label: 'Direct Messages', icon: MessageCircle },
    { id: 'assets', label: 'Digital Assets', icon: Server },
    { id: 'jobs', label: 'System Jobs', icon: Layers },
    { id: 'settings', label: 'Settings', icon: Settings },
  ];

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col md:flex-row">
      {/* Mobile Header */}
      <div className="md:hidden bg-white shadow-sm p-4 flex justify-between items-center z-20">
        <h1 className="font-bold text-xl text-blue-600">HR Assistant</h1>
        <button onClick={() => setIsSidebarOpen(!isSidebarOpen)}>
          {isSidebarOpen ? <X /> : <Menu />}
        </button>
      </div>

      {/* Sidebar */}
      <aside className={`
        fixed inset-y-0 left-0 z-10 w-64 bg-slate-900 text-white flex flex-col transform transition-transform duration-200 ease-in-out
        md:relative md:translate-x-0
        ${isSidebarOpen ? 'translate-x-0' : '-translate-x-full'}
      `}>
        <div className="p-6 border-b border-slate-800">
          <h1 className="text-2xl font-bold bg-gradient-to-r from-blue-400 to-cyan-300 bg-clip-text text-transparent">
            HR Assistant
          </h1>
          <p className="text-xs text-slate-400 mt-1">Administration Console</p>
        </div>

        <nav className="p-4 space-y-2 flex-1">
          {navItems.map((item) => (
            <button
              key={item.id}
              onClick={() => {
                setActiveTab(item.id);
                setIsSidebarOpen(false);
              }}
              className={`w-full flex items-center space-x-3 px-4 py-3 rounded-lg transition-colors
                ${activeTab === item.id 
                  ? 'bg-blue-600 text-white shadow-lg shadow-blue-900/50' 
                  : 'text-slate-300 hover:bg-slate-800 hover:text-white'}
              `}
            >
              <item.icon size={20} />
              <span className="font-medium">{item.label}</span>
            </button>
          ))}
        </nav>

        <div className="p-4 border-t border-slate-800 space-y-3">
            <div className="bg-slate-800 rounded p-3 text-xs text-slate-400">
                <p className="font-semibold text-slate-300">System Status</p>
                <div className="flex items-center mt-2 space-x-2">
                    <span className="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    <span>Backend Sync Active</span>
                </div>
                <div className="flex items-center mt-1 space-x-2">
                    <span className="w-2 h-2 bg-green-500 rounded-full"></span>
                    <span>Mailcow Connected</span>
                </div>
                 <div className="flex items-center mt-1 space-x-2">
                    <span className="w-2 h-2 bg-green-500 rounded-full"></span>
                    <span>GitLab Connected</span>
                </div>
                 <div className="flex items-center mt-1 space-x-2">
                    <span className="w-2 h-2 bg-green-500 rounded-full"></span>
                    <span>Keycloak Connected</span>
                </div>
            </div>
            <div className="text-sm text-slate-300 p-2 rounded-lg bg-slate-800/50">
                <div className="flex items-center gap-2 text-slate-400 text-xs">
                    <Building size={14}/>
                    <span className="font-semibold">{tenant.name}</span>
                </div>
                 <div className="flex items-center justify-between mt-2 pt-2 border-t border-slate-700/50">
                    <span className="text-xs truncate">{user.email}</span>
                    <button onClick={onLogout} className="text-slate-400 hover:text-red-400 p-1 rounded-full hover:bg-slate-700">
                        <LogOut size={16} />
                    </button>
                 </div>
            </div>
        </div>
      </aside>

      {/* Main Content */}
      <main className="flex-1 overflow-y-auto h-screen p-4 md:p-8">
        <div className="max-w-7xl mx-auto">
          {children}
        </div>
      </main>
    </div>
  );
};

export default Layout;