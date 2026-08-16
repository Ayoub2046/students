import React from 'react';
import { 
  MapPin, 
  Calendar, 
  Tag, 
  Archive, 
  CheckCircle, 
  Clock, 
  AlertCircle, 
  ArrowRight,
  ShieldCheck
} from 'lucide-react';
import { Item } from '../types';

interface ItemCardProps {
  item: Item;
  onSelect: (item: Item) => void;
  onClaim?: (item: Item) => void;
}

export const ItemCard: React.FC<ItemCardProps> = ({ item, onSelect, onClaim }) => {
  const isFound = item.type === 'found';

  const getStatusBadge = () => {
    switch (item.status) {
      case 'ready_for_handover':
        return <span className="bg-emerald-100 text-emerald-800 border border-emerald-300 text-[10px] font-bold px-1.5 py-0.5 rounded font-mono">READY HANDOVER</span>;
      case 'under_verification':
        return <span className="bg-amber-100 text-amber-800 border border-amber-300 text-[10px] font-bold px-1.5 py-0.5 rounded font-mono">IN VERIFICATION</span>;
      case 'returned':
        return <span className="bg-slate-100 text-slate-700 border border-slate-300 text-[10px] font-bold px-1.5 py-0.5 rounded font-mono">RETURNED</span>;
      case 'unclaimed':
        return <span className="bg-rose-100 text-rose-800 border border-rose-300 text-[10px] font-bold px-1.5 py-0.5 rounded font-mono">90D+ UNCLAIMED</span>;
      default:
        return <span className="bg-indigo-100 text-indigo-800 border border-indigo-300 text-[10px] font-bold px-1.5 py-0.5 rounded font-mono">AVAILABLE</span>;
    }
  };

  return (
    <div 
      onClick={() => onSelect(item)}
      className="bg-white border border-slate-200 rounded-lg overflow-hidden shadow-xs hover:shadow-md hover:border-indigo-300 transition-all cursor-pointer flex flex-col group"
    >
      {/* Top Image & Type Badge Banner */}
      <div className="relative h-36 bg-slate-100 overflow-hidden">
        <img 
          src={item.imageUrl} 
          alt={item.title}
          className="w-full h-full object-cover group-hover:scale-103 transition-transform duration-300"
          loading="lazy"
          referrerPolicy="no-referrer"
        />
        <div className="absolute top-2 left-2 flex items-center gap-1.5">
          <span className={`text-[10px] font-extrabold px-2 py-0.5 rounded shadow-xs tracking-wider uppercase ${
            isFound ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'
          }`}>
            {item.type}
          </span>
          {getStatusBadge()}
        </div>

        <div className="absolute top-2 right-2">
          <span className="bg-slate-900/80 backdrop-blur-xs text-white font-mono font-bold text-[10px] px-1.5 py-0.5 rounded">
            {item.referenceNo}
          </span>
        </div>

        {item.daysHeld >= 90 && (
          <div className="absolute bottom-0 inset-x-0 bg-rose-600 text-white text-[9px] font-bold text-center py-0.5 font-mono">
            CRITICAL RETENTION: {item.daysHeld} DAYS HELD
          </div>
        )}
      </div>

      {/* Content Area */}
      <div className="p-3 flex-1 flex flex-col justify-between space-y-2">
        <div>
          <div className="flex items-center gap-1.5 text-[10px] text-slate-500 font-mono mb-1">
            <Tag className="w-3 h-3 text-indigo-500" />
            <span className="font-semibold text-slate-700">{item.category}</span>
            <span>&bull;</span>
            <Calendar className="w-3 h-3 text-slate-400" />
            <span>{item.dateEvent}</span>
          </div>

          <h3 className="text-xs font-bold text-slate-900 group-hover:text-indigo-600 transition-colors line-clamp-1">
            {item.title}
          </h3>

          <p className="text-[11px] text-slate-600 line-clamp-2 mt-1 leading-relaxed">
            {item.description}
          </p>
        </div>

        {/* Footer Meta Details */}
        <div className="pt-2 border-t border-slate-100 flex flex-col gap-1 text-[10px] text-slate-500">
          <div className="flex items-center gap-1 text-slate-600 truncate">
            <MapPin className="w-3 h-3 text-rose-500 shrink-0" />
            <span className="truncate">{item.building}</span>
          </div>

          {item.storageLocation && (
            <div className="flex items-center justify-between font-mono text-[9px] text-indigo-700 bg-indigo-50/70 px-1.5 py-0.5 rounded border border-indigo-100">
              <span className="truncate">{item.storageLocation.rack} &bull; {item.storageLocation.bin}</span>
              <Archive className="w-2.5 h-2.5 text-indigo-500" />
            </div>
          )}

          <div className="flex items-center justify-between pt-1">
            <span className="text-[10px] text-slate-400 font-mono">
              Reported by {item.reportedBy.name.split(' ')[0]}
            </span>
            <span className="text-indigo-600 font-bold text-[11px] flex items-center gap-0.5 group-hover:translate-x-0.5 transition-transform">
              View Details <ArrowRight className="w-3 h-3" />
            </span>
          </div>
        </div>

      </div>
    </div>
  );
};
