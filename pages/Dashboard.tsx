


import React from 'react';
import { Employee, Feeling } from '../types';
import { PieChart, Pie, Cell, ResponsiveContainer, Tooltip, Legend } from 'recharts';
import { Bell, Calendar, TrendingUp, MessageCircle, X } from 'lucide-react';
import { sendTelegramMessage } from '../services/telegramService';


interface DashboardProps {
  employees: Employee[];
}

const Dashboard: React.FC<DashboardProps> = ({ employees }) => {
  const [draft, setDraft] = React.useState<string | null>(null);
  const [targetEmp, setTargetEmp] = React.useState<Employee | null>(null);

  // Calculate Sentiment Stats
  const feelingCounts = employees.reduce((acc, emp) => {
    const last = emp.feelingsLog[emp.feelingsLog.length - 1]?.feeling;
    if (last) acc[last] = (acc[last] || 0) + 1;
    return acc;
  }, {} as Record<string, number>);

  const chartData = [
    { name: 'Happy', value: feelingCounts[Feeling.HAPPY] || 0, color: '#22c55e' },
    { name: 'Neutral', value: feelingCounts[Feeling.NEUTRAL] || 0, color: '#94a3b8' },
    { name: 'Sad', value: feelingCounts[Feeling.SAD] || 0, color: '#ef4444' },
  ].filter(d => d.value > 0);

  // Find upcoming birthdays (next 30 days)
  const upcomingBirthdays = employees.filter(emp => {
    const today = new Date();
    const dob = new Date(emp.birthday);
    const currentYearDob = new Date(today.getFullYear(), dob.getMonth(), dob.getDate());
    const diffTime = currentYearDob.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
    return diffDays >= 0 && diffDays <= 30;
  });

  const handleOpenDraft = (emp: Employee) => {
    setTargetEmp(emp);
    // Auto-generate initial draft based on context (Birthday in this case)
    const msg = `Happy Birthday ${emp.fullName}! We hope you have a wonderful day! 🎂`;
    setDraft(msg);
  };

  const handleSend = async () => {
      if(targetEmp?.telegramChatId && draft) {
          await sendTelegramMessage(targetEmp.telegramChatId, draft);
          alert("Message sent!");
          setDraft(null);
          setTargetEmp(null);
      } else {
          alert("User has no connected Telegram Chat ID.");
      }
  };

  return (
    <div className="space-y-6">
      <header className="mb-8">
        <h2 className="text-3xl font-bold text-gray-800">HR Command Center</h2>
        <p className="text-gray-500">Welcome back. Here's what's happening today.</p>
      </header>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        {/* Sentiment Card */}
        <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100 col-span-1 md:col-span-2">
          <div className="flex justify-between items-center mb-4">
            <h3 className="font-semibold text-lg flex items-center space-x-2">
              <TrendingUp className="text-blue-500" />
              <span>Team Sentiment Overview</span>
            </h3>
            <span className="text-xs font-medium bg-blue-100 text-blue-700 px-2 py-1 rounded">Last 24h</span>
          </div>
          <div className="h-64 flex items-center justify-center">
            {chartData.length > 0 ? (
              <ResponsiveContainer width="100%" height="100%">
                <PieChart>
                  <Pie
                    data={chartData}
                    cx="50%"
                    cy="50%"
                    innerRadius={60}
                    outerRadius={80}
                    paddingAngle={5}
                    dataKey="value"
                  >
                    {chartData.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={entry.color} />
                    ))}
                  </Pie>
                  <Tooltip />
                  <Legend verticalAlign="bottom" height={36}/>
                </PieChart>
              </ResponsiveContainer>
            ) : (
                <p className="text-gray-400">No sentiment data available for today.</p>
            )}
          </div>
        </div>

        {/* Reminders Card */}
        <div className="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
          <h3 className="font-semibold text-lg flex items-center space-x-2 mb-4">
            <Bell className="text-amber-500" />
            <span>Reminders</span>
          </h3>
          
          <div className="space-y-4">
            {upcomingBirthdays.length === 0 && <p className="text-gray-400 text-sm">No upcoming events.</p>}
            {upcomingBirthdays.map(emp => (
                <div key={emp.id} className="flex flex-col p-3 bg-amber-50 border border-amber-100 rounded-lg">
                    <div className="flex justify-between items-center">
                        <div className="flex items-center space-x-3">
                            <div className="bg-amber-200 p-2 rounded-full">
                                <Calendar size={16} className="text-amber-700" />
                            </div>
                            <div>
                                <p className="font-medium text-gray-800">{emp.fullName}</p>
                                <p className="text-xs text-gray-500">Birthday: {emp.birthday}</p>
                            </div>
                        </div>
                    </div>
                    {emp.telegramChatId && (
                        <button 
                            onClick={() => handleOpenDraft(emp)}
                            className="mt-3 flex items-center justify-center gap-2 text-xs bg-white border border-amber-200 text-amber-700 py-1.5 px-2 rounded hover:bg-amber-100 transition-colors w-full text-center"
                        >
                            <MessageCircle size={14} /> Draft Message
                        </button>
                    )}
                </div>
            ))}
          </div>
        </div>
      </div>

      {/* Draft Modal */}
      {draft !== null && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div className="bg-white rounded-xl max-w-lg w-full p-6 shadow-2xl transform transition-all">
                <div className="flex justify-between items-center mb-4">
                    <h3 className="text-lg font-bold text-gray-800">Draft Message for {targetEmp?.fullName}</h3>
                    <button onClick={() => setDraft(null)}><X size={24} className="text-gray-400"/></button>
                </div>
                
                <textarea 
                    className="w-full bg-gray-50 p-4 rounded border border-gray-200 mb-4 font-mono text-sm text-gray-700 outline-none focus:ring-2 focus:ring-blue-500"
                    rows={4}
                    value={draft}
                    onChange={(e) => setDraft(e.target.value)}
                />

                <div className="flex space-x-3 justify-end items-center">
                    <button 
                        onClick={() => setDraft(null)}
                        className="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg"
                    >
                        Discard
                    </button>
                    <button 
                         onClick={handleSend}
                        className="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg shadow-lg shadow-blue-500/30"
                    >
                        Send Now
                    </button>
                </div>
            </div>
        </div>
      )}
    </div>
  );
};

export default Dashboard;