import { Item, Claim, CampusLocation, StorageLocker, AuditLog, NotificationItem, MessageThread, User } from '../types';

export const CURRENT_USER: User = {
  id: 'usr_101',
  name: 'Alex Rivera',
  email: 'alex.rivera@university.edu',
  universityId: 'STU-994821',
  role: 'student',
  department: 'Computer Science & AI',
  phone: '+1 (555) 382-9011',
  avatar: 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80'
};

export const STAFF_USERS: Record<string, User> = {
  student: {
    id: 'usr_101',
    name: 'Alex Rivera',
    email: 'alex.rivera@university.edu',
    universityId: 'STU-994821',
    role: 'student',
    department: 'Computer Science',
    phone: '+1 (555) 382-9011'
  },
  staff: {
    id: 'usr_202',
    name: 'Prof. Marcus Vance',
    email: 'm.vance@university.edu',
    universityId: 'FAC-441209',
    role: 'staff',
    department: 'Faculty of Engineering',
    phone: '+1 (555) 912-3344'
  },
  officer: {
    id: 'usr_303',
    name: 'Officer David Sterling',
    email: 'd.sterling@campuspolice.university.edu',
    universityId: 'OFF-771904',
    role: 'officer',
    department: 'Campus Safety & Lost Desk',
    phone: '+1 (555) 880-1290'
  },
  admin: {
    id: 'usr_404',
    name: 'Dr. Elena Rostova',
    email: 'e.rostova@university.edu',
    universityId: 'ADM-100233',
    role: 'admin',
    department: 'Student Affairs & Campus Services',
    phone: '+1 (555) 443-8899'
  }
};

export const CAMPUS_LOCATIONS: CampusLocation[] = [
  {
    id: 'loc_1',
    name: 'Main University Library (Franklin Hall)',
    building: 'Franklin Memorial Library',
    campus: 'Central Quad',
    lat: 37.7749,
    lng: -122.4194,
    xPercent: 48,
    yPercent: 36,
    activeItemsCount: 14,
    description: 'Ground floor study desks, 2nd floor silent reading room, and 3rd floor computer lab.'
  },
  {
    id: 'loc_2',
    name: 'Student Center & Food Commons',
    building: 'Student Life Pavilion',
    campus: 'South Campus',
    lat: 37.7732,
    lng: -122.4178,
    xPercent: 28,
    yPercent: 58,
    activeItemsCount: 9,
    description: 'Dining hall tables, coffee bar lounge, lost & found intake desk Room 104.'
  },
  {
    id: 'loc_3',
    name: 'Science & Engineering Complex',
    building: 'Turing-Curie Science Hall',
    campus: 'North Campus',
    lat: 37.7765,
    lng: -122.4210,
    xPercent: 72,
    yPercent: 24,
    activeItemsCount: 11,
    description: 'Lecture Hall 101, Robotics Lab 3B, and physics breakout corridors.'
  },
  {
    id: 'loc_4',
    name: 'Athletics & Recreation Center',
    building: 'Pioneer Fieldhouse',
    campus: 'West Campus',
    lat: 37.7720,
    lng: -122.4235,
    xPercent: 18,
    yPercent: 78,
    activeItemsCount: 7,
    description: 'Gym locker rooms, basketball bleachers, and indoor swimming deck.'
  },
  {
    id: 'loc_5',
    name: 'Humanities & Fine Arts Center',
    building: 'Shakespeare Hall',
    campus: 'East Campus',
    lat: 37.7758,
    lng: -122.4150,
    xPercent: 82,
    yPercent: 62,
    activeItemsCount: 5,
    description: 'Main Auditorium, music practice studios, and art studio gallery.'
  },
  {
    id: 'loc_6',
    name: 'Campus Shuttle Station & Transit Hub',
    building: 'Transit Pavilion',
    campus: 'South-East Gate',
    lat: 37.7710,
    lng: -122.4160,
    xPercent: 60,
    yPercent: 85,
    activeItemsCount: 6,
    description: 'Bus bay 3 bench area, bicycle racks, and rideshare pickup zone.'
  }
];

export const STORAGE_LOCKERS: StorageLocker[] = [
  { id: 'lock_A1', rack: 'Rack A (Electronics)', bin: 'Bin A-01 (High Value)', category: 'Electronics', capacity: 15, currentCount: 12, isFull: false, status: 'near_capacity' },
  { id: 'lock_A2', rack: 'Rack A (Electronics)', bin: 'Bin A-02 (Accessories)', category: 'Electronics', capacity: 25, currentCount: 18, isFull: false, status: 'available' },
  { id: 'lock_B1', rack: 'Rack B (Wallets & Cards)', bin: 'Bin B-01 (Secure Safe)', category: 'Wallets & Cards', capacity: 30, currentCount: 14, isFull: false, status: 'available' },
  { id: 'lock_B2', rack: 'Rack B (Keys & Access)', bin: 'Bin B-02 (Key Cabinet)', category: 'Keys & IDs', capacity: 40, currentCount: 22, isFull: false, status: 'available' },
  { id: 'lock_C1', rack: 'Rack C (Bags & Clothing)', bin: 'Bin C-01 (Backpacks)', category: 'Bags & Luggage', capacity: 12, currentCount: 11, isFull: false, status: 'near_capacity' },
  { id: 'lock_C2', rack: 'Rack C (Bags & Clothing)', bin: 'Bin C-02 (Jackets/Hats)', category: 'Clothing', capacity: 20, currentCount: 8, isFull: false, status: 'available' },
  { id: 'lock_D1', rack: 'Rack D (Books & Academic)', bin: 'Bin D-01 (Textbooks)', category: 'Books & Notes', capacity: 30, currentCount: 15, isFull: false, status: 'available' },
  { id: 'lock_V1', rack: 'Vault 1 (High Value Jewelry)', bin: 'Safe Box 04', category: 'Jewelry & Watches', capacity: 10, currentCount: 9, isFull: false, status: 'near_capacity' },
];

export const INITIAL_ITEMS: Item[] = [
  {
    id: 'itm_001',
    referenceNo: 'LF-2026-8812',
    title: 'Apple MacBook Pro 14" Space Gray',
    type: 'found',
    category: 'Electronics',
    description: 'Found on 2nd floor library study carrel 24. Space Gray, sticker on palm rest reads "Rust > C++". In a black felt sleeve.',
    distinctiveFeatures: 'Has a small scratch near the MagSafe port and custom terminal wallpaper on lockscreen.',
    locationId: 'loc_1',
    locationName: 'Main University Library (Franklin Hall)',
    building: 'Franklin Memorial Library',
    dateReported: '2026-08-14',
    dateEvent: '2026-08-14',
    status: 'ready_for_handover',
    imageUrl: 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=500&auto=format&fit=crop&q=80',
    reportedBy: {
      id: 'usr_505',
      name: 'Sarah Jenkins (Library Desk)',
      email: 's.jenkins@library.university.edu',
      universityId: 'STF-300192',
      phone: '+1 (555) 771-0021'
    },
    storageLocation: {
      rack: 'Rack A (Electronics)',
      bin: 'Bin A-01 (High Value)',
      lockerId: 'lock_A1'
    },
    secretVerificationHint: 'Verify exact sticker slogan and screen password attempt / serial number.',
    serialNumberMasked: 'C02G****MD6T',
    claimsCount: 1,
    daysHeld: 2
  },
  {
    id: 'itm_002',
    referenceNo: 'LF-2026-8819',
    title: 'Leather Bi-Fold Wallet with Student ID',
    type: 'found',
    category: 'Wallets & Cards',
    description: 'Brown leather Timberland wallet found on dining bench in Student Center near the juice bar.',
    distinctiveFeatures: 'Contains student bus transit pass and keycard with initial "M.K."',
    locationId: 'loc_2',
    locationName: 'Student Center & Food Commons',
    building: 'Student Life Pavilion',
    dateReported: '2026-08-15',
    dateEvent: '2026-08-15',
    status: 'under_verification',
    imageUrl: 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=500&auto=format&fit=crop&q=80',
    reportedBy: {
      id: 'usr_606',
      name: 'Carlos Mendez (Cafeteria Staff)',
      email: 'c.mendez@dining.university.edu',
      universityId: 'STF-440192'
    },
    storageLocation: {
      rack: 'Rack B (Wallets & Cards)',
      bin: 'Bin B-01 (Secure Safe)',
      lockerId: 'lock_B1'
    },
    secretVerificationHint: 'State the card provider and exact transit pass ID number.',
    serialNumberMasked: 'ID-992****',
    claimsCount: 2,
    daysHeld: 1
  },
  {
    id: 'itm_003',
    referenceNo: 'LF-2026-8790',
    title: 'Sony WH-1000XM5 Noise Canceling Headphones',
    type: 'lost',
    category: 'Electronics',
    description: 'Left in Science Lecture Hall 101 after CS301 Operating Systems lecture. Midnight blue color with silver hinges.',
    distinctiveFeatures: 'Carrying case has an orange carabiner attached. Left ear cushion has slight wear.',
    locationId: 'loc_3',
    locationName: 'Science & Engineering Complex',
    building: 'Turing-Curie Science Hall',
    dateReported: '2026-08-12',
    dateEvent: '2026-08-12',
    status: 'pending',
    imageUrl: 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500&auto=format&fit=crop&q=80',
    reportedBy: {
      id: 'usr_101',
      name: 'Alex Rivera',
      email: 'alex.rivera@university.edu',
      universityId: 'STU-994821',
      phone: '+1 (555) 382-9011'
    },
    rewardOffered: '$40 Starbucks Gift Card',
    claimsCount: 0,
    daysHeld: 4
  },
  {
    id: 'itm_004',
    referenceNo: 'LF-2026-8765',
    title: 'Set of 4 Brass Dorm Keys with Red Lanyard',
    type: 'found',
    category: 'Keys & IDs',
    description: 'Found near the bicycle stands at Pioneer Fieldhouse gym entrance.',
    distinctiveFeatures: 'Has a red Metropolitan University lanyard and a miniature Yoda rubber keychain.',
    locationId: 'loc_4',
    locationName: 'Athletics & Recreation Center',
    building: 'Pioneer Fieldhouse',
    dateReported: '2026-08-10',
    dateEvent: '2026-08-10',
    status: 'available',
    imageUrl: 'https://images.unsplash.com/photo-1582139329536-e7284fece509?w=500&auto=format&fit=crop&q=80',
    reportedBy: {
      id: 'usr_707',
      name: 'Gym Desk Attendant',
      email: 'rec.desk@university.edu',
      universityId: 'STF-881290'
    },
    storageLocation: {
      rack: 'Rack B (Keys & Access)',
      bin: 'Bin B-02 (Key Cabinet)',
      lockerId: 'lock_B2'
    },
    secretVerificationHint: 'Identify the engraved number on the dorm room key.',
    claimsCount: 0,
    daysHeld: 6
  },
  {
    id: 'itm_005',
    referenceNo: 'LF-2026-8740',
    title: 'Hydro Flask 32oz Wide Mouth Water Bottle',
    type: 'found',
    category: 'Personal Belongings',
    description: 'Pacific Blue metal bottle left in Shakespeare Hall room 204.',
    distinctiveFeatures: 'Covered with NASA, GitHub, and National Parks stickers.',
    locationId: 'loc_5',
    locationName: 'Humanities & Fine Arts Center',
    building: 'Shakespeare Hall',
    dateReported: '2026-08-08',
    dateEvent: '2026-08-08',
    status: 'available',
    imageUrl: 'https://images.unsplash.com/photo-1602143407151-7111542de6e8?w=500&auto=format&fit=crop&q=80',
    reportedBy: {
      id: 'usr_808',
      name: 'Janitorial Staff (East Wing)',
      email: 'facilities@university.edu',
      universityId: 'STF-550123'
    },
    storageLocation: {
      rack: 'Rack C (Bags & Clothing)',
      bin: 'Bin C-02 (Jackets/Hats)',
      lockerId: 'lock_C2'
    },
    claimsCount: 0,
    daysHeld: 8
  },
  {
    id: 'itm_006',
    referenceNo: 'LF-2026-8610',
    title: 'The North Face Resolve Waterproof Jacket (L)',
    type: 'found',
    category: 'Clothing',
    description: 'Black rain jacket found draped over a bench at the Campus Shuttle Station Bay 3.',
    distinctiveFeatures: 'Size Large, faint white paint mark on left sleeve cuff.',
    locationId: 'loc_6',
    locationName: 'Campus Shuttle Station & Transit Hub',
    building: 'Transit Pavilion',
    dateReported: '2026-08-01',
    dateEvent: '2026-08-01',
    status: 'unclaimed',
    imageUrl: 'https://images.unsplash.com/photo-1548883354-7622d03aca27?w=500&auto=format&fit=crop&q=80',
    reportedBy: {
      id: 'usr_909',
      name: 'Shuttle Driver Davis',
      email: 'transit@university.edu',
      universityId: 'STF-110094'
    },
    storageLocation: {
      rack: 'Rack C (Bags & Clothing)',
      bin: 'Bin C-02 (Jackets/Hats)',
      lockerId: 'lock_C2'
    },
    claimsCount: 0,
    daysHeld: 92 // Unclaimed 90+ days
  },
  {
    id: 'itm_007',
    referenceNo: 'LF-2026-8550',
    title: 'TI-84 Plus CE Graphing Calculator (Rose Gold)',
    type: 'found',
    category: 'Electronics',
    description: 'Found in Franklin Library 3rd floor math study section.',
    distinctiveFeatures: 'Name etched inside sliding cover: "H. Zhang", purple silicone bumper case.',
    locationId: 'loc_1',
    locationName: 'Main University Library (Franklin Hall)',
    building: 'Franklin Memorial Library',
    dateReported: '2026-07-28',
    dateEvent: '2026-07-28',
    status: 'returned',
    imageUrl: 'https://images.unsplash.com/photo-1594980596870-8aa52a78d8cd?w=500&auto=format&fit=crop&q=80',
    reportedBy: {
      id: 'usr_505',
      name: 'Sarah Jenkins (Library Desk)',
      email: 's.jenkins@library.university.edu',
      universityId: 'STF-300192'
    },
    claimsCount: 1,
    daysHeld: 19
  },
  {
    id: 'itm_008',
    referenceNo: 'LF-2026-8830',
    title: 'Ray-Ban Wayfarer Polarized Sunglasses in Case',
    type: 'lost',
    category: 'Personal Belongings',
    description: 'Lost somewhere around the Central Quad lawn during Friday outdoor campus market festival.',
    distinctiveFeatures: 'Matte black frame with tortoiseshell temples, brown leather Ray-Ban snap case.',
    locationId: 'loc_1',
    locationName: 'Main University Library (Franklin Hall)',
    building: 'Central Quad Lawn',
    dateReported: '2026-08-15',
    dateEvent: '2026-08-15',
    status: 'pending',
    imageUrl: 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=500&auto=format&fit=crop&q=80',
    reportedBy: {
      id: 'usr_202',
      name: 'Prof. Marcus Vance',
      email: 'm.vance@university.edu',
      universityId: 'FAC-441209',
      phone: '+1 (555) 912-3344'
    },
    rewardOffered: '$25 Cash / Amazon Card',
    claimsCount: 0,
    daysHeld: 1
  }
];

export const INITIAL_CLAIMS: Claim[] = [
  {
    id: 'clm_101',
    itemId: 'itm_001',
    itemTitle: 'Apple MacBook Pro 14" Space Gray',
    itemReferenceNo: 'LF-2026-8812',
    claimedBy: {
      id: 'usr_101',
      name: 'Alex Rivera',
      email: 'alex.rivera@university.edu',
      universityId: 'STU-994821',
      phone: '+1 (555) 382-9011'
    },
    submittedDate: '2026-08-14 16:30',
    status: 'approved',
    proofDetails: 'I have the original Apple Store electronic invoice and purchase receipt with serial number ending in MD6T. My terminal user profile name is "arivera".',
    serialNumberProvided: 'C02G9941MD6T',
    officerNotes: 'Serial number verified against Apple device system record. Owner identity confirmed with university photo badge.',
    verifiedByOfficer: 'Officer David Sterling (OFF-771904)',
    handoverDate: '2026-08-16 10:00 AM'
  },
  {
    id: 'clm_102',
    itemId: 'itm_002',
    itemTitle: 'Leather Bi-Fold Wallet with Student ID',
    itemReferenceNo: 'LF-2026-8819',
    claimedBy: {
      id: 'usr_333',
      name: 'Maya Kowalski',
      email: 'm.kowalski@university.edu',
      universityId: 'STU-992144',
      phone: '+1 (555) 234-9988'
    },
    submittedDate: '2026-08-15 18:45',
    status: 'under_verification',
    proofDetails: 'My student card ID is STU-992144 inside the plastic window. Inside there is $35 cash and a library card.',
    serialNumberProvided: 'ID-992144',
    officerNotes: 'Claim details align with wallet contents. Scheduled for physical verification at Student Center Desk Room 104.'
  }
];

export const INITIAL_AUDIT_LOGS: AuditLog[] = [
  {
    id: 'log_901',
    timestamp: '2026-08-16 08:45:12',
    actor: 'Officer David Sterling',
    actorRole: 'officer',
    action: 'CLAIM_VERIFIED',
    target: 'LF-2026-8812 (MacBook Pro 14")',
    details: 'Verified proof of ownership with electronic invoice match for claim #clm_101.',
    ipAddress: '10.24.110.88'
  },
  {
    id: 'log_902',
    timestamp: '2026-08-15 17:20:04',
    actor: 'Carlos Mendez (Cafeteria)',
    actorRole: 'staff',
    action: 'ITEM_INTAKE',
    target: 'LF-2026-8819 (Leather Wallet)',
    details: 'Logged found wallet from Student Center; placed in Rack B, Bin B-01.',
    ipAddress: '10.24.115.12'
  },
  {
    id: 'log_903',
    timestamp: '2026-08-14 14:10:55',
    actor: 'Sarah Jenkins',
    actorRole: 'staff',
    action: 'ITEM_INTAKE',
    target: 'LF-2026-8812 (MacBook Pro)',
    details: 'Registered found laptop from Franklin Library 2nd floor desk.',
    ipAddress: '10.24.102.40'
  },
  {
    id: 'log_904',
    timestamp: '2026-08-12 11:32:00',
    actor: 'Dr. Elena Rostova',
    actorRole: 'admin',
    action: 'SYSTEM_SETTINGS_UPDATE',
    target: 'Retention Policy',
    details: 'Updated unclaimed policy interval to 90 days with automatic alert flag.',
    ipAddress: '10.24.100.5'
  }
];

export const INITIAL_NOTIFICATIONS: NotificationItem[] = [
  {
    id: 'notif_1',
    title: 'Claim Approved! Ready for Handover',
    message: 'Your claim for MacBook Pro 14" (LF-2026-8812) has been approved. Visit Student Center Room 104 with photo ID.',
    timestamp: '15 mins ago',
    read: false,
    type: 'handover',
    linkId: 'LF-2026-8812'
  },
  {
    id: 'notif_2',
    title: 'Potential Match Found',
    message: 'A found item "Sony Noise Canceling Headphones" matches your reported lost item description.',
    timestamp: '2 hours ago',
    read: false,
    type: 'match',
    linkId: 'LF-2026-8790'
  },
  {
    id: 'notif_3',
    title: 'Storage Retention Notice',
    message: '1 item (LF-2026-8610) has exceeded the 90-day retention threshold and is marked for disposition.',
    timestamp: '1 day ago',
    read: true,
    type: 'system',
    linkId: 'LF-2026-8610'
  }
];

export const INITIAL_MESSAGES: MessageThread[] = [
  {
    id: 'thrd_01',
    itemId: 'itm_001',
    itemTitle: 'Apple MacBook Pro 14" Space Gray',
    referenceNo: 'LF-2026-8812',
    recipientName: 'Officer David Sterling',
    recipientRole: 'Lost & Found Officer',
    unreadCount: 1,
    messages: [
      {
        id: 'msg_1',
        senderId: 'usr_101',
        senderName: 'Alex Rivera',
        isOfficer: false,
        text: 'Hello Officer Sterling, I submitted proof with my Apple invoice. Can I come pick it up today at 11am?',
        timestamp: '09:12 AM'
      },
      {
        id: 'msg_2',
        senderId: 'usr_303',
        senderName: 'Officer David Sterling',
        isOfficer: true,
        text: 'Yes Alex, your invoice and serial number match our physical check. Please bring your university student ID for the handover digital signature.',
        timestamp: '09:25 AM'
      }
    ]
  }
];

export const ALL_USERS: User[] = [
  {
    id: 'usr_101',
    name: 'Alex Rivera',
    email: 'alex.rivera@university.edu',
    universityId: 'STU-994821',
    role: 'student',
    department: 'Computer Science & AI',
    phone: '+1 (555) 382-9011',
    status: 'active',
    joinedDate: '2024-09-01',
    lastActive: 'Just now'
  },
  {
    id: 'usr_102',
    name: 'Maya Kowalski',
    email: 'm.kowalski@university.edu',
    universityId: 'STU-992144',
    role: 'student',
    department: 'Biomedical Engineering',
    phone: '+1 (555) 234-9988',
    status: 'active',
    joinedDate: '2023-09-01',
    lastActive: '15 mins ago'
  },
  {
    id: 'usr_103',
    name: 'Liam Chen',
    email: 'l.chen@university.edu',
    universityId: 'STU-883012',
    role: 'student',
    department: 'Business & Finance',
    phone: '+1 (555) 441-2098',
    status: 'active',
    joinedDate: '2025-01-15',
    lastActive: '1 hour ago'
  },
  {
    id: 'usr_104',
    name: 'Chloe Bennett',
    email: 'c.bennett@university.edu',
    universityId: 'STU-910283',
    role: 'student',
    department: 'Architecture & Design',
    phone: '+1 (555) 672-8819',
    status: 'active',
    joinedDate: '2024-09-01',
    lastActive: '3 hours ago'
  },
  {
    id: 'usr_202',
    name: 'Sarah Jenkins',
    email: 's.jenkins@library.university.edu',
    universityId: 'STF-300192',
    role: 'staff',
    department: 'Central Library Circulation Desk',
    phone: '+1 (555) 771-0021',
    status: 'active',
    joinedDate: '2021-03-15',
    lastActive: 'Online now'
  },
  {
    id: 'usr_203',
    name: 'Carlos Mendez',
    email: 'c.mendez@dining.university.edu',
    universityId: 'STF-440192',
    role: 'staff',
    department: 'Student Center Dining Pavilion',
    phone: '+1 (555) 321-4400',
    status: 'active',
    joinedDate: '2022-08-10',
    lastActive: '20 mins ago'
  },
  {
    id: 'usr_204',
    name: 'Rachel Adams',
    email: 'r.adams@gym.university.edu',
    universityId: 'STF-881290',
    role: 'staff',
    department: 'Athletics & Recreation Fieldhouse',
    phone: '+1 (555) 880-9922',
    status: 'active',
    joinedDate: '2023-01-20',
    lastActive: '2 hours ago'
  },
  {
    id: 'usr_303',
    name: 'Officer David Sterling',
    email: 'd.sterling@campuspolice.university.edu',
    universityId: 'OFF-771904',
    role: 'officer',
    department: 'Campus Safety & Evidence Vault',
    phone: '+1 (555) 880-1290',
    status: 'active',
    joinedDate: '2019-11-04',
    lastActive: 'Online now'
  },
  {
    id: 'usr_304',
    name: 'Sgt. Robert Martinez',
    email: 'r.martinez@campuspolice.university.edu',
    universityId: 'OFF-662810',
    role: 'officer',
    department: 'Campus Security Patrol & Dispatch',
    phone: '+1 (555) 880-1295',
    status: 'active',
    joinedDate: '2018-05-12',
    lastActive: '35 mins ago'
  },
  {
    id: 'usr_404',
    name: 'Dr. Elena Rostova',
    email: 'e.rostova@university.edu',
    universityId: 'ADM-100233',
    role: 'admin',
    department: 'Dean of Student Affairs & Services',
    phone: '+1 (555) 443-8899',
    status: 'active',
    joinedDate: '2016-07-01',
    lastActive: 'Online now'
  },
  {
    id: 'usr_405',
    name: 'Arthur Smith',
    email: 'a.smith@admin.university.edu',
    universityId: 'ADM-100234',
    role: 'admin',
    department: 'Central Administration & Campus IT',
    phone: '+1 (555) 443-8811',
    status: 'active',
    joinedDate: '2017-02-14',
    lastActive: 'Online now'
  }
];

export const CATEGORIES = [
  'All Categories',
  'Electronics',
  'Wallets & Cards',
  'Keys & IDs',
  'Bags & Luggage',
  'Clothing',
  'Books & Notes',
  'Personal Belongings',
  'Jewelry & Watches'
];
