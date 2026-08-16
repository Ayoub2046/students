import React, { useState } from 'react';
import { 
  ShieldCheck, 
  Lock, 
  Key, 
  FileCheck2, 
  AlertTriangle, 
  Clock, 
  Search, 
  UserCheck, 
  CheckCircle2, 
  MapPin, 
  FileSpreadsheet, 
  PlusCircle, 
  ArrowRight,
  Sparkles,
  Printer,
  ShieldAlert
} from 'lucide-react';
import { Item, Claim, User, StorageLocker } from '../types';
import { STORAGE_LOCKERS } from '../data/mockData';

interface OfficerDashboardProps {
  items: Item[];
  claims: Claim[];
  currentUser: User;
  onNavigateTab: (tab: string) => void;
  onSelectItem: (item: Item) => void;
  onOpenReportModal: (type: 'lost' | 'found') => void;
  onOpenHandoverModal: (item: Item, claim?: Claim) => void;
}

export const OfficerDashboard: React.FC<OfficerDashboardProps> = ({
  items,
  claims,
  currentUser,
  onNavigateTab,
  onSelectItem,
  onOpenReportModal,
  onOpenHandoverModal
}) => {
  const [activeTab, setActiveTab] = useState<'vault' | 'verification' | 'retention'>('vault');
  const [searchSerial, setSearchSerial] = useState('');

  const highValueCategories = ['Electronics', 'Wallets & Cards', 'Jewelry & Watches'];
  const highValueItems = items.filter(i => highValueCategories.includes(i.category) && i.status !== 'returned');
  const pendingClaims = claims.filter(c => c.status === 'under_verification' || c.status === 'pending');
  const readyHandoverItems = items.filter(i => i.status === 'ready_for_handover');
  const expiredRetentionItems = items.filter(i => i.daysHeld >= 90 || i.status === 'unclaimed');

  return (
    <div className="w-full space-y-4">
      
      {/* Officer Security & Custody Banner */}
      <div className="bg-[#111827] text-white rounded-xl p-4 sm:p-5 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border border-slate-700">
        <div>
          <div className="flex items-center gap-2 mb-1">
            <span className="px-2 py-0.5 rounded bg-amber-600/90 text-[10px] font-mono font-bold tracking-wider uppercase flex items-center gap-1">
              <Lock className="w-3 h-3" />
              <span>Campus Security & Evidence Vault</span>
            </span>
            <span className="flex items-center gap-1 text-[11px] text-amber-400 font-mono">
              VAULT 1 & 2 SECURED
            </span>
          </div>
          <h1 className="text-lg sm:text-xl font-bold tracking-tight text-white">
            {currentUser.name} &bull; Custody Officer Station
          </h1>
          <p className="text-xs text-slate-300 mt-0.5">
            Department: <span className="font-semibold text-white">{currentUser.department}</span> &bull; Badge / ID: <span className="font-mono text-amber-400 font-bold">{currentUser.universityId}</span>
          </p>
        </div>

        <div className="flex items-center gap-2 flex-wrap">
          <button
            onClick={() => onOpenReportModal('found')}
            className="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-sm transition-all"
          >
            <PlusCircle className="w-4 h-4" />
            <span>+ Log Vault Custody</span>
          </button>
          <button
            onClick={() => onNavigateTab('storage')}
            className="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-amber-300 rounded-lg text-xs font-semibold flex items-center gap-1.5 border border-slate-600 shadow-sm"
          >
            <Lock className="w-3.5 h-3.5 text-amber-400" />
            <span>Vault Lockers</span>
          </button>
        </div>
      </div>

      {/* High-Security KPI Stats */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
        
        <div className="bg-white rounded-xl border border-slate-200 p-3.5 shadow-xs">
          <div className="flex items-center justify-between text-slate-500 font-mono text-[10px] uppercase font-bold">
            <span>High-Value in Vault</span>
            <Lock className="w-4 h-4 text-amber-600" />
          </div>
          <div className="text-2xl font-bold text-slate-900 font-mono my-1">
            {highValueItems.length} Items
          </div>
          <div className="text-[10px] text-amber-700 font-semibold">
            Laptops, Wallets & Jewelry Lockbox
          </div>
        </div>

        <div className="bg-white rounded-xl border border-slate-200 p-3.5 shadow-xs">
          <div className="flex items-center justify-between text-slate-500 font-mono text-[10px] uppercase font-bold">
            <span>Awaiting Verification</span>
            <FileCheck2 className="w-4 h-4 text-indigo-600" />
          </div>
          <div className="text-2xl font-bold text-slate-900 font-mono my-1">
            {pendingClaims.length} Claims
          </div>
          <div className="text-[10px] text-indigo-700 font-semibold">
            Serial proof & photo ID matching
          </div>
        </div>

        <div className="bg-white rounded-xl border border-slate-200 p-3.5 shadow-xs">
          <div className="flex items-center justify-between text-slate-500 font-mono text-[10px] uppercase font-bold">
            <span>Ready for Signature</span>
            <CheckCircle2 className="w-4 h-4 text-emerald-600" />
          </div>
          <div className="text-2xl font-bold text-slate-900 font-mono my-1">
            {readyHandoverItems.length}
          </div>
          <div className="text-[10px] text-emerald-700 font-semibold">
            Digital custody receipt queue
          </div>
        </div>

        <div className="bg-white rounded-xl border border-slate-200 p-3.5 shadow-xs">
          <div className="flex items-center justify-between text-slate-500 font-mono text-[10px] uppercase font-bold">
            <span>90-Day Retention Notice</span>
            <Clock className="w-4 h-4 text-rose-600" />
          </div>
          <div className="text-2xl font-bold text-slate-900 font-mono my-1">
            {expiredRetentionItems.length}
          </div>
          <div className="text-[10px] text-rose-700 font-semibold">
            Unclaimed legal disposition
          </div>
        </div>

      </div>

      {/* Interactive Officer Operational Panels */}
      <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-xs space-y-4">
        
        {/* Navigation Tabs */}
        <div className="flex items-center justify-between border-b border-slate-200 pb-2 flex-wrap gap-2">
          <div className="flex items-center gap-1.5">
            <button
              onClick={() => setActiveTab('vault')}
              className={`px-3 py-1.5 text-xs font-bold rounded-lg transition-colors font-mono ${
                activeTab === 'vault' 
                  ? 'bg-amber-600 text-white shadow-xs' 
                  : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
              }`}
            >
              High-Value Vault Items ({highValueItems.length})
            </button>
            <button
              onClick={() => setActiveTab('verification')}
              className={`px-3 py-1.5 text-xs font-bold rounded-lg transition-colors font-mono ${
                activeTab === 'verification' 
                  ? 'bg-indigo-600 text-white shadow-xs' 
                  : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
              }`}
            >
              Ownership Proof Queue ({claims.length})
            </button>
            <button
              onClick={() => setActiveTab('retention')}
              className={`px-3 py-1.5 text-xs font-bold rounded-lg transition-colors font-mono ${
                activeTab === 'retention' 
                  ? 'bg-rose-600 text-white shadow-xs' 
                  : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
              }`}
            >
              90-Day Retention Audit ({expiredRetentionItems.length})
            </button>
          </div>

          <div className="text-[11px] font-mono text-slate-400">
            Chain of Custody Standard: ISO/IEC 27001 Certified
          </div>
        </div>

        {/* Tab 1: High-Value Vault Registry */}
        {activeTab === 'vault' && (
          <div className="space-y-3">
            <div className="flex items-center justify-between text-xs">
              <span className="text-slate-500 font-mono">
                Items stored in double-locked safe boxes with serial tracking
              </span>
              <button
                onClick={() => onNavigateTab('storage')}
                className="text-amber-700 font-bold hover:underline"
              >
                Inspect Vault Grid &rarr;
              </button>
            </div>

            <div className="overflow-x-auto">
              <table className="w-full text-left text-xs">
                <thead>
                  <tr className="bg-slate-50 text-slate-500 font-mono text-[10px] uppercase border-b border-slate-200">
                    <th className="py-2 px-3 font-bold">Evidence Ref</th>
                    <th className="py-2 px-3 font-bold">Item Description</th>
                    <th className="py-2 px-3 font-bold">Category</th>
                    <th className="py-2 px-3 font-bold">Masked Serial No</th>
                    <th className="py-2 px-3 font-bold">Vault Safe Loc</th>
                    <th className="py-2 px-3 font-bold">Status</th>
                    <th className="py-2 px-3 font-bold text-right">Officer Action</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-slate-100 font-sans">
                  {highValueItems.map(item => (
                    <tr key={item.id} className="hover:bg-slate-50 transition-colors">
                      <td className="py-2.5 px-3 font-mono font-bold text-amber-900">
                        {item.referenceNo}
                      </td>
                      <td className="py-2.5 px-3 font-semibold text-slate-800">
                        <div className="flex items-center gap-2">
                          <img src={item.imageUrl} alt="" className="w-7 h-7 rounded object-cover border border-slate-200 shrink-0" referrerPolicy="no-referrer" />
                          <span>{item.title}</span>
                        </div>
                      </td>
                      <td className="py-2.5 px-3 text-slate-600 font-mono">
                        {item.category}
                      </td>
                      <td className="py-2.5 px-3 font-mono text-slate-500 text-[11px]">
                        {item.serialNumberMasked || 'NO_SERIAL_LOGGED'}
                      </td>
                      <td className="py-2.5 px-3 font-mono text-slate-700 font-bold text-[11px]">
                        {item.storageLocation?.bin || 'Vault 1 (Safe Box 04)'}
                      </td>
                      <td className="py-2.5 px-3">
                        <span className={`text-[9px] font-mono font-bold px-1.5 py-0.2 rounded uppercase ${
                          item.status === 'ready_for_handover' ? 'bg-emerald-100 text-emerald-800' :
                          item.status === 'under_verification' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600'
                        }`}>
                          {item.status.replace('_', ' ')}
                        </span>
                      </td>
                      <td className="py-2.5 px-3 text-right">
                        {item.status === 'ready_for_handover' ? (
                          <button
                            onClick={() => onOpenHandoverModal(item)}
                            className="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-bold font-mono shadow-xs"
                          >
                            Digital Sign Release
                          </button>
                        ) : (
                          <button
                            onClick={() => onSelectItem(item)}
                            className="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-semibold"
                          >
                            Inspect Evidence
                          </button>
                        )}
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        )}

        {/* Tab 2: Ownership Proof Queue */}
        {activeTab === 'verification' && (
          <div className="space-y-3">
            <div className="divide-y divide-slate-100">
              {claims.map(claim => {
                const item = items.find(i => i.id === claim.itemId);
                return (
                  <div key={claim.id} className="py-3.5 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div className="space-y-1">
                      <div className="flex items-center gap-2">
                        <span className="font-mono text-xs font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-200">
                          Claim #{claim.id} &bull; {claim.itemReferenceNo}
                        </span>
                        <span className={`text-[10px] font-mono font-bold px-1.5 py-0.2 rounded uppercase ${
                          claim.status === 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'
                        }`}>
                          {claim.status}
                        </span>
                      </div>
                      <h4 className="text-xs font-bold text-slate-900">{claim.itemTitle}</h4>
                      <p className="text-[11px] text-slate-600">
                        Claimed by: <strong className="text-slate-800">{claim.claimedBy.name}</strong> ({claim.claimedBy.universityId}) &bull; {claim.claimedBy.phone}
                      </p>
                      <div className="p-2 bg-slate-50 rounded border border-slate-200 text-[11px] text-slate-700 mt-1 max-w-xl font-mono">
                        <strong>Student Proof:</strong> "{claim.proofDetails}"
                      </div>
                    </div>

                    <div className="flex items-center gap-2 self-end md:self-center shrink-0">
                      {item && (
                        <button
                          onClick={() => onOpenHandoverModal(item, claim)}
                          className="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-bold shadow-xs flex items-center gap-1"
                        >
                          <UserCheck className="w-3.5 h-3.5" />
                          <span>Handover Station</span>
                        </button>
                      )}
                      <button
                        onClick={() => item && onSelectItem(item)}
                        className="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold"
                      >
                        Item Details
                      </button>
                    </div>
                  </div>
                );
              })}
            </div>
          </div>
        )}

        {/* Tab 3: 90-Day Retention */}
        {activeTab === 'retention' && (
          <div className="space-y-3">
            <div className="p-3 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-900">
              <strong>University Statutory Retention Compliance:</strong>
              <p className="text-[11px] mt-0.5 text-rose-800">
                Items uncollected past 90 calendar days are officially slated for university charity donation or state property auction.
              </p>
            </div>

            <div className="divide-y divide-slate-100">
              {expiredRetentionItems.map(item => (
                <div key={item.id} className="py-2.5 flex items-center justify-between gap-3">
                  <div className="flex items-center gap-3">
                    <img src={item.imageUrl} alt="" className="w-10 h-10 rounded object-cover border border-slate-200 shrink-0" referrerPolicy="no-referrer" />
                    <div>
                      <div className="flex items-center gap-2">
                        <span className="font-mono text-xs font-bold text-rose-800">{item.referenceNo}</span>
                        <span className="font-mono text-[10px] text-rose-700 bg-rose-100 px-1.5 py-0.2 rounded font-bold">
                          {item.daysHeld} DAYS IN VAULT
                        </span>
                      </div>
                      <h4 className="text-xs font-bold text-slate-900">{item.title}</h4>
                      <span className="text-[10px] text-slate-500 font-mono">Found: {item.building} on {item.dateReported}</span>
                    </div>
                  </div>

                  <button
                    onClick={() => alert(`Item ${item.referenceNo} transferred to Campus Student Charity Program. Disposition certificate logged to audit trail.`)}
                    className="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded text-xs font-bold font-mono shadow-xs"
                  >
                    Transfer to Donation
                  </button>
                </div>
              ))}
            </div>
          </div>
        )}

      </div>

    </div>
  );
};
