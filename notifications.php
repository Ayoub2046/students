<?php
/**
 * In-App Notifications Center
 */
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/classes/Notification.php';

$pageTitle = "Notifications";
$userId = (int)$currentUser['id'];
$notifModel = new Notification();

// Mark all as read if requested
if (isset($_GET['action']) && $_GET['action'] === 'read_all') {
    $notifModel->markAllAsRead($userId);
    setFlash('success', 'All notifications marked as read.');
    header('Location: ' . APP_URL . '/notifications.php');
    exit;
}

// Mark single as read
if (isset($_GET['read_id'])) {
    $notifModel->markAsRead((int)$_GET['read_id'], $userId);
    if (!empty($_GET['redirect'])) {
        header('Location: ' . $_GET['redirect']);
        exit;
    }
}

$notifications = $notifModel->getUserNotifications($userId, 50, 0);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container-fluid px-lg-4 py-4">
    <?= renderFlash() ?>

    <div class="row g-4">
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1">Notifications & System Alerts</h4>
                        <p class="text-muted small mb-0">Updates regarding your reports, claim reviews, potential matches, and handover notices.</p>
                    </div>
                    <?php if (!empty($notifications)): ?>
                        <a href="<?= APP_URL ?>/notifications.php?action=read_all" class="btn btn-outline-secondary btn-sm rounded-pill">
                            <i class="bi bi-check2-all me-1"></i> Mark All as Read
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Notification Items List -->
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="list-group list-group-flush">
                    <?php if (empty($notifications)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-bell-slash fs-1 d-block mb-2"></i>
                            You have no notifications at this time.
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $n): ?>
                            <div class="list-group-item p-3.5 border-bottom <?= empty($n['is_read']) ? 'bg-light bg-opacity-50' : '' ?>">
                                <div class="d-flex justify-content-between align-items-start gap-3">
                                    <div class="d-flex gap-3">
                                        <div class="rounded-circle p-2 d-flex align-items-center justify-content-center <?= empty($n['is_read']) ? 'bg-primary text-white' : 'bg-light text-muted border' ?>" style="width: 42px; height: 42px; min-width: 42px;">
                                            <?php if ($n['type'] === 'match'): ?>
                                                <i class="bi bi-stars"></i>
                                            <?php elseif ($n['type'] === 'claim_approved'): ?>
                                                <i class="bi bi-check-circle"></i>
                                            <?php elseif ($n['type'] === 'claim_rejected'): ?>
                                                <i class="bi bi-x-circle"></i>
                                            <?php else: ?>
                                                <i class="bi bi-bell"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1 <?= empty($n['is_read']) ? 'text-primary' : 'text-dark' ?>">
                                                <?= e($n['title']) ?>
                                                <?php if (empty($n['is_read'])): ?>
                                                    <span class="badge bg-danger rounded-pill ms-2" style="font-size: 0.65rem;">NEW</span>
                                                <?php endif; ?>
                                            </h6>
                                            <p class="text-secondary small mb-1"><?= e($n['message']) ?></p>
                                            <small class="text-muted"><i class="bi bi-clock me-1"></i> <?= timeAgo($n['created_at']) ?></small>
                                        </div>
                                    </div>
                                    <?php if (!empty($n['link'])): ?>
                                        <a href="<?= APP_URL ?>/notifications.php?read_id=<?= $n['id'] ?>&redirect=<?= urlencode(APP_URL . '/' . ltrim($n['link'], '/')) ?>" class="btn btn-outline-primary btn-sm rounded-pill text-nowrap">
                                            View Details <i class="bi bi-arrow-right ms-1"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
