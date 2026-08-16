import React from 'react';
import { 
  CheckCircle2, 
  Server, 
  Database, 
  ShieldCheck, 
  Activity, 
  Clock,
  Radio,
  Cpu
} from 'lucide-react';

interface FooterProps {
  totalItems: number;
}

export const Footer: React.FC<FooterProps> = ({ totalItems }) => {
  return (
    <footer className="w-full bg-[#0a0f1d] text-slate-400 border-t border-slate-800 text-[10px] sm:text-[11px] font-mono py-2 px-3 sm:px-6 flex flex-col sm:flex-row items-center justify-between gap-2 shrink-0 z-20">
      
      {/* Left: Database & Backend Engine Status */}
      <div className="flex items-center gap-2.5 flex-wrap">
        <div className="flex items-center gap-1 text-emerald-400 font-bold">
          <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          <span>CONNECTED TO: MySQL / MariaDB @ localhost:3306 [utf8mb4_unicode_ci]</span>
        </div>
        <span className="hidden md:inline text-slate-700">|</span>
        <div className="hidden md:flex items-center gap-1 text-slate-300">
          <Cpu className="w-3 h-3 text-indigo-400" />
          <span>MEMORY USAGE: 14.8MB / 128MB</span>
        </div>
        <span className="hidden lg:inline text-slate-700">|</span>
        <div className="hidden lg:flex items-center gap-1 text-slate-300">
          <span>PHP 8.2.10 (PDO_MYSQL)</span>
        </div>
      </div>

      {/* Right: Security & Version */}
      <div className="flex items-center gap-2.5">
        <span className="hidden sm:inline text-slate-400">
          SESSION: <strong className="text-slate-200">ACTIVE_SSL_2026</strong>
        </span>
        <span className="hidden sm:inline text-slate-700">|</span>
        <span className="text-indigo-400 font-bold">
          UniLostFound &bull; v2.4.1 Production
        </span>
      </div>

    </footer>
  );
};
