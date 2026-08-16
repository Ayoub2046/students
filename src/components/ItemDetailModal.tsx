import React, { useState } from 'react';
import { 
  X, 
  MapPin, 
  Calendar, 
  Tag, 
  Archive, 
  ShieldCheck, 
  User, 
  Phone, 
  Mail, 
  QrCode, 
  Sparkles, 
  CheckCircle2, 
  AlertTriangle, 
  Share2, 
  Printer, 
  Clock,
  Send,
  Lock
} from 'lucide-react';
import confetti from 'canvas-confetti';
import { Item, User as UserType, Claim } from '../types';

interface ItemDetailModalProps {
  item: Item | null;
  currentUser: UserType;
  onClose: () => void;
  onSubmitClaim: (claimData: { itemId: string; proofDetails: string; serialNumber: string }) => void;
  onStartHandover: (item: Item) => void;
  allItems: Item[];
}

export const ItemDetailModal: React.FC<ItemDetailModalProps> = ({
  item,
  currentUser,
  onClose,
  onSubmitClaim,
  onStartHandover,
  allItems
}) => {
  if (!item) return null;

  const [activeTab, setActiveTab] = useState<'details' | 'claim' | 'matching' | 'qr'>('details');
  const [proofText, setProofText] = useState('');
  const [serialNumber, setSerialNumber] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [claimSubmittedSuccess, setClaimSubmittedSuccess] = useState(false);

  // Find potential matching items (if lost, look for found with same category or similar title)
  const potentialMatches = allItems.filter(other => 
    other.id !== item.id && 
    other.type !== item.type && 
    (other.category === item.category || other.locationId === item.locationId)
  );

  const handleSubmitClaim = (e: React.FormEvent) => {
    e.preventDefault();
    if (!proofText.trim()) return;

    setIsSubmitting(true);
    setTimeout(() => {
      onSubmitClaim({
        itemId: item.id,
        proofDetails: proofText,
        serialNumber: serialNumber
      });
      setIsSubmitting(false);
      setClaimSubmittedSuccess(true);
      confetti({
        particleCount: 80,
        spread: 60,
        origin: { y: 0.6 }
      });
    }, 600);
  };

  const isOfficerOrAdmin = currentUser.role === 'officer' || currentUser.role === 'admin';

  return (
    <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 z-50 overflow-y-auto">
      <div className="bg-white rounded-xl shadow-2xl border border-slate-200 w-full max-w-3xl overflow-hidden flex flex-col max-h-[90vh]">
        
        {/* Header */}
        <div className="px-4 py-3 bg-[#1e1b4b] text-white flex items-center justify-between border-b border-indigo-900">
          <div className="flex items-center gap-2">
            <span className={`text-[10px] font-mono font-bold px-2 py-0.5 rounded uppercase ${
              item.type === 'found' ? 'bg-emerald-600' : 'bg-rose-600'
            }`}>
              {item.type} Item
            </span>
            <span className="font-mono text-xs text-indigo-300 font-bold">
              {item.referenceNo}
            </span>
          </div>

          <div className="flex items-center gap-2">
            <button 
              onClick={() => window.print()}
              className="p-1 rounded text-indigo-300 hover:text-white hover:bg-indigo-900/80"
              title="Print record"
            >
              <Printer className="w-4 h-4" />
            </button>
            <button 
              onClick={onClose}
              className="p-1 rounded text-indigo-300 hover:text-white hover:bg-indigo-900/80"
            >
              <X className="w-5 h-5" />
            </button>
          </div>
        </div>

        {/* Tab Navigation */}
        <div className="flex items-center border-b border-slate-200 bg-slate-50 px-4 text-xs font-semibold">
          <button
            onClick={() => setActiveTab('details')}
            className={`py-2.5 px-3 border-b-2 transition-colors ${
              activeTab === 'details' 
                ? 'border-indigo-600 text-indigo-700 font-bold bg-white' 
                : 'border-transparent text-slate-600 hover:text-slate-900'
            }`}
          >
            Item Details & Custody
          </button>
          
          {item.type === 'found' && (
            <button
              onClick={() => setActiveTab('claim')}
              className={`py-2.5 px-3 border-b-2 transition-colors flex items-center gap-1.5 ${
                activeTab === 'claim' 
                  ? 'border-indigo-600 text-indigo-700 font-bold bg-white' 
                  : 'border-transparent text-slate-600 hover:text-slate-900'
              }`}
            >
              <ShieldCheck className="w-3.5 h-3.5 text-emerald-600" />
              <span>Claim Ownership</span>
            </button>
          )}

          <button
            onClick={() => setActiveTab('matching')}
            className={`py-2.5 px-3 border-b-2 transition-colors flex items-center gap-1.5 ${
              activeTab === 'matching' 
                ? 'border-indigo-600 text-indigo-700 font-bold bg-white' 
                : 'border-transparent text-slate-600 hover:text-slate-900'
            }`}
          >
            <Sparkles className="w-3.5 h-3.5 text-purple-600" />
            <span>AI Matches ({potentialMatches.length})</span>
          </button>

          <button
            onClick={() => setActiveTab('qr')}
            className={`py-2.5 px-3 border-b-2 transition-colors flex items-center gap-1.5 ${
              activeTab === 'qr' 
                ? 'border-indigo-600 text-indigo-700 font-bold bg-white' 
                : 'border-transparent text-slate-600 hover:text-slate-900'
            }`}
          >
            <QrCode className="w-3.5 h-3.5 text-slate-600" />
            <span>Tag & QR Code</span>
          </button>
        </div>

        {/* Modal Body */}
        <div className="p-4 sm:p-5 overflow-y-auto flex-1 text-xs space-y-4">
          
          {activeTab === 'details' && (
            <div className="space-y-4">
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {/* Photo */}
                <div className="relative rounded-lg overflow-hidden border border-slate-200 bg-slate-100 h-56 flex items-center justify-center">
                  <img 
                    src={item.imageUrl} 
                    alt={item.title}
                    className="w-full h-full object-cover"
                    referrerPolicy="no-referrer"
                  />
                  <div className="absolute bottom-2 left-2 right-2 bg-slate-900/80 backdrop-blur-xs text-white text-[10px] p-1.5 rounded flex items-center justify-between font-mono">
                    <span>Intake Location: {item.building}</span>
                    <span>{item.dateEvent}</span>
                  </div>
                </div>

                {/* Main Specs */}
                <div className="space-y-2.5">
                  <div>
                    <span className="text-[10px] font-mono font-bold text-slate-400 uppercase tracking-wider">
                      {item.category}
                    </span>
                    <h2 className="text-base font-bold text-slate-900 leading-snug">
                      {item.title}
                    </h2>
                  </div>

                  <div className="p-2.5 bg-slate-50 border border-slate-200 rounded-md space-y-1.5">
                    <div className="flex items-center justify-between">
                      <span className="text-slate-500 font-mono">Status:</span>
                      <span className="font-bold uppercase text-indigo-700 font-mono">
                        {item.status.replace(/_/g, ' ')}
                      </span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span className="text-slate-500 font-mono">Date Logged:</span>
                      <span className="font-semibold text-slate-800">{item.dateReported}</span>
                    </div>
                    <div className="flex items-center justify-between">
                      <span className="text-slate-500 font-mono">Location:</span>
                      <span className="font-semibold text-slate-800 truncate max-w-[180px]">{item.locationName}</span>
                    </div>
                    {item.daysHeld > 0 && (
                      <div className="flex items-center justify-between">
                        <span className="text-slate-500 font-mono">Storage Duration:</span>
                        <span className="font-mono font-bold text-slate-800">{item.daysHeld} Days in Custody</span>
                      </div>
                    )}
                  </div>

                  {item.storageLocation && (
                    <div className="p-2.5 bg-indigo-50/70 border border-indigo-200 rounded-md">
                      <div className="text-[10px] font-bold uppercase text-indigo-800 font-mono mb-1 flex items-center gap-1">
                        <Archive className="w-3 h-3 text-indigo-600" />
                        <span>Physical Storage Locker</span>
                      </div>
                      <div className="grid grid-cols-2 gap-2 text-[11px] font-mono">
                        <div>
                          <span className="text-slate-500 block text-[9px]">RACK</span>
                          <span className="font-bold text-slate-800">{item.storageLocation.rack}</span>
                        </div>
                        <div>
                          <span className="text-slate-500 block text-[9px]">BIN / SAFE</span>
                          <span className="font-bold text-indigo-700">{item.storageLocation.bin}</span>
                        </div>
                      </div>
                    </div>
                  )}
                </div>
              </div>

              {/* Description & Distinctive Features */}
              <div className="space-y-2 pt-2 border-t border-slate-200">
                <div>
                  <h4 className="font-bold text-slate-800 mb-0.5">Item Description</h4>
                  <p className="text-slate-600 leading-relaxed bg-slate-50 p-2.5 rounded border border-slate-200">
                    {item.description}
                  </p>
                </div>

                {item.distinctiveFeatures && (
                  <div>
                    <h4 className="font-bold text-slate-800 mb-0.5">Distinctive Identifying Marks</h4>
                    <p className="text-slate-600 leading-relaxed bg-slate-50 p-2.5 rounded border border-slate-200 font-mono text-[11px]">
                      {item.distinctiveFeatures}
                    </p>
                  </div>
                )}
              </div>

              {/* Reporter Info & Chain of Custody */}
              <div className="p-3 bg-slate-50 rounded-lg border border-slate-200 text-[11px] space-y-1.5">
                <div className="font-bold text-slate-700 uppercase tracking-wider font-mono text-[10px]">
                  Intake & Custody Record
                </div>
                <div className="flex flex-wrap items-center justify-between gap-2 text-slate-600">
                  <span>Reported by: <strong>{item.reportedBy.name}</strong> ({item.reportedBy.universityId})</span>
                  <span>Contact: <strong>{item.reportedBy.email}</strong></span>
                </div>
              </div>

              {/* Officer / Admin Direct Handover Button */}
              {isOfficerOrAdmin && item.type === 'found' && (
                <div className="p-3 bg-emerald-50 border border-emerald-200 rounded-lg flex items-center justify-between">
                  <div>
                    <h5 className="font-bold text-emerald-900">Officer Handover Console</h5>
                    <p className="text-[11px] text-emerald-700">Conduct student ID verification and digital signature release.</p>
                  </div>
                  <button
                    onClick={() => onStartHandover(item)}
                    className="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-3 py-1.5 rounded-md text-xs shadow-xs transition-colors"
                  >
                    Open Handover Pad
                  </button>
                </div>
              )}
            </div>
          )}

          {activeTab === 'claim' && (
            <div className="space-y-3">
              {claimSubmittedSuccess ? (
                <div className="p-6 text-center bg-emerald-50 rounded-lg border border-emerald-200 space-y-2">
                  <CheckCircle2 className="w-10 h-10 text-emerald-600 mx-auto" />
                  <h3 className="text-sm font-bold text-emerald-900">Claim Submitted Successfully!</h3>
                  <p className="text-xs text-emerald-700 max-w-md mx-auto">
                    Your claim for <strong>{item.title}</strong> ({item.referenceNo}) has been queued for verification. The lost & found intake officer will review your serial and proof documentation.
                  </p>
                  <div className="pt-2">
                    <span className="font-mono text-[11px] bg-white px-2 py-1 rounded border border-emerald-200 text-emerald-800">
                      Tracking Claim ID: CLM-{Math.floor(1000 + Math.random() * 9000)}
                    </span>
                  </div>
                </div>
              ) : (
                <form onSubmit={handleSubmitClaim} className="space-y-3">
                  <div className="p-3 bg-amber-50 border border-amber-200 rounded-lg">
                    <div className="flex items-center gap-2 text-amber-800 font-bold mb-1">
                      <Lock className="w-4 h-4 text-amber-600" />
                      <span>Proof of Ownership Requirement</span>
                    </div>
                    <p className="text-[11px] text-amber-700 leading-relaxed">
                      To prevent unauthorized handovers, describe non-public details (passcode hints, stickers, invoices, contents, or serial numbers).
                    </p>
                  </div>

                  <div>
                    <label className="block text-[11px] font-bold text-slate-700 uppercase font-mono mb-1">
                      Proof of Ownership & Specific Identifying Details *
                    </label>
                    <textarea
                      rows={3}
                      value={proofText}
                      onChange={(e) => setProofText(e.target.value)}
                      placeholder="e.g. Exact stickers on the back, unique engravings, contents of the bag, date/time you realized it was missing..."
                      className="w-full p-2.5 border border-slate-300 rounded-md text-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                      required
                    />
                  </div>

                  <div>
                    <label className="block text-[11px] font-bold text-slate-700 uppercase font-mono mb-1">
                      Serial Number / Model ID (If Applicable)
                    </label>
                    <input
                      type="text"
                      value={serialNumber}
                      onChange={(e) => setSerialNumber(e.target.value)}
                      placeholder="e.g. C02G9941MD6T or Student ID # on card"
                      className="w-full p-2 border border-slate-300 rounded-md text-xs font-mono"
                    />
                  </div>

                  <div className="p-2.5 bg-slate-50 rounded border border-slate-200 text-[11px] text-slate-600 space-y-0.5">
                    <div className="font-bold text-slate-700">Claimant Verification Profile</div>
                    <div>Name: <strong>{currentUser.name}</strong> ({currentUser.universityId})</div>
                    <div>Email: <strong>{currentUser.email}</strong></div>
                  </div>

                  <button
                    type="submit"
                    disabled={isSubmitting}
                    className="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-md text-xs shadow-xs transition-colors flex items-center justify-center gap-1.5"
                  >
                    {isSubmitting ? (
                      <span>Verifying with Database...</span>
                    ) : (
                      <>
                        <Send className="w-3.5 h-3.5" />
                        <span>Submit Claim for Officer Review</span>
                      </>
                    )}
                  </button>
                </form>
              )}
            </div>
          )}

          {activeTab === 'matching' && (
            <div className="space-y-3">
              <div className="p-3 bg-purple-50 border border-purple-200 rounded-lg flex items-center justify-between">
                <div className="flex items-center gap-2">
                  <Sparkles className="w-4 h-4 text-purple-600" />
                  <div>
                    <h4 className="font-bold text-purple-950">AI Automated Cross-Match</h4>
                    <p className="text-[11px] text-purple-800">Scanned active lost & found records by taxonomy and location.</p>
                  </div>
                </div>
                <span className="font-mono font-bold text-xs bg-white text-purple-700 px-2 py-0.5 rounded border border-purple-200">
                  {potentialMatches.length} Candidate(s)
                </span>
              </div>

              {potentialMatches.length === 0 ? (
                <div className="text-center py-8 text-slate-400 text-xs">
                  No automated cross-matches found currently.
                </div>
              ) : (
                <div className="space-y-2">
                  {potentialMatches.map(match => (
                    <div key={match.id} className="p-3 bg-white border border-slate-200 rounded-lg flex items-center justify-between gap-3 hover:border-indigo-300 transition-colors">
                      <div className="flex items-center gap-3">
                        <img src={match.imageUrl} alt="" className="w-12 h-12 rounded object-cover border border-slate-200" referrerPolicy="no-referrer" />
                        <div>
                          <div className="flex items-center gap-1.5 mb-0.5">
                            <span className={`text-[9px] font-bold px-1.5 py-0.2 rounded font-mono uppercase ${
                              match.type === 'found' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'
                            }`}>
                              {match.type}
                            </span>
                            <span className="font-mono text-[10px] text-indigo-700 font-bold">{match.referenceNo}</span>
                          </div>
                          <h5 className="font-bold text-slate-800 text-xs">{match.title}</h5>
                          <span className="text-[10px] text-slate-500">{match.locationName} &bull; {match.dateEvent}</span>
                        </div>
                      </div>

                      <div className="text-right">
                        <span className="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-1 rounded border border-emerald-200 block mb-1">
                          94% Match
                        </span>
                        <span className="text-[10px] text-slate-400 font-mono">{match.category}</span>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </div>
          )}

          {activeTab === 'qr' && (
            <div className="space-y-4 text-center py-2">
              <div className="p-4 bg-slate-50 border border-slate-200 rounded-xl inline-block mx-auto">
                {/* Visual QR Code Generator */}
                <div className="w-40 h-40 bg-white border-2 border-slate-800 p-2 mx-auto flex flex-col items-center justify-center relative">
                  <div className="grid grid-cols-6 gap-1 w-full h-full p-1 bg-slate-900/5">
                    <div className="bg-slate-900 col-span-2 row-span-2 rounded-xs"></div>
                    <div className="bg-slate-900 col-span-2"></div>
                    <div className="bg-slate-900 col-span-2 row-span-2 rounded-xs"></div>
                    <div className="bg-slate-900 col-span-1"></div>
                    <div className="bg-slate-900 col-span-1"></div>
                    <div className="bg-slate-900 col-span-2 row-span-2 rounded-xs"></div>
                    <div className="bg-slate-900 col-span-2"></div>
                    <div className="bg-slate-900 col-span-2"></div>
                  </div>
                  <div className="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <span className="bg-white px-1.5 py-0.5 rounded text-[9px] font-mono font-bold text-indigo-700 border border-slate-300">
                      {item.referenceNo}
                    </span>
                  </div>
                </div>
                <div className="mt-2 font-mono font-bold text-xs text-slate-800">
                  {item.referenceNo}
                </div>
                <div className="text-[10px] text-slate-500 font-mono">
                  Metropolitan University Lost & Found
                </div>
              </div>

              <div className="max-w-xs mx-auto space-y-2">
                <p className="text-[11px] text-slate-600">
                  Attach this physical QR label to the intake bag or storage locker bin for instant scanning and verification.
                </p>
                <button
                  onClick={() => window.print()}
                  className="w-full py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-md text-xs flex items-center justify-center gap-1.5"
                >
                  <Printer className="w-3.5 h-3.5" />
                  <span>Print Barcode / Tag Label</span>
                </button>
              </div>
            </div>
          )}

        </div>

        {/* Footer */}
        <div className="p-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs">
          <span className="font-mono text-[11px] text-slate-500">
            RECORD ID: {item.id}
          </span>
          <button
            onClick={onClose}
            className="px-3 py-1.5 bg-white border border-slate-300 rounded font-semibold text-slate-700 hover:bg-slate-100"
          >
            Close
          </button>
        </div>

      </div>
    </div>
  );
};
