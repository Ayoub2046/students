import React, { useState } from 'react';
import { 
  Users, 
  Search, 
  UserPlus, 
  ShieldCheck, 
  ShieldAlert, 
  MoreVertical, 
  CheckCircle2, 
  XCircle, 
  Mail, 
  Phone, 
  Building, 
  Edit3, 
  Trash2, 
  KeyRound,
  Filter,
  UserCheck
} from 'lucide-react';
import { User, UserRole } from '../types';

interface UsersViewProps {
  users: User[];
  onAddUser: (user: User) => void;
  onUpdateUser: (user: User) => void;
  currentUser: User;
}

export const UsersView: React.FC<UsersViewProps> = ({
  users,
  onAddUser,
  onUpdateUser,
  currentUser
}) => {
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedRole, setSelectedRole] = useState<string>('all');
  const [selectedStatus, setSelectedStatus] = useState<string>('all');
  const [showAddModal, setShowAddModal] = useState(false);

  // New User Form State
  const [newName, setNewName] = useState('');
  const [newEmail, setNewEmail] = useState('');
  const [newUniversityId, setNewUniversityId] = useState('');
  const [newRole, setNewRole] = useState<UserRole>('student');
  const [newDepartment, setNewDepartment] = useState('');
  const [newPhone, setNewPhone] = useState('');

  const filteredUsers = users.filter(u => {
    const matchesSearch = 
      u.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
      u.email.toLowerCase().includes(searchQuery.toLowerCase()) ||
      u.universityId.toLowerCase().includes(searchQuery.toLowerCase()) ||
      (u.department && u.department.toLowerCase().includes(searchQuery.toLowerCase()));

    const matchesRole = selectedRole === 'all' || u.role === selectedRole;
    const matchesStatus = selectedStatus === 'all' || (u.status || 'active') === selectedStatus;

    return matchesSearch && matchesRole && matchesStatus;
  });

  const handleCreateUser = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newName || !newEmail || !newUniversityId) return;

    const created: User = {
      id: `usr_${Date.now()}`,
      name: newName,
      email: newEmail,
      universityId: newUniversityId,
      role: newRole,
      department: newDepartment || (newRole === 'student' ? 'General Undergraduate' : 'Campus Operations'),
      phone: newPhone || '+1 (555) 000-0000',
      status: 'active',
      joinedDate: new Date().toISOString().split('T')[0],
      lastActive: 'Just registered'
    };

    onAddUser(created);
    setShowAddModal(false);
    setNewName('');
    setNewEmail('');
    setNewUniversityId('');
    setNewDepartment('');
    setNewPhone('');
  };

  const handleToggleStatus = (user: User) => {
    const updatedStatus = user.status === 'suspended' ? 'active' : 'suspended';
    onUpdateUser({ ...user, status: updatedStatus });
  };

  const handleChangeRole = (user: User, newRole: UserRole) => {
    onUpdateUser({ ...user, role: newRole });
  };

  const studentCount = users.filter(u => u.role === 'student').length;
  const staffCount = users.filter(u => u.role === 'staff').length;
  const officerCount = users.filter(u => u.role === 'officer').length;
  const adminCount = users.filter(u => u.role === 'admin').length;

  return (
    <div className="w-full space-y-4">
      
      {/* Header Banner */}
      <div className="bg-white rounded-xl border border-slate-200 p-4 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div>
          <div className="flex items-center gap-2">
            <Users className="w-5 h-5 text-indigo-600" />
            <h1 className="text-base sm:text-lg font-bold text-slate-900 tracking-tight">
              University User & Staff Directory
            </h1>
          </div>
          <p className="text-xs text-slate-500 mt-0.5">
            Manage authenticated students, front-desk intake staff, campus security officers, and administrators.
          </p>
        </div>

        <button
          onClick={() => setShowAddModal(true)}
          className="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold font-mono flex items-center gap-1.5 shadow-xs transition-colors shrink-0"
        >
          <UserPlus className="w-4 h-4" />
          <span>+ Enroll New User</span>
        </button>
      </div>

      {/* Role Summary Badges */}
      <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div 
          onClick={() => setSelectedRole(selectedRole === 'student' ? 'all' : 'student')}
          className={`p-3 rounded-xl border cursor-pointer transition-all ${
            selectedRole === 'student' ? 'bg-blue-50 border-blue-300 ring-2 ring-blue-500' : 'bg-white border-slate-200 hover:border-slate-300'
          }`}
        >
          <div className="text-[10px] font-mono font-bold uppercase text-slate-400">Students</div>
          <div className="text-xl font-bold font-mono text-slate-900 my-0.5">{studentCount}</div>
          <div className="text-[10px] text-blue-600 font-semibold">Self-service portal access</div>
        </div>

        <div 
          onClick={() => setSelectedRole(selectedRole === 'staff' ? 'all' : 'staff')}
          className={`p-3 rounded-xl border cursor-pointer transition-all ${
            selectedRole === 'staff' ? 'bg-emerald-50 border-emerald-300 ring-2 ring-emerald-500' : 'bg-white border-slate-200 hover:border-slate-300'
          }`}
        >
          <div className="text-[10px] font-mono font-bold uppercase text-slate-400">Desk Staff</div>
          <div className="text-xl font-bold font-mono text-slate-900 my-0.5">{staffCount}</div>
          <div className="text-[10px] text-emerald-600 font-semibold">Library & Dining intake</div>
        </div>

        <div 
          onClick={() => setSelectedRole(selectedRole === 'officer' ? 'all' : 'officer')}
          className={`p-3 rounded-xl border cursor-pointer transition-all ${
            selectedRole === 'officer' ? 'bg-amber-50 border-amber-300 ring-2 ring-amber-500' : 'bg-white border-slate-200 hover:border-slate-300'
          }`}
        >
          <div className="text-[10px] font-mono font-bold uppercase text-slate-400">Security Officers</div>
          <div className="text-xl font-bold font-mono text-slate-900 my-0.5">{officerCount}</div>
          <div className="text-[10px] text-amber-600 font-semibold">Vault custody & release</div>
        </div>

        <div 
          onClick={() => setSelectedRole(selectedRole === 'admin' ? 'all' : 'admin')}
          className={`p-3 rounded-xl border cursor-pointer transition-all ${
            selectedRole === 'admin' ? 'bg-purple-50 border-purple-300 ring-2 ring-purple-500' : 'bg-white border-slate-200 hover:border-slate-300'
          }`}
        >
          <div className="text-[10px] font-mono font-bold uppercase text-slate-400">Administrators</div>
          <div className="text-xl font-bold font-mono text-slate-900 my-0.5">{adminCount}</div>
          <div className="text-[10px] text-purple-600 font-semibold">Full system authority</div>
        </div>
      </div>

      {/* Filter and Search Bar */}
      <div className="bg-white rounded-xl border border-slate-200 p-3 shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5">
        <div className="relative flex-1">
          <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
          <input
            type="text"
            placeholder="Search by user name, university ID (STU/STF/ADM), email, or department..."
            value={searchQuery}
            onChange={(e) => setSearchQuery(e.target.value)}
            className="w-full pl-9 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:border-indigo-500 focus:bg-white"
          />
        </div>

        <div className="flex items-center gap-2">
          <select
            value={selectedRole}
            onChange={(e) => setSelectedRole(e.target.value)}
            className="text-xs bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 font-mono text-slate-700 font-semibold focus:outline-none"
          >
            <option value="all">All Roles</option>
            <option value="student">Students</option>
            <option value="staff">Desk Staff</option>
            <option value="officer">Security Officers</option>
            <option value="admin">Administrators</option>
          </select>

          <select
            value={selectedStatus}
            onChange={(e) => setSelectedStatus(e.target.value)}
            className="text-xs bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 font-mono text-slate-700 font-semibold focus:outline-none"
          >
            <option value="all">All Statuses</option>
            <option value="active">Active</option>
            <option value="suspended">Suspended</option>
          </select>
        </div>
      </div>

      {/* Users Master Table */}
      <div className="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-left text-xs">
            <thead>
              <tr className="bg-slate-50 text-slate-500 font-mono text-[10px] uppercase border-b border-slate-200">
                <th className="py-2.5 px-3 font-bold">University ID</th>
                <th className="py-2.5 px-3 font-bold">User Name & Email</th>
                <th className="py-2.5 px-3 font-bold">Role</th>
                <th className="py-2.5 px-3 font-bold">Department / Major</th>
                <th className="py-2.5 px-3 font-bold">Status</th>
                <th className="py-2.5 px-3 font-bold">Last Active</th>
                <th className="py-2.5 px-3 font-bold text-right">Actions</th>
              </tr>
            </thead>
            <tbody className="divide-y divide-slate-100 font-sans">
              {filteredUsers.map(user => (
                <tr key={user.id} className="hover:bg-slate-50/80 transition-colors">
                  <td className="py-2.5 px-3 font-mono font-bold text-indigo-700 whitespace-nowrap">
                    {user.universityId}
                  </td>
                  <td className="py-2.5 px-3">
                    <div className="flex items-center gap-2">
                      <div className={`w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs text-white shrink-0 ${
                        user.role === 'admin' ? 'bg-purple-600' :
                        user.role === 'officer' ? 'bg-amber-600' :
                        user.role === 'staff' ? 'bg-emerald-600' : 'bg-indigo-600'
                      }`}>
                        {user.name.charAt(0)}
                      </div>
                      <div>
                        <div className="font-bold text-slate-900">{user.name}</div>
                        <div className="text-[11px] text-slate-500 font-mono">{user.email}</div>
                      </div>
                    </div>
                  </td>
                  <td className="py-2.5 px-3 whitespace-nowrap">
                    <span className={`text-[10px] font-mono font-bold px-2 py-0.5 rounded uppercase border ${
                      user.role === 'admin' ? 'bg-purple-50 text-purple-800 border-purple-200' :
                      user.role === 'officer' ? 'bg-amber-50 text-amber-800 border-amber-200' :
                      user.role === 'staff' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' :
                      'bg-blue-50 text-blue-800 border-blue-200'
                    }`}>
                      {user.role}
                    </span>
                  </td>
                  <td className="py-2.5 px-3 text-slate-600 font-mono text-[11px] max-w-xs truncate">
                    {user.department || 'N/A'}
                  </td>
                  <td className="py-2.5 px-3 whitespace-nowrap">
                    <span className={`text-[9px] font-mono font-bold px-1.5 py-0.2 rounded uppercase ${
                      user.status === 'suspended' ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800'
                    }`}>
                      {user.status || 'active'}
                    </span>
                  </td>
                  <td className="py-2.5 px-3 text-slate-400 font-mono text-[11px] whitespace-nowrap">
                    {user.lastActive || 'Today'}
                  </td>
                  <td className="py-2.5 px-3 text-right whitespace-nowrap">
                    <div className="flex items-center justify-end gap-1.5">
                      <select
                        value={user.role}
                        onChange={(e) => handleChangeRole(user, e.target.value as UserRole)}
                        className="text-[10px] font-mono bg-slate-100 border border-slate-200 rounded px-1.5 py-1 text-slate-700 font-semibold focus:outline-none"
                      >
                        <option value="student">Student</option>
                        <option value="staff">Staff</option>
                        <option value="officer">Officer</option>
                        <option value="admin">Admin</option>
                      </select>
                      <button
                        onClick={() => handleToggleStatus(user)}
                        className={`p-1 rounded text-[10px] font-bold font-mono transition-colors ${
                          user.status === 'suspended' ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 hover:bg-rose-100 hover:text-rose-700'
                        }`}
                        title={user.status === 'suspended' ? 'Reactivate User' : 'Suspend User'}
                      >
                        {user.status === 'suspended' ? 'Reactivate' : 'Suspend'}
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {/* Footer info */}
        <div className="p-2.5 bg-slate-50 border-t border-slate-200 text-slate-500 text-[11px] font-mono flex items-center justify-between">
          <span>Showing {filteredUsers.length} of {users.length} enrolled campus accounts</span>
          <span>Role Permissions Enforced &bull; SSO Connected</span>
        </div>
      </div>

      {/* Add New User Modal */}
      {showAddModal && (
        <div className="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center p-3">
          <div className="bg-white rounded-xl max-w-md w-full p-5 shadow-2xl border border-slate-200 space-y-4">
            <div className="flex items-center justify-between pb-2 border-b border-slate-200">
              <h3 className="text-sm font-bold text-slate-900 font-mono flex items-center gap-1.5">
                <UserPlus className="w-4 h-4 text-indigo-600" />
                <span>Enroll New Campus User / Staff</span>
              </h3>
              <button 
                onClick={() => setShowAddModal(false)}
                className="text-slate-400 hover:text-slate-700 text-lg leading-none"
              >
                &times;
              </button>
            </div>

            <form onSubmit={handleCreateUser} className="space-y-3 text-xs">
              <div>
                <label className="font-bold text-slate-700 block mb-1">Full Name *</label>
                <input
                  type="text"
                  required
                  placeholder="e.g. Jordan Lee"
                  value={newName}
                  onChange={(e) => setNewName(e.target.value)}
                  className="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                />
              </div>

              <div className="grid grid-cols-2 gap-2">
                <div>
                  <label className="font-bold text-slate-700 block mb-1">University ID *</label>
                  <input
                    type="text"
                    required
                    placeholder="e.g. STU-998811"
                    value={newUniversityId}
                    onChange={(e) => setNewUniversityId(e.target.value)}
                    className="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg font-mono focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label className="font-bold text-slate-700 block mb-1">Role Type *</label>
                  <select
                    value={newRole}
                    onChange={(e) => setNewRole(e.target.value as UserRole)}
                    className="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg font-mono font-bold focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  >
                    <option value="student">Student</option>
                    <option value="staff">Desk Staff</option>
                    <option value="officer">Security Officer</option>
                    <option value="admin">Administrator</option>
                  </select>
                </div>
              </div>

              <div>
                <label className="font-bold text-slate-700 block mb-1">Campus Email *</label>
                <input
                  type="email"
                  required
                  placeholder="jordan.lee@university.edu"
                  value={newEmail}
                  onChange={(e) => setNewEmail(e.target.value)}
                  className="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg font-mono focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                />
              </div>

              <div className="grid grid-cols-2 gap-2">
                <div>
                  <label className="font-bold text-slate-700 block mb-1">Department / Major</label>
                  <input
                    type="text"
                    placeholder="e.g. Mechanical Eng."
                    value={newDepartment}
                    onChange={(e) => setNewDepartment(e.target.value)}
                    className="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
                <div>
                  <label className="font-bold text-slate-700 block mb-1">Contact Phone</label>
                  <input
                    type="tel"
                    placeholder="+1 (555) 000-0000"
                    value={newPhone}
                    onChange={(e) => setNewPhone(e.target.value)}
                    className="w-full px-2.5 py-1.5 border border-slate-300 rounded-lg font-mono focus:ring-1 focus:ring-indigo-500 focus:outline-none"
                  />
                </div>
              </div>

              <div className="pt-2 flex items-center justify-end gap-2 border-t border-slate-200">
                <button
                  type="button"
                  onClick={() => setShowAddModal(false)}
                  className="px-3 py-1.5 text-slate-600 hover:bg-slate-100 rounded-lg font-semibold"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold font-mono shadow-xs"
                >
                  Create User Record
                </button>
              </div>
            </form>
          </div>
        </div>
      )}

    </div>
  );
};
