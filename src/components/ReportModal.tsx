import React, { useState } from 'react';
import { 
  X, 
  Upload, 
  MapPin, 
  Calendar, 
  Tag, 
  Archive, 
  AlertOctagon, 
  PlusCircle, 
  CheckCircle2,
  FileImage,
  Lock
} from 'lucide-react';
import { Item, ItemType, User, CampusLocation } from '../types';
import { CATEGORIES, CAMPUS_LOCATIONS, STORAGE_LOCKERS } from '../data/mockData';

interface ReportModalProps {
  isOpen: boolean;
  onClose: () => void;
  type: ItemType;
  currentUser: User;
  onItemCreated: (newItem: Item) => void;
}

export const ReportModal: React.FC<ReportModalProps> = ({
  isOpen,
  onClose,
  type,
  currentUser,
  onItemCreated
}) => {
  if (!isOpen) return null;

  const isFound = type === 'found';
  const [title, setTitle] = useState('');
  const [category, setCategory] = useState('Electronics');
  const [locationId, setLocationId] = useState(CAMPUS_LOCATIONS[0].id);
  const [dateEvent, setDateEvent] = useState(new Date().toISOString().split('T')[0]);
  const [description, setDescription] = useState('');
  const [distinctiveFeatures, setDistinctiveFeatures] = useState('');
  const [reward, setReward] = useState('');
  const [secretHint, setSecretHint] = useState('');
  const [selectedLockerId, setSelectedLockerId] = useState(STORAGE_LOCKERS[0].id);
  const [imageUrl, setImageUrl] = useState(
    isFound 
      ? 'https://images.unsplash.com/photo-1544717305-2782549b5136?w=500&auto=format&fit=crop&q=80'
      : 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=500&auto=format&fit=crop&q=80'
  );

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!title.trim() || !description.trim()) return;

    const loc = CAMPUS_LOCATIONS.find(l => l.id === locationId) || CAMPUS_LOCATIONS[0];
    const locker = STORAGE_LOCKERS.find(l => l.id === selectedLockerId) || STORAGE_LOCKERS[0];
    const refNumber = `LF-${new Date().getFullYear()}-${Math.floor(1000 + Math.random() * 9000)}`;

    const newItem: Item = {
      id: `itm_${Date.now()}`,
      referenceNo: refNumber,
      title,
      type,
      category,
      description,
      distinctiveFeatures,
      locationId: loc.id,
      locationName: loc.name,
      building: loc.building,
      dateReported: new Date().toISOString().split('T')[0],
      dateEvent,
      status: isFound ? 'available' : 'pending',
      imageUrl,
      reportedBy: {
        id: currentUser.id,
        name: currentUser.name,
        email: currentUser.email,
        universityId: currentUser.universityId,
        phone: currentUser.phone
      },
      storageLocation: isFound ? {
        rack: locker.rack,
        bin: locker.bin,
        lockerId: locker.id
      } : undefined,
      secretVerificationHint: secretHint || undefined,
      rewardOffered: (!isFound && reward) ? reward : undefined,
      claimsCount: 0,
      daysHeld: 0
    };

    onItemCreated(newItem);
    onClose();
  };

  return (
    <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 z-50 overflow-y-auto">
      <div className="bg-white rounded-xl shadow-2xl border border-slate-200 w-full max-w-2xl overflow-hidden flex flex-col max-h-[92vh]">
        
        {/* Header */}
        <div className={`px-4 py-3 text-white flex items-center justify-between ${
          isFound ? 'bg-emerald-700' : 'bg-rose-700'
        }`}>
          <div className="flex items-center gap-2">
            {isFound ? <PlusCircle className="w-5 h-5" /> : <AlertOctagon className="w-5 h-5" />}
            <span className="font-bold text-sm">
              {isFound ? 'Report Found Item & Intake to Storage' : 'Report Lost Item on Campus'}
            </span>
          </div>
          <button onClick={onClose} className="text-white/80 hover:text-white">
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Form Body */}
        <form onSubmit={handleSubmit} className="p-4 sm:p-5 overflow-y-auto flex-1 text-xs space-y-3.5">
          
          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label className="block text-[10px] font-bold uppercase text-slate-500 font-mono mb-1">
                Item Title / Name *
              </label>
              <input
                type="text"
                value={title}
                onChange={(e) => setTitle(e.target.value)}
                placeholder="e.g. Apple AirPods Pro 2 in Black Case"
                className="w-full p-2 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                required
              />
            </div>

            <div>
              <label className="block text-[10px] font-bold uppercase text-slate-500 font-mono mb-1">
                Category Taxonomy *
              </label>
              <select
                value={category}
                onChange={(e) => setCategory(e.target.value)}
                className="w-full p-2 border border-slate-300 rounded text-xs bg-white"
              >
                {CATEGORIES.filter(c => c !== 'All Categories').map(cat => (
                  <option key={cat} value={cat}>{cat}</option>
                ))}
              </select>
            </div>
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label className="block text-[10px] font-bold uppercase text-slate-500 font-mono mb-1">
                Campus Location *
              </label>
              <select
                value={locationId}
                onChange={(e) => setLocationId(e.target.value)}
                className="w-full p-2 border border-slate-300 rounded text-xs bg-white"
              >
                {CAMPUS_LOCATIONS.map(loc => (
                  <option key={loc.id} value={loc.id}>{loc.name}</option>
                ))}
              </select>
            </div>

            <div>
              <label className="block text-[10px] font-bold uppercase text-slate-500 font-mono mb-1">
                Date {isFound ? 'Found' : 'Lost'} *
              </label>
              <input
                type="date"
                value={dateEvent}
                onChange={(e) => setDateEvent(e.target.value)}
                className="w-full p-2 border border-slate-300 rounded text-xs"
                required
              />
            </div>
          </div>

          {/* Description */}
          <div>
            <label className="block text-[10px] font-bold uppercase text-slate-500 font-mono mb-1">
              Detailed Description & Context *
            </label>
            <textarea
              rows={3}
              value={description}
              onChange={(e) => setDescription(e.target.value)}
              placeholder="Provide color, brand, condition, and the exact room or area where it was located..."
              className="w-full p-2 border border-slate-300 rounded text-xs focus:ring-1 focus:ring-indigo-500"
              required
            />
          </div>

          {/* Distinctive features */}
          <div>
            <label className="block text-[10px] font-bold uppercase text-slate-500 font-mono mb-1">
              Distinctive Features / Engravings / Secret Clues
            </label>
            <input
              type="text"
              value={distinctiveFeatures}
              onChange={(e) => setDistinctiveFeatures(e.target.value)}
              placeholder="e.g. Cat sticker on back, initials 'A.R.' on case, chipped corner"
              className="w-full p-2 border border-slate-300 rounded text-xs font-mono"
            />
          </div>

          {/* Found specific: Locker Storage Assignment */}
          {isFound && (
            <div className="p-3 bg-indigo-50/70 border border-indigo-200 rounded-lg space-y-2">
              <div className="flex items-center gap-1.5 text-indigo-900 font-bold font-mono text-[11px]">
                <Archive className="w-3.5 h-3.5 text-indigo-600" />
                <span>Physical Locker & Storage Bin Assignment</span>
              </div>
              <select
                value={selectedLockerId}
                onChange={(e) => setSelectedLockerId(e.target.value)}
                className="w-full p-1.5 border border-indigo-200 rounded text-xs bg-white font-mono"
              >
                {STORAGE_LOCKERS.map(l => (
                  <option key={l.id} value={l.id}>
                    {l.rack} &bull; {l.bin} ({l.currentCount}/{l.capacity} items)
                  </option>
                ))}
              </select>
            </div>
          )}

          {/* Lost specific: Reward info */}
          {!isFound && (
            <div>
              <label className="block text-[10px] font-bold uppercase text-slate-500 font-mono mb-1">
                Optional Reward Offering
              </label>
              <input
                type="text"
                value={reward}
                onChange={(e) => setReward(e.target.value)}
                placeholder="e.g. $30 Coffee Gift Card / Cash"
                className="w-full p-2 border border-slate-300 rounded text-xs"
              />
            </div>
          )}

          {/* Image URL preview */}
          <div>
            <label className="block text-[10px] font-bold uppercase text-slate-500 font-mono mb-1">
              Item Photo Preview URL
            </label>
            <div className="flex gap-2 items-center">
              <input
                type="text"
                value={imageUrl}
                onChange={(e) => setImageUrl(e.target.value)}
                className="flex-1 p-2 border border-slate-300 rounded text-xs font-mono text-slate-600"
              />
              <img 
                src={imageUrl} 
                alt="" 
                className="w-8 h-8 rounded object-cover border border-slate-300 shrink-0" 
                referrerPolicy="no-referrer"
              />
            </div>
          </div>

          {/* Reporter Profile Preview */}
          <div className="p-2.5 bg-slate-50 border border-slate-200 rounded text-[11px] text-slate-600 flex items-center justify-between">
            <span>Logging as: <strong>{currentUser.name}</strong> ({currentUser.universityId})</span>
            <span className="font-mono text-slate-400">{currentUser.role.toUpperCase()}</span>
          </div>

          <div className="pt-2 flex items-center justify-end gap-2">
            <button
              type="button"
              onClick={onClose}
              className="px-3 py-1.5 bg-white border border-slate-300 rounded font-semibold text-slate-700 hover:bg-slate-100"
            >
              Cancel
            </button>
            <button
              type="submit"
              className={`px-4 py-1.5 text-white font-bold rounded shadow-xs text-xs ${
                isFound ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-rose-600 hover:bg-rose-700'
              }`}
            >
              {isFound ? 'Record Found Item' : 'Submit Lost Item'}
            </button>
          </div>

        </form>
      </div>
    </div>
  );
};
