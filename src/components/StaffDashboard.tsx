import React, { useState } from 'react';
import { 
  PlusCircle, 
  AlertOctagon, 
  Search, 
  CheckCircle2, 
  Archive, 
  Clock, 
  QrCode, 
  Printer, 
  FileText, 
  ArrowRight,
  Sparkles,
  Inbox,
  UserCheck,
  Tag,
  MapPin,
  Phone,
  Mail
} from 'lucide-react';
import { Item, Claim, User, StorageLocker } from '../types';
import { STORAGE_LOCKERS, CAMPUS_LOCATIONS } from '../data/mockData';

interface StaffDashboardProps {
  items: Item[];
  claims: Claim[];
  currentUser: User;
  onNavigateTab: (tab: string) => void;
  onSelectItem: (item: Item) => void;
  onOpenReportModal: (type: 'lost' | 'found') => void;
  onOpenHandoverModal: (item: Item, claim?: Claim) => void;
}

export const StaffDashboard: React.FC<StaffDashboardProps> = ({
  items,
  claims,
  currentUser,
  onNavigateTab,
  onSelectItem,
  onOpenReportModal,
  onOpenHandoverModal
}) => {
  const [quickSearch, setQuickSearch] = useState('');
  const [selectedLockerFilter, setSelectedLockerFilter] = useState('all');

  const readyForHandoverItems = items.filter(i => i.status === 'ready_for_handover');
  const underVerificationItems = items.filter(i => i.status === 'under_verification');
  const todayFoundItems = items.filter(i => i.type === 'found');
  const urgentLostItems = items.filter(i => i.type === 'lost');

  const filteredItems = items.filter(i => {
    if (!quickSearch) return true;
    const q = quickSearch.toLowerCase();
    return i.title.toLowerCase().includes(q) ||
           i.referenceNo.toLowerCase().includes(q) ||
           i.category.toLowerCase().includes(q) ||
           i.building.toLowerCase().includes(q);
  });

  return (
    <div className="w-full space-y-4">
      
      {/* Front Desk Operations Ribbon */}
      <div className="bg-[#0f172a] text-white rounded-xl p-4 sm:p-5 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border border-slate-800">
        <div>
          <div className="flex items-center gap-2 mb-1">
            <span className="px-2 py-0.5 rounded bg-emerald-600/90 text-[10px] font-mono font-bold tracking-wider uppercase">
              Circulation & Intake Desk Station
            </span>
            <span className="flex items-center gap-1 text-[11px] text-emerald-400 font-mono">
              <span className="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
              DESK ACTIVE &bull; SHIFT #304
            </span>
          </div>
          <h1 className="text-lg sm:text-xl font-bold tracking-tight text-white">
            {currentUser.name} &bull; Front Desk Terminal
          </h1>
          <p className="text-xs text-slate-300 mt-0.5">
            Station: <span className="font-semibold text-white">{currentUser.department}</span> &bull; Operator ID: <span className="font-mono text-emerald-400 font-bold">{currentUser.universityId}</span>
          </p>
        </div>

        {/* Quick Intake Desk Buttons */}
        <div className="flex items-center gap-2 flex-wrap">
          <button
            onClick={() => onOpenReportModal('found')}
            className="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-sm transition-all"
          >
            <PlusCircle className="w-4 h-4" />
            <span>+ Intake Found Item</span>
          </button>
          <button
            onClick={() => onOpenReportModal('lost')}
            className="px-3 py-2 bg-rose-600/90 hover:bg-rose-600 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-sm transition-all"
          >
            <AlertOctagon className="w-4 h-4" />
            <span>Log Lost Bulletin</span>
          </button>
          <button
            onClick={() => onNavigateTab('storage')}
            className="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 rounded-lg text-xs font-semibold flex items-center gap-1.5 border border-slate-700 shadow-sm"
          >
            <Archive className="w-4 h-4 text-indigo-400" />
            <span>Locker Bins</span>
          </button>
        </div>
      </div>

      {/* Staff Operational Metrics */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
        
        <div className="bg-white rounded-xl border border-slate-200 p-3.5 shadow-xs">
          <div className="flex items-center justify-between text-slate-500 font-mono text-[10px] uppercase font-bold">
            <span>Ready for Handover</span>
            <CheckCircle2 className="w-4 h-4 text-emerald-600" />
          </div>
          <div className="text-2xl font-bold text-slate-900 font-mono my-1">
            {readyForHandoverItems.length}
          </div>
          <div className="text-[10px] text-emerald-700 font-semibold flex items-center gap-1">
            <span>Claimant verified &bull; Ready at Desk</span>
          </div>
        </div>

        <div className="bg-white rounded-xl border border-slate-200 p-3.5 shadow-xs">
          <div className="flex items-center justify-between text-slate-500 font-mono text-[10px] uppercase font-bold">
            <span>Under Verification</span>
            <Clock className="w-4 h-4 text-amber-600" />
          </div>
          <div className="text-2xl font-bold text-slate-900 font-mono my-1">
            {underVerificationItems.length}
          </div>
          <div className="text-[10px] text-amber-700 font-semibold">
            Serial / Proof review pending
          </div>
        </div>

        <div className="bg-white rounded-xl border border-slate-200 p-3.5 shadow-xs">
          <div className="flex items-center justify-between text-slate-500 font-mono text-[10px] uppercase font-bold">
            <span>Available in Storage</span>
            <Archive className="w-4 h-4 text-indigo-600" />
          </div>
          <div className="text-2xl font-bold text-slate-900 font-mono my-1">
            {todayFoundItems.filter(i => i.status === 'available').length}
          </div>
          <div className="text-[10px] text-indigo-700 font-semibold">
            Cataloged in Racks A, B, C, D
          </div>
        </div>

        <div className="bg-white rounded-xl border border-slate-200 p-3.5 shadow-xs">
          <div className="flex items-center justify-between text-slate-500 font-mono text-[10px] uppercase font-bold">
            <span>Open Lost Bulletins</span>
            <AlertOctagon className="w-4 h-4 text-rose-600" />
          </div>
          <div className="text-2xl font-bold text-slate-900 font-mono my-1">
            {urgentLostItems.length}
          </div>
          <div className="text-[10px] text-rose-700 font-semibold">
            Student search alerts active
          </div>
        </div>

      </div>

      {/* Main Desk Workbench */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        {/* Left 2 Cols: Ready for Pickup & Rapid Desk Lookup */}
        <div className="lg:col-span-2 space-y-4">
          
          {/* Pickups & Handover Station */}
          <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-xs space-y-3">
            <div className="flex items-center justify-between pb-2 border-b border-slate-100">
              <div className="flex items-center gap-2">
                <span className="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <h3 className="text-xs font-bold uppercase tracking-wider text-slate-800 font-mono">
                  Claimant Handover & Digital Signature Desk
                </h3>
              </div>
              <span className="text-[10px] font-mono text-emerald-800 bg-emerald-100 px-2 py-0.5 rounded font-bold">
                {readyForHandoverItems.length} READY AT DESK
              </span>
            </div>

            {readyForHandoverItems.length === 0 ? (
              <div className="p-6 text-center text-slate-400 text-xs font-mono">
                No items currently awaiting claimant pickup at this station.
              </div>
            ) : (
              <div className="space-y-2.5">
                {readyForHandoverItems.map(item => {
                  const claim = claims.find(c => c.itemId === item.id);
                  return (
                    <div key={item.id} className="p-3 bg-emerald-50/50 rounded-lg border border-emerald-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                      <div className="flex items-center gap-3">
                        <img src={item.imageUrl} alt="" className="w-12 h-12 rounded object-cover border border-emerald-300 shrink-0" referrerPolicy="no-referrer" />
                        <div>
                          <div className="flex items-center gap-2 mb-0.5">
                            <span className="font-mono text-xs font-bold text-emerald-800">{item.referenceNo}</span>
                            <span className="text-[10px] font-mono bg-white text-emerald-700 px-1.5 py-0.2 rounded border border-emerald-300 font-bold">
                              Locker: {item.storageLocation?.bin || 'Vault Safe'}
                            </span>
                          </div>
                          <h4 className="text-xs font-bold text-slate-900">{item.title}</h4>
                          <p className="text-[11px] text-slate-600 mt-0.5">
                            Owner: <strong className="text-slate-800">{claim?.claimedBy.name || 'Alex Rivera'}</strong> ({claim?.claimedBy.universityId || 'STU-994821'})
                          </p>
                        </div>
                      </div>

                      <div className="flex items-center gap-2 self-end sm:self-center shrink-0">
                        <button
                          onClick={() => onSelectItem(item)}
                          className="px-2.5 py-1.5 bg-white hover:bg-slate-100 text-slate-700 rounded text-xs font-semibold border border-slate-300 shadow-xs"
                        >
                          Details
                        </button>
                        <button
                          onClick={() => onOpenHandoverModal(item, claim)}
                          className="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-bold shadow-xs flex items-center gap-1"
                        >
                          <UserCheck className="w-3.5 h-3.5" />
                          <span>Release Custody</span>
                        </button>
                      </div>
                    </div>
                  );
                })}
              </div>
            )}
          </div>

          {/* Quick Desk Inventory Look-up Table */}
          <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-xs space-y-3">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-100">
              <div>
                <h3 className="text-xs font-bold uppercase tracking-wider text-slate-800 font-mono">
                  Quick Desk Inventory Lookup
                </h3>
                <p className="text-[11px] text-slate-400">Search active locker bins & verify incoming walk-ins</p>
              </div>

              {/* Fast Search Input */}
              <div className="relative w-full sm:w-64">
                <Search className="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
                <input
                  type="text"
                  placeholder="Filter by ref, title, room..."
                  value={quickSearch}
                  onChange={(e) => setQuickSearch(e.target.value)}
                  className="w-full pl-8 pr-2.5 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-md focus:outline-none focus:border-indigo-500"
                />
              </div>
            </div>

            <div className="overflow-x-auto max-h-72 overflow-y-auto">
              <table className="w-full text-left text-xs">
                <thead>
                  <tr className="bg-slate-50 text-slate-500 font-mono text-[10px] uppercase border-b border-slate-200">
                    <th className="py-2 px-2.5 font-bold">Ref ID</th>
                    <th className="py-2 px-2.5 font-bold">Item Description</th>
                    <th className="py-2 px-2.5 font-bold">Category</th>
                    <th className="py-2 px-2.5 font-bold">Location</th>
                    <th className="py-2 px-2.5 font-bold">Storage Bin</th>
                    <th className="py-2 px-2.5 font-bold">Status</th>
                    <th className="py-2 px-2.5 font-bold text-right">Action</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 font-sans">
                  {filteredItems.slice(0, 8).map(item => (
                    <tr key={item.id} className="hover:bg-slate-50/80 transition-colors">
                      <td className="py-2 px-2.5 font-mono text-indigo-700 font-bold whitespace-nowrap">
                        {item.referenceNo}
                      </td>
                      <td className="py-2 px-2.5 font-semibold text-slate-800 max-w-xs truncate">
                        {item.title}
                      </td>
                      <td className="py-2 px-2.5 text-slate-600 whitespace-nowrap">
                        {item.category}
                      </td>
                      <td className="py-2 px-2.5 text-slate-500 font-mono text-[11px] whitespace-nowrap">
                        {item.building}
                      </td>
                      <td className="py-2 px-2.5 whitespace-nowrap">
                        <span className="font-mono text-[10px] bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded border border-slate-200">
                          {item.storageLocation?.bin || 'Unassigned'}
                        </span>
                      </td>
                      <td className="py-2 px-2.5 whitespace-nowrap">
                        <span className={`text-[9px] font-mono font-bold px-1.5 py-0.2 rounded uppercase ${
                          item.status === 'ready_for_handover' ? 'bg-emerald-100 text-emerald-800' :
                          item.status === 'under_verification' ? 'bg-amber-100 text-amber-800' :
                          item.status === 'available' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-600'
                        }`}>
                          {item.status.replace('_', ' ')}
                        </span>
                      </td>
                      <td className="py-2 px-2.5 text-right whitespace-nowrap">
                        <button
                          onClick={() => onSelectItem(item)}
                          className="text-[11px] font-bold text-indigo-600 hover:text-indigo-800"
                        >
                          View
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>

          </div>

        </div>

        {/* Right Col: Urgent Lost Bulletins & Locker Quick Assign */}
        <div className="space-y-4">
          
          {/* Urgent Lost Reports Bulletins */}
          <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-xs space-y-3">
            <div className="flex items-center justify-between pb-2 border-b border-slate-100">
              <h3 className="text-xs font-bold uppercase tracking-wider text-slate-800 font-mono flex items-center gap-1.5">
                <AlertOctagon className="w-3.5 h-3.5 text-rose-600" />
                <span>Active Lost Reports Broadcast</span>
              </h3>
              <span className="text-[10px] font-mono text-rose-700 bg-rose-50 px-1.5 py-0.2 rounded font-bold">
                {urgentLostItems.length} OPEN
              </span>
            </div>

            <div className="space-y-2 max-h-64 overflow-y-auto pr-1">
              {urgentLostItems.map(item => (
                <div 
                  key={item.id}
                  onClick={() => onSelectItem(item)}
                  className="p-2.5 bg-rose-50/40 rounded-lg border border-rose-100 hover:border-rose-300 cursor-pointer transition-colors"
                >
                  <div className="flex items-center justify-between text-xs mb-1">
                    <span className="font-mono text-[10px] font-bold text-rose-800">{item.referenceNo}</span>
                    <span className="text-[10px] text-slate-400 font-mono">{item.dateReported}</span>
                  </div>
                  <h4 className="text-xs font-bold text-slate-900 leading-snug">{item.title}</h4>
                  <p className="text-[11px] text-slate-600 line-clamp-2 mt-1">{item.description}</p>
                  {item.rewardOffered && (
                    <div className="mt-1.5 text-[10px] font-mono text-emerald-700 bg-emerald-50 px-1.5 py-0.5 rounded inline-block font-bold">
                      Reward: {item.rewardOffered}
                    </div>
                  )}
                </div>
              ))}
            </div>
          </div>

          {/* Quick Barcode & Physical Locker Tag Tool */}
          <div className="bg-indigo-950 text-white rounded-xl p-4 shadow-xs space-y-3 border border-indigo-900">
            <div className="flex items-center justify-between pb-2 border-b border-indigo-800">
              <h3 className="text-xs font-bold uppercase tracking-wider text-indigo-200 font-mono flex items-center gap-1.5">
                <QrCode className="w-3.5 h-3.5 text-indigo-400" />
                <span>Physical Locker Tag Generator</span>
              </h3>
            </div>
            <p className="text-[11px] text-indigo-200 leading-relaxed">
              Generate standardized thermal adhesive tags with barcode and QR verification link for physical evidence lockers.
            </p>
            <div className="bg-indigo-900/60 p-2.5 rounded border border-indigo-700/60 font-mono text-[11px] space-y-1">
              <div className="text-indigo-300">Format: Standard Code-128 / QR-2026</div>
              <div className="text-emerald-400 font-bold">Connected: Zebra ZD421 Thermal Printer</div>
            </div>
            <button
              onClick={() => alert('Sending batch thermal locker tags (LF-2026-8812, LF-2026-8819) to Zebra Thermal Printer... Print Job Queued.')}
              className="w-full py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-xs font-bold transition-colors flex items-center justify-center gap-1.5"
            >
              <Printer className="w-3.5 h-3.5" />
              <span>Print Pending Intake Tags</span>
            </button>
          </div>

        </div>

      </div>

    </div>
  );
};
