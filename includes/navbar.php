<?php
/**
 * Global Navigation Bar
 */
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Notification.php';

$isAuth = Auth::check();
$user = Auth::user();
$unreadCount = 0;
if ($isAuth && $user) {
    $notifService = new Notification();
    $unreadCount = $notifService->getUnreadCount((int)$user['id']);
}
$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
?>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top shadow-sm py-1.5" style="background-color: #312e81; border-bottom: 1px solid #3730a3;">
    <div class="container-fluid px-lg-4">
        <!-- Brand Logo -->
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-white py-0" href="<?= APP_URL ?>/index.php">
            <span class="bg-indigo-600 text-white rounded p-1 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px; background-color: #4f46e5;">
                <i class="bi bi-compass-fill fs-6"></i>
            </span>
            <div class="d-flex flex-column leading-tight">
                <span class="fs-6 fw-bold tracking-tight text-white"><?= e(APP_NAME) ?></span>
                <small class="text-indigo-200 fw-normal" style="font-size: 0.65rem; color: #c7d2fe;"><?= e(APP_SLOGAN) ?></small>
            </div>
        </a>

        <!-- Mobile Toggler -->
        <button class="navbar-toggler border-0 p-1 text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Links -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 gap-1">
                <li class="nav-item">
                    <a class="nav-link <?= ($currentScript == 'index.php') ? 'active' : '' ?>" href="<?= APP_URL ?>/index.php">
                        <i class="bi bi-house-door me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentScript == 'items.php') ? 'active' : '' ?>" href="<?= APP_URL ?>/items.php">
                        <i class="bi bi-grid me-1"></i> Inventory
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-warning-emphasis <?= ($currentScript == 'report-lost.php') ? 'active' : '' ?>" href="<?= APP_URL ?>/report-lost.php">
                        <i class="bi bi-exclamation-octagon me-1"></i> Report Lost
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-info-emphasis <?= ($currentScript == 'report-found.php') ? 'active' : '' ?>" href="<?= APP_URL ?>/report-found.php">
                        <i class="bi bi-plus-circle me-1"></i> Report Found
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($currentScript == 'map.php') ? 'active' : '' ?>" href="<?= APP_URL ?>/map.php">
                        <i class="bi bi-geo-alt me-1"></i> Campus Map
                    </a>
                </li>
            </ul>

            <!-- Right Actions -->
            <div class="d-flex align-items-center gap-2">
                <?php if ($isAuth): ?>
                    <!-- Quick Role Switch / Indicator -->
                    <?php if ($user['role'] === 'admin'): ?>
                        <a href="<?= APP_URL ?>/admin/dashboard.php" class="btn btn-outline-light btn-sm fw-medium border-indigo-700" style="font-size: 0.75rem; border-color: #4f46e5;">
                            <i class="bi bi-speedometer2 me-1"></i> Admin Panel
                        </a>
                    <?php elseif ($user['role'] === 'officer'): ?>
                        <a href="<?= APP_URL ?>/officer/dashboard.php" class="btn btn-outline-light btn-sm fw-medium border-indigo-700" style="font-size: 0.75rem; border-color: #4f46e5;">
                            <i class="bi bi-shield-check me-1"></i> Officer Desk
                        </a>
                    <?php else: ?>
                        <a href="<?= APP_URL ?>/dashboard.php" class="btn btn-outline-light btn-sm fw-medium border-indigo-700" style="font-size: 0.75rem; border-color: #4f46e5;">
                            <i class="bi bi-columns-gap me-1"></i> Dashboard
                        </a>
                    <?php endif; ?>

                    <!-- Notification Bell -->
                    <a href="<?= APP_URL ?>/notifications.php" class="btn btn-sm position-relative px-2 py-1 text-white" style="background-color: #3730a3;" title="Notifications">
                        <i class="bi bi-bell-fill"></i>
                        <?php if ($unreadCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded bg-danger border border-light" style="font-size: 0.6rem; padding: 0.15rem 0.35rem;">
                                <?= $unreadCount ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- User Menu Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm dropdown-toggle d-flex align-items-center gap-2 py-1 px-2.5 shadow-sm rounded" type="button" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="bg-indigo-600 text-white rounded d-flex align-items-center justify-content-center fw-bold" style="width: 22px; height: 22px; font-size: 0.75rem; background-color: #4f46e5;">
                                <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                            </div>
                            <span class="d-none d-md-inline fw-semibold small text-truncate" style="max-width: 130px; font-size: 0.8rem;"><?= e($user['full_name']) ?></span>
                            <span class="badge bg-secondary-subtle text-secondary border rounded text-uppercase" style="font-size: 0.6rem;"><?= e($user['role']) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border mt-1 rounded" aria-labelledby="userMenuDropdown">
                            <li class="px-3 py-2 border-bottom bg-light">
                                <p class="mb-0 fw-bold small"><?= e($user['full_name']) ?></p>
                                <small class="text-muted font-monospace" style="font-size: 0.7rem;"><?= e($user['email']) ?></small>
                                <div class="mt-1"><span class="badge bg-primary-subtle text-primary font-monospace"><?= e($user['university_id']) ?></span></div>
                            </li>
                            <li><a class="dropdown-item py-1.5" href="<?= APP_URL ?>/dashboard.php"><i class="bi bi-columns-gap me-2 text-primary"></i> Dashboard</a></li>
                            <li><a class="dropdown-item py-1.5" href="<?= APP_URL ?>/my-reports.php"><i class="bi bi-folder2-open me-2 text-primary"></i> My Reports</a></li>
                            <li><a class="dropdown-item py-1.5" href="<?= APP_URL ?>/my-claims.php"><i class="bi bi-clipboard-check me-2 text-primary"></i> My Claims</a></li>
                            <li><a class="dropdown-item py-1.5" href="<?= APP_URL ?>/messages.php"><i class="bi bi-chat-dots me-2 text-primary"></i> Messages</a></li>
                            <li><a class="dropdown-item py-1.5" href="<?= APP_URL ?>/profile.php"><i class="bi bi-person me-2 text-primary"></i> Account Settings</a></li>
                            
                            <?php if (in_array($user['role'], ['admin', 'officer'])): ?>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li class="dropdown-header text-uppercase fw-bold" style="font-size: 0.65rem;">Staff Portals</li>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <li><a class="dropdown-item py-1.5" href="<?= APP_URL ?>/admin/dashboard.php"><i class="bi bi-shield-lock me-2 text-danger"></i> Administration</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item py-1.5" href="<?= APP_URL ?>/officer/dashboard.php"><i class="bi bi-person-badge me-2 text-info"></i> Officer Console</a></li>
                            <?php endif; ?>

                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item py-1.5 text-danger" href="<?= APP_URL ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Log Out</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="<?= APP_URL ?>/login.php" class="btn btn-outline-light btn-sm px-2.5 rounded">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Log In
                    </a>
                    <a href="<?= APP_URL ?>/register.php" class="btn btn-warning btn-sm px-2.5 fw-semibold rounded text-dark">
                        <i class="bi bi-person-plus me-1"></i> Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
