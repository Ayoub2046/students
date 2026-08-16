import React, { useState } from 'react';
import { 
  Compass, 
  Search, 
  Bell, 
  PlusCircle, 
  AlertOctagon, 
  ShieldCheck, 
  UserCheck, 
  Grid, 
  MapPin, 
  ChevronDown, 
  Menu, 
  X, 
  Users, 
  BarChart3, 
  MessageSquare, 
  FolderOpen,
  FileSpreadsheet,
  CheckCircle2,
  Lock,
  Layers,
  Inbox
} from 'lucide-react';
import { User, UserRole, NotificationItem } from '../types';

interface HeaderProps {
  currentUser: User;
  onRoleChange: (role: UserRole) => void;
  activeView: string;
  setActiveView: (view: string) => void;
  searchQuery: string;
  setSearchQuery: (query: string) => void;
  onOpenReportLost: () => void;
  onOpenReportFound: () => void;
  notifications: NotificationItem[];
  onMarkNotificationRead: (id: string) => void;
  toggleMobileMenu: () => void;
  isMobileMenuOpen: boolean;
  onExportCSV: () => void;
}

export const Header: React.FC<HeaderProps> = ({
  currentUser,
  onRoleChange,
  activeView,
  setActiveView,
  searchQuery,
  setSearchQuery,
  onOpenReportLost,
  onOpenReportFound,
  notifications,
  onMarkNotificationRead,
  toggleMobileMenu,
  isMobileMenuOpen,
  onExportCSV
}) => {
  const [showNotifications, setShowNotifications] = useState(false);
  const [showUserMenu, setShowUserMenu] = useState(false);
  const unreadCount = notifications.filter(n => !n.read).length;

  const NAV_ITEMS = [
    { id: 'dashboard', label: 'Dashboard', icon: Layers },
    { id: 'inventory', label: 'Inventory', icon: Grid },
    { id: 'claims', label: 'Claims', icon: CheckCircle2 },
    { id: 'users', label: 'Users', icon: Users },
    { id: 'map', label: 'Campus Map', icon: MapPin },
    { id: 'analytics', label: 'Reports', icon: BarChart3 },
    { id: 'messages', label: 'Messages', icon: MessageSquare }
  ];

  return (
    <header className="w-full bg-[#1e1b4b] border-b border-[#312e81] text-white sticky top-0 z-40 shadow-md">
      
      {/* Top Main Navigation Bar */}
      <div className="w-full px-3 sm:px-6 py-2.5 flex items-center justify-between gap-3">
        
        {/* Left: Brand Logo */}
        <div className="flex items-center gap-3 shrink-0">
          <button 
            onClick={toggleMobileMenu}
            className="lg:hidden p-1.5 rounded text-indigo-200 hover:text-white hover:bg-indigo-900/60 focus:outline-none"
            aria-label="Toggle menu"
          >
            {isMobileMenuOpen ? <X className="w-5 h-5" /> : <Menu className="w-5 h-5" />}
          </button>

          <button 
            onClick={() => setActiveView('dashboard')}
            className="flex items-center gap-2.5 text-left focus:outline-none group"
          >
            <div className="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white shadow-inner group-hover:bg-indigo-500 transition-colors font-bold font-mono">
              <Compass className="w-5 h-5" />
            </div>
            <div className="flex flex-col leading-none">
              <div className="flex items-center gap-1.5">
                <span className="text-sm font-extrabold tracking-tight text-white font-sans">
                  UniLostFound
                </span>
                <span className="text-[10px] font-mono text-emerald-400 font-bold bg-emerald-950/80 px-1.5 py-0.2 rounded border border-emerald-700/50">
                  v2.4.1
                </span>
              </div>
              <span className="text-[9px] text-indigo-300 font-mono tracking-wider mt-0.5">
                UNIVERSITY LOST & FOUND SYSTEM
              </span>
            </div>
          </button>
        </div>

        {/* Center: Primary Navigation Tabs */}
        <nav className="hidden lg:flex items-center gap-1">
          {NAV_ITEMS.map(tab => {
            const Icon = tab.icon;
            const isActive = activeView === tab.id;
            return (
              <button
                key={tab.id}
                onClick={() => setActiveView(tab.id)}
                className={`px-3 py-1.5 text-xs font-semibold rounded-md transition-all flex items-center gap-1.5 ${
                  isActive 
                    ? 'bg-indigo-600 text-white shadow-sm font-bold' 
                    : 'text-indigo-200 hover:text-white hover:bg-indigo-900/60'
                }`}
              >
                <Icon className="w-3.5 h-3.5" />
                <span>{tab.label}</span>
              </button>
            );
          })}
        </nav>

        {/* Right: Actions, Role Selector & User Profile */}
        <div className="flex items-center gap-2 shrink-0">
          
          {/* Quick Intake Actions */}
          <div className="hidden md:flex items-center gap-1.5">
            <button
              onClick={onOpenReportLost}
              className="flex items-center gap-1 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold px-2.5 py-1.5 rounded-md border border-rose-500 shadow-xs transition-all"
            >
              <AlertOctagon className="w-3.5 h-3.5" />
              <span>Lost</span>
            </button>
            <button
              onClick={onOpenReportFound}
              className="flex items-center gap-1 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold px-2.5 py-1.5 rounded-md border border-emerald-500 shadow-xs transition-all"
            >
              <PlusCircle className="w-3.5 h-3.5" />
              <span>Found</span>
            </button>
            <button
              onClick={onExportCSV}
              className="flex items-center gap-1 bg-indigo-950/80 hover:bg-indigo-900 text-indigo-200 hover:text-white text-xs font-mono font-semibold px-2.5 py-1.5 rounded-md border border-indigo-700/60 transition-all"
              title="Export Full Inventory as CSV"
            >
              <FileSpreadsheet className="w-3.5 h-3.5 text-indigo-400" />
              <span className="hidden xl:inline">Export CSV</span>
            </button>
          </div>

          {/* Notifications Dropdown */}
          <div className="relative">
            <button
              onClick={() => setShowNotifications(!showNotifications)}
              className="p-1.5 rounded-md bg-indigo-950/70 border border-indigo-700/60 text-indigo-200 hover:text-white hover:bg-indigo-900/80 transition-colors relative"
              aria-label="Notifications"
            >
              <Bell className="w-4 h-4" />
              {unreadCount > 0 && (
                <span className="absolute -top-1 -right-1 bg-rose-500 text-white font-mono font-bold text-[9px] w-4 h-4 rounded-full flex items-center justify-center border border-indigo-950">
                  {unreadCount}
                </span>
              )}
            </button>

            {showNotifications && (
              <div className="absolute right-0 mt-2 w-80 sm:w-96 bg-white text-slate-800 rounded-lg shadow-xl border border-slate-200 z-50 overflow-hidden">
                <div className="px-3.5 py-2 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                  <span className="text-xs font-bold uppercase tracking-wider text-slate-600 font-mono">System Alerts & Updates</span>
                  <span className="text-[10px] font-mono text-indigo-600 font-bold">{unreadCount} UNREAD</span>
                </div>
                <div className="max-h-72 overflow-y-auto divide-y divide-slate-100">
                  {notifications.length === 0 ? (
                    <div className="p-4 text-center text-xs text-slate-400 font-mono">No new notifications</div>
                  ) : (
                    notifications.map(n => (
                      <div 
                        key={n.id} 
                        onClick={() => onMarkNotificationRead(n.id)}
                        className={`p-3 text-xs cursor-pointer hover:bg-slate-50 transition-colors ${!n.read ? 'bg-indigo-50/60' : ''}`}
                      >
                        <div className="flex items-center justify-between mb-1">
                          <span className="font-bold text-slate-800">{n.title}</span>
                          <span className="text-[10px] font-mono text-slate-400">{n.timestamp}</span>
                        </div>
                        <p className="text-slate-600 text-[11px] leading-relaxed">{n.message}</p>
                      </div>
                    ))
                  )}
                </div>
                <div className="p-2 bg-slate-50 border-t border-slate-200 text-center">
                  <button 
                    onClick={() => setShowNotifications(false)}
                    className="text-[11px] font-semibold text-indigo-600 hover:text-indigo-800 font-mono"
                  >
                    Close
                  </button>
                </div>
              </div>
            )}
          </div>

          {/* Quick Role Switcher Pill & Dropdown */}
          <div className="relative">
            <button
              onClick={() => setShowUserMenu(!showUserMenu)}
              className="flex items-center gap-2 bg-indigo-950/80 hover:bg-indigo-900 border border-indigo-700/70 py-1 px-2.5 rounded-lg text-left transition-all"
            >
              <div className={`w-6 h-6 rounded flex items-center justify-center font-bold text-xs text-white border ${
                currentUser.role === 'admin' ? 'bg-purple-600 border-purple-400' :
                currentUser.role === 'officer' ? 'bg-amber-600 border-amber-400' :
                currentUser.role === 'staff' ? 'bg-emerald-600 border-emerald-400' :
                'bg-indigo-600 border-indigo-400'
              }`}>
                {currentUser.name.charAt(0)}
              </div>
              <div className="hidden sm:flex flex-col text-[11px] leading-tight">
                <span className="font-bold text-white max-w-[120px] truncate">{currentUser.name}</span>
                <span className="text-[9px] font-mono text-indigo-300 uppercase tracking-tight flex items-center gap-1">
                  <span>Role: {currentUser.role}</span>
                </span>
              </div>
              <ChevronDown className="w-3.5 h-3.5 text-indigo-300" />
            </button>

            {showUserMenu && (
              <div className="absolute right-0 mt-2 w-72 bg-white text-slate-800 rounded-xl shadow-2xl border border-slate-200 z-50 overflow-hidden">
                <div className="p-3.5 bg-slate-50 border-b border-slate-200">
                  <div className="flex items-center justify-between">
                    <span className="font-bold text-xs text-slate-900">{currentUser.name}</span>
                    <span className="px-1.5 py-0.2 rounded bg-indigo-100 text-indigo-700 font-mono font-bold text-[10px] uppercase">
                      {currentUser.role}
                    </span>
                  </div>
                  <p className="text-[11px] font-mono text-slate-500 mt-0.5">{currentUser.email}</p>
                  <p className="text-[10px] text-slate-600 font-mono mt-1">
                    {currentUser.department} &bull; ID: {currentUser.universityId}
                  </p>
                </div>

                <div className="p-3">
                  <div className="text-[10px] font-bold uppercase text-slate-400 tracking-wider mb-2 font-mono">
                    Switch Test User Role Dashboard
                  </div>
                  
                  <div className="grid grid-cols-2 gap-1.5">
                    <button
                      onClick={() => { onRoleChange('admin'); setShowUserMenu(false); }}
                      className={`p-2 rounded-lg text-left transition-all border text-xs ${
                        currentUser.role === 'admin' 
                          ? 'bg-purple-50 border-purple-300 text-purple-900 font-bold' 
                          : 'bg-slate-50 border-slate-200 hover:bg-slate-100 text-slate-700'
                      }`}
                    >
                      <div className="font-bold">Admin</div>
                      <div className="text-[10px] text-slate-500">Dr. Elena / IT</div>
                    </button>

                    <button
                      onClick={() => { onRoleChange('staff'); setShowUserMenu(false); }}
                      className={`p-2 rounded-lg text-left transition-all border text-xs ${
                        currentUser.role === 'staff' 
                          ? 'bg-emerald-50 border-emerald-300 text-emerald-900 font-bold' 
                          : 'bg-slate-50 border-slate-200 hover:bg-slate-100 text-slate-700'
                      }`}
                    >
                      <div className="font-bold">Desk Staff</div>
                      <div className="text-[10px] text-slate-500">Sarah / Library</div>
                    </button>

                    <button
                      onClick={() => { onRoleChange('officer'); setShowUserMenu(false); }}
                      className={`p-2 rounded-lg text-left transition-all border text-xs ${
                        currentUser.role === 'officer' 
                          ? 'bg-amber-50 border-amber-300 text-amber-900 font-bold' 
                          : 'bg-slate-50 border-slate-200 hover:bg-slate-100 text-slate-700'
                      }`}
                    >
                      <div className="font-bold">Officer</div>
                      <div className="text-[10px] text-slate-500">David / Vault</div>
                    </button>

                    <button
                      onClick={() => { onRoleChange('student'); setShowUserMenu(false); }}
                      className={`p-2 rounded-lg text-left transition-all border text-xs ${
                        currentUser.role === 'student' 
                          ? 'bg-blue-50 border-blue-300 text-blue-900 font-bold' 
                          : 'bg-slate-50 border-slate-200 hover:bg-slate-100 text-slate-700'
                      }`}
                    >
                      <div className="font-bold">Student</div>
                      <div className="text-[10px] text-slate-500">Alex / Student</div>
                    </button>
                  </div>
                </div>

                <div className="border-t border-slate-100 p-2 bg-slate-50 flex items-center justify-between text-xs">
                  <button 
                    onClick={() => {
                      setActiveView('my-reports');
                      setShowUserMenu(false);
                    }}
                    className="text-[11px] font-semibold text-indigo-600 hover:text-indigo-800"
                  >
                    My Submissions & Claims
                  </button>
                  <button
                    onClick={() => setShowUserMenu(false)}
                    className="text-[11px] text-slate-500 hover:text-slate-700"
                  >
                    Dismiss
                  </button>
                </div>
              </div>
            )}
          </div>

        </div>

      </div>

      {/* Mobile Sub-Navigation Bar */}
      <div className="lg:hidden px-3 py-1.5 bg-[#17143a] border-t border-indigo-900/60 overflow-x-auto flex items-center gap-1.5 scrollbar-none">
        {NAV_ITEMS.map(tab => {
          const Icon = tab.icon;
          const isActive = activeView === tab.id;
          return (
            <button
              key={tab.id}
              onClick={() => setActiveView(tab.id)}
              className={`px-2.5 py-1 text-[11px] font-semibold rounded whitespace-nowrap flex items-center gap-1 ${
                isActive 
                  ? 'bg-indigo-600 text-white font-bold' 
                  : 'text-indigo-300 hover:text-white'
              }`}
            >
              <Icon className="w-3 h-3" />
              <span>{tab.label}</span>
            </button>
          );
        })}
      </div>

    </header>
  );
};
