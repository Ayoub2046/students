import React, { useState } from 'react';
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
  ShieldCheck, 
  TrendingUp, 
  Users, 
  Archive, 
  Clock, 
  AlertTriangle, 
  Server, 
  Database, 
  FileSpreadsheet, 
  UserPlus, 
  Layers, 
  RefreshCw, 
  CheckCircle2, 
  ArrowRight,
  ShieldAlert,
  HardDrive,
  Activity,
  PlusCircle
} from 'lucide-react';
import { Item, AuditLog, User } from '../types';
import { ALL_USERS, CAMPUS_LOCATIONS, STORAGE_LOCKERS } from '../data/mockData';

interface AdminDashboardProps {
  items: Item[];
  auditLogs: AuditLog[];
  currentUser: User;
  onNavigateTab: (tab: string) => void;
  onSelectItem: (item: Item) => void;
  onOpenReportModal: (type: 'lost' | 'found') => void;
}

const MONTHLY_TREND = [
  { month: 'Mar', lost: 24, found: 30, returned: 22 },
  { month: 'Apr', lost: 35, found: 42, returned: 34 },
  { month: 'May', lost: 48, found: 56, returned: 47 },
  { month: 'Jun', lost: 19, found: 25, returned: 21 },
  { month: 'Jul', lost: 22, found: 28, returned: 24 },
  { month: 'Aug', lost: 38, found: 45, returned: 39 }
];

const CATEGORY_DISTRIBUTION = [
  { name: 'Electronics', count: 18, fill: '#4f46e5' },
  { name: 'Wallets & IDs', count: 32, fill: '#10b981' },
  { name: 'Keys', count: 24, fill: '#f59e0b' },
  { name: 'Clothing', count: 15, fill: '#ec4899' },
  { name: 'Bags & Notes', count: 20, fill: '#8b5cf6' }
];

export const AdminDashboard: React.FC<AdminDashboardProps> = ({
  items,
  auditLogs,
  currentUser,
  onNavigateTab,
  onSelectItem,
  onOpenReportModal
}) => {
  const [retentionDismissed, setRetentionDismissed] = useState(false);

  const returnedCount = items.filter(i => i.status === 'returned').length;
  const activeCount = items.filter(i => i.status !== 'returned' && i.status !== 'disposed').length;
  const readyHandoverCount = items.filter(i => i.status === 'ready_for_handover').length;
  const underVerificationCount = items.filter(i => i.status === 'under_verification').length;
  const expiredRetentionItems = items.filter(i => i.daysHeld >= 90 || i.status === 'unclaimed');
  const recoveryRate = Math.round((returnedCount / (items.length || 1)) * 100);

  return (
    <div className="w-full space-y-4">
      
      {/* Top Welcome & System Status Ribbon */}
      <div className="bg-[#1e1b4b] text-white rounded-xl p-4 sm:p-5 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border border-indigo-900">
        <div>
          <div className="flex items-center gap-2 mb-1">
            <span className="px-2 py-0.5 rounded bg-indigo-600/80 text-[10px] font-mono font-bold tracking-wider uppercase border border-indigo-400/40">
              Executive Admin Console
            </span>
            <span className="flex items-center gap-1 text-[11px] text-emerald-400 font-mono">
              <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
              CLUSTER HEALTH NORMAL
            </span>
          </div>
          <h1 className="text-lg sm:text-xl font-bold tracking-tight text-white">
            Welcome back, {currentUser.name}
          </h1>
          <p className="text-xs text-indigo-200 mt-0.5">
            {currentUser.department} &bull; University ID: <span className="font-mono text-white font-bold">{currentUser.universityId}</span>
          </p>
        </div>

        {/* Quick Executive Actions */}
        <div className="flex items-center gap-2 flex-wrap">
          <button
            onClick={() => onOpenReportModal('found')}
            className="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-sm transition-all"
          >
            <PlusCircle className="w-3.5 h-3.5" />
            <span>+ Found Intake</span>
          </button>
          <button
            onClick={() => onNavigateTab('users')}
            className="px-3 py-1.5 bg-indigo-800 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold flex items-center gap-1.5 border border-indigo-600 shadow-sm transition-all"
          >
            <Users className="w-3.5 h-3.5 text-indigo-300" />
            <span>Manage Users</span>
          </button>
          <button
            onClick={() => {
              const headers = 'ID,ReferenceNo,Title,Category,Type,Location,Date,Status\n';
              const rows = items.map(i => `"${i.id}","${i.referenceNo}","${i.title}","${i.category}","${i.type}","${i.building}","${i.dateReported}","${i.status}"`).join('\n');
              const blob = new Blob([headers + rows], { type: 'text/csv' });
              const url = URL.createObjectURL(blob);
              const a = document.createElement('a');
              a.href = url;
              a.download = `unilostfound_admin_export_${Date.now()}.csv`;
              a.click();
            }}
            className="px-3 py-1.5 bg-indigo-950/90 hover:bg-indigo-900 text-indigo-200 hover:text-white rounded-lg text-xs font-mono font-semibold flex items-center gap-1.5 border border-indigo-700/60 shadow-sm transition-all"
          >
            <FileSpreadsheet className="w-3.5 h-3.5 text-indigo-400" />
            <span>Export CSV</span>
          </button>
        </div>
      </div>

      {/* KPI Cards Grid */}
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-3">
        
        <div className="bg-white rounded-xl border border-slate-200 p-3.5 shadow-xs flex flex-col justify-between">
          <div className="flex items-center justify-between text-slate-500 font-mono text-[10px] uppercase font-bold">
            <span>Overall Recovery Rate</span>
            <TrendingUp className="w-4 h-4 text-emerald-600" />
          </div>
          <div className="my-1.5">
            <div className="text-2xl font-bold text-slate-900 font-mono">{recoveryRate}%</div>
            <div className="text-[10px] text-emerald-600 font-semibold flex items-center gap-1">
              <span>+6.4%</span> vs previous academic term
            </div>
          </div>
          <div className="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
            <div className="bg-emerald-500 h-full rounded-full" style={{ width: `${recoveryRate}%` }}></div>
          </div>
        </div>

        <div className="bg-white rounded-xl border border-slate-200 p-3.5 shadow-xs flex flex-col justify-between">
          <div className="flex items-center justify-between text-slate-500 font-mono text-[10px] uppercase font-bold">
            <span>Pending Claims & Handover</span>
            <Clock className="w-4 h-4 text-indigo-600" />
          </div>
          <div className="my-1.5">
            <div className="text-2xl font-bold text-slate-900 font-mono">
              {underVerificationCount + readyHandoverCount}
            </div>
            <div className="text-[10px] text-indigo-600 font-semibold">
              {readyHandoverCount} ready for digital signature release
            </div>
          </div>
          <button 
            onClick={() => onNavigateTab('claims')}
            className="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 text-left inline-flex items-center gap-0.5"
          >
            Review Queue <ArrowRight className="w-3 h-3" />
          </button>
        </div>

        <div className="bg-white rounded-xl border border-slate-200 p-3.5 shadow-xs flex flex-col justify-between">
          <div className="flex items-center justify-between text-slate-500 font-mono text-[10px] uppercase font-bold">
            <span>Total Logged Inventory</span>
            <Archive className="w-4 h-4 text-purple-600" />
          </div>
          <div className="my-1.5">
            <div className="text-2xl font-bold text-slate-900 font-mono">{items.length} Items</div>
            <div className="text-[10px] text-slate-500 font-mono">
              {activeCount} active in holding vaults
            </div>
          </div>
          <button 
            onClick={() => onNavigateTab('inventory')}
            className="text-[11px] font-bold text-purple-600 hover:text-purple-800 text-left inline-flex items-center gap-0.5"
          >
            View Master Registry <ArrowRight className="w-3 h-3" />
          </button>
        </div>

        <div className="bg-white rounded-xl border border-slate-200 p-3.5 shadow-xs flex flex-col justify-between">
          <div className="flex items-center justify-between text-slate-500 font-mono text-[10px] uppercase font-bold">
            <span>Enrolled Campus Users</span>
            <Users className="w-4 h-4 text-blue-600" />
          </div>
          <div className="my-1.5">
            <div className="text-2xl font-bold text-slate-900 font-mono">{ALL_USERS.length} Active</div>
            <div className="text-[10px] text-blue-600 font-semibold">
              Students, Desk Staff & Officers
            </div>
          </div>
          <button 
            onClick={() => onNavigateTab('users')}
            className="text-[11px] font-bold text-blue-600 hover:text-blue-800 text-left inline-flex items-center gap-0.5"
          >
            Manage User Directory <ArrowRight className="w-3 h-3" />
          </button>
        </div>

      </div>

      {/* 90-Day Retention Legal Alert Banner */}
      {!retentionDismissed && expiredRetentionItems.length > 0 && (
        <div className="bg-rose-50 border border-rose-200 rounded-xl p-3.5 flex items-start justify-between gap-3 text-rose-900">
          <div className="flex items-start gap-2.5">
            <AlertTriangle className="w-5 h-5 text-rose-600 shrink-0 mt-0.5" />
            <div>
              <div className="font-bold text-xs flex items-center gap-2">
                <span>90-Day Unclaimed Property Threshold Reached</span>
                <span className="bg-rose-200 text-rose-800 text-[10px] font-mono px-1.5 py-0.2 rounded font-bold">
                  {expiredRetentionItems.length} ITEM(S) ELIGIBLE FOR DISPOSITION
                </span>
              </div>
              <p className="text-[11px] text-rose-700 mt-0.5">
                Under state university statutory policy, items held past 90 days without ownership claim may be transferred to campus donation or public student charity auction.
              </p>
            </div>
          </div>
          <div className="flex items-center gap-2 shrink-0">
            <button
              onClick={() => onNavigateTab('storage')}
              className="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded text-[11px] font-bold shadow-xs"
            >
              Open Retention Desk
            </button>
            <button
              onClick={() => setRetentionDismissed(true)}
              className="text-rose-500 hover:text-rose-700 text-xs px-1"
            >
              &times;
            </button>
          </div>
        </div>
      )}

      {/* Charts & Campus Breakdown Section */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        {/* Intake vs Return Trend Chart */}
        <div className="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-4 shadow-xs">
          <div className="flex items-center justify-between pb-2.5 border-b border-slate-100 mb-3">
            <div>
              <h3 className="text-xs font-bold uppercase tracking-wider text-slate-800 font-mono">
                Monthly Intake vs Return Recovery Volume
              </h3>
              <p className="text-[11px] text-slate-400">Campus-wide lost vs found items resolved</p>
            </div>
            <span className="text-[10px] font-mono text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200 font-bold">
              2026 ACADEMIC TERM
            </span>
          </div>

          <div className="h-64 w-full">
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={MONTHLY_TREND} margin={{ top: 10, right: 10, left: -20, bottom: 0 }}>
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

        {/* Category Breakdown & Storage Capacity */}
        <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-xs flex flex-col justify-between space-y-3">
          <div>
            <div className="flex items-center justify-between pb-2 border-b border-slate-100">
              <h3 className="text-xs font-bold uppercase tracking-wider text-slate-800 font-mono">
                Storage Lockers & Vaults
              </h3>
              <span className="text-[10px] font-mono text-slate-400">8 Racks</span>
            </div>

            <div className="space-y-2 mt-3">
              {STORAGE_LOCKERS.slice(0, 4).map(locker => {
                const pct = Math.round((locker.currentCount / locker.capacity) * 100);
                return (
                  <div key={locker.id} className="p-2 bg-slate-50 rounded-lg border border-slate-200">
                    <div className="flex items-center justify-between text-xs mb-1">
                      <span className="font-bold text-slate-800 font-mono text-[11px]">{locker.rack}</span>
                      <span className="font-mono text-[10px] text-slate-500 font-bold">{locker.currentCount} / {locker.capacity}</span>
                    </div>
                    <div className="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                      <div 
                        className={`h-full rounded-full ${pct > 80 ? 'bg-amber-500' : 'bg-indigo-600'}`} 
                        style={{ width: `${pct}%` }}
                      ></div>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>

          <button
            onClick={() => onNavigateTab('storage')}
            className="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold font-mono transition-colors flex items-center justify-center gap-1"
          >
            <Layers className="w-3.5 h-3.5 text-indigo-600" />
            <span>Manage All 8 Vaults & Lockers</span>
          </button>
        </div>

      </div>

      {/* Bottom Grid: Recent High Priority Intake & Live Audit Stream */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
        
        {/* Recent High Priority Inventory Items */}
        <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-xs space-y-3">
          <div className="flex items-center justify-between pb-2 border-b border-slate-100">
            <h3 className="text-xs font-bold uppercase tracking-wider text-slate-800 font-mono flex items-center gap-1.5">
              <Archive className="w-3.5 h-3.5 text-indigo-600" />
              <span>Recent Intakes Awaiting Resolution</span>
            </h3>
            <button 
              onClick={() => onNavigateTab('inventory')}
              className="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 font-mono"
            >
              All Items ({items.length})
            </button>
          </div>

          <div className="divide-y divide-slate-100 max-h-72 overflow-y-auto">
            {items.slice(0, 5).map(item => (
              <div 
                key={item.id}
                onClick={() => onSelectItem(item)}
                className="py-2.5 flex items-center justify-between gap-3 hover:bg-slate-50 px-1 rounded cursor-pointer transition-colors"
              >
                <div className="flex items-center gap-2.5">
                  <img src={item.imageUrl} alt="" className="w-9 h-9 rounded object-cover border border-slate-200 shrink-0" referrerPolicy="no-referrer" />
                  <div className="overflow-hidden">
                    <div className="flex items-center gap-1.5 mb-0.5">
                      <span className="font-mono text-[10px] text-indigo-700 font-bold">{item.referenceNo}</span>
                      <span className={`text-[9px] font-mono font-bold px-1.5 py-0.2 rounded uppercase ${
                        item.type === 'found' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'
                      }`}>
                        {item.type}
                      </span>
                    </div>
                    <h4 className="text-xs font-bold text-slate-900 truncate max-w-xs">{item.title}</h4>
                    <span className="text-[10px] text-slate-500 font-mono">{item.building}</span>
                  </div>
                </div>

                <div className="text-right shrink-0">
                  <span className={`text-[10px] font-mono font-bold px-2 py-0.5 rounded uppercase block mb-1 ${
                    item.status === 'ready_for_handover' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600'
                  }`}>
                    {item.status.replace('_', ' ')}
                  </span>
                  <span className="text-[10px] text-indigo-600 font-semibold">Inspect &rarr;</span>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Real-time Audit Trail Logs */}
        <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-xs space-y-3">
          <div className="flex items-center justify-between pb-2 border-b border-slate-100">
            <h3 className="text-xs font-bold uppercase tracking-wider text-slate-800 font-mono flex items-center gap-1.5">
              <ShieldCheck className="w-3.5 h-3.5 text-emerald-600" />
              <span>Immutable Chain of Custody Audit Trail</span>
            </h3>
            <span className="text-[10px] font-mono text-slate-400">Live Stream</span>
          </div>

          <div className="space-y-2 max-h-72 overflow-y-auto pr-1">
            {auditLogs.map(log => (
              <div key={log.id} className="p-2.5 bg-slate-50 rounded-lg border border-slate-200 text-xs font-mono">
                <div className="flex items-center justify-between mb-1">
                  <span className="font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.2 rounded border border-indigo-200 text-[10px]">
                    {log.action}
                  </span>
                  <span className="text-[10px] text-slate-400">{log.timestamp}</span>
                </div>
                <div className="font-sans text-[11px] text-slate-800 mb-0.5">
                  <strong>{log.actor}</strong> ({log.actorRole}): {log.details}
                </div>
                <div className="text-[10px] text-slate-500 font-mono truncate">
                  Target: {log.target} &bull; IP: {log.ipAddress}
                </div>
              </div>
            ))}
          </div>
        </div>

      </div>

    </div>
  );
};
