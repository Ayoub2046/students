import React, { useState } from 'react';
import { 
  Archive, 
  Layers, 
  Clock, 
  AlertTriangle, 
  Plus, 
  CheckCircle2, 
  ArrowRight,
  ShieldAlert,
  Search
} from 'lucide-react';
import { StorageLocker, Item } from '../types';
import { STORAGE_LOCKERS } from '../data/mockData';

interface StorageManagerProps {
  items: Item[];
  onSelectItem: (item: Item) => void;
}

export const StorageManager: React.FC<StorageManagerProps> = ({ items, onSelectItem }) => {
  const [selectedLocker, setSelectedLocker] = useState<StorageLocker>(STORAGE_LOCKERS[0]);
  const [filterQuery, setFilterQuery] = useState('');

  // 90-day retention items
  const retentionAlertItems = items.filter(i => i.daysHeld >= 90 || i.status === 'unclaimed');

  // Items in selected locker
  const lockerItems = items.filter(i => 
    i.storageLocation?.lockerId === selectedLocker.id ||
    (i.storageLocation?.rack === selectedLocker.rack && i.storageLocation?.bin === selectedLocker.bin)
  );

  return (
    <div className="w-full space-y-4">
      
      {/* Top Summary Banner */}
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div className="bg-white border border-slate-200 rounded-lg p-3 shadow-xs">
          <div className="flex items-center justify-between text-slate-500 font-mono text-[10px] uppercase">
            <span>Total Storage Racks</span>
            <Archive className="w-4 h-4 text-indigo-600" />
          </div>
          <div className="text-xl font-bold text-slate-900 font-mono mt-1">
            {STORAGE_LOCKERS.length} Racks / Vaults
          </div>
          <div className="text-[10px] text-emerald-600 font-semibold mt-0.5">
            Physical Vaults & Secure Lockers Online
          </div>
        </div>

        <div className="bg-white border border-slate-200 rounded-lg p-3 shadow-xs">
          <div className="flex items-center justify-between text-slate-500 font-mono text-[10px] uppercase">
            <span>Overall Capacity Load</span>
            <Layers className="w-4 h-4 text-indigo-600" />
          </div>
          <div className="text-xl font-bold text-slate-900 font-mono mt-1">
            68.4%
          </div>
          <div className="w-full bg-slate-100 rounded-full h-1.5 mt-1 overflow-hidden">
            <div className="bg-indigo-600 h-full rounded-full" style={{ width: '68.4%' }}></div>
          </div>
        </div>

        <div className="bg-white border border-rose-200 rounded-lg p-3 shadow-xs bg-rose-50/40">
          <div className="flex items-center justify-between text-rose-700 font-mono text-[10px] uppercase font-bold">
            <span>90-Day Retention Expirations</span>
            <AlertTriangle className="w-4 h-4 text-rose-600" />
          </div>
          <div className="text-xl font-bold text-rose-800 font-mono mt-1">
            {retentionAlertItems.length} Item(s)
          </div>
          <div className="text-[10px] text-rose-600 font-semibold mt-0.5">
            Pending public auction or donation disposition
          </div>
        </div>
      </div>

      {/* Main Locker Grid & Locker Items Split */}
      <div className="grid grid-cols-1 lg:grid-cols-3 gap-4">
        
        {/* Left: Lockers List */}
        <div className="lg:col-span-1 bg-white border border-slate-200 rounded-lg p-3.5 shadow-xs space-y-3">
          <div className="flex items-center justify-between">
            <h3 className="text-xs font-bold uppercase tracking-wider text-slate-700 font-mono flex items-center gap-1.5">
              <Archive className="w-3.5 h-3.5 text-indigo-600" />
              <span>Storage Compartments</span>
            </h3>
            <span className="text-[10px] font-mono text-slate-400">8 Locations</span>
          </div>

          <div className="space-y-2 max-h-[480px] overflow-y-auto pr-1">
            {STORAGE_LOCKERS.map(locker => {
              const isSelected = selectedLocker.id === locker.id;
              const percent = Math.round((locker.currentCount / locker.capacity) * 100);

              return (
                <div
                  key={locker.id}
                  onClick={() => setSelectedLocker(locker)}
                  className={`p-2.5 rounded-lg border cursor-pointer transition-all ${
                    isSelected 
                      ? 'bg-indigo-50/80 border-indigo-400 ring-1 ring-indigo-400/30' 
                      : 'bg-slate-50/60 border-slate-200 hover:bg-slate-100'
                  }`}
                >
                  <div className="flex items-center justify-between mb-1">
                    <span className="font-bold text-xs text-slate-800 font-mono">{locker.rack}</span>
                    <span className={`text-[9px] font-bold px-1.5 py-0.2 rounded font-mono uppercase ${
                      locker.status === 'near_capacity' 
                        ? 'bg-amber-100 text-amber-800' 
                        : 'bg-emerald-100 text-emerald-800'
                    }`}>
                      {locker.status.replace('_', ' ')}
                    </span>
                  </div>

                  <div className="text-[11px] text-slate-600 font-mono mb-1.5">
                    {locker.bin} &bull; <span className="text-slate-400">{locker.category}</span>
                  </div>

                  <div className="flex items-center justify-between text-[10px] text-slate-500 font-mono mb-1">
                    <span>Stored: {locker.currentCount} / {locker.capacity} Units</span>
                    <span className="font-bold">{percent}%</span>
                  </div>

                  <div className="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                    <div 
                      className={`h-full rounded-full ${percent > 80 ? 'bg-amber-500' : 'bg-indigo-600'}`} 
                      style={{ width: `${percent}%` }}
                    ></div>
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Right: Items Inside Selected Locker */}
        <div className="lg:col-span-2 bg-white border border-slate-200 rounded-lg p-3.5 shadow-xs flex flex-col justify-between">
          <div>
            <div className="pb-2.5 border-b border-slate-200 flex items-center justify-between flex-wrap gap-2">
              <div>
                <div className="flex items-center gap-2 mb-0.5">
                  <span className="font-mono text-xs font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">
                    {selectedLocker.rack}
                  </span>
                  <span className="font-mono text-xs text-slate-700 font-bold">
                    {selectedLocker.bin}
                  </span>
                </div>
                <div className="text-[11px] text-slate-500">
                  Primary Category: <strong>{selectedLocker.category}</strong>
                </div>
              </div>

              <div className="text-right font-mono text-xs text-slate-600">
                Holding: <strong className="text-slate-900">{lockerItems.length}</strong> active intake item(s)
              </div>
            </div>

            {/* Locker inventory list */}
            <div className="divide-y divide-slate-100 max-h-[420px] overflow-y-auto mt-2 pr-1">
              {lockerItems.length === 0 ? (
                <div className="py-12 text-center text-slate-400 text-xs">
                  No items currently stored in this bin compartment.
                </div>
              ) : (
                lockerItems.map(item => (
                  <div
                    key={item.id}
                    onClick={() => onSelectItem(item)}
                    className="py-2.5 px-2 hover:bg-slate-50 rounded cursor-pointer transition-colors flex items-center justify-between gap-3 group"
                  >
                    <div className="flex items-center gap-3">
                      <img src={item.imageUrl} alt="" className="w-10 h-10 rounded object-cover border border-slate-200 shrink-0" referrerPolicy="no-referrer" />
                      <div>
                        <div className="flex items-center gap-2 mb-0.5">
                          <span className="font-mono font-bold text-[10px] text-indigo-700">{item.referenceNo}</span>
                          <span className="text-[10px] text-slate-400 font-mono">{item.dateReported}</span>
                        </div>
                        <h4 className="text-xs font-bold text-slate-800 group-hover:text-indigo-600 transition-colors">
                          {item.title}
                        </h4>
                        <div className="text-[10px] text-slate-500 truncate max-w-sm">{item.description}</div>
                      </div>
                    </div>

                    <div className="text-right shrink-0">
                      <span className="text-[10px] font-mono font-bold text-slate-600 bg-slate-100 px-2 py-1 rounded block mb-1">
                        {item.daysHeld} Days in Storage
                      </span>
                      <button className="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 inline-flex items-center gap-0.5">
                        Inspect <ArrowRight className="w-3 h-3" />
                      </button>
                    </div>
                  </div>
                ))
              )}
            </div>
          </div>

          <div className="pt-3 border-t border-slate-200 text-[11px] text-slate-500 flex items-center justify-between font-mono">
            <span>SECURITY AUDIT: VAULT ACCESS RESTRICTED TO AUTHORIZED OFFICERS</span>
            <span>POLICE LOG ID: SAF-2026</span>
          </div>
        </div>

      </div>

      {/* 90-Day Retention Expiration Table */}
      {retentionAlertItems.length > 0 && (
        <div className="bg-white border border-rose-300 rounded-lg p-3.5 shadow-xs space-y-2">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-2 text-rose-800 font-bold text-xs font-mono uppercase">
              <ShieldAlert className="w-4 h-4 text-rose-600" />
              <span>Retention Compliance Desk (90+ Days Unclaimed Property)</span>
            </div>
            <span className="text-[10px] font-mono font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded border border-rose-200">
              LEGAL NOTICE THRESHOLD REACHED
            </span>
          </div>

          <div className="divide-y divide-slate-100">
            {retentionAlertItems.map(item => (
              <div key={item.id} className="py-2 flex items-center justify-between text-xs gap-3">
                <div className="flex items-center gap-3">
                  <span className="font-mono font-bold text-rose-700 text-[11px]">{item.referenceNo}</span>
                  <div>
                    <span className="font-bold text-slate-800">{item.title}</span>
                    <span className="text-slate-400 text-[10px] block font-mono">
                      Logged {item.dateReported} &bull; Stored {item.daysHeld} Days
                    </span>
                  </div>
                </div>

                <div className="flex items-center gap-2">
                  <button
                    onClick={() => onSelectItem(item)}
                    className="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-semibold"
                  >
                    Review File
                  </button>
                  <button
                    onClick={() => alert(`Marked ${item.referenceNo} for campus charity donation transfer.`)}
                    className="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded text-[11px] font-bold"
                  >
                    Transfer to Campus Donation
                  </button>
                </div>
              </div>
            ))}
          </div>
        </div>
      )}

    </div>
  );
};
