import React, { useState } from 'react';
import { 
  CheckCircle2, 
  Clock, 
  Search, 
  UserCheck, 
  ShieldCheck, 
  AlertCircle, 
  FileText, 
  Filter,
  ArrowRight,
  Printer
} from 'lucide-react';
import { Claim, Item, User } from '../types';

interface ClaimsViewProps {
  claims: Claim[];
  items: Item[];
  currentUser: User;
  onOpenHandoverModal: (item: Item, claim?: Claim) => void;
  onSelectItem: (item: Item) => void;
}

export const ClaimsView: React.FC<ClaimsViewProps> = ({
  claims,
  items,
  currentUser,
  onOpenHandoverModal,
  onSelectItem
}) => {
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedStatus, setSelectedStatus] = useState<string>('all');

  const filteredClaims = claims.filter(c => {
    const matchesSearch = 
      c.itemTitle.toLowerCase().includes(searchQuery.toLowerCase()) ||
      c.itemReferenceNo.toLowerCase().includes(searchQuery.toLowerCase()) ||
      c.claimedBy.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      c.claimedBy.universityId.toLowerCase().includes(searchQuery.toLowerCase());

    const matchesStatus = selectedStatus === 'all' || c.status === selectedStatus;
    return matchesSearch && matchesStatus;
  });

  const approvedCount = claims.filter(c => c.status === 'approved').length;
  const underVerificationCount = claims.filter(c => c.status === 'under_verification').length;

  return (
    <div className="w-full space-y-4">
      
      {/* Header Banner */}
      <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div>
          <div className="flex items-center gap-2">
            <CheckCircle2 className="w-5 h-5 text-emerald-600" />
            <h1 className="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
              Ownership Claims & Handover Verification Desk
            </h1>
          </div>
          <p className="text-xs text-slate-500 mt-0.5">
            Review student submitted proof of ownership, match masked serial numbers, and process digital custody handovers.
          </p>
        </div>

        <div className="flex items-center gap-2">
          <span className="px-3 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg text-xs font-mono font-bold">
            {approvedCount} Ready for Pickup
          </span>
          <span className="px-3 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-lg text-xs font-mono font-bold">
            {underVerificationCount} In Review
          </span>
        </div>
      </div>

      {/* Filter and Search Bar */}
      <div className="bg-white rounded-xl border border-slate-200 p-3 shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5">
        <div className="relative flex-1">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            placeholder="Search by claim ID, item ref (LF-2026-...), claimant name, or student ID..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full pl-9 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:bg-white"
          />
        </div>

        <div className="flex items-center gap-2">
          <select
            value={selectedStatus}
            onChange={(e) => setSelectedStatus(e.target.value)}
            className="text-xs bg-slate-50 border border-slate-200 rounded-lg px-3 py-1.5 font-mono text-slate-700 font-semibold focus:outline-none"
          >
            <option value="all">All Claim Statuses</option>
            <option value="approved">Approved / Ready Handover</option>
            <option value="under_verification">Under Verification</option>
            <option value="pending">Pending</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>
      </div>

      {/* Claims List Cards */}
      <div className="space-y-3">
        {filteredClaims.map(claim => {
          const item = items.find(i => i.id === claim.itemId);
          return (
            <div 
              key={claim.id} 
              className="bg-white rounded-xl border border-slate-200 p-4 shadow-xs space-y-3 hover:border-slate-300 transition-colors"
            >
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-2 border-b border-slate-100">
                <div className="flex items-center gap-2">
                  <span className="font-mono text-xs font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">
                    CLAIM #{claim.id}
                  </span>
                  <span className="font-mono text-xs font-bold text-slate-900">
                    {claim.itemReferenceNo} &bull; {claim.itemTitle}
                  </span>
                </div>
                <div className="flex items-center gap-2">
                  <span className="text-[11px] font-mono text-slate-400">
                    Submitted: {claim.submittedDate}
                  </span>
                  <span className={`text-[10px] font-mono font-bold px-2 py-0.5 rounded uppercase ${
                    claim.status === 'approved' ? 'bg-emerald-100 text-emerald-800' :
                    claim.status === 'under_verification' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600'
                  }`}>
                    {claim.status}
                  </span>
                </div>
              </div>

              <div className="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                {/* Claimant Details */}
                <div className="p-2.5 bg-slate-50 rounded-lg border border-slate-200 space-y-1">
                  <span className="font-mono text-[10px] font-bold text-slate-400 uppercase">Claimant Details</span>
                  <div className="font-bold text-slate-800">{claim.claimedBy.name}</div>
                  <div className="text-[11px] font-mono text-slate-500">ID: {claim.claimedBy.universityId}</div>
                  <div className="text-[11px] text-slate-500">{claim.claimedBy.email} &bull; {claim.claimedBy.phone}</div>
                </div>

                {/* Submitted Proof */}
                <div className="p-2.5 bg-slate-50 rounded-lg border border-slate-200 space-y-1 md:col-span-2">
                  <span className="font-mono text-[10px] font-bold text-slate-400 uppercase">Proof of Ownership Description</span>
                  <p className="text-[11px] text-slate-700 font-mono leading-relaxed">
                    "{claim.proofDetails}"
                  </p>
                  {claim.serialNumberProvided && (
                    <div className="text-[11px] font-mono text-indigo-700 font-bold mt-1">
                      Serial Number Match Provided: <span className="bg-indigo-100 px-1.5 py-0.5 rounded">{claim.serialNumberProvided}</span>
                    </div>
                  )}
                </div>
              </div>

              {/* Officer Notes if present */}
              {claim.officerNotes && (
                <div className="p-2 bg-indigo-50/50 rounded border border-indigo-100 text-[11px] font-mono text-indigo-900 flex items-center justify-between">
                  <span><strong>Officer Audit Note:</strong> {claim.officerNotes}</span>
                  {claim.verifiedByOfficer && <span className="text-indigo-600">{claim.verifiedByOfficer}</span>}
                </div>
              )}

              {/* Actions */}
              <div className="flex items-center justify-end gap-2 pt-1">
                {item && (
                  <button
                    onClick={() => onSelectItem(item)}
                    className="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold"
                  >
                    View Item Record
                  </button>
                )}
                {item && (
                  <button
                    onClick={() => onOpenHandoverModal(item, claim)}
                    className="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold font-mono shadow-xs flex items-center gap-1.5"
                  >
                    <UserCheck className="w-3.5 h-3.5" />
                    <span>Process Digital Handover & Release</span>
                  </button>
                )}
              </div>
            </div>
          );
        })}
      </div>

    </div>
  );
};
