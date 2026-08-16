import React, { useState } from 'react';
import { 
  ArrowUpDown, 
  MapPin, 
  Archive, 
  Eye, 
  ShieldCheck, 
  ExternalLink,
  ChevronRight
} from 'lucide-react';
import { Item } from '../types';

interface ItemTableViewProps {
  items: Item[];
  onSelect: (item: Item) => void;
}

export const ItemTableView: React.FC<ItemTableViewProps> = ({ items, onSelect }) => {
  const [sortField, setSortField] = useState<keyof Item>('dateReported');
  const [sortDirection, setSortDirection] = useState<'asc' | 'desc'>('desc');

  const handleSort = (field: keyof Item) => {
    if (sortField === field) {
      setSortDirection(sortDirection === 'asc' ? 'desc' : 'asc');
    } else {
      setSortField(field);
      setSortDirection('desc');
    }
  };

  const sortedItems = [...items].sort((a, b) => {
    const aVal = a[sortField] || '';
    const bVal = b[sortField] || '';
    if (aVal < bVal) return sortDirection === 'asc' ? -1 : 1;
    if (aVal > bVal) return sortDirection === 'asc' ? 1 : -1;
    return 0;
  });

  return (
    <div className="w-full bg-white border border-slate-200 rounded-lg overflow-hidden shadow-xs">
      <div className="overflow-x-auto">
        <table className="w-full text-left border-collapse">
          <thead>
            <tr className="bg-slate-50 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider font-mono">
              <th className="py-2.5 px-3">Ref ID</th>
              <th className="py-2.5 px-3">Type</th>
              <th className="py-2.5 px-3 cursor-pointer hover:text-slate-800" onClick={() => handleSort('title')}>
                <div className="flex items-center gap-1">
                  <span>Item & Description</span>
                  <ArrowUpDown className="w-3 h-3 text-slate-400" />
                </div>
              </th>
              <th className="py-2.5 px-3">Category</th>
              <th className="py-2.5 px-3 cursor-pointer hover:text-slate-800" onClick={() => handleSort('locationName')}>
                <div className="flex items-center gap-1">
                  <span>Found/Lost Location</span>
                  <ArrowUpDown className="w-3 h-3 text-slate-400" />
                </div>
              </th>
              <th className="py-2.5 px-3 cursor-pointer hover:text-slate-800" onClick={() => handleSort('dateEvent')}>
                <div className="flex items-center gap-1">
                  <span>Date</span>
                  <ArrowUpDown className="w-3 h-3 text-slate-400" />
                </div>
              </th>
              <th className="py-2.5 px-3">Storage Bin</th>
              <th className="py-2.5 px-3">Status</th>
              <th className="py-2.5 px-3 text-right">Action</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-100 text-xs">
            {sortedItems.map(item => (
              <tr 
                key={item.id} 
                onClick={() => onSelect(item)}
                className="hover:bg-slate-50/80 transition-colors cursor-pointer group"
              >
                <td className="py-2 px-3 font-mono font-bold text-indigo-700 text-[11px] whitespace-nowrap">
                  {item.referenceNo}
                </td>

                <td className="py-2 px-3 whitespace-nowrap">
                  <span className={`text-[10px] font-extrabold px-2 py-0.5 rounded uppercase tracking-wider ${
                    item.type === 'found' 
                      ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' 
                      : 'bg-rose-100 text-rose-800 border border-rose-300'
                  }`}>
                    {item.type}
                  </span>
                </td>

                <td className="py-2 px-3 max-w-xs">
                  <div className="flex items-center gap-2">
                    <img 
                      src={item.imageUrl} 
                      alt="" 
                      className="w-8 h-8 rounded object-cover shrink-0 border border-slate-200"
                      referrerPolicy="no-referrer"
                    />
                    <div className="overflow-hidden">
                      <div className="font-bold text-slate-900 truncate group-hover:text-indigo-600 transition-colors">
                        {item.title}
                      </div>
                      <div className="text-[10px] text-slate-500 truncate">{item.description}</div>
                    </div>
                  </div>
                </td>

                <td className="py-2 px-3 text-slate-700 whitespace-nowrap font-medium text-[11px]">
                  {item.category}
                </td>

                <td className="py-2 px-3 text-slate-600 whitespace-nowrap text-[11px]">
                  <div className="flex items-center gap-1">
                    <MapPin className="w-3 h-3 text-rose-500 shrink-0" />
                    <span className="truncate max-w-[140px]">{item.building}</span>
                  </div>
                </td>

                <td className="py-2 px-3 text-slate-500 font-mono text-[10px] whitespace-nowrap">
                  {item.dateEvent}
                </td>

                <td className="py-2 px-3 whitespace-nowrap">
                  {item.storageLocation ? (
                    <span className="font-mono text-[10px] bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded border border-slate-200">
                      {item.storageLocation.bin.split(' ')[0]}
                    </span>
                  ) : (
                    <span className="text-[10px] text-slate-400">&mdash;</span>
                  )}
                </td>

                <td className="py-2 px-3 whitespace-nowrap">
                  {item.status === 'ready_for_handover' && (
                    <span className="bg-emerald-100 text-emerald-800 font-mono font-bold text-[10px] px-1.5 py-0.5 rounded border border-emerald-300">
                      READY HANDOVER
                    </span>
                  )}
                  {item.status === 'under_verification' && (
                    <span className="bg-amber-100 text-amber-800 font-mono font-bold text-[10px] px-1.5 py-0.5 rounded border border-amber-300">
                      VERIFYING
                    </span>
                  )}
                  {item.status === 'available' && (
                    <span className="bg-indigo-100 text-indigo-800 font-mono font-bold text-[10px] px-1.5 py-0.5 rounded border border-indigo-300">
                      AVAILABLE
                    </span>
                  )}
                  {item.status === 'returned' && (
                    <span className="bg-slate-100 text-slate-700 font-mono font-bold text-[10px] px-1.5 py-0.5 rounded border border-slate-300">
                      RETURNED
                    </span>
                  )}
                  {item.status === 'unclaimed' && (
                    <span className="bg-rose-100 text-rose-800 font-mono font-bold text-[10px] px-1.5 py-0.5 rounded border border-rose-300">
                      UNCLAIMED
                    </span>
                  )}
                </td>

                <td className="py-2 px-3 text-right whitespace-nowrap">
                  <button className="text-indigo-600 hover:text-indigo-900 font-bold text-xs p-1 rounded hover:bg-indigo-50 inline-flex items-center gap-1">
                    <span>Inspect</span>
                    <ChevronRight className="w-3.5 h-3.5" />
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};
