import React from 'react';
import { 
  Layers, 
  Grid, 
  MapPin, 
  Archive, 
  ShieldCheck, 
  BarChart3, 
  MessageSquare, 
  CheckCircle2, 
  Clock, 
  AlertOctagon, 
  PlusCircle, 
  Users, 
  FolderOpen,
  Filter,
  RefreshCw,
  Search,
  Check
} from 'lucide-react';
import { User, Item } from '../types';
import { CATEGORIES, CAMPUS_LOCATIONS } from '../data/mockData';

interface SidebarProps {
  currentUser: User;
  activeView: string;
  setActiveView: (view: string) => void;
  selectedCategory: string;
  setSelectedCategory: (cat: string) => void;
  selectedType: 'all' | 'lost' | 'found';
  setSelectedType: (type: 'all' | 'lost' | 'found') => void;
  selectedStatus: string;
  setSelectedStatus: (status: string) => void;
  selectedLocation: string;
  setSelectedLocation: (loc: string) => void;
  items: Item[];
  onOpenReportLost: () => void;
  onOpenReportFound: () => void;
  isMobileOpen: boolean;
  closeMobileMenu: () => void;
}

export const Sidebar: React.FC<SidebarProps> = ({
  currentUser,
  activeView,
  setActiveView,
  selectedCategory,
  setSelectedCategory,
  selectedType,
  setSelectedType,
  selectedStatus,
  setSelectedStatus,
  selectedLocation,
  setSelectedLocation,
  items,
  onOpenReportLost,
  onOpenReportFound,
  isMobileOpen,
  closeMobileMenu
}) => {
  const lostCount = items.filter(i => i.type === 'lost').length;
  const foundCount = items.filter(i => i.type === 'found').length;
  const readyHandoverCount = items.filter(i => i.status === 'ready_for_handover').length;
  const underVerificationCount = items.filter(i => i.status === 'under_verification').length;
  const unclaimedCount = items.filter(i => i.daysHeld >= 90 || i.status === 'unclaimed').length;

  const handleNavClick = (viewId: string) => {
    setActiveView(viewId);
    closeMobileMenu();
  };

  return (
    <>
      {/* Mobile Backdrop */}
      {isMobileOpen && (
        <div 
          className="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-30 lg:hidden"
          onClick={closeMobileMenu}
        />
      )}

      <aside className={`
        fixed lg:static top-[53px] bottom-0 left-0 w-64 bg-white border-r border-slate-200 z-30 flex flex-col justify-between overflow-y-auto transition-transform duration-200 ease-in-out shrink-0
        ${isMobileOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'}
      `}>
        <div className="p-3 space-y-4">
          
          {/* Quick Intake Actions on Mobile */}
          <div className="grid grid-cols-2 gap-1.5 md:hidden">
            <button
              onClick={() => { onOpenReportLost(); closeMobileMenu(); }}
              className="flex items-center justify-center gap-1 bg-rose-600 text-white text-xs font-bold py-1.5 rounded"
            >
              <AlertOctagon className="w-3.5 h-3.5" />
              <span>Report Lost</span>
            </button>
            <button
              onClick={() => { onOpenReportFound(); closeMobileMenu(); }}
              className="flex items-center justify-center gap-1 bg-emerald-600 text-white text-xs font-bold py-1.5 rounded"
            >
              <PlusCircle className="w-3.5 h-3.5" />
              <span>Report Found</span>
            </button>
          </div>

          {/* Primary View Navigation */}
          <div>
            <div className="px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-slate-400 font-mono">
              Core Modules
            </div>
            <div className="space-y-0.5 mt-1">
              
              <button
                onClick={() => handleNavClick('dashboard')}
                className={`w-full flex items-center justify-between px-2.5 py-1.5 text-xs font-semibold rounded-md transition-colors ${
                  activeView === 'dashboard' 
                    ? 'bg-indigo-50 text-indigo-700 font-bold border-l-3 border-indigo-600' 
                    : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'
                }`}
              >
                <div className="flex items-center gap-2">
                  <Layers className="w-4 h-4 text-indigo-600" />
                  <span>
                    {currentUser.role === 'admin' ? 'Admin Dashboard' :
                     currentUser.role === 'staff' ? 'Staff Desk' :
                     currentUser.role === 'officer' ? 'Officer Vault' : 'Student Portal'}
                  </span>
                </div>
                <span className="text-[9px] font-mono font-bold uppercase bg-indigo-100 text-indigo-800 px-1.5 py-0.2 rounded">
                  {currentUser.role}
                </span>
              </button>

              <button
                onClick={() => handleNavClick('inventory')}
                className={`w-full flex items-center justify-between px-2.5 py-1.5 text-xs font-semibold rounded-md transition-colors ${
                  activeView === 'inventory' 
                    ? 'bg-indigo-50 text-indigo-700 font-bold border-l-3 border-indigo-600' 
                    : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'
                }`}
              >
                <div className="flex items-center gap-2">
                  <Grid className="w-4 h-4 text-slate-700" />
                  <span>Inventory Catalog</span>
                </div>
                <span className="text-[10px] font-mono font-bold bg-slate-100 text-slate-600 px-1.5 py-0.2 rounded">
                  {items.length}
                </span>
              </button>

              <button
                onClick={() => handleNavClick('claims')}
                className={`w-full flex items-center justify-between px-2.5 py-1.5 text-xs font-semibold rounded-md transition-colors ${
                  activeView === 'claims' 
                    ? 'bg-indigo-50 text-indigo-700 font-bold border-l-3 border-indigo-600' 
                    : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'
                }`}
              >
                <div className="flex items-center gap-2">
                  <CheckCircle2 className="w-4 h-4 text-emerald-600" />
                  <span>Claims & Verification</span>
                </div>
                {readyHandoverCount > 0 && (
                  <span className="text-[10px] font-mono bg-emerald-100 text-emerald-800 font-bold px-1.5 py-0.2 rounded">
                    {readyHandoverCount} Ready
                  </span>
                )}
              </button>

              <button
                onClick={() => handleNavClick('users')}
                className={`w-full flex items-center justify-between px-2.5 py-1.5 text-xs font-semibold rounded-md transition-colors ${
                  activeView === 'users' 
                    ? 'bg-indigo-50 text-indigo-700 font-bold border-l-3 border-indigo-600' 
                    : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'
                }`}
              >
                <div className="flex items-center gap-2">
                  <Users className="w-4 h-4 text-blue-600" />
                  <span>User Directory</span>
                </div>
              </button>

              <button
                onClick={() => handleNavClick('map')}
                className={`w-full flex items-center justify-between px-2.5 py-1.5 text-xs font-semibold rounded-md transition-colors ${
                  activeView === 'map' 
                    ? 'bg-indigo-50 text-indigo-700 font-bold border-l-3 border-indigo-600' 
                    : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'
                }`}
              >
                <div className="flex items-center gap-2">
                  <MapPin className="w-4 h-4 text-rose-500" />
                  <span>Campus Map</span>
                </div>
                <span className="text-[10px] font-mono text-emerald-700 bg-emerald-50 px-1.5 py-0.2 rounded">
                  Live
                </span>
              </button>

              <button
                onClick={() => handleNavClick('storage')}
                className={`w-full flex items-center justify-between px-2.5 py-1.5 text-xs font-semibold rounded-md transition-colors ${
                  activeView === 'storage' 
                    ? 'bg-indigo-50 text-indigo-700 font-bold border-l-3 border-indigo-600' 
                    : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'
                }`}
              >
                <div className="flex items-center gap-2">
                  <Archive className="w-4 h-4 text-purple-600" />
                  <span>Physical Storage Grid</span>
                </div>
              </button>

              <button
                onClick={() => handleNavClick('analytics')}
                className={`w-full flex items-center justify-between px-2.5 py-1.5 text-xs font-semibold rounded-md transition-colors ${
                  activeView === 'analytics' 
                    ? 'bg-indigo-50 text-indigo-700 font-bold border-l-3 border-indigo-600' 
                    : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'
                }`}
              >
                <div className="flex items-center gap-2">
                  <BarChart3 className="w-4 h-4 text-amber-600" />
                  <span>Reports & Analytics</span>
                </div>
              </button>

              <button
                onClick={() => handleNavClick('messages')}
                className={`w-full flex items-center justify-between px-2.5 py-1.5 text-xs font-semibold rounded-md transition-colors ${
                  activeView === 'messages' 
                    ? 'bg-indigo-50 text-indigo-700 font-bold border-l-3 border-indigo-600' 
                    : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'
                }`}
              >
                <div className="flex items-center gap-2">
                  <MessageSquare className="w-4 h-4 text-blue-500" />
                  <span>Desk Communications</span>
                </div>
                <span className="text-[10px] font-mono bg-blue-100 text-blue-700 font-bold px-1.5 py-0.2 rounded">
                  1
                </span>
              </button>

            </div>
          </div>

          {/* System Status Summary Box (Matching Screenshot) */}
          <div className="bg-slate-50 p-2.5 rounded-lg border border-slate-200 text-xs space-y-1.5 font-mono">
            <div className="text-[10px] font-bold uppercase text-slate-400">
              System Status
            </div>
            <div className="flex items-center justify-between text-[11px]">
              <span className="text-slate-600">Pending Claims:</span>
              <span className="font-bold text-amber-700 bg-amber-100 px-1.5 py-0.2 rounded">
                {underVerificationCount + readyHandoverCount}
              </span>
            </div>
            <div className="flex items-center justify-between text-[11px]">
              <span className="text-slate-600">Recent Finds:</span>
              <span className="font-bold text-emerald-700 bg-emerald-100 px-1.5 py-0.2 rounded">
                {foundCount}
              </span>
            </div>
            <div className="flex items-center justify-between text-[11px]">
              <span className="text-slate-600">Active Reports:</span>
              <span className="font-bold text-indigo-700 bg-indigo-100 px-1.5 py-0.2 rounded">
                {items.length}
              </span>
            </div>
          </div>

          {/* Inventory Filters (Active in Inventory View or always accessible) */}
          <div className="pt-2 border-t border-slate-200 space-y-2.5">
            <div className="flex items-center justify-between px-1">
              <span className="text-[10px] font-bold uppercase tracking-wider text-slate-400 font-mono">
                Filters
              </span>
              {(selectedType !== 'all' || selectedCategory !== 'All Categories' || selectedStatus !== 'all' || selectedLocation !== 'all') && (
                <button 
                  onClick={() => {
                    setSelectedType('all');
                    setSelectedCategory('All Categories');
                    setSelectedStatus('all');
                    setSelectedLocation('all');
                  }}
                  className="text-[10px] text-indigo-600 hover:text-indigo-800 font-semibold font-mono"
                >
                  Reset
                </button>
              )}
            </div>

            {/* Category Dropdown */}
            <div>
              <label className="text-[10px] font-bold uppercase text-slate-500 block mb-1 font-mono">
                Category
              </label>
              <select
                value={selectedCategory}
                onChange={(e) => setSelectedCategory(e.target.value)}
                className="w-full text-xs bg-slate-50 border border-slate-300 rounded px-2 py-1.5 text-slate-700 focus:outline-none focus:border-indigo-500"
              >
                {CATEGORIES.map(c => (
                  <option key={c} value={c}>{c}</option>
                ))}
              </select>
            </div>

            {/* Location Dropdown */}
            <div>
              <label className="text-[10px] font-bold uppercase text-slate-500 block mb-1 font-mono">
                Location
              </label>
              <select
                value={selectedLocation}
                onChange={(e) => setSelectedLocation(e.target.value)}
                className="w-full text-xs bg-slate-50 border border-slate-300 rounded px-2 py-1.5 text-slate-700 focus:outline-none focus:border-indigo-500 truncate"
              >
                <option value="all">All Locations</option>
                {CAMPUS_LOCATIONS.map(loc => (
                  <option key={loc.id} value={loc.building}>{loc.building}</option>
                ))}
              </select>
            </div>

            {/* Status Checkboxes */}
            <div className="space-y-1 pt-1">
              <label className="text-[10px] font-bold uppercase text-slate-500 block font-mono">
                Status
              </label>
              <div className="space-y-1 text-xs">
                {[
                  { id: 'all', label: 'All Statuses' },
                  { id: 'lost', label: 'Lost' },
                  { id: 'found', label: 'Found' },
                  { id: 'ready_for_handover', label: 'Ready for Pickup' },
                  { id: 'returned', label: 'Claimed / Returned' }
                ].map(st => (
                  <label key={st.id} className="flex items-center gap-2 cursor-pointer text-slate-700 hover:text-slate-900">
                    <input
                      type="radio"
                      name="sidebarStatus"
                      checked={
                        st.id === 'lost' || st.id === 'found' 
                          ? selectedType === st.id 
                          : selectedStatus === st.id
                      }
                      onChange={() => {
                        if (st.id === 'lost' || st.id === 'found') {
                          setSelectedType(st.id);
                          setSelectedStatus('all');
                        } else {
                          setSelectedType('all');
                          setSelectedStatus(st.id);
                        }
                      }}
                      className="text-indigo-600 focus:ring-indigo-500 rounded"
                    />
                    <span className="text-[11px]">{st.label}</span>
                  </label>
                ))}
              </div>
            </div>

            <button
              onClick={() => {
                setActiveView('inventory');
                closeMobileMenu();
              }}
              className="w-full py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-xs font-bold font-mono transition-colors shadow-xs"
            >
              Apply Filters
            </button>
          </div>

        </div>

        {/* Bottom User Persona Badge */}
        <div className="p-3 bg-slate-50 border-t border-slate-200">
          <div className="flex items-center gap-2">
            <div className={`w-7 h-7 rounded flex items-center justify-center font-bold text-xs text-white ${
              currentUser.role === 'admin' ? 'bg-purple-600' :
              currentUser.role === 'officer' ? 'bg-amber-600' :
              currentUser.role === 'staff' ? 'bg-emerald-600' : 'bg-indigo-600'
            }`}>
              {currentUser.name.charAt(0)}
            </div>
            <div className="flex flex-col text-[11px] leading-tight overflow-hidden">
              <span className="font-bold text-slate-800 truncate">{currentUser.name}</span>
              <span className="text-[10px] text-slate-500 font-mono truncate uppercase">{currentUser.role} &bull; {currentUser.universityId}</span>
            </div>
          </div>
        </div>

      </aside>
    </>
  );
};
