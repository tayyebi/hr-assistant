

import React, { useState } from 'react';
import { Employee, Team } from '../types';
import { Users, Mail, Settings, Plus, X } from 'lucide-react';

interface TeamsProps {
  teams: Team[];
  employees: Employee[];
  setTeams: (teams: Team[]) => void;
  setEmployees: (employees: Employee[]) => void;
  tenantId: string;
}

const Teams: React.FC<TeamsProps> = ({ teams, employees, setTeams, setEmployees, tenantId }) => {
  const [selectedTeam, setSelectedTeam] = useState<Team | null>(null);
  const [isAliasModalOpen, setIsAliasModalOpen] = useState(false);
  const [isCreateTeamModalOpen, setIsCreateTeamModalOpen] = useState(false);
  const [newAlias, setNewAlias] = useState('');
  const [newTeamName, setNewTeamName] = useState('');

  const handleCreateTeamClick = () => {
    setIsCreateTeamModalOpen(true);
  };

  const handleSaveNewTeam = () => {
    if (!newTeamName.trim()) {
      alert("Team name cannot be empty.");
      return;
    }
    const newTeam: Team = {
        id: `team_${Date.now()}`,
        tenantId: tenantId,
        name: newTeamName.trim(),
        description: 'New team description',
        memberIds: [],
        emailAliases: []
    };
    setTeams([...teams, newTeam]);
    setNewTeamName('');
    setIsCreateTeamModalOpen(false);
  };

  const handleAddMember = (teamId: string, employeeId: string) => {
    const team = teams.find(t => t.id === teamId);
    if (!team || team.memberIds.includes(employeeId)) return;
    
    const updatedTeam = { ...team, memberIds: [...team.memberIds, employeeId] };
    setTeams(teams.map(t => t.id === teamId ? updatedTeam : t));
    setEmployees(employees.map(e => e.id === employeeId ? { ...e, teamId } : e));
  };

  const handleRemoveMember = (teamId: string, employeeId: string) => {
    const team = teams.find(t => t.id === teamId);
    if (!team) return;

    const updatedTeam = { ...team, memberIds: team.memberIds.filter(id => id !== employeeId) };
    setTeams(teams.map(t => t.id === teamId ? updatedTeam : t));
    setEmployees(employees.map(e => e.id === employeeId ? { ...e, teamId: undefined } : e));
  };

  const handleRemoveAlias = (teamId: string, aliasToRemove: string) => {
    if (!window.confirm(`Are you sure you want to remove the alias "${aliasToRemove}"?`)) {
        return;
    }
      
    const team = teams.find(t => t.id === teamId);
    if (!team) return;

    const updatedTeam = {
      ...team,
      emailAliases: team.emailAliases.filter(alias => alias !== aliasToRemove)
    };
      
    setTeams(teams.map(t => (t.id === teamId ? updatedTeam : t)));

    if (selectedTeam && selectedTeam.id === teamId) {
        setSelectedTeam(updatedTeam);
    }
    
    alert(`Alias ${aliasToRemove} queued for deletion.`);
  };

  const handleAddAlias = () => {
      if (!selectedTeam || !newAlias) return;
      if (!newAlias.includes('@')) {
          alert("Please enter a valid email format");
          return;
      }
      
      const updatedTeam = { ...selectedTeam, emailAliases: [...selectedTeam.emailAliases, newAlias] };
      setTeams(teams.map(t => t.id === selectedTeam.id ? updatedTeam : t));
      setSelectedTeam(updatedTeam);
      setNewAlias('');
      setIsAliasModalOpen(false);
      alert(`Alias ${newAlias} queued for creation.`);
  };

  return (
    <div className="space-y-6">
      <header className="flex justify-between items-center">
        <div>
            <h2 className="text-2xl font-bold text-gray-800">Team Management</h2>
            <p className="text-gray-500 text-sm">Organize people and assign functional aliases.</p>
        </div>
        <button onClick={handleCreateTeamClick} className="bg-slate-800 text-white px-4 py-2 rounded-lg flex items-center space-x-2 hover:bg-slate-700">
            <Plus size={16} />
            <span>Create Team</span>
        </button>
      </header>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {teams.map(team => (
            <div key={team.id} className="bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col">
                <div className="p-6 border-b border-gray-100 flex justify-between items-start">
                    <div>
                        <h3 className="text-xl font-bold text-gray-800">{team.name}</h3>
                        <p className="text-sm text-gray-500 mt-1">{team.description}</p>
                    </div>
                    <div className="flex space-x-2">
                         <button 
                            onClick={() => { setSelectedTeam(team); setIsAliasModalOpen(true); }}
                            className="p-2 text-gray-400 hover:bg-blue-50 hover:text-blue-600 rounded transition"
                            title="Manage Email Aliases"
                         >
                             <Mail size={18} />
                         </button>
                    </div>
                </div>

                {team.emailAliases.length > 0 && (
                    <div className="px-6 py-3 bg-blue-50/50 border-b border-gray-100">
                        <p className="text-xs font-semibold text-blue-800 uppercase tracking-wide mb-2">Active Email Aliases</p>
                        <div className="flex flex-wrap gap-2">
                            {team.emailAliases.map(alias => (
                                <div key={alias} className="flex items-center text-xs bg-white border border-blue-200 text-blue-700 px-2 py-1 rounded-full shadow-sm">
                                    <span>{alias}</span>
                                    <button 
                                        onClick={() => handleRemoveAlias(team.id, alias)} 
                                        className="ml-1.5 text-blue-400 hover:text-blue-700 p-0.5 rounded-full hover:bg-blue-100"
                                        title={`Remove alias ${alias}`}
                                    >
                                        <X size={12} />
                                    </button>
                                </div>
                            ))}
                        </div>
                    </div>
                )}

                <div className="p-6 flex-1">
                    <div className="flex justify-between items-center mb-3">
                        <h4 className="font-semibold text-gray-700 text-sm">Members ({team.memberIds.length})</h4>
                        <select 
                            className="text-xs border border-gray-300 rounded p-1"
                            onChange={(e) => {
                                if(e.target.value) handleAddMember(team.id, e.target.value);
                                e.target.value = '';
                            }}
                            value=""
                        >
                            <option value="">+ Add Member</option>
                            {employees.filter(e => !team.memberIds.includes(e.id)).map(e => (
                                <option key={e.id} value={e.id}>{e.fullName}</option>
                            ))}
                        </select>
                    </div>
                    <ul className="space-y-2">
                        {team.memberIds.map(mid => {
                            const member = employees.find(e => e.id === mid);
                            if (!member) return null;
                            return (
                                <li key={mid} className="flex justify-between items-center bg-gray-50 p-2 rounded">
                                    <div className="flex items-center space-x-2">
                                        <div className="w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center text-xs font-bold text-slate-600">
                                            {member.fullName[0]}
                                        </div>
                                        <span className="text-sm text-gray-700">{member.fullName}</span>
                                    </div>
                                    <button onClick={() => handleRemoveMember(team.id, mid)} className="text-gray-400 hover:text-red-500">
                                        <X size={14} />
                                    </button>
                                </li>
                            );
                        })}
                        {team.memberIds.length === 0 && <li className="text-sm text-gray-400 italic">No members yet.</li>}
                    </ul>
                </div>
                
                <div className="p-4 bg-gray-50 rounded-b-xl border-t border-gray-100">
                     <p className="text-sm text-gray-400 text-center">Sentiment analysis unavailable.</p>
                </div>
            </div>
        ))}
      </div>

      {isAliasModalOpen && selectedTeam && (
          <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
              <div className="bg-white rounded-lg p-6 w-full max-w-md">
                  <h3 className="text-lg font-bold mb-4">Add Email Alias for {selectedTeam.name}</h3>
                  <p className="text-sm text-gray-500 mb-4">This will create an alias in your configured Mail service.</p>
                  
                  <input 
                    type="email" 
                    placeholder="e.g. support@example.com"
                    value={newAlias}
                    onChange={e => setNewAlias(e.target.value)}
                    className="w-full border p-2 rounded mb-4"
                  />

                  <div className="flex justify-end space-x-2">
                      <button onClick={() => setIsAliasModalOpen(false)} className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Cancel</button>
                      <button onClick={handleAddAlias} className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Create Alias</button>
                  </div>
              </div>
          </div>
      )}

      {isCreateTeamModalOpen && (
        <div className="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
          <div className="bg-white rounded-xl max-w-md w-full">
            <div className="p-6 border-b border-gray-100 flex justify-between items-center">
              <h3 className="text-xl font-bold text-gray-800">Create New Team</h3>
              <button onClick={() => setIsCreateTeamModalOpen(false)} className="text-gray-400 hover:text-gray-600">
                <span className="text-2xl">&times;</span>
              </button>
            </div>
            
            <form onSubmit={(e) => { e.preventDefault(); handleSaveNewTeam(); }} className="p-6 space-y-4">
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Team Name</label>
                <input 
                  required 
                  type="text" 
                  className="w-full p-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 outline-none" 
                  value={newTeamName} 
                  onChange={e => setNewTeamName(e.target.value)}
                  placeholder="e.g. Marketing Team"
                />
              </div>

              <div className="pt-4 flex justify-end space-x-3 border-t border-gray-100 mt-6">
                <button type="button" onClick={() => setIsCreateTeamModalOpen(false)} className="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded">Cancel</button>
                <button type="submit" className="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 shadow-md">
                    Create Team
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};

export default Teams;