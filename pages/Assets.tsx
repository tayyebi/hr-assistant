

import React, { useState } from 'react';
import { Employee, ServiceType, AppConfig } from '../types';
import { getServiceAdapter } from '../services/integrationService';
import { createJob } from '../services/jobQueue';
import { Server, GitBranch, Mail, Shield, Plus, CheckCircle, AlertCircle, RefreshCw, Ban, Key, Layers } from 'lucide-react';

interface AssetsProps {
    employees: Employee[];
    config: AppConfig;
    tenantId: string;
}

const Assets: React.FC<AssetsProps> = ({ employees, config, tenantId }) => {
    const [activeService, setActiveService] = useState<ServiceType>(ServiceType.MAILCOW);
    const [status, setStatus] = useState<{msg: string, type: 'success' | 'info'} | null>(null);

    // Job Helper
    const queueAction = async (service: ServiceType, action: string, targetName: string, payload: any) => {
        await createJob(tenantId, service, action, targetName, payload, config);
        setStatus({ msg: `Task "${action}" for ${targetName} queued successfully. Check "System Jobs" for progress.`, type: 'info' });
        setTimeout(() => setStatus(null), 5000);
    };

    const renderServiceContent = () => {
        switch (activeService) {
            case ServiceType.MAILCOW:
                return <MailcowPanel employees={employees} queueAction={queueAction} config={config} />;
            case ServiceType.GITLAB:
                return <GitlabPanel employees={employees} queueAction={queueAction} config={config} />;
            case ServiceType.KEYCLOAK:
                return <KeycloakPanel employees={employees} queueAction={queueAction} config={config} />;
            default:
                return <div className="p-10 text-center text-gray-400">Service integration coming soon.</div>;
        }
    };

    return (
        <div className="space-y-6">
            <header>
                <h2 className="text-2xl font-bold text-gray-800">Digital Assets & Services</h2>
                <p className="text-gray-500">Provision accounts and manage access via background tasks.</p>
            </header>

            {/* Service Tabs */}
            <div className="flex space-x-4 border-b border-gray-200 overflow-x-auto">
                <ServiceTab 
                    isActive={activeService === ServiceType.MAILCOW} 
                    onClick={() => setActiveService(ServiceType.MAILCOW)}
                    icon={<Mail size={18}/>}
                    label="Mail Service"
                />
                <ServiceTab 
                    isActive={activeService === ServiceType.GITLAB} 
                    onClick={() => setActiveService(ServiceType.GITLAB)}
                    icon={<GitBranch size={18}/>}
                    label="Git Service"
                />
                <ServiceTab 
                    isActive={activeService === ServiceType.KEYCLOAK} 
                    onClick={() => setActiveService(ServiceType.KEYCLOAK)}
                    icon={<Key size={18}/>}
                    label="Keycloak IAM"
                />
                <ServiceTab 
                    isActive={activeService === ServiceType.JIRA} 
                    onClick={() => setActiveService(ServiceType.JIRA)}
                    icon={<Server size={18}/>}
                    label="Project Management"
                />
            </div>

            {/* Status Feedback */}
            {status && (
                <div className={`p-4 rounded-lg flex items-center gap-2 ${status.type === 'success' ? 'bg-green-50 text-green-800' : 'bg-blue-50 text-blue-800'}`}>
                    {status.type === 'success' ? <CheckCircle size={20}/> : <Layers size={20}/>}
                    <span>{status.msg}</span>
                    <button onClick={() => setStatus(null)} className="ml-auto text-sm underline opacity-70">Dismiss</button>
                </div>
            )}

            {/* Content Area */}
            <div className="bg-white rounded-xl shadow-sm border border-gray-100 min-h-[400px]">
                {renderServiceContent()}
            </div>
        </div>
    );
};

const ServiceTab = ({ isActive, onClick, icon, label }: any) => (
    <button 
        onClick={onClick}
        className={`flex items-center space-x-2 px-4 py-3 border-b-2 transition-colors whitespace-nowrap ${
            isActive 
                ? 'border-blue-600 text-blue-600 font-medium' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
        }`}
    >
        {icon}
        <span>{label}</span>
    </button>
);

// --- Sub-Panels ---

const MailcowPanel = ({ employees, queueAction, config }: any) => {
    return (
        <div className="p-6">
            <div className="flex justify-between items-center mb-6">
                <h3 className="font-bold text-lg text-gray-800">Mail Service Management</h3>
                <div className="text-sm text-gray-500">Connected to: {config.mailcow.url}</div>
            </div>
            
            <table className="w-full text-left">
                <thead className="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th className="px-4 py-3">Employee</th>
                        <th className="px-4 py-3">Email Status</th>
                        <th className="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                    {employees.map((emp: Employee) => {
                        const account = emp.accounts?.find(a => a.service === ServiceType.MAILCOW);
                        return (
                            <tr key={emp.id} className="hover:bg-gray-50">
                                <td className="px-4 py-3 font-medium text-gray-700">{emp.fullName}</td>
                                <td className="px-4 py-3">
                                    {account ? (
                                        <span className={`inline-flex items-center px-2 py-1 rounded text-xs ${account.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}`}>
                                            {account.status === 'active' ? 'Active: ' : 'Suspended: '} {account.accountId}
                                        </span>
                                    ) : (
                                        <span className="text-gray-400 text-xs italic">No Mailbox</span>
                                    )}
                                </td>
                                <td className="px-4 py-3 text-right space-x-2">
                                    {account ? (
                                        <>
                                            <button 
                                                onClick={() => queueAction(ServiceType.MAILCOW, 'RESET_CREDENTIAL', emp.fullName, account.accountId)}
                                                className="text-xs border border-gray-300 px-2 py-1 rounded hover:bg-gray-100"
                                            >
                                                Reset PW
                                            </button>
                                            <button 
                                                onClick={() => queueAction(ServiceType.MAILCOW, 'DEACTIVATE', emp.fullName, account.accountId)}
                                                className="text-xs text-red-600 hover:bg-red-50 px-2 py-1 rounded flex items-center gap-1 float-right ml-2"
                                                title="Suspend Account"
                                            >
                                                <Ban size={12}/> Suspend
                                            </button>
                                        </>
                                    ) : (
                                        <button 
                                            onClick={() => queueAction(ServiceType.MAILCOW, 'PROVISION', emp.fullName, emp)}
                                            className="text-xs bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700"
                                        >
                                            Create Mailbox
                                        </button>
                                    )}
                                </td>
                            </tr>
                        );
                    })}
                </tbody>
            </table>
        </div>
    );
};

const GitlabPanel = ({ employees, queueAction, config }: any) => {
    const adapter = getServiceAdapter(ServiceType.GITLAB);
    const [projects, setProjects] = useState<string[]>([]);
    const [selectedProject, setSelectedProject] = useState('');
    const [targetEmp, setTargetEmp] = useState<string | null>(null);

    React.useEffect(() => {
        if(adapter.getProjectList) {
            adapter.getProjectList(config).then(setProjects);
        }
    }, []);

    const handleAssign = () => {
        if (!targetEmp || !selectedProject) return;
        const emp = employees.find((e: Employee) => e.id === targetEmp);
        const account = emp?.accounts?.find(a => a.service === ServiceType.GITLAB);
        
        if (account) {
            queueAction(ServiceType.GITLAB, 'ASSIGN_PROJECT', emp.fullName, { accountId: account.accountId, projectId: selectedProject });
            setTargetEmp(null);
        }
    };

    return (
        <div className="p-6">
            <div className="flex justify-between items-center mb-6">
                <h3 className="font-bold text-lg text-gray-800">Git Service Users & Projects</h3>
                <div className="text-sm text-gray-500">Connected to: {config.gitlab.url}</div>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div className="md:col-span-2">
                    <table className="w-full text-left">
                        <thead className="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th className="px-4 py-3">Employee</th>
                                <th className="px-4 py-3">Git User</th>
                                <th className="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {employees.map((emp: Employee) => {
                                const account = emp.accounts?.find(a => a.service === ServiceType.GITLAB);
                                return (
                                    <tr key={emp.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 font-medium text-gray-700">{emp.fullName}</td>
                                        <td className="px-4 py-3">
                                            {account ? (
                                                <span className="inline-flex items-center gap-1 px-2 py-1 rounded bg-orange-100 text-orange-800 text-xs">
                                                   @{account.accountId}
                                                </span>
                                            ) : (
                                                <span className="text-gray-400 text-xs italic">Not on Git Service</span>
                                            )}
                                        </td>
                                        <td className="px-4 py-3 text-right">
                                            {account ? (
                                                <button 
                                                    onClick={() => setTargetEmp(emp.id)}
                                                    className="text-xs text-blue-600 hover:underline"
                                                >
                                                    Assign to Project
                                                </button>
                                            ) : (
                                                <button 
                                                    onClick={() => queueAction(ServiceType.GITLAB, 'PROVISION', emp.fullName, emp)}
                                                    className="text-xs bg-orange-600 text-white px-3 py-1 rounded hover:bg-orange-700"
                                                >
                                                    Create User
                                                </button>
                                            )}
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>

                {/* Assignment Sidebar */}
                <div className="bg-gray-50 p-4 rounded-lg border border-gray-200 h-fit">
                    <h4 className="font-semibold text-gray-800 mb-3">Project Assignment</h4>
                    {!targetEmp ? (
                        <p className="text-sm text-gray-500">Select an existing Git service user from the list to assign them to a project.</p>
                    ) : (
                        <div className="space-y-3">
                            <p className="text-sm font-medium">
                                Assigning: {employees.find((e: Employee) => e.id === targetEmp)?.fullName}
                            </p>
                            <select 
                                className="w-full p-2 border rounded bg-white"
                                value={selectedProject}
                                onChange={(e) => setSelectedProject(e.target.value)}
                            >
                                <option value="">Select Project...</option>
                                {projects.map(p => <option key={p} value={p}>{p}</option>)}
                            </select>
                            <div className="flex gap-2">
                                <button 
                                    onClick={() => setTargetEmp(null)}
                                    className="flex-1 py-1 text-sm bg-gray-200 rounded hover:bg-gray-300"
                                >
                                    Cancel
                                </button>
                                <button 
                                    onClick={handleAssign}
                                    disabled={!selectedProject}
                                    className="flex-1 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
                                >
                                    Queue Job
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
};

const KeycloakPanel = ({ employees, queueAction, config }: any) => {
    return (
        <div className="p-6">
            <div className="flex justify-between items-center mb-6">
                <h3 className="font-bold text-lg text-gray-800">Identity & Access Management</h3>
                <div className="text-sm text-gray-500">Connected to: {config.keycloak.url} (Realm: {config.keycloak.realm})</div>
            </div>
            
            <table className="w-full text-left">
                <thead className="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th className="px-4 py-3">Employee</th>
                        <th className="px-4 py-3">Keycloak User</th>
                        <th className="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                    {employees.map((emp: Employee) => {
                        const account = emp.accounts?.find(a => a.service === ServiceType.KEYCLOAK);
                        return (
                            <tr key={emp.id} className="hover:bg-gray-50">
                                <td className="px-4 py-3 font-medium text-gray-700">{emp.fullName}</td>
                                <td className="px-4 py-3">
                                    {account ? (
                                        <span className={`inline-flex items-center px-2 py-1 rounded text-xs bg-indigo-100 text-indigo-800`}>
                                            <Shield size={10} className="mr-1"/>
                                            {account.accountId}
                                        </span>
                                    ) : (
                                        <span className="text-gray-400 text-xs italic">No Identity</span>
                                    )}
                                </td>
                                <td className="px-4 py-3 text-right space-x-2">
                                    {account ? (
                                        <>
                                            <button 
                                                onClick={() => queueAction(ServiceType.KEYCLOAK, 'RESET_CREDENTIAL', emp.fullName, account.accountId)}
                                                className="text-xs border border-gray-300 px-2 py-1 rounded hover:bg-gray-100"
                                            >
                                                Send Reset Email
                                            </button>
                                            <button 
                                                onClick={() => queueAction(ServiceType.KEYCLOAK, 'DEACTIVATE', emp.fullName, account.accountId)}
                                                className="text-xs text-red-600 hover:bg-red-50 px-2 py-1 rounded flex items-center gap-1 float-right ml-2"
                                            >
                                                Disable
                                            </button>
                                        </>
                                    ) : (
                                        <button 
                                            onClick={() => queueAction(ServiceType.KEYCLOAK, 'PROVISION', emp.fullName, emp)}
                                            className="text-xs bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700"
                                        >
                                            Federate User
                                        </button>
                                    )}
                                </td>
                            </tr>
                        );
                    })}
                </tbody>
            </table>
        </div>
    );
};

export default Assets;