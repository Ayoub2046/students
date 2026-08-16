export type ItemType = 'lost' | 'found';

export type ItemStatus = 
  | 'available' 
  | 'pending' 
  | 'under_verification' 
  | 'ready_for_handover' 
  | 'returned' 
  | 'unclaimed' 
  | 'disposed';

export type UserRole = 'student' | 'staff' | 'officer' | 'admin';

export interface User {
  id: string;
  name: string;
  email: string;
  universityId: string;
  role: UserRole;
  department?: string;
  phone?: string;
  avatar?: string;
  status?: 'active' | 'suspended';
  joinedDate?: string;
  lastActive?: string;
}

export interface Item {
  id: string;
  referenceNo: string;
  title: string;
  type: ItemType;
  category: string;
  description: string;
  distinctiveFeatures: string;
  locationId: string;
  locationName: string;
  building: string;
  dateReported: string;
  dateEvent: string;
  status: ItemStatus;
  imageUrl: string;
  reportedBy: {
    id: string;
    name: string;
    email: string;
    universityId: string;
    phone?: string;
  };
  storageLocation?: {
    rack: string;
    bin: string;
    lockerId: string;
  };
  secretVerificationHint?: string;
  serialNumberMasked?: string;
  rewardOffered?: string;
  claimsCount: number;
  daysHeld: number;
}

export interface Claim {
  id: string;
  itemId: string;
  itemTitle: string;
  itemReferenceNo: string;
  claimedBy: {
    id: string;
    name: string;
    email: string;
    universityId: string;
    phone: string;
  };
  submittedDate: string;
  status: 'pending' | 'under_verification' | 'approved' | 'rejected' | 'handed_over';
  proofDetails: string;
  serialNumberProvided?: string;
  proofDocumentUrl?: string;
  officerNotes?: string;
  verifiedByOfficer?: string;
  handoverDate?: string;
  signatureDataUrl?: string;
}

export interface CampusLocation {
  id: string;
  name: string;
  building: string;
  campus: string;
  lat: number;
  lng: number;
  xPercent: number; // For interactive visual map coordinate
  yPercent: number;
  activeItemsCount: number;
  description: string;
}

export interface StorageLocker {
  id: string;
  rack: string;
  bin: string;
  category: string;
  capacity: number;
  currentCount: number;
  isFull: boolean;
  status: 'available' | 'near_capacity' | 'full';
}

export interface AuditLog {
  id: string;
  timestamp: string;
  actor: string;
  actorRole: UserRole;
  action: string;
  target: string;
  details: string;
  ipAddress: string;
}

export interface NotificationItem {
  id: string;
  title: string;
  message: string;
  timestamp: string;
  read: boolean;
  type: 'match' | 'claim' | 'handover' | 'system';
  linkId?: string;
}

export interface MessageThread {
  id: string;
  itemId: string;
  itemTitle: string;
  referenceNo: string;
  recipientName: string;
  recipientRole: string;
  unreadCount: number;
  messages: {
    id: string;
    senderId: string;
    senderName: string;
    isOfficer: boolean;
    text: string;
    timestamp: string;
  }[];
}
