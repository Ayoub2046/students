<?php
/**
 * University Lost & Found Management System
 * System Constants
 */

// User Roles
define('ROLE_STUDENT', 'student');
define('ROLE_STAFF', 'staff');
define('ROLE_OFFICER', 'officer');
define('ROLE_ADMIN', 'admin');

// Item Types
define('ITEM_TYPE_LOST', 'lost');
define('ITEM_TYPE_FOUND', 'found');

// Item Statuses
define('STATUS_PENDING', 'pending');
define('STATUS_APPROVED', 'approved');
define('STATUS_REJECTED', 'rejected');
define('STATUS_AVAILABLE', 'available');
define('STATUS_CLAIMED', 'claimed');
define('STATUS_UNDER_VERIFICATION', 'under_verification');
define('STATUS_READY_FOR_HANDOVER', 'ready_for_handover');
define('STATUS_RETURNED', 'returned');
define('STATUS_UNCLAIMED', 'unclaimed');
define('STATUS_DISPOSED', 'disposed');
define('STATUS_CANCELLED', 'cancelled');

// Claim Statuses
define('CLAIM_PENDING', 'pending');
define('CLAIM_UNDER_REVIEW', 'under_review');
define('CLAIM_APPROVED', 'approved');
define('CLAIM_REJECTED', 'rejected');
define('CLAIM_CANCELLED', 'cancelled');
define('CLAIM_COMPLETED', 'completed');

// Upload Paths
define('UPLOAD_PATH_ITEMS', ROOT_PATH . 'uploads/items/');
define('UPLOAD_PATH_PROFILES', ROOT_PATH . 'uploads/profiles/');
define('UPLOAD_PATH_EVIDENCE', ROOT_PATH . 'uploads/evidence/');
define('UPLOAD_MAX_FILE_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);
define('ALLOWED_MIMES', ['image/jpeg', 'image/png', 'image/webp']);
