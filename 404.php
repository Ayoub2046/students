<?php
/**
 * 404 - Not Found Page
 */
require_once __DIR__ . '/config/config.php';
$pageTitle = "404 - Page Not Found";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5 text-center my-auto">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-5 bg-white">
                <i class="bi bi-compass display-1 text-primary mb-3"></i>
                <h1 class="display-4 fw-bold text-dark">404</h1>
                <h4 class="fw-bold text-secondary mb-2">Item or Page Not Found</h4>
                <p class="text-muted small mb-4">The page or item reference you requested does not exist or may have been archived.</p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="<?= APP_URL ?>/index.php" class="btn btn-primary rounded-pill px-4">
                        <i class="bi bi-house-door me-1"></i> Return Home
                    </a>
                    <a href="<?= APP_URL ?>/items.php" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="bi bi-search me-1"></i> Browse Items
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
