<?php
/**
 * 403 - Forbidden Page
 */
require_once __DIR__ . '/config/config.php';
$pageTitle = "403 - Access Denied";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5 text-center my-auto">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-5 bg-white">
                <i class="bi bi-shield-x display-1 text-danger mb-3"></i>
                <h1 class="display-4 fw-bold text-dark">403</h1>
                <h4 class="fw-bold text-secondary mb-2">Access Denied</h4>
                <p class="text-muted small mb-4">You do not have the required administrative or officer clearance to view this restricted console.</p>
                <div>
                    <a href="<?= APP_URL ?>/dashboard.php" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-columns-gap me-1"></i> Return to My Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
