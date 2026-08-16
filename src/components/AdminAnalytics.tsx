import React from 'react';
import { 
  BarChart, 
  Bar, 
  XAxis, 
  YAxis, 
  CartesianGrid, 
  Tooltip, 
  ResponsiveContainer, 
  PieChart, 
  Pie, 
  Cell,
  LineChart,
  Line
} from 'recharts';
import { 
  TrendingUp, 
  ShieldCheck, 
  RotateCcw, 
  Clock, 
  Users, 
  Archive, 
  FileSpreadsheet,
  CheckCircle2
} from 'lucide-react';
import { Item, AuditLog } from '../types';

interface AdminAnalyticsProps {
  items: Item[];
  auditLogs: AuditLog[];
}

const CATEGORY_DATA = [
  { name: 'Electronics', count: 18, returned: 14 },
  { name: 'Wallets & Cards', count: 24, returned: 22 },
  { name: 'Keys & IDs', count: 32, returned: 29 },
  { name: 'Clothing', count: 15, returned: 8 },
  { name: 'Bags & Luggage', count: 11, returned: 9 },
  { name: 'Books & Notes', count: 9, returned: 7 }
];

const RECOVERY_TREND = [
  { month: 'Mar', lost: 24, found: 30, returned: 22 },
  { month: 'Apr', lost: 35, found: 42, returned: 34 },
  { month: 'May', lost: 48, found: 56, returned: 47 },
  { month: 'Jun', lost: 19, found: 25, returned: 21 },
  { month: 'Jul', lost: 22, found: 28, returned: 24 },
  { month: 'Aug', lost: 38, found: 45, returned: 39 }
];

const PIE_STATUS = [
  { name: 'Returned to Owner', value: 68, color: '#10b981' },
  { name: 'In Storage Vault', value: 22, color: '#4f46e5' },
  { name: 'Pending Verification', value: 7, color: '#f59e0b' },
  { name: '90D Disposition', value: 3, color: '#ef4444' }
];

export const AdminAnalytics: React.FC<AdminAnalyticsProps> = ({ items, auditLogs }) => {
  return (
    <div className="w-full space-y-4">
      
      {/* High Density Metric Cards */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div className="bg-white border border-slate-200 rounded-lg p-3 shadow-xs">
          <div className="flex items-center justify-between text-slate-500 font-mono text-[10px] uppercase">
            <span>Overall Recovery Rate</span>
            <TrendingUp className="w-4 h-4 text-emerald-600" />
          </div>
          <div className="text-2xl font-bold text-slate-900 font-mono mt-1">
            86.4%
          </div>
          <div className="text-[10px] text-emerald-600 font-semibold mt-0.5">
            +4.2% from previous term
          </div>
        </div>

        <div className="bg-white border border-slate-200 rounded-lg p-3 shadow-xs">
          <div className="flex items-center justify-between text-slate-500 font-mono text-[10px] uppercase">
            <span>Average Return Time</span>
            <Clock className="w-4 h-4 text-indigo-600" />
          </div>
          <div className="text-2xl font-bold text-slate-900 font-mono mt-1">
            1.8 Days
          </div>
          <div className="text-[10px] text-indigo-600 font-semibold mt-0.5">
            Fastest turnaround for IDs & Cards
          </div>
        </div>

        <div className="bg-white border border-slate-200 rounded-lg p-3 shadow-xs">
          <div className="flex items-center justify-between text-slate-500 font-mono text-[10px] uppercase">
            <span>Total Logged Items (YTD)</span>
            <Archive className="w-4 h-4 text-purple-600" />
          </div>
          <div className="text-2xl font-bold text-slate-900 font-mono mt-1">
            842 Units
          </div>
          <div className="text-[10px] text-purple-600 font-semibold mt-0.5">
            100% verified chain of custody
          </div>
        </div>

        <div className="bg-white border border-slate-200 rounded-lg p-3 shadow-xs">
          <div className="flex items-center justify-between text-slate-500 font-mono text-[10px] uppercase">
            <span>Active Campus Claimants</span>
            <Users className="w-4 h-4 text-blue-600" />
          </div>
          <div className="text-2xl font-bold text-slate-900 font-mono mt-1">
            512 Users
          </div>
          <div className="text-[10px] text-blue-600 font-semibold mt-0.5">
            Students, faculty & staff enrolled
          </div>
        </div>
      </div>

      {/* Chart Rows */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
        
        {/* Trend Chart */}
        <div className="bg-white border border-slate-200 rounded-lg p-3.5 shadow-xs">
          <div className="flex items-center justify-between pb-2 border-b border-slate-100 mb-3">
            <h3 className="text-xs font-bold uppercase tracking-wider text-slate-700 font-mono">
              Monthly Intake vs Successful Return Volume
            </h3>
            <span className="text-[10px] font-mono text-slate-400">2026 Academic Year</span>
          </div>

          <div className="h-60 w-full">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={RECOVERY_TREND} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
                <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9" />
                <XAxis dataKey="month" tick={{ fontSize: 11 }} />
                <YAxis tick={{ fontSize: 11 }} />
                <Tooltip 
                  contentStyle={{ backgroundColor: '#1e1b4b', color: '#fff', borderRadius: '6px', fontSize: '11px', border: 'none' }}
                />
                <Bar dataKey="found" fill="#4f46e5" name="Items Found" radius={[4, 4, 0, 0]} />
                <Bar dataKey="returned" fill="#10b981" name="Items Returned" radius={[4, 4, 0, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>

        {/* Category breakdown */}
        <div className="bg-white border border-slate-200 rounded-lg p-3.5 shadow-xs">
          <div className="flex items-center justify-between pb-2 border-b border-slate-100 mb-3">
            <h3 className="text-xs font-bold uppercase tracking-wider text-slate-700 font-mono">
              Recovery by Item Category
            </h3>
            <span className="text-[10px] font-mono text-slate-400">Resolution Status</span>
          </div>

          <div className="h-60 w-full">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart layout="vertical" data={CATEGORY_DATA} margin={{ top: 5, right: 15, left: 20, bottom: 5 }}>
                <CartesianGrid strokeDasharray="3 3" stroke="#f1f5f9" />
                <XAxis type="number" tick={{ fontSize: 11 }} />
                <YAxis dataKey="name" type="category" tick={{ fontSize: 10 }} width={85} />
                <Tooltip 
                  contentStyle={{ backgroundColor: '#1e1b4b', color: '#fff', borderRadius: '6px', fontSize: '11px', border: 'none' }}
                />
                <Bar dataKey="count" fill="#94a3b8" name="Total Logged" radius={[0, 4, 4, 0]} />
                <Bar dataKey="returned" fill="#059669" name="Returned" radius={[0, 4, 4, 0]} />
              </BarChart>
            </ResponsiveContainer>
          </div>
        </div>

      </div>

      {/* System Audit Logs Feed */}
      <div className="bg-white border border-slate-200 rounded-lg p-3.5 shadow-xs space-y-3">
        <div className="flex items-center justify-between pb-2 border-b border-slate-100">
          <div className="flex items-center gap-2">
            <ShieldCheck className="w-4 h-4 text-indigo-600" />
            <h3 className="text-xs font-bold uppercase tracking-wider text-slate-700 font-mono">
              Immutable Chain of Custody Audit Trail
            </h3>
          </div>
          <button 
            onClick={() => alert('Audit logs exported to CSV.')}
            className="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 font-mono"
          >
            <FileSpreadsheet className="w-3.5 h-3.5" />
            <span>Export CSV</span>
          </button>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs border-collapse">
            <thead>
              <tr className="bg-slate-50 text-[10px] font-bold text-slate-500 font-mono uppercase border-b border-slate-200">
                <th className="py-2 px-3">Timestamp</th>
                <th className="py-2 px-3">Authorized Actor</th>
                <th className="py-2 px-3">Action Event</th>
                <th className="py-2 px-3">Target Reference</th>
                <th className="py-2 px-3">Operation Details</th>
                <th className="py-2 px-3">Terminal IP</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 text-[11px] font-mono">
              {auditLogs.map(log => (
                <tr key={log.id} className="hover:bg-slate-50/80">
                  <td className="py-2 px-3 text-slate-500 whitespace-nowrap">{log.timestamp}</td>
                  <td className="py-2 px-3 font-semibold text-slate-800 whitespace-nowrap">
                    {log.actor} <span className="text-[9px] text-slate-400 uppercase">({log.actorRole})</span>
                  </td>
                  <td className="py-2 px-3 whitespace-nowrap">
                    <span className="bg-indigo-50 text-indigo-700 px-1.5 py-0.5 rounded border border-indigo-200 text-[10px] font-bold">
                      {log.action}
                    </span>
                  </td>
                  <td className="py-2 px-3 font-bold text-slate-900 whitespace-nowrap">{log.target}</td>
                  <td className="py-2 px-3 text-slate-600 font-sans text-xs max-w-xs truncate">{log.details}</td>
                  <td className="py-2 px-3 text-slate-400 whitespace-nowrap">{log.ipAddress}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>

    </div>
  );
};
