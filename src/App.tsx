import React, { useState, useMemo } from 'react';
import { 
  Grid, 
  List, 
  PlusCircle, 
  AlertOctagon, 
  MapPin, 
  ShieldCheck, 
  CheckCircle2, 
  Clock, 
  Inbox, 
  Search,
  Filter,
  Tag,
  Archive,
  ArrowRight,
  TrendingUp,
  FileSpreadsheet,
  X,
  ChevronLeft,
  ChevronRight,
  UserCheck
} from 'lucide-react';
import confetti from 'canvas-confetti';
import { Header } from './components/Header';
import { Sidebar } from './components/Sidebar';
import { ItemCard } from './components/ItemCard';
import { ItemTableView } from './components/ItemTableView';
import { ItemDetailModal } from './components/ItemDetailModal';
import { ReportModal } from './components/ReportModal';
import { CampusMap } from './components/CampusMap';
import { OfficerHandoverModal } from './components/OfficerHandoverModal';
import { StorageManager } from './components/StorageManager';
import { AdminAnalytics } from './components/AdminAnalytics';
import { MessagesView } from './components/MessagesView';
import { UsersView } from './components/UsersView';
import { ClaimsView } from './components/ClaimsView';
import { AdminDashboard } from './components/AdminDashboard';
import { StaffDashboard } from './components/StaffDashboard';
import { OfficerDashboard } from './components/OfficerDashboard';
import { StudentDashboard } from './components/StudentDashboard';
import { Footer } from './components/Footer';

import { 
  Item, 
  User, 
  UserRole, 
  Claim, 
  AuditLog, 
  NotificationItem, 
  ItemType 
} from './types';
import { 
  CURRENT_USER, 
  STAFF_USERS, 
  INITIAL_ITEMS, 
  INITIAL_CLAIMS, 
  INITIAL_AUDIT_LOGS, 
  INITIAL_NOTIFICATIONS,
  ALL_USERS,
  CATEGORIES,
  CAMPUS_LOCATIONS
} from './data/mockData';

export default function App() {
  // Global State
  const [currentUser, setCurrentUser] = useState<User>(STAFF_USERS.admin);
  const [items, setItems] = useState<Item[]>(INITIAL_ITEMS);
  const [claims, setClaims] = useState<Claim[]>(INITIAL_CLAIMS);
  const [users, setUsers] = useState<User[]>(ALL_USERS);
  const [auditLogs, setAuditLogs] = useState<AuditLog[]>(INITIAL_AUDIT_LOGS);
  const [notifications, setNotifications] = useState<NotificationItem[]>(INITIAL_NOTIFICATIONS);

  // Active View & Navigation
  const [activeView, setActiveView] = useState<string>('dashboard');
  const [viewMode, setViewMode] = useState<'table' | 'grid'>('table');
  const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);

  // Filters & Search
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCategory, setSelectedCategory] = useState('All Categories');
  const [selectedLocation, setSelectedLocation] = useState('all');
  const [selectedType, setSelectedType] = useState<'all' | 'lost' | 'found'>('all');
  const [selectedStatus, setSelectedStatus] = useState('all');

  // Pagination for inventory table
  const [currentPage, setCurrentPage] = useState(1);
  const pageSize = 8;

  // Modals
  const [inspectingItem, setInspectingItem] = useState<Item | null>(null);
  const [handoverItem, setHandoverItem] = useState<Item | null>(null);
  const [activeClaimForHandover, setActiveClaimForHandover] = useState<Claim | undefined>(undefined);
  const [reportModalType, setReportModalType] = useState<ItemType | null>(null);

  // Switch role handler (student, staff, officer, admin)
  const handleRoleChange = (role: UserRole) => {
    setCurrentUser(STAFF_USERS[role]);
    setActiveView('dashboard');
  };

  // Filter items based on active criteria
  const filteredItems = useMemo(() => {
    return items.filter(item => {
      // Search query
      if (searchQuery.trim()) {
        const q = searchQuery.toLowerCase();
        const matchesQuery = 
          item.title.toLowerCase().includes(q) ||
          item.description.toLowerCase().includes(q) ||
          item.referenceNo.toLowerCase().includes(q) ||
          item.category.toLowerCase().includes(q) ||
          item.building.toLowerCase().includes(q) ||
          (item.reportedBy?.name && item.reportedBy.name.toLowerCase().includes(q));
        if (!matchesQuery) return false;
      }

      // Type filter
      if (selectedType !== 'all' && item.type !== selectedType) return false;

      // Category filter
      if (selectedCategory !== 'All Categories' && item.category !== selectedCategory) return false;

      // Location filter
      if (selectedLocation !== 'all' && item.building !== selectedLocation) return false;

      // Status filter
      if (selectedStatus !== 'all' && item.status !== selectedStatus) return false;

      return true;
    });
  }, [items, searchQuery, selectedType, selectedCategory, selectedLocation, selectedStatus]);

  // Paginated items
  const paginatedItems = useMemo(() => {
    const start = (currentPage - 1) * pageSize;
    return filteredItems.slice(start, start + pageSize);
  }, [filteredItems, currentPage]);

  const totalPages = Math.ceil(filteredItems.length / pageSize) || 1;

  // Export CSV Function
  const handleExportCSV = () => {
    const headers = 'ID,ReferenceNo,Title,Type,Category,Location,DateReported,Status,StorageBin\n';
    const rows = filteredItems.map(i => 
      `"${i.id}","${i.referenceNo}","${i.title.replace(/"/g, '""')}","${i.type}","${i.category}","${i.building}","${i.dateReported}","${i.status}","${i.storageLocation?.bin || ''}"`
    ).join('\n');
    
    const blob = new Blob([headers + rows], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.setAttribute('href', url);
    link.setAttribute('download', `unilostfound_export_${Date.now()}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  // Handle new report creation
  const handleItemCreated = (newItem: Item) => {
    setItems([newItem, ...items]);

    // Add audit log
    const newLog: AuditLog = {
      id: `log_${Date.now()}`,
      timestamp: new Date().toISOString().replace('T', ' ').substring(0, 19),
      actor: currentUser.name,
      actorRole: currentUser.role,
      action: newItem.type === 'found' ? 'ITEM_INTAKE' : 'LOST_REPORT_SUBMITTED',
      target: `${newItem.referenceNo} (${newItem.title})`,
      details: `Created new ${newItem.type} record at ${newItem.building}.`,
      ipAddress: '10.24.110.45'
    };
    setAuditLogs([newLog, ...auditLogs]);

    // Add notification
    const newNotif: NotificationItem = {
      id: `notif_${Date.now()}`,
      title: newItem.type === 'found' ? 'Found Item Registered' : 'Lost Item Logged',
      message: `Record ${newItem.referenceNo} for "${newItem.title}" was successfully submitted.`,
      timestamp: 'Just now',
      read: false,
      type: 'system',
      linkId: newItem.referenceNo
    };
    setNotifications([newNotif, ...notifications]);

    confetti({
      particleCount: 60,
      spread: 45,
      origin: { y: 0.6 }
    });
  };

  // Handle claim submission
  const handleSubmitClaim = (claimData: { itemId: string; proofDetails: string; serialNumber: string }) => {
    const targetItem = items.find(i => i.id === claimData.itemId);
    if (!targetItem) return;

    const newClaim: Claim = {
      id: `clm_${Date.now()}`,
      itemId: targetItem.id,
      itemTitle: targetItem.title,
      itemReferenceNo: targetItem.referenceNo,
      claimedBy: {
        id: currentUser.id,
        name: currentUser.name,
        email: currentUser.email,
        universityId: currentUser.universityId,
        phone: currentUser.phone || '+1 (555) 000-0000'
      },
      submittedDate: new Date().toISOString().replace('T', ' ').substring(0, 16),
      status: 'pending',
      proofDetails: claimData.proofDetails,
      serialNumberProvided: claimData.serialNumber
    };

    setClaims([newClaim, ...claims]);

    // Update item status
    setItems(items.map(i => i.id === targetItem.id ? { ...i, status: 'under_verification', claimsCount: i.claimsCount + 1 } : i));

    // Audit log
    const newLog: AuditLog = {
      id: `log_${Date.now()}`,
      timestamp: new Date().toISOString().replace('T', ' ').substring(0, 19),
      actor: currentUser.name,
      actorRole: currentUser.role,
      action: 'CLAIM_SUBMITTED',
      target: `${targetItem.referenceNo} (${targetItem.title})`,
      details: `Claimant provided ownership proof with serial verification.`,
      ipAddress: '10.24.110.45'
    };
    setAuditLogs([newLog, ...auditLogs]);
  };

  // Handle officer handover completion
  const handleCompleteHandover = (data: {
    itemId: string;
    studentId: string;
    studentName: string;
    idType: string;
    officerNotes: string;
    signatureData: string;
  }) => {
    const targetItem = items.find(i => i.id === data.itemId);
    if (!targetItem) return;

    // Update item status to returned
    setItems(items.map(i => i.id === targetItem.id ? { ...i, status: 'returned' } : i));

    // Update claim if exists
    setClaims(claims.map(c => c.itemId === targetItem.id ? { ...c, status: 'handed_over', handoverDate: new Date().toISOString() } : c));

    // Audit log
    const newLog: AuditLog = {
      id: `log_${Date.now()}`,
      timestamp: new Date().toISOString().replace('T', ' ').substring(0, 19),
      actor: currentUser.name,
      actorRole: currentUser.role,
      action: 'ITEM_HANDED_OVER',
      target: `${targetItem.referenceNo} (${targetItem.title})`,
      details: `Released to student ${data.studentName} (${data.studentId}) via ${data.idType}. Digital signature captured.`,
      ipAddress: '10.24.110.88'
    };
    setAuditLogs([newLog, ...auditLogs]);

    confetti({
      particleCount: 80,
      spread: 60,
      origin: { y: 0.6 }
    });
  };

  const handleMarkNotificationRead = (id: string) => {
    setNotifications(notifications.map(n => n.id === id ? { ...n, read: true } : n));
  };

  const handleAddUser = (newUser: User) => {
    setUsers([newUser, ...users]);
    const newLog: AuditLog = {
      id: `log_${Date.now()}`,
      timestamp: new Date().toISOString().replace('T', ' ').substring(0, 19),
      actor: currentUser.name,
      actorRole: currentUser.role,
      action: 'USER_ENROLLED',
      target: `${newUser.universityId} (${newUser.name})`,
      details: `Created new user account with role ${newUser.role}.`,
      ipAddress: '10.24.100.5'
    };
    setAuditLogs([newLog, ...auditLogs]);
  };

  const handleUpdateUser = (updatedUser: User) => {
    setUsers(users.map(u => u.id === updatedUser.id ? updatedUser : u));
  };

  return (
    <div className="min-h-screen w-full flex flex-col bg-slate-100 text-slate-800 font-sans antialiased overflow-x-hidden">
      
      {/* Top Header */}
      <Header
        currentUser={currentUser}
        onRoleChange={handleRoleChange}
        activeView={activeView}
        setActiveView={setActiveView}
        searchQuery={searchQuery}
        setSearchQuery={setSearchQuery}
        onOpenReportLost={() => setReportModalType('lost')}
        onOpenReportFound={() => setReportModalType('found')}
        notifications={notifications}
        onMarkNotificationRead={handleMarkNotificationRead}
        toggleMobileMenu={() => setIsMobileMenuOpen(!isMobileMenuOpen)}
        isMobileMenuOpen={isMobileMenuOpen}
        onExportCSV={handleExportCSV}
      />

      {/* Main Body Section */}
      <div className="flex-1 flex w-full relative">
        
        {/* Navigation Sidebar */}
        <Sidebar
          currentUser={currentUser}
          activeView={activeView}
          setActiveView={setActiveView}
          selectedCategory={selectedCategory}
          setSelectedCategory={setSelectedCategory}
          selectedType={selectedType}
          setSelectedType={setSelectedType}
          selectedStatus={selectedStatus}
          setSelectedStatus={setSelectedStatus}
          selectedLocation={selectedLocation}
          setSelectedLocation={setSelectedLocation}
          items={items}
          onOpenReportLost={() => setReportModalType('lost')}
          onOpenReportFound={() => setReportModalType('found')}
          isMobileOpen={isMobileMenuOpen}
          closeMobileMenu={() => setIsMobileMenuOpen(false)}
        />

        {/* Content View Canvas */}
        <main className="flex-1 p-3 sm:p-5 lg:p-6 overflow-y-auto w-full">
          
          {/* VIEW: ROLE SPECIFIC DASHBOARD */}
          {activeView === 'dashboard' && (
            <div className="w-full space-y-4">
              {currentUser.role === 'admin' && (
                <AdminDashboard
                  items={items}
                  auditLogs={auditLogs}
                  currentUser={currentUser}
                  onNavigateTab={(tab) => setActiveView(tab)}
                  onSelectItem={(item) => setInspectingItem(item)}
                  onOpenReportModal={(type) => setReportModalType(type)}
                />
              )}

              {currentUser.role === 'staff' && (
                <StaffDashboard
                  items={items}
                  claims={claims}
                  currentUser={currentUser}
                  onNavigateTab={(tab) => setActiveView(tab)}
                  onSelectItem={(item) => setInspectingItem(item)}
                  onOpenReportModal={(type) => setReportModalType(type)}
                  onOpenHandoverModal={(item, claim) => {
                    setHandoverItem(item);
                    setActiveClaimForHandover(claim);
                  }}
                />
              )}

              {currentUser.role === 'officer' && (
                <OfficerDashboard
                  items={items}
                  claims={claims}
                  currentUser={currentUser}
                  onNavigateTab={(tab) => setActiveView(tab)}
                  onSelectItem={(item) => setInspectingItem(item)}
                  onOpenReportModal={(type) => setReportModalType(type)}
                  onOpenHandoverModal={(item, claim) => {
                    setHandoverItem(item);
                    setActiveClaimForHandover(claim);
                  }}
                />
              )}

              {currentUser.role === 'student' && (
                <StudentDashboard
                  items={items}
                  claims={claims}
                  currentUser={currentUser}
                  onNavigateTab={(tab) => setActiveView(tab)}
                  onSelectItem={(item) => setInspectingItem(item)}
                  onOpenReportModal={(type) => setReportModalType(type)}
                />
              )}
            </div>
          )}

          {/* VIEW: MASTER INVENTORY EXPLORER (Matches Screenshot Layout) */}
          {activeView === 'inventory' && (
            <div className="w-full space-y-3">
              
              {/* Top Search & Actions Control Header */}
              <div className="bg-white border border-slate-200 rounded-xl p-3.5 shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                
                {/* Search Bar */}
                <div className="relative flex-1">
                  <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                  <input
                    type="text"
                    placeholder="Search by ID, Item Name, or Owner (e.g. LF-2026, MacBook, Wallet)..."
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    className="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:bg-white"
                  />
                  {searchQuery && (
                    <button 
                      onClick={() => setSearchQuery('')}
                      className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 text-sm font-mono"
                    >
                      &times;
                    </button>
                  )}
                </div>

                {/* Right Actions */}
                <div className="flex items-center gap-2 flex-wrap">
                  <button
                    onClick={handleExportCSV}
                    className="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-mono font-semibold flex items-center gap-1.5 border border-slate-300 transition-colors shadow-xs"
                  >
                    <FileSpreadsheet className="w-3.5 h-3.5 text-slate-600" />
                    <span>Export CSV</span>
                  </button>

                  <button
                    onClick={() => setReportModalType('found')}
                    className="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold font-mono flex items-center gap-1.5 shadow-xs transition-colors"
                  >
                    <PlusCircle className="w-4 h-4" />
                    <span>+ New Entry</span>
                  </button>

                  <div className="flex items-center bg-slate-100 p-0.5 rounded-lg border border-slate-200">
                    <button
                      onClick={() => setViewMode('table')}
                      className={`p-1.5 rounded-md text-xs font-semibold flex items-center gap-1 ${
                        viewMode === 'table' ? 'bg-white text-indigo-700 shadow-xs' : 'text-slate-500 hover:text-slate-800'
                      }`}
                      title="Table View"
                    >
                      <List className="w-3.5 h-3.5" />
                    </button>
                    <button
                      onClick={() => setViewMode('grid')}
                      className={`p-1.5 rounded-md text-xs font-semibold flex items-center gap-1 ${
                        viewMode === 'grid' ? 'bg-white text-indigo-700 shadow-xs' : 'text-slate-500 hover:text-slate-800'
                      }`}
                      title="Card Grid View"
                    >
                      <Grid className="w-3.5 h-3.5" />
                    </button>
                  </div>
                </div>

              </div>

              {/* Active Filter Summary Bar */}
              <div className="flex items-center justify-between px-1 text-xs text-slate-500 font-mono">
                <span>
                  Showing {paginatedItems.length > 0 ? (currentPage - 1) * pageSize + 1 : 0} - {Math.min(currentPage * pageSize, filteredItems.length)} of {filteredItems.length} active records
                </span>
                {(selectedCategory !== 'All Categories' || selectedType !== 'all' || selectedLocation !== 'all' || selectedStatus !== 'all' || searchQuery) && (
                  <button 
                    onClick={() => {
                      setSelectedCategory('All Categories');
                      setSelectedType('all');
                      setSelectedLocation('all');
                      setSelectedStatus('all');
                      setSearchQuery('');
                    }}
                    className="text-indigo-600 font-bold hover:underline"
                  >
                    Reset Filters
                  </button>
                )}
              </div>

              {/* Items Display */}
              {filteredItems.length === 0 ? (
                <div className="bg-white rounded-xl border border-slate-200 p-12 text-center space-y-3">
                  <Archive className="w-12 h-12 text-slate-300 mx-auto" />
                  <h3 className="text-sm font-bold text-slate-700">No matching records found</h3>
                  <p className="text-xs text-slate-500 max-w-sm mx-auto">
                    Try adjusting your search criteria or register a new lost/found item entry.
                  </p>
                </div>
              ) : viewMode === 'table' ? (
                <div className="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
                  <ItemTableView
                    items={paginatedItems}
                    onSelect={(i) => setInspectingItem(i)}
                  />
                  
                  {/* Table Pagination Footer */}
                  <div className="p-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs font-mono">
                    <span className="text-slate-500">
                      Page {currentPage} of {totalPages}
                    </span>
                    <div className="flex items-center gap-1">
                      <button
                        onClick={() => setCurrentPage(Math.max(1, currentPage - 1))}
                        disabled={currentPage === 1}
                        className="px-2 py-1 rounded bg-white border border-slate-200 text-slate-700 disabled:opacity-40 hover:bg-slate-100"
                      >
                        <ChevronLeft className="w-3.5 h-3.5" />
                      </button>
                      
                      {Array.from({ length: totalPages }, (_, i) => i + 1).map(p => (
                        <button
                          key={p}
                          onClick={() => setCurrentPage(p)}
                          className={`w-7 h-7 rounded text-xs font-bold ${
                            currentPage === p 
                              ? 'bg-indigo-600 text-white' 
                              : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-100'
                          }`}
                        >
                          {p}
                        </button>
                      ))}

                      <button
                        onClick={() => setCurrentPage(Math.min(totalPages, currentPage + 1))}
                        disabled={currentPage === totalPages}
                        className="px-2 py-1 rounded bg-white border border-slate-200 text-slate-700 disabled:opacity-40 hover:bg-slate-100"
                      >
                        <ChevronRight className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>
                </div>
              ) : (
                <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3.5">
                  {paginatedItems.map(item => (
                    <ItemCard
                      key={item.id}
                      item={item}
                      onSelect={(i) => setInspectingItem(i)}
                    />
                  ))}
                </div>
              )}

            </div>
          )}

          {/* VIEW: CLAIMS VERIFICATION */}
          {activeView === 'claims' && (
            <ClaimsView
              claims={claims}
              items={items}
              currentUser={currentUser}
              onSelectItem={(i) => setInspectingItem(i)}
              onOpenHandoverModal={(item, claim) => {
                setHandoverItem(item);
                setActiveClaimForHandover(claim);
              }}
            />
          )}

          {/* VIEW: USERS MANAGEMENT */}
          {activeView === 'users' && (
            <UsersView
              users={users}
              currentUser={currentUser}
              onAddUser={handleAddUser}
              onUpdateUser={handleUpdateUser}
            />
          )}

          {/* VIEW: CAMPUS MAP */}
          {activeView === 'map' && (
            <div className="w-full">
              <CampusMap
                items={items}
                onSelectItem={(i) => setInspectingItem(i)}
              />
            </div>
          )}

          {/* VIEW: PHYSICAL STORAGE LOCKERS */}
          {activeView === 'storage' && (
            <div className="w-full">
              <StorageManager
                items={items}
                onSelectItem={(i) => setInspectingItem(i)}
              />
            </div>
          )}

          {/* VIEW: REPORTS & ANALYTICS */}
          {activeView === 'analytics' && (
            <div className="w-full">
              <AdminAnalytics
                items={items}
                auditLogs={auditLogs}
              />
            </div>
          )}

          {/* VIEW: DESK MESSAGES */}
          {activeView === 'messages' && (
            <div className="w-full">
              <MessagesView currentUser={currentUser} />
            </div>
          )}

          {/* VIEW: MY SUBMISSIONS & CLAIMS */}
          {activeView === 'my-reports' && (
            <div className="space-y-4 max-w-5xl mx-auto">
              <div className="bg-white border border-slate-200 rounded-xl p-4 shadow-xs">
                <h2 className="text-sm font-bold text-slate-900 font-sans">
                  My University Activity & Filed Claims
                </h2>
                <p className="text-[11px] text-slate-500 mt-0.5">
                  Logged in as <strong>{currentUser.name}</strong> ({currentUser.universityId}) &bull; {currentUser.department}
                </p>
              </div>

              {/* Claims List */}
              <div className="bg-white border border-slate-200 rounded-xl p-4 shadow-xs space-y-3">
                <h3 className="text-xs font-bold uppercase tracking-wider text-slate-700 font-mono">
                  My Submitted Claims & Verification Status
                </h3>
                <div className="divide-y divide-slate-100">
                  {claims.map(claim => (
                    <div key={claim.id} className="py-3 flex items-center justify-between gap-3 text-xs flex-wrap">
                      <div>
                        <div className="flex items-center gap-2 mb-1">
                          <span className="font-mono font-bold text-[10px] text-indigo-700 bg-indigo-50 px-1.5 py-0.2 rounded border border-indigo-200">
                            {claim.itemReferenceNo}
                          </span>
                          <span className={`text-[9px] font-mono font-bold px-2 py-0.5 rounded uppercase ${
                            claim.status === 'approved' || claim.status === 'handed_over' 
                              ? 'bg-emerald-100 text-emerald-800' 
                              : 'bg-amber-100 text-amber-800'
                          }`}>
                            {claim.status.replace('_', ' ')}
                          </span>
                        </div>
                        <h4 className="font-bold text-slate-900">{claim.itemTitle}</h4>
                        <p className="text-[11px] text-slate-500 mt-0.5">{claim.proofDetails}</p>
                      </div>

                      <div className="text-right">
                        <span className="text-[10px] text-slate-400 font-mono block">Submitted: {claim.submittedDate}</span>
                        {claim.status === 'approved' && (
                          <span className="text-[11px] font-bold text-emerald-600">
                            Visit Room 104 with Photo ID for Pickup
                          </span>
                        )}
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          )}

        </main>

      </div>

      {/* Technical Status Bar Footer */}
      <Footer totalItems={items.length} />

      {/* Modal: Item Detail & Claim Inspection */}
      <ItemDetailModal
        item={inspectingItem}
        currentUser={currentUser}
        onClose={() => setInspectingItem(null)}
        onSubmitClaim={handleSubmitClaim}
        onStartHandover={(i) => {
          setInspectingItem(null);
          setHandoverItem(i);
        }}
        allItems={items}
      />

      {/* Modal: Report Lost / Found Intake */}
      <ReportModal
        isOpen={reportModalType !== null}
        onClose={() => setReportModalType(null)}
        type={reportModalType || 'lost'}
        currentUser={currentUser}
        onItemCreated={handleItemCreated}
      />

      {/* Modal: Officer Digital Signature Handover */}
      <OfficerHandoverModal
        item={handoverItem}
        currentUser={currentUser}
        onClose={() => {
          setHandoverItem(null);
          setActiveClaimForHandover(undefined);
        }}
        onCompleteHandover={handleCompleteHandover}
      />

    </div>
  );
}
