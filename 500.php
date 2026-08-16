<?php
/**
 * 500 - Internal Server Error Page
 */
require_once __DIR__ . '/config/config.php';
$pageTitle = "500 - System Error";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5 text-center my-auto">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-5 bg-white">
                <i class="bi bi-exclamation-triangle display-1 text-warning mb-3"></i>
                <h1 class="display-4 fw-bold text-dark">500</h1>
                <h4 class="fw-bold text-secondary mb-2">Internal Service Exception</h4>
                <p class="text-muted small mb-4">A database or system error occurred. Please verify MySQL service status in XAMPP control panel.</p>
                <div>
                    <a href="<?= APP_URL ?>/index.php" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh Home
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
