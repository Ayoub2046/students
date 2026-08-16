<?php
/**
 * Context-Aware Dashboard Sidebar
 */
require_once __DIR__ . '/../classes/Auth.php';

$user = Auth::user();
$role = $user['role'] ?? 'student';
$script = basename($_SERVER['SCRIPT_NAME'] ?? '');
$currentDir = basename(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
?>
<div class="card border rounded-3 overflow-hidden mb-4 bg-white shadow-sm" style="border-color: #e2e8f0;">
    <!-- User Quick Info High Density Card -->
    <div class="p-3 text-start border-bottom" style="background-color: #f8fafc; border-color: #e2e8f0;">
        <div class="d-flex align-items-center gap-2.5">
            <div class="bg-indigo-600 text-white rounded d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 38px; height: 38px; background-color: #4f46e5; font-size: 1rem;">
                <?= strtoupper(substr($user['full_name'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="d-flex flex-column overflow-hidden">
                <span class="fw-bold text-dark text-truncate" style="font-size: 0.85rem;"><?= e($user['full_name'] ?? 'User') ?></span>
                <span class="text-muted font-monospace text-truncate" style="font-size: 0.7rem;"><?= e($user['email'] ?? '') ?></span>
                <div class="d-flex align-items-center gap-1.5 mt-1">
                    <span class="badge bg-primary-subtle text-primary border rounded text-uppercase" style="font-size: 0.6rem;"><?= e($role) ?></span>
                    <span class="badge bg-light text-secondary border rounded font-monospace" style="font-size: 0.6rem;"><?= e($user['university_id'] ?? 'N/A') ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Links -->
    <div class="list-group list-group-flush p-1.5">
        <?php if ($currentDir === 'admin' || ($role === 'admin' && $currentDir !== 'officer' && strpos($script, 'admin') !== false)): ?>
            <!-- Admin Navigation -->
            <div class="px-2.5 py-1 text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 0.05em;">Administration</div>
            <a href="<?= APP_URL ?>/admin/dashboard.php" class="list-group-item list-group-item-action <?= ($script == 'dashboard.php') ? 'active' : '' ?>">
                <i class="bi bi-speedometer2 me-2 text-primary"></i> Admin Dashboard
            </a>
            <a href="<?= APP_URL ?>/admin/items.php" class="list-group-item list-group-item-action <?= ($script == 'items.php') ? 'active' : '' ?>">
                <i class="bi bi-collection me-2 text-primary"></i> All Inventory Items
            </a>
            <a href="<?= APP_URL ?>/admin/claims.php" class="list-group-item list-group-item-action <?= ($script == 'claims.php') ? 'active' : '' ?>">
                <i class="bi bi-patch-check me-2 text-primary"></i> All Claims
            </a>
            <a href="<?= APP_URL ?>/admin/users.php" class="list-group-item list-group-item-action <?= ($script == 'users.php') ? 'active' : '' ?>">
                <i class="bi bi-people me-2 text-primary"></i> User Directory
            </a>
            <a href="<?= APP_URL ?>/admin/faculties.php" class="list-group-item list-group-item-action <?= ($script == 'faculties.php') ? 'active' : '' ?>">
                <i class="bi bi-bank me-2 text-primary"></i> Faculties
            </a>
            <a href="<?= APP_URL ?>/admin/departments.php" class="list-group-item list-group-item-action <?= ($script == 'departments.php') ? 'active' : '' ?>">
                <i class="bi bi-diagram-3 me-2 text-primary"></i> Departments
            </a>
            <a href="<?= APP_URL ?>/admin/categories.php" class="list-group-item list-group-item-action <?= ($script == 'categories.php') ? 'active' : '' ?>">
                <i class="bi bi-tags me-2 text-primary"></i> Categories Taxonomy
            </a>
            <a href="<?= APP_URL ?>/admin/locations.php" class="list-group-item list-group-item-action <?= ($script == 'locations.php') ? 'active' : '' ?>">
                <i class="bi bi-geo-alt me-2 text-primary"></i> Campus Locations
            </a>
            <a href="<?= APP_URL ?>/admin/analytics.php" class="list-group-item list-group-item-action <?= ($script == 'analytics.php') ? 'active' : '' ?>">
                <i class="bi bi-graph-up-arrow me-2 text-primary"></i> Analytics & Trends
            </a>
            <a href="<?= APP_URL ?>/admin/audit-logs.php" class="list-group-item list-group-item-action <?= ($script == 'audit-logs.php') ? 'active' : '' ?>">
                <i class="bi bi-journal-text me-2 text-primary"></i> Audit Logs
            </a>
            <a href="<?= APP_URL ?>/admin/settings.php" class="list-group-item list-group-item-action <?= ($script == 'settings.php') ? 'active' : '' ?>">
                <i class="bi bi-gear me-2 text-primary"></i> System Settings
            </a>

        <?php elseif ($currentDir === 'officer' || ($role === 'officer')): ?>
            <!-- Officer Navigation -->
            <div class="px-2.5 py-1 text-uppercase text-muted fw-bold" style="font-size: 0.65rem; letter-spacing: 0.05em;">Officer Operations</div>
            <a href="<?= APP_URL ?>/officer/dashboard.php" class="list-group-item list-group-item-action <?= ($script == 'dashboard.php') ? 'active' : '' ?>">
                <i class="bi bi-shield-check me-2 text-info"></i> Officer Dashboard
            </a>
            <a href="<?= APP_URL ?>/officer/reports.php" class="list-group-item list-group-item-action <?= ($script == 'reports.php' || $script == 'report-view.php') ? 'active' : '' ?>">
                <i class="bi bi-inbox me-2 text-info"></i> Pending Review
            </a>
            <a href="<?= APP_URL ?>/officer/claims.php" class="list-group-item list-group-item-action <?= ($script == 'claims.php' || $script == 'claim-view.php') ? 'active' : '' ?>">
                <i class="bi bi-clipboard2-check me-2 text-info"></i> Claims Queue
            </a>
            <a href="<?= APP_URL ?>/officer/storage.php" class="list-group-item list-group-item-action <?= ($script == 'storage.php') ? 'active' : '' ?>">
                <i class="bi bi-archive me-2 text-info"></i> Physical Storage
            </a>
            <a href="<?= APP_URL ?>/officer/handover.php" class="list-group-item list-group-item-action <?= ($script == 'handover.php') ? 'active' : '' ?>">
                <i class="bi bi-hand-thumbs-up me-2 text-info"></i> Return & Handover
            </a>
            <a href="<?= APP_URL ?>/officer/unclaimed.php" class="list-group-item list-group-item-action <?= ($script == 'unclaimed.php') ? 'active' : '' ?>">
                <i class="bi bi-clock-history me-2 text-info"></i> Unclaimed (90+ Days)
            </a>
        <?php endif; ?>

        <!-- General User Portal Section -->
        <div class="px-2.5 py-1 text-uppercase text-muted fw-bold mt-1.5" style="font-size: 0.65rem; letter-spacing: 0.05em;">My Portal</div>
        <a href="<?= APP_URL ?>/dashboard.php" class="list-group-item list-group-item-action <?= ($script == 'dashboard.php' && $currentDir !== 'admin' && $currentDir !== 'officer') ? 'active' : '' ?>">
            <i class="bi bi-columns-gap me-2 text-primary"></i> Overview
        </a>
        <a href="<?= APP_URL ?>/report-lost.php" class="list-group-item list-group-item-action <?= ($script == 'report-lost.php') ? 'active' : '' ?>">
            <i class="bi bi-exclamation-octagon me-2 text-danger"></i> Report Lost Item
        </a>
        <a href="<?= APP_URL ?>/report-found.php" class="list-group-item list-group-item-action <?= ($script == 'report-found.php') ? 'active' : '' ?>">
            <i class="bi bi-plus-circle me-2 text-success"></i> Report Found Item
        </a>
        <a href="<?= APP_URL ?>/my-reports.php" class="list-group-item list-group-item-action <?= ($script == 'my-reports.php') ? 'active' : '' ?>">
            <i class="bi bi-folder2-open me-2 text-primary"></i> My Reported Items
        </a>
        <a href="<?= APP_URL ?>/my-claims.php" class="list-group-item list-group-item-action <?= ($script == 'my-claims.php') ? 'active' : '' ?>">
            <i class="bi bi-card-checklist me-2 text-primary"></i> My Submitted Claims
        </a>
        <a href="<?= APP_URL ?>/messages.php" class="list-group-item list-group-item-action <?= ($script == 'messages.php') ? 'active' : '' ?>">
            <i class="bi bi-chat-dots me-2 text-primary"></i> Messages & Inquiries
        </a>
        <a href="<?= APP_URL ?>/notifications.php" class="list-group-item list-group-item-action <?= ($script == 'notifications.php') ? 'active' : '' ?>">
            <i class="bi bi-bell me-2 text-primary"></i> Notifications
        </a>
        <a href="<?= APP_URL ?>/profile.php" class="list-group-item list-group-item-action <?= ($script == 'profile.php') ? 'active' : '' ?>">
            <i class="bi bi-person-gear me-2 text-primary"></i> Account Settings
        </a>
    </div>

    <!-- Quick Logout Action -->
    <div class="card-footer bg-light p-2 border-top">
        <a href="<?= APP_URL ?>/logout.php" class="btn btn-outline-danger btn-sm w-100 py-1">
            <i class="bi bi-box-arrow-right me-1"></i> Sign Out
        </a>
    </div>
</div>
