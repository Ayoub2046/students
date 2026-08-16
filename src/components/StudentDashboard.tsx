import React from 'react';
import { 
  AlertOctagon, 
  PlusCircle, 
  Sparkles, 
  CheckCircle2, 
  Clock, 
  Search, 
  MapPin, 
  QrCode, 
  ArrowRight, 
  MessageSquare,
  ShieldCheck,
  Inbox,
  FolderOpen
} from 'lucide-react';
import { Item, Claim, User } from '../types';

interface StudentDashboardProps {
  items: Item[];
  claims: Claim[];
  currentUser: User;
  onNavigateTab: (tab: string) => void;
  onSelectItem: (item: Item) => void;
  onOpenReportModal: (type: 'lost' | 'found') => void;
}

export const StudentDashboard: React.FC<StudentDashboardProps> = ({
  items,
  claims,
  currentUser,
  onNavigateTab,
  onSelectItem,
  onOpenReportModal
}) => {
  // Filter items reported by or claimed by this student
  const myReportedLost = items.filter(i => i.type === 'lost' && i.reportedBy?.id === currentUser.id);
  const myReportedFound = items.filter(i => i.type === 'found' && i.reportedBy?.id === currentUser.id);
  const myClaims = claims.filter(c => c.claimedBy.id === currentUser.id);

  // Smart Match detection: find items matching lost items
  const potentialMatches = items.filter(i => 
    i.type === 'found' && 
    (i.category === 'Electronics' || i.title.toLowerCase().includes('macbook') || i.title.toLowerCase().includes('headphones'))
  );

  return (
    <div className="w-full space-y-4">
      
      {/* Student Portal Welcome Banner */}
      <div className="bg-gradient-to-r from-[#1e1b4b] to-[#312e81] text-white rounded-xl p-4 sm:p-5 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border border-indigo-800">
        <div>
          <div className="flex items-center gap-2 mb-1">
            <span className="px-2 py-0.5 rounded bg-indigo-500/80 text-[10px] font-mono font-bold tracking-wider uppercase">
              Student Recovery Portal
            </span>
            <span className="text-[11px] text-indigo-300 font-mono">
              CAMPUS ID: <strong className="text-white font-bold">{currentUser.universityId}</strong>
            </span>
          </div>
          <h1 className="text-lg sm:text-xl font-bold tracking-tight text-white">
            Hello, {currentUser.name}
          </h1>
          <p className="text-xs text-indigo-200 mt-0.5">
            {currentUser.department} &bull; Central Lost & Found Student Self-Service
          </p>
        </div>

        {/* Quick Report Actions */}
        <div className="flex items-center gap-2.5 flex-wrap">
          <button
            onClick={() => onOpenReportModal('lost')}
            className="px-3.5 py-2 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-sm transition-all"
          >
            <AlertOctagon className="w-4 h-4" />
            <span>I Lost an Item</span>
          </button>
          <button
            onClick={() => onOpenReportModal('found')}
            className="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-bold flex items-center gap-1.5 shadow-sm transition-all"
          >
            <PlusCircle className="w-4 h-4" />
            <span>I Found an Item</span>
          </button>
        </div>
      </div>

      {/* AI Smart Match Notification Box */}
      <div className="bg-indigo-50 border border-indigo-200 rounded-xl p-4 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-indigo-950">
        <div className="flex items-start gap-3">
          <div className="w-9 h-9 rounded-lg bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm">
            <Sparkles className="w-5 h-5" />
          </div>
          <div>
            <div className="font-bold text-xs flex items-center gap-2">
              <span>Automatic Campus Match Found!</span>
              <span className="bg-indigo-600 text-white text-[9px] font-mono px-1.5 py-0.2 rounded font-bold uppercase">
                High Confidence Match
              </span>
            </div>
            <p className="text-[11px] text-indigo-800 mt-0.5 leading-relaxed">
              We identified <strong>"Apple MacBook Pro 14" Space Gray"</strong> (Ref: LF-2026-8812) handed in at the Central Library desk that matches your lost equipment report.
            </p>
          </div>
        </div>

        <button
          onClick={() => {
            const matched = items.find(i => i.referenceNo === 'LF-2026-8812');
            if (matched) onSelectItem(matched);
          }}
          className="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shrink-0 shadow-xs flex items-center gap-1"
        >
          <span>Claim This Item</span>
          <ArrowRight className="w-3.5 h-3.5" />
        </button>
      </div>

      {/* Student Personal Stats & Active Submissions */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        {/* Left 2 Cols: My Active Lost Reports & Tracking Timeline */}
        <div className="lg:col-span-2 space-y-4">
          
          {/* Active Claims & Handover Pass */}
          <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-xs space-y-3">
            <div className="flex items-center justify-between pb-2 border-b border-slate-100">
              <h3 className="text-xs font-bold uppercase tracking-wider text-slate-800 font-mono flex items-center gap-1.5">
                <CheckCircle2 className="w-3.5 h-3.5 text-emerald-600" />
                <span>My Active Claims & Pickup Slips</span>
              </h3>
              <span className="text-[10px] font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded font-bold">
                {myClaims.length} SUBMITTED
              </span>
            </div>

            {myClaims.length === 0 ? (
              <div className="p-6 text-center text-slate-400 text-xs font-mono">
                You haven't submitted any ownership claims yet. Browse the inventory to claim an item.
              </div>
            ) : (
              <div className="space-y-3">
                {myClaims.map(claim => {
                  const item = items.find(i => i.id === claim.itemId);
                  return (
                    <div key={claim.id} className="p-3.5 bg-emerald-50/60 rounded-xl border border-emerald-200 space-y-2.5">
                      <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                          <span className="font-mono text-xs font-bold text-emerald-800">
                            Claim #{claim.id} &bull; {claim.itemReferenceNo}
                          </span>
                          <h4 className="text-xs font-bold text-slate-900 mt-0.5">{claim.itemTitle}</h4>
                        </div>
                        <span className="self-start sm:self-auto px-2.5 py-1 bg-emerald-600 text-white rounded font-mono text-[10px] font-bold uppercase shadow-xs">
                          {claim.status === 'approved' ? 'CLAIM APPROVED - READY FOR PICKUP' : claim.status}
                        </span>
                      </div>

                      {/* Pickup Instructions Bar */}
                      <div className="p-2.5 bg-white rounded-lg border border-emerald-200 text-xs flex items-center justify-between gap-3">
                        <div className="flex items-center gap-2">
                          <MapPin className="w-4 h-4 text-emerald-600 shrink-0" />
                          <span className="text-[11px] text-slate-700">
                            <strong>Pickup Desk:</strong> Student Life Pavilion, Room 104 (Mon-Fri 8:00 AM - 5:00 PM)
                          </span>
                        </div>
                        {item && (
                          <button
                            onClick={() => onSelectItem(item)}
                            className="text-[11px] font-bold text-emerald-700 hover:underline shrink-0"
                          >
                            View Pickup Ticket &rarr;
                          </button>
                        )}
                      </div>
                    </div>
                  );
                })}
              </div>
            )}
          </div>

          {/* My Reported Lost Items */}
          <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-xs space-y-3">
            <div className="flex items-center justify-between pb-2 border-b border-slate-100">
              <h3 className="text-xs font-bold uppercase tracking-wider text-slate-800 font-mono flex items-center gap-1.5">
                <AlertOctagon className="w-3.5 h-3.5 text-rose-600" />
                <span>My Reported Lost Items</span>
              </h3>
              <button 
                onClick={() => onOpenReportModal('lost')}
                className="text-[11px] font-bold text-rose-600 hover:text-rose-800 font-mono"
              >
                + Report Another
              </button>
            </div>

            <div className="divide-y divide-slate-100">
              {myReportedLost.map(item => (
                <div key={item.id} className="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                  <div className="flex items-center gap-3">
                    <img src={item.imageUrl} alt="" className="w-10 h-10 rounded object-cover border border-slate-200 shrink-0" referrerPolicy="no-referrer" />
                    <div>
                      <div className="flex items-center gap-2">
                        <span className="font-mono text-xs font-bold text-rose-800">{item.referenceNo}</span>
                        <span className="text-[10px] font-mono text-slate-500">{item.dateReported}</span>
                      </div>
                      <h4 className="text-xs font-bold text-slate-900">{item.title}</h4>
                      <p className="text-[11px] text-slate-500 font-mono">Lost at: {item.building}</p>
                    </div>
                  </div>

                  <div className="flex items-center gap-2 self-end sm:self-center">
                    <span className="text-[10px] font-mono font-bold bg-amber-100 text-amber-800 px-2 py-0.5 rounded">
                      SEARCH ACTIVE
                    </span>
                    <button
                      onClick={() => onSelectItem(item)}
                      className="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-xs font-semibold"
                    >
                      View Report
                    </button>
                  </div>
                </div>
              ))}
            </div>
          </div>

        </div>

        {/* Right Col: Quick Campus Directory Explorer & Contact Desk */}
        <div className="space-y-4">
          
          {/* Quick Browse Campus Directory */}
          <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-xs space-y-3">
            <h3 className="text-xs font-bold uppercase tracking-wider text-slate-800 font-mono">
              Lost Something Else?
            </h3>
            <p className="text-[11px] text-slate-500 leading-relaxed">
              Search all {items.filter(i => i.type === 'found').length} recently found items handed in across campus libraries, dining halls, and gyms.
            </p>
            <button
              onClick={() => onNavigateTab('inventory')}
              className="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold font-mono transition-colors flex items-center justify-center gap-1.5 shadow-xs"
            >
              <Search className="w-3.5 h-3.5" />
              <span>Search Found Items Inventory</span>
            </button>
            <button
              onClick={() => onNavigateTab('map')}
              className="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold font-mono transition-colors flex items-center justify-center gap-1.5"
            >
              <MapPin className="w-3.5 h-3.5 text-emerald-600" />
              <span>Explore Interactive Campus Map</span>
            </button>
          </div>

          {/* Student Direct Desk Messaging */}
          <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-xs space-y-3">
            <div className="flex items-center justify-between pb-2 border-b border-slate-100">
              <h3 className="text-xs font-bold uppercase tracking-wider text-slate-800 font-mono flex items-center gap-1.5">
                <MessageSquare className="w-3.5 h-3.5 text-blue-600" />
                <span>Desk Communication</span>
              </h3>
              <span className="text-[10px] font-mono text-emerald-600 font-bold">DESK ONLINE</span>
            </div>
            <p className="text-[11px] text-slate-600">
              Need urgent help regarding a lost passport, laptop, or room key? Message the on-duty campus lost & found officer directly.
            </p>
            <button
              onClick={() => onNavigateTab('messages')}
              className="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold font-mono transition-colors flex items-center justify-center gap-1.5 shadow-xs"
            >
              <MessageSquare className="w-3.5 h-3.5" />
              <span>Open Desk Chat (1 New)</span>
            </button>
          </div>

        </div>

      </div>

    </div>
  );
};
