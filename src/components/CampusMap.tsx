import React, { useState } from 'react';
import { 
  MapPin, 
  Layers, 
  Info, 
  Building, 
  ExternalLink, 
  Filter,
  CheckCircle2,
  AlertOctagon,
  X
} from 'lucide-react';
import { CampusLocation, Item } from '../types';
import { CAMPUS_LOCATIONS } from '../data/mockData';

interface CampusMapProps {
  items: Item[];
  onSelectItem: (item: Item) => void;
}

export const CampusMap: React.FC<CampusMapProps> = ({ items, onSelectItem }) => {
  const [selectedLocation, setSelectedLocation] = useState<CampusLocation | null>(CAMPUS_LOCATIONS[0]);
  const [filterType, setFilterType] = useState<'all' | 'lost' | 'found'>('all');

  const locationItems = items.filter(item => {
    if (!selectedLocation) return false;
    const matchLoc = item.locationId === selectedLocation.id;
    if (filterType === 'all') return matchLoc;
    return matchLoc && item.type === filterType;
  });

  return (
    <div className="w-full flex flex-col xl:flex-row gap-4 h-full">
      
      {/* Map Canvas Visualizer */}
      <div className="flex-1 bg-slate-900 rounded-xl border border-slate-700 p-4 relative overflow-hidden flex flex-col justify-between min-h-[420px] shadow-inner">
        
        {/* Top Controls Overlay */}
        <div className="flex items-center justify-between z-10 gap-2 flex-wrap">
          <div className="bg-slate-800/90 backdrop-blur-xs border border-slate-700 rounded-lg p-2 flex items-center gap-2 text-white">
            <Building className="w-4 h-4 text-indigo-400" />
            <span className="text-xs font-bold font-mono uppercase tracking-wider">
              Metropolitan Campus Map (Geo-Telemetry)
            </span>
          </div>

          <div className="flex items-center gap-1 bg-slate-800/90 backdrop-blur-xs border border-slate-700 p-1 rounded-lg">
            <button
              onClick={() => setFilterType('all')}
              className={`px-2 py-1 text-[10px] font-bold rounded ${
                filterType === 'all' ? 'bg-indigo-600 text-white' : 'text-slate-400 hover:text-white'
              }`}
            >
              All Pins
            </button>
            <button
              onClick={() => setFilterType('found')}
              className={`px-2 py-1 text-[10px] font-bold rounded ${
                filterType === 'found' ? 'bg-emerald-600 text-white' : 'text-slate-400 hover:text-emerald-400'
              }`}
            >
              Found Items
            </button>
            <button
              onClick={() => setFilterType('lost')}
              className={`px-2 py-1 text-[10px] font-bold rounded ${
                filterType === 'lost' ? 'bg-rose-600 text-white' : 'text-slate-400 hover:text-rose-400'
              }`}
            >
              Lost Items
            </button>
          </div>
        </div>

        {/* The Graphic Campus Grid / Ground Blueprint */}
        <div className="relative w-full h-[320px] my-2 bg-slate-950/60 rounded-lg border border-slate-800 overflow-hidden">
          {/* Subtle Grid Lines */}
          <div className="absolute inset-0 bg-[radial-gradient(#334155_1px,transparent_1px)] [background-size:20px_20px] opacity-40"></div>

          {/* Stylized Quad pathway lines */}
          <svg className="absolute inset-0 w-full h-full stroke-slate-800 stroke-[2] pointer-events-none">
            <line x1="20%" y1="20%" x2="80%" y2="80%" strokeDasharray="4 4" />
            <line x1="20%" y1="80%" x2="80%" y2="20%" strokeDasharray="4 4" />
            <circle cx="50%" cy="50%" r="90" fill="none" stroke="#1e293b" />
          </svg>

          {/* Interactive Campus Location Pins */}
          {CAMPUS_LOCATIONS.map(loc => {
            const isSelected = selectedLocation?.id === loc.id;
            const itemsAtLoc = items.filter(i => i.locationId === loc.id);
            const foundCount = itemsAtLoc.filter(i => i.type === 'found').length;
            const lostCount = itemsAtLoc.filter(i => i.type === 'lost').length;

            return (
              <button
                key={loc.id}
                onClick={() => setSelectedLocation(loc)}
                style={{ left: `${loc.xPercent}%`, top: `${loc.yPercent}%` }}
                className={`absolute -translate-x-1/2 -translate-y-1/2 group focus:outline-none transition-all duration-200 ${
                  isSelected ? 'z-20 scale-110' : 'z-10 hover:scale-105'
                }`}
              >
                <div className={`flex items-center gap-1.5 px-2 py-1 rounded-full text-[10px] font-bold shadow-lg border transition-all ${
                  isSelected 
                    ? 'bg-indigo-600 text-white border-indigo-400 ring-4 ring-indigo-500/30' 
                    : 'bg-slate-800 text-slate-200 border-slate-600 hover:bg-slate-700'
                }`}>
                  <MapPin className={`w-3 h-3 ${isSelected ? 'text-white' : 'text-indigo-400'}`} />
                  <span className="font-mono text-[10px]">{loc.building.split(' ')[0]}</span>
                  <span className="bg-slate-900/60 px-1 py-0.2 rounded text-[9px] font-mono text-emerald-400">
                    {itemsAtLoc.length}
                  </span>
                </div>

                {/* Hover Popover */}
                <div className="hidden group-hover:block absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 w-44 bg-slate-900 text-white p-2 rounded border border-slate-700 text-left text-[10px] shadow-xl pointer-events-none z-30">
                  <div className="font-bold text-white mb-0.5">{loc.name}</div>
                  <div className="text-slate-400 text-[9px]">{loc.campus}</div>
                  <div className="flex gap-2 mt-1 font-mono text-[9px]">
                    <span className="text-emerald-400">{foundCount} Found</span>
                    <span className="text-rose-400">{lostCount} Lost</span>
                  </div>
                </div>
              </button>
            );
          })}
        </div>

        {/* Bottom Legend */}
        <div className="flex items-center justify-between text-[11px] text-slate-400 z-10 bg-slate-800/80 p-2 rounded-lg border border-slate-700">
          <div className="flex items-center gap-3">
            <span className="flex items-center gap-1">
              <span className="w-2 h-2 rounded-full bg-emerald-500"></span> Found Depot
            </span>
            <span className="flex items-center gap-1">
              <span className="w-2 h-2 rounded-full bg-rose-500"></span> Lost Reports
            </span>
            <span className="flex items-center gap-1 font-mono text-[10px] text-indigo-300">
              Active Hotspots: {CAMPUS_LOCATIONS.length} Zones
            </span>
          </div>
          <span className="font-mono text-[10px] text-slate-500">Click any pin to inspect inventory</span>
        </div>

      </div>

      {/* Right Drawer: Selected Location Items List */}
      <div className="w-full xl:w-88 bg-white border border-slate-200 rounded-xl p-4 flex flex-col justify-between shadow-xs">
        {selectedLocation ? (
          <div className="flex-1 flex flex-col">
            <div className="pb-3 border-b border-slate-200">
              <div className="flex items-center justify-between">
                <span className="text-[10px] font-mono font-bold uppercase text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded border border-indigo-200">
                  {selectedLocation.campus}
                </span>
                <span className="text-[10px] font-mono text-slate-400">
                  {locationItems.length} active item(s)
                </span>
              </div>
              <h3 className="text-sm font-bold text-slate-900 mt-1">
                {selectedLocation.name}
              </h3>
              <p className="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                {selectedLocation.description}
              </p>
            </div>

            {/* List of items at this location */}
            <div className="flex-1 overflow-y-auto my-3 space-y-2 max-h-[380px] pr-1">
              {locationItems.length === 0 ? (
                <div className="py-8 text-center text-slate-400 text-xs">
                  No {filterType !== 'all' ? filterType : ''} items recorded at this building.
                </div>
              ) : (
                locationItems.map(item => (
                  <div
                    key={item.id}
                    onClick={() => onSelectItem(item)}
                    className="p-2.5 bg-slate-50 hover:bg-indigo-50/60 border border-slate-200 hover:border-indigo-300 rounded-lg cursor-pointer transition-all flex items-center gap-2.5"
                  >
                    <img
                      src={item.imageUrl}
                      alt=""
                      className="w-10 h-10 rounded object-cover border border-slate-200 shrink-0"
                      referrerPolicy="no-referrer"
                    />
                    <div className="flex-1 overflow-hidden">
                      <div className="flex items-center gap-1.5 mb-0.5">
                        <span className={`text-[8px] font-extrabold px-1.5 py-0.2 rounded font-mono uppercase ${
                          item.type === 'found' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'
                        }`}>
                          {item.type}
                        </span>
                        <span className="font-mono text-[10px] text-slate-500 font-bold">{item.referenceNo}</span>
                      </div>
                      <h4 className="text-xs font-bold text-slate-800 truncate">{item.title}</h4>
                      <div className="text-[10px] text-slate-500 truncate">{item.category} &bull; {item.dateEvent}</div>
                    </div>
                  </div>
                ))
              )}
            </div>
          </div>
        ) : (
          <div className="text-center py-12 text-slate-400 text-xs">
            Select a campus location on the map to view inventory.
          </div>
        )}
      </div>

    </div>
  );
};
