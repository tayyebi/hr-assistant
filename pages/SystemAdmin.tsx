
import React, { useState, useEffect } from 'react';
import { User, Tenant } from '../types';
import { getTenants, createTenant } from '../services/storage';
import { Plus, Building, LogOut } from 'lucide-react';

interface SystemAdminProps {
    user: User;
    onLogout: () => void;
}

const SystemAdmin: React.FC<SystemAdminProps> = ({ user, onLogout }) => {
    const [tenants, setTenants] = useState<Tenant[]>([]);
    const [newTenantName, setNewTenantName] = useState('');

    useEffect(() => {
        setTenants(getTenants());
    }, []);

    const handleCreateTenant = (e: React.FormEvent) => {
        e.preventDefault();
        if (newTenantName.trim()) {
            createTenant(newTenantName.trim());
            setTenants(getTenants()); // Refresh the list
            setNewTenantName('');
        }
    };

    return (
        <div className="min-h-screen bg-gray-50">
            <header className="bg-white shadow-sm">
                <div className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-bold text-blue-600">HR Assistant</h1>
                        <p className="text-sm text-gray-500">System Administration</p>
                    </div>
                    <div className="flex items-center gap-4">
                        <span className="text-sm text-gray-600">{user.email}</span>
                        <button onClick={onLogout} className="flex items-center gap-2 text-sm text-red-600 hover:text-red-800">
                            <LogOut size={16} />
                            Logout
                        </button>
                    </div>
                </div>
            </header>

            <main className="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <h2 className="text-xl font-bold text-gray-800 mb-6">Tenant Management</h2>
                
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {/* Tenant List */}
                    <div className="md:col-span-2 bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                        <h3 className="font-semibold text-lg text-gray-700 mb-4">All Tenants</h3>
                        <ul className="space-y-3">
                            {tenants.map(tenant => (
                                <li key={tenant.id} className="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <Building className="text-blue-500" />
                                    <div>
                                        <p className="font-medium text-gray-800">{tenant.name}</p>
                                        <p className="text-xs text-gray-500 font-mono">{tenant.id}</p>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </div>

                    {/* Create Tenant Form */}
                    <div className="bg-white p-6 rounded-xl border border-gray-100 shadow-sm h-fit">
                        <h3 className="font-semibold text-lg text-gray-700 mb-4">Add New Tenant</h3>
                        <form onSubmit={handleCreateTenant} className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-600 mb-1">Business Name</label>
                                <input 
                                    type="text"
                                    value={newTenantName}
                                    onChange={(e) => setNewTenantName(e.target.value)}
                                    className="w-full p-2 border rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"
                                    placeholder="e.g. Innovate Inc."
                                    required
                                />
                            </div>
                            <button type="submit" className="w-full py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 flex items-center justify-center gap-2">
                                <Plus size={18} />
                                Create Tenant
                            </button>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    );
};

export default SystemAdmin;
