<?php
/**
 * Global Helper Functions
 */

// Escape HTML for XSS prevention
function e(?string $string): string {
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

// CSRF Token Helpers
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

function verifyCsrfToken(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Flash Message Helpers
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = [
        'type'    => $type, // success, danger, warning, info
        'message' => $message
    ];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function renderFlash(): string {
    $flash = getFlash();
    if (!$flash) return '';
    $type = e($flash['type']);
    $msg = e($flash['message']);
    return "
    <div class=\"alert alert-{$type} alert-dismissible fade show shadow-sm\" role=\"alert\">
        <i class=\"bi bi-info-circle-fill me-2\"></i> {$msg}
        <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
    </div>";
}

// Format Dates
function formatDate(?string $datetime, string $format = 'M d, Y'): string {
    if (empty($datetime)) return 'N/A';
    return date($format, strtotime($datetime));
}

function formatDateTime(?string $datetime): string {
    if (empty($datetime)) return 'N/A';
    return date('M d, Y - h:i A', strtotime($datetime));
}

function timeAgo(?string $datetime): string {
    if (empty($datetime)) return 'Just now';
    $time = strtotime($datetime);
    $diff = time() - $time;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return round($diff / 60) . ' mins ago';
    if ($diff < 86400) return round($diff / 3600) . ' hours ago';
    if ($diff < 604800) return round($diff / 86400) . ' days ago';
    return date('M d, Y', $time);
}

// Status Badges
function getStatusBadge(string $status): string {
    $map = [
        'pending'            => ['warning', 'bi-hourglass-split', 'Pending Review'],
        'approved'           => ['info', 'bi-check-circle', 'Approved'],
        'available'          => ['success', 'bi-box-seam', 'Available'],
        'claimed'            => ['primary', 'bi-person-check', 'Claim Submitted'],
        'under_verification' => ['warning', 'bi-shield-check', 'Under Verification'],
        'ready_for_handover' => ['success', 'bi-hand-thumbs-up', 'Ready for Handover'],
        'returned'           => ['secondary', 'bi-check2-all', 'Returned / Handed Over'],
        'unclaimed'          => ['dark', 'bi-clock-history', 'Unclaimed (90+ Days)'],
        'disposed'           => ['danger', 'bi-trash', 'Disposed / Donated'],
        'rejected'           => ['danger', 'bi-x-circle', 'Rejected'],
        'under_review'       => ['info', 'bi-search', 'Under Review'],
        'completed'          => ['success', 'bi-award', 'Completed']
    ];

    $info = $map[$status] ?? ['secondary', 'bi-tag', ucfirst($status)];
    return "<span class=\"badge bg-{$info[0]} d-inline-flex align-items-center gap-1 px-2.5 py-1.5\"><i class=\"bi {$info[1]}\"></i> {$info[2]}</span>";
}

function getTypeBadge(string $type): string {
    if ($type === 'lost') {
        return '<span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-search me-1"></i>LOST ITEM</span>';
    }
    return '<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-box2-heart me-1"></i>FOUND ITEM</span>';
}

// Upload Helper
function uploadFile(array $file, string $destinationDir): ?string {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if ($file['size'] > UPLOAD_MAX_FILE_SIZE) {
        return null;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return null;
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, ALLOWED_MIMES)) {
        return null;
    }

    if (!is_dir($destinationDir)) {
        mkdir($destinationDir, 0755, true);
    }

    $newName = 'img_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $targetPath = rtrim($destinationDir, '/') . '/' . $newName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $newName;
    }

    return null;
}
