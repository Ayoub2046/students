import React, { useState, useRef } from 'react';
import { 
  X, 
  ShieldCheck, 
  CheckCircle2, 
  Printer, 
  PenTool, 
  Eraser, 
  FileCheck, 
  UserCheck, 
  QrCode,
  Lock,
  ArrowRight
} from 'lucide-react';
import confetti from 'canvas-confetti';
import { Item, User } from '../types';

interface OfficerHandoverModalProps {
  item: Item | null;
  currentUser: User;
  onClose: () => void;
  onCompleteHandover: (handoverData: {
    itemId: string;
    studentId: string;
    studentName: string;
    idType: string;
    officerNotes: string;
    signatureData: string;
  }) => void;
}

export const OfficerHandoverModal: React.FC<OfficerHandoverModalProps> = ({
  item,
  currentUser,
  onClose,
  onCompleteHandover
}) => {
  if (!item) return null;

  const [studentName, setStudentName] = useState('Alex Rivera');
  const [studentId, setStudentId] = useState('STU-994821');
  const [idType, setIdType] = useState('University Student ID Card');
  const [officerNotes, setOfficerNotes] = useState('Verified photo ID & ownership invoice match. Item released in full operating condition.');
  const [isCompleted, setIsCompleted] = useState(false);
  const [isDrawing, setIsDrawing] = useState(false);
  const [hasSignature, setHasSignature] = useState(false);

  const canvasRef = useRef<HTMLCanvasElement | null>(null);

  const startDrawing = (e: React.MouseEvent<HTMLCanvasElement> | React.TouchEvent<HTMLCanvasElement>) => {
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    const rect = canvas.getBoundingClientRect();
    const x = ('touches' in e) ? e.touches[0].clientX - rect.left : e.clientX - rect.left;
    const y = ('touches' in e) ? e.touches[0].clientY - rect.top : e.clientY - rect.top;

    ctx.beginPath();
    ctx.moveTo(x, y);
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.strokeStyle = '#1e1b4b';
    setIsDrawing(true);
    setHasSignature(true);
  };

  const draw = (e: React.MouseEvent<HTMLCanvasElement> | React.TouchEvent<HTMLCanvasElement>) => {
    if (!isDrawing) return;
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    const rect = canvas.getBoundingClientRect();
    const x = ('touches' in e) ? e.touches[0].clientX - rect.left : e.clientX - rect.left;
    const y = ('touches' in e) ? e.touches[0].clientY - rect.top : e.clientY - rect.top;

    ctx.lineTo(x, y);
    ctx.stroke();
  };

  const stopDrawing = () => {
    setIsDrawing(false);
  };

  const clearSignature = () => {
    const canvas = canvasRef.current;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    setHasSignature(false);
  };

  const handleFinishHandover = (e: React.FormEvent) => {
    e.preventDefault();
    const signatureData = canvasRef.current?.toDataURL() || '';

    onCompleteHandover({
      itemId: item.id,
      studentId,
      studentName,
      idType,
      officerNotes,
      signatureData
    });

    setIsCompleted(true);
    confetti({
      particleCount: 120,
      spread: 70,
      origin: { y: 0.6 }
    });
  };

  return (
    <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4 z-50 overflow-y-auto">
      <div className="bg-white rounded-xl shadow-2xl border border-slate-200 w-full max-w-2xl overflow-hidden flex flex-col max-h-[92vh]">
        
        {/* Header */}
        <div className="px-4 py-3 bg-[#1e1b4b] text-white flex items-center justify-between border-b border-indigo-900">
          <div className="flex items-center gap-2">
            <ShieldCheck className="w-5 h-5 text-emerald-400" />
            <span className="font-bold text-sm">
              Official Handover & Custody Release Console
            </span>
          </div>
          <button onClick={onClose} className="text-indigo-300 hover:text-white">
            <X className="w-5 h-5" />
          </button>
        </div>

        {isCompleted ? (
          /* Handover Success & Printable Receipt */
          <div className="p-6 text-center space-y-4 overflow-y-auto flex-1 text-xs">
            <div className="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto">
              <CheckCircle2 className="w-8 h-8" />
            </div>

            <h3 className="text-base font-bold text-slate-900">
              Item Released & Handover Completed!
            </h3>
            <p className="text-xs text-slate-600 max-w-md mx-auto">
              The custody chain for <strong>{item.title}</strong> has officially been transferred to <strong>{studentName}</strong> ({studentId}).
            </p>

            {/* Official Digital Receipt Ticket */}
            <div className="p-4 bg-slate-50 border border-slate-300 rounded-xl text-left font-mono space-y-2 max-w-md mx-auto shadow-inner text-[11px]">
              <div className="border-b border-slate-200 pb-2 text-center">
                <div className="font-bold text-slate-900">METROPOLITAN UNIVERSITY</div>
                <div className="text-[9px] text-slate-500">CAMPUS LOST & FOUND CUSTODY RECEIPT</div>
                <div className="text-[10px] text-indigo-700 font-bold mt-1">REC-2026-{Math.floor(10000 + Math.random() * 90000)}</div>
              </div>

              <div className="grid grid-cols-2 gap-2 text-[10px]">
                <div>
                  <span className="text-slate-400 block">ITEM:</span>
                  <span className="font-bold text-slate-800">{item.title}</span>
                </div>
                <div>
                  <span className="text-slate-400 block">REF NO:</span>
                  <span className="font-bold text-indigo-700">{item.referenceNo}</span>
                </div>
                <div>
                  <span className="text-slate-400 block">RECIPIENT:</span>
                  <span className="font-bold text-slate-800">{studentName}</span>
                </div>
                <div>
                  <span className="text-slate-400 block">STUDENT ID:</span>
                  <span className="font-bold text-slate-800">{studentId}</span>
                </div>
                <div>
                  <span className="text-slate-400 block">RELEASING OFFICER:</span>
                  <span className="font-bold text-slate-800">{currentUser.name}</span>
                </div>
                <div>
                  <span className="text-slate-400 block">DATE & TIME:</span>
                  <span className="font-bold text-slate-800">{new Date().toLocaleString()}</span>
                </div>
              </div>

              <div className="pt-2 border-t border-slate-200 text-center text-[9px] text-slate-400">
                DIGITALLY VERIFIED AND SEALED &bull; COMPLIANCE LOG REGISTERED
              </div>
            </div>

            <div className="flex items-center justify-center gap-2 pt-2">
              <button
                onClick={() => window.print()}
                className="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded text-xs flex items-center gap-1.5"
              >
                <Printer className="w-3.5 h-3.5" />
                <span>Print Official Receipt</span>
              </button>
              <button
                onClick={onClose}
                className="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded text-xs"
              >
                Done & Return to Desk
              </button>
            </div>
          </div>
        ) : (
          /* Handover Verification Form */
          <form onSubmit={handleFinishHandover} className="p-4 sm:p-5 overflow-y-auto flex-1 text-xs space-y-3.5">
            
            {/* Item Quick Overview */}
            <div className="p-3 bg-indigo-50/70 border border-indigo-200 rounded-lg flex items-center gap-3">
              <img src={item.imageUrl} alt="" className="w-12 h-12 rounded object-cover border border-indigo-200 shrink-0" referrerPolicy="no-referrer" />
              <div className="overflow-hidden flex-1">
                <div className="flex items-center gap-2 mb-0.5">
                  <span className="text-[10px] font-mono font-bold text-indigo-700 bg-white px-1.5 py-0.2 rounded border border-indigo-200">
                    {item.referenceNo}
                  </span>
                  <span className="text-[10px] text-slate-500 font-mono">{item.category}</span>
                </div>
                <h4 className="font-bold text-slate-900 text-xs truncate">{item.title}</h4>
                {item.storageLocation && (
                  <div className="text-[10px] text-slate-600 font-mono">
                    Storage Locker: {item.storageLocation.rack} &bull; {item.storageLocation.bin}
                  </div>
                )}
              </div>
            </div>

            {/* Recipient Verification Fields */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label className="block text-[10px] font-bold uppercase text-slate-500 font-mono mb-1">
                  Recipient Full Legal Name *
                </label>
                <input
                  type="text"
                  value={studentName}
                  onChange={(e) => setStudentName(e.target.value)}
                  className="w-full p-2 border border-slate-300 rounded text-xs"
                  required
                />
              </div>

              <div>
                <label className="block text-[10px] font-bold uppercase text-slate-500 font-mono mb-1">
                  University / Student ID Number *
                </label>
                <input
                  type="text"
                  value={studentId}
                  onChange={(e) => setStudentId(e.target.value)}
                  className="w-full p-2 border border-slate-300 rounded text-xs font-mono"
                  required
                />
              </div>
            </div>

            <div>
              <label className="block text-[10px] font-bold uppercase text-slate-500 font-mono mb-1">
                Identification Document Presented *
              </label>
              <select
                value={idType}
                onChange={(e) => setIdType(e.target.value)}
                className="w-full p-2 border border-slate-300 rounded text-xs bg-white"
              >
                <option>University Student ID Card</option>
                <option>State Driver's License / Real ID</option>
                <option>National Passport</option>
                <option>Faculty / Staff Badge</option>
              </select>
            </div>

            {/* Digital Signature Pad */}
            <div>
              <div className="flex items-center justify-between mb-1">
                <label className="text-[10px] font-bold uppercase text-slate-500 font-mono flex items-center gap-1">
                  <PenTool className="w-3 h-3 text-indigo-600" />
                  <span>Claimant Digital Signature Pad (Touch / Mouse) *</span>
                </label>
                <button
                  type="button"
                  onClick={clearSignature}
                  className="text-[10px] text-rose-600 hover:text-rose-800 font-semibold flex items-center gap-0.5"
                >
                  <Eraser className="w-3 h-3" />
                  <span>Clear Pad</span>
                </button>
              </div>

              <div className="border border-slate-300 rounded-lg overflow-hidden bg-slate-50 relative">
                <canvas
                  ref={canvasRef}
                  width={560}
                  height={110}
                  onMouseDown={startDrawing}
                  onMouseMove={draw}
                  onMouseUp={stopDrawing}
                  onMouseLeave={stopDrawing}
                  onTouchStart={startDrawing}
                  onTouchMove={draw}
                  onTouchEnd={stopDrawing}
                  className="w-full h-[110px] bg-white cursor-crosshair block"
                />
                {!hasSignature && (
                  <div className="absolute inset-0 flex items-center justify-center pointer-events-none text-slate-400 text-xs italic">
                    Sign here with finger or mouse cursor to acknowledge handover
                  </div>
                )}
              </div>
            </div>

            {/* Officer Notes */}
            <div>
              <label className="block text-[10px] font-bold uppercase text-slate-500 font-mono mb-1">
                Officer Handover & Inspection Notes
              </label>
              <input
                type="text"
                value={officerNotes}
                onChange={(e) => setOfficerNotes(e.target.value)}
                className="w-full p-2 border border-slate-300 rounded text-xs"
              />
            </div>

            {/* Officer Signature Badge */}
            <div className="p-2.5 bg-slate-50 rounded border border-slate-200 text-[11px] text-slate-600 flex items-center justify-between">
              <div>
                <span className="text-slate-400 block text-[9px] font-mono">AUTHORIZED OFFICER</span>
                <strong>{currentUser.name}</strong> ({currentUser.universityId})
              </div>
              <span className="font-mono text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 text-[10px] font-bold">
                AUTHORIZED RELEASE
              </span>
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
                className="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded shadow-xs text-xs flex items-center gap-1.5"
              >
                <CheckCircle2 className="w-3.5 h-3.5" />
                <span>Authorize Handover & Release Item</span>
              </button>
            </div>

          </form>
        )}

      </div>
    </div>
  );
};
