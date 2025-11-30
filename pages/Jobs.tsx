import React, { useState, useEffect } from 'react';
import { SystemJob, AppConfig } from '../types';
import { RefreshCw, CheckCircle, XCircle, Clock, AlertCircle, Play } from 'lucide-react';
import { retryJob } from '../services/jobQueue';
import { getTenantData } from '../services/storage';

interface JobsProps {
    tenantId: string;
    config: AppConfig;
}

const Jobs: React.FC<JobsProps> = ({ tenantId, config }) => {
    const [jobs, setJobs] = useState<SystemJob[]>([]);
    const [autoRefresh, setAutoRefresh] = useState(true);

    const fetchJobs = () => {
        try {
            const data = getTenantData(tenantId);
            setJobs(data.jobs || []);
        } catch (e) {
            console.error("Failed to fetch jobs", e);
        }
    };

    useEffect(() => {
        fetchJobs();
        if (autoRefresh) {
            const interval = setInterval(fetchJobs, 2000); // Poll every 2s
            return () => clearInterval(interval);
        }
    }, [tenantId, autoRefresh]);

    const handleRetry = async (jobId: string) => {
        await retryJob(tenantId, jobId, config);
        fetchJobs(); // Update immediately to show pending status
    };

    const getStatusIcon = (status: SystemJob['status']) => {
        switch (status) {
            case 'completed': return <CheckCircle size={18} className="text-green-500" />;
            case 'failed': return <XCircle size={18} className="text-red-500" />;
            case 'processing': return <RefreshCw size={18} className="text-blue-500 animate-spin" />;
            case 'pending': return <Clock size={18} className="text-gray-400" />;
        }
    };

    const getStatusColor = (status: SystemJob['status']) => {
         switch (status) {
            case 'completed': return 'bg-green-50 text-green-700 border-green-100';
            case 'failed': return 'bg-red-50 text-red-700 border-red-100';
            case 'processing': return 'bg-blue-50 text-blue-700 border-blue-100';
            case 'pending': return 'bg-gray-50 text-gray-600 border-gray-100';
        }
    };

    return (
        <div className="space-y-6">
            <header className="flex justify-between items-center">
                <div>
                    <h2 className="text-2xl font-bold text-gray-800">System Jobs</h2>
                    <p className="text-gray-500">Monitor background tasks and service synchronization.</p>
                </div>
                <div className="flex items-center gap-3">
                    <label className="flex items-center text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" checked={autoRefresh} onChange={e => setAutoRefresh(e.target.checked)} className="mr-2"/>
                        Auto-refresh
                    </label>
                    <button onClick={fetchJobs} className="p-2 hover:bg-gray-100 rounded-full text-gray-500">
                        <RefreshCw size={18} />
                    </button>
                </div>
            </header>

            <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <table className="w-full text-left">
                    <thead className="bg-gray-50 text-xs uppercase text-gray-500 border-b border-gray-200">
                        <tr>
                            <th className="px-6 py-4">Status</th>
                            <th className="px-6 py-4">Service</th>
                            <th className="px-6 py-4">Action</th>
                            <th className="px-6 py-4">Target</th>
                            <th className="px-6 py-4">Result / Message</th>
                            <th className="px-6 py-4">Time</th>
                            <th className="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {jobs.length === 0 && (
                            <tr>
                                <td colSpan={7} className="px-6 py-12 text-center text-gray-400">
                                    No jobs found. Perform actions in "Digital Assets" to see tasks here.
                                </td>
                            </tr>
                        )}
                        {jobs.map(job => (
                            <tr key={job.id} className="hover:bg-gray-50 transition">
                                <td className="px-6 py-4">
                                    <span className={`inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-medium border ${getStatusColor(job.status)}`}>
                                        {getStatusIcon(job.status)}
                                        <span className="capitalize">{job.status}</span>
                                    </span>
                                </td>
                                <td className="px-6 py-4 text-sm font-medium text-gray-700 uppercase">{job.service}</td>
                                <td className="px-6 py-4 text-sm text-gray-600">{job.action.replace('_', ' ')}</td>
                                <td className="px-6 py-4 text-sm text-gray-800 font-medium">{job.targetName}</td>
                                <td className="px-6 py-4 text-sm text-gray-600 max-w-xs truncate" title={job.result || ''}>
                                    {job.result || '-'}
                                </td>
                                <td className="px-6 py-4 text-xs text-gray-500">
                                    {new Date(job.updatedAt).toLocaleTimeString()}
                                </td>
                                <td className="px-6 py-4 text-right">
                                    {job.status === 'failed' && (
                                        <button 
                                            onClick={() => handleRetry(job.id)}
                                            className="text-xs flex items-center gap-1 text-blue-600 hover:text-blue-800 ml-auto bg-blue-50 px-2 py-1 rounded hover:bg-blue-100 transition"
                                        >
                                            <Play size={12}/> Retry
                                        </button>
                                    )}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default Jobs;
