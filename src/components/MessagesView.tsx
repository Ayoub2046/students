import React, { useState } from 'react';
import { 
  MessageSquare, 
  Send, 
  User, 
  ShieldCheck, 
  Clock, 
  Lock,
  Archive
} from 'lucide-react';
import { MessageThread, User as UserType } from '../types';
import { INITIAL_MESSAGES } from '../data/mockData';

interface MessagesViewProps {
  currentUser: UserType;
}

export const MessagesView: React.FC<MessagesViewProps> = ({ currentUser }) => {
  const [threads, setThreads] = useState<MessageThread[]>(INITIAL_MESSAGES);
  const [selectedThread, setSelectedThread] = useState<MessageThread>(INITIAL_MESSAGES[0]);
  const [newMsgText, setNewMsgText] = useState('');

  const handleSendMessage = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newMsgText.trim()) return;

    const newMsg = {
      id: `msg_${Date.now()}`,
      senderId: currentUser.id,
      senderName: currentUser.name,
      isOfficer: currentUser.role === 'officer' || currentUser.role === 'admin',
      text: newMsgText,
      timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
    };

    const updatedThreads = threads.map(t => {
      if (t.id === selectedThread.id) {
        return {
          ...t,
          messages: [...t.messages, newMsg]
        };
      }
      return t;
    });

    setThreads(updatedThreads);
    setSelectedThread({
      ...selectedThread,
      messages: [...selectedThread.messages, newMsg]
    });
    setNewMsgText('');
  };

  return (
    <div className="w-full bg-white border border-slate-200 rounded-xl overflow-hidden shadow-xs flex flex-col md:flex-row h-[560px]">
      
      {/* Threads List Sidebar */}
      <div className="w-full md:w-80 border-r border-slate-200 bg-slate-50/70 flex flex-col">
        <div className="p-3 border-b border-slate-200 bg-white">
          <div className="flex items-center justify-between">
            <h3 className="text-xs font-bold uppercase tracking-wider text-slate-700 font-mono flex items-center gap-1.5">
              <MessageSquare className="w-3.5 h-3.5 text-indigo-600" />
              <span>Inquiry Channels</span>
            </h3>
            <span className="text-[10px] font-mono text-slate-400">{threads.length} active</span>
          </div>
        </div>

        <div className="overflow-y-auto flex-1 divide-y divide-slate-100">
          {threads.map(thread => (
            <div
              key={thread.id}
              onClick={() => setSelectedThread(thread)}
              className={`p-3 cursor-pointer transition-colors ${
                selectedThread.id === thread.id 
                  ? 'bg-indigo-50/90 border-l-3 border-indigo-600' 
                  : 'hover:bg-slate-100'
              }`}
            >
              <div className="flex items-center justify-between mb-1">
                <span className="font-mono font-bold text-[10px] text-indigo-700 bg-white px-1.5 py-0.2 rounded border border-indigo-200">
                  {thread.referenceNo}
                </span>
                <span className="text-[10px] text-slate-400 font-mono">
                  {thread.messages[thread.messages.length - 1]?.timestamp}
                </span>
              </div>
              <h4 className="text-xs font-bold text-slate-800 truncate mb-0.5">{thread.itemTitle}</h4>
              <p className="text-[11px] text-slate-500 truncate">
                {thread.messages[thread.messages.length - 1]?.text}
              </p>
            </div>
          ))}
        </div>

        <div className="p-2.5 bg-slate-100 border-t border-slate-200 text-[10px] text-slate-500 flex items-center gap-1.5 font-mono">
          <Lock className="w-3 h-3 text-slate-400" />
          <span>Encrypted Student-Desk Communications</span>
        </div>
      </div>

      {/* Main Conversation Stream */}
      <div className="flex-1 flex flex-col justify-between bg-white">
        
        {/* Thread Header */}
        <div className="p-3 border-b border-slate-200 flex items-center justify-between bg-slate-50">
          <div>
            <div className="flex items-center gap-2 mb-0.5">
              <span className="text-xs font-bold text-slate-900">{selectedThread.itemTitle}</span>
              <span className="text-[10px] font-mono text-indigo-700 font-bold">{selectedThread.referenceNo}</span>
            </div>
            <div className="text-[11px] text-slate-500">
              Assigned Officer: <strong>{selectedThread.recipientName}</strong> ({selectedThread.recipientRole})
            </div>
          </div>
          <span className="text-[10px] font-mono font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
            ACTIVE CASE
          </span>
        </div>

        {/* Message bubbles */}
        <div className="p-4 overflow-y-auto flex-1 space-y-3">
          {selectedThread.messages.map(msg => {
            const isMe = msg.senderId === currentUser.id;

            return (
              <div
                key={msg.id}
                className={`flex flex-col ${isMe ? 'items-end' : 'items-start'}`}
              >
                <div className="flex items-center gap-1.5 mb-0.5 text-[10px] text-slate-400 font-mono">
                  <span className="font-bold text-slate-700">{msg.senderName}</span>
                  <span>&bull;</span>
                  <span>{msg.timestamp}</span>
                </div>
                <div className={`p-3 rounded-xl max-w-md text-xs leading-relaxed ${
                  isMe 
                    ? 'bg-indigo-600 text-white rounded-br-none shadow-xs' 
                    : 'bg-slate-100 text-slate-800 rounded-bl-none border border-slate-200'
                }`}>
                  {msg.text}
                </div>
              </div>
            );
          })}
        </div>

        {/* Reply Form */}
        <form onSubmit={handleSendMessage} className="p-3 border-t border-slate-200 bg-slate-50 flex items-center gap-2">
          <input
            type="text"
            value={newMsgText}
            onChange={(e) => setNewMsgText(e.target.value)}
            placeholder="Type verification notes or inquiry message..."
            className="flex-1 p-2 bg-white border border-slate-300 rounded-lg text-xs focus:ring-1 focus:ring-indigo-500"
          />
          <button
            type="submit"
            className="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg text-xs flex items-center gap-1 shadow-xs"
          >
            <Send className="w-3.5 h-3.5" />
            <span>Send</span>
          </button>
        </form>

      </div>

    </div>
  );
};
