<?php
/**
 * University Lost & Found - Public Landing Page
 * "Lost Today, Find Tomorrow"
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Item.php';
require_once __DIR__ . '/classes/Database.php';

$pageTitle = "Lost Today, Find Tomorrow";

// Real Statistics from Database
$pdo = Database::getInstance()->getConnection();
$stats = [
    'total_lost'     => (int)$pdo->query("SELECT COUNT(*) FROM items WHERE type = 'lost'")->fetchColumn(),
    'total_found'    => (int)$pdo->query("SELECT COUNT(*) FROM items WHERE type = 'found'")->fetchColumn(),
    'total_returned' => (int)$pdo->query("SELECT COUNT(*) FROM items WHERE status = 'returned'")->fetchColumn(),
    'active_users'   => (int)$pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn()
];

// Recent Approved Items for Display
$itemModel = new Item();
$recentItems = $itemModel->getItems(['public_only' => true], 6, 0);

// Categories
$categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container-fluid px-lg-5 py-4">
    <?= renderFlash() ?>

    <!-- Hero Section -->
    <div class="hero-banner p-4 p-md-5 mb-5 shadow-lg position-relative overflow-hidden">
        <div class="row align-items-center gy-4 position-relative" style="z-index: 2;">
            <div class="col-lg-7">
                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fw-bold text-uppercase mb-3 shadow-sm">
                    <i class="bi bi-patch-check-fill me-1"></i> Official University Lost & Found
                </span>
                <h1 class="display-5 fw-extrabold text-white mb-3">
                    Lost Something?<br>Find It Again.
                </h1>
                <p class="lead text-white-75 mb-4 pe-lg-4">
                    <strong>Lost Today, Find Tomorrow.</strong> A centralized, secure campus platform connecting students and staff with misplaced belongings through verified claims and storage tracking.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?= APP_URL ?>/report-lost.php" class="btn btn-warning btn-lg px-4 py-2.5 rounded-pill fw-bold text-dark shadow-sm">
                        <i class="bi bi-exclamation-octagon me-2"></i> Report Lost Item
                    </a>
                    <a href="<?= APP_URL ?>/report-found.php" class="btn btn-light btn-lg px-4 py-2.5 rounded-pill fw-bold text-primary shadow-sm">
                        <i class="bi bi-plus-circle me-2"></i> Report Found Item
                    </a>
                    <a href="<?= APP_URL ?>/items.php" class="btn btn-outline-light btn-lg px-4 py-2.5 rounded-pill fw-semibold">
                        <i class="bi bi-search me-2"></i> Browse Directory
                    </a>
                </div>
            </div>
            <div class="col-lg-5 text-center d-none d-lg-block">
                <div class="bg-white bg-opacity-10 border border-white border-opacity-25 rounded-4 p-4 shadow-sm backdrop-blur">
                    <i class="bi bi-shield-check display-1 text-warning mb-2 d-block"></i>
                    <h5 class="fw-bold text-white mb-1">Secure Claim Verification</h5>
                    <p class="small text-white-75 mb-0">Every claim is securely vetted with hidden serial numbers and physical verification before handover.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Search Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-5" style="margin-top: -3.5rem; z-index: 10; position: relative;">
        <form action="<?= APP_URL ?>/items.php" method="GET" class="row g-3 align-items-center">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Search by title, brand, model, color or reference code...">
                </div>
            </div>
            <div class="col-md-3">
                <select name="category_id" class="form-select bg-light">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select bg-light">
                    <option value="">All Types</option>
                    <option value="found">Found Items</option>
                    <option value="lost">Lost Reports</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100 fw-semibold rounded-3 py-2">
                    <i class="bi bi-search me-1"></i> Search
                </button>
            </div>
        </form>
    </div>

    <!-- Live Statistics Counter -->
    <div class="row g-3 mb-5">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 text-center h-100 bg-white">
                <div class="text-danger mb-1 fs-2"><i class="bi bi-search"></i></div>
                <h3 class="fw-bold text-dark mb-0"><?= number_format($stats['total_lost']) ?></h3>
                <small class="text-muted fw-semibold">Total Lost Reports</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 text-center h-100 bg-white">
                <div class="text-success mb-1 fs-2"><i class="bi bi-box2-heart"></i></div>
                <h3 class="fw-bold text-dark mb-0"><?= number_format($stats['total_found']) ?></h3>
                <small class="text-muted fw-semibold">Total Found Items</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 text-center h-100 bg-white">
                <div class="text-primary mb-1 fs-2"><i class="bi bi-patch-check-fill"></i></div>
                <h3 class="fw-bold text-dark mb-0"><?= number_format($stats['total_returned']) ?></h3>
                <small class="text-muted fw-semibold">Returned to Owners</small>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 text-center h-100 bg-white">
                <div class="text-info mb-1 fs-2"><i class="bi bi-people-fill"></i></div>
                <h3 class="fw-bold text-dark mb-0"><?= number_format($stats['active_users']) ?></h3>
                <small class="text-muted fw-semibold">Active Campus Users</small>
            </div>
        </div>
    </div>

    <!-- How It Works Section -->
    <div class="mb-5">
        <div class="text-center mb-4">
            <span class="badge bg-primary-subtle text-primary px-3 py-1.5 rounded-pill fw-bold text-uppercase">End-to-End Workflow</span>
            <h2 class="fw-bold mt-2">How The System Works</h2>
            <p class="text-muted">A clear, transparent process from initial report to physical item return.</p>
        </div>

        <div class="row g-3">
            <div class="col-md-2dot4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 text-center h-100 bg-white card-hover">
                    <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-2 fw-bold" style="width: 48px; height: 48px;">1</div>
                    <h6 class="fw-bold mb-1">1. Report</h6>
                    <p class="small text-muted mb-0">Submit details, dates, campus location, and photos of lost or found items.</p>
                </div>
            </div>
            <div class="col-md-2dot4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 text-center h-100 bg-white card-hover">
                    <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-2 fw-bold" style="width: 48px; height: 48px;">2</div>
                    <h6 class="fw-bold mb-1">2. Review & Match</h6>
                    <p class="small text-muted mb-0">Officers approve items and our rule-based AI engine calculates similarity matches.</p>
                </div>
            </div>
            <div class="col-md-2dot4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 text-center h-100 bg-white card-hover">
                    <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-2 fw-bold" style="width: 48px; height: 48px;">3</div>
                    <h6 class="fw-bold mb-1">3. Claim</h6>
                    <p class="small text-muted mb-0">Owners submit ownership claims answering hidden verification questions.</p>
                </div>
            </div>
            <div class="col-md-2dot4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 text-center h-100 bg-white card-hover">
                    <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-2 fw-bold" style="width: 48px; height: 48px;">4</div>
                    <h6 class="fw-bold mb-1">4. Verify</h6>
                    <p class="small text-muted mb-0">Officer inspects claims, verifies matching serials or distinctive markings.</p>
                </div>
            </div>
            <div class="col-md-2dot4 col-sm-6">
                <div class="card border-0 shadow-sm rounded-4 p-3 text-center h-100 bg-white card-hover">
                    <div class="bg-primary-subtle text-primary rounded-circle d-inline-flex align-items-center justify-content-center mx-auto mb-2 fw-bold" style="width: 48px; height: 48px;">5</div>
                    <h6 class="fw-bold mb-1">5. Return</h6>
                    <p class="small text-muted mb-0">Item is safely handed over in Room 104 with ID verification and signed receipt.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Recently Reported Items -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h3 class="fw-bold mb-0">Recently Listed Items</h3>
                <small class="text-muted">Browse recent lost and found items on campus</small>
            </div>
            <a href="<?= APP_URL ?>/items.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                View All Items <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        <div class="row g-4">
            <?php if (empty($recentItems)): ?>
                <div class="col-12 text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                    No items reported yet.
                </div>
            <?php else: ?>
                <?php foreach ($recentItems as $item): ?>
                    <div class="col-md-4 col-sm-6">
                        <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 card-hover">
                            <!-- Image Wrap -->
                            <div class="item-img-wrap">
                                <?php if (!empty($item['primary_image'])): ?>
                                    <img src="<?= APP_URL ?>/uploads/items/<?= e($item['primary_image']) ?>" alt="<?= e($item['title']) ?>" onerror="this.src='https://images.unsplash.com/photo-1584438784894-089d6a62b8fa?w=500&auto=format&fit=crop&q=60'">
                                <?php else: ?>
                                    <div class="d-flex flex-column align-items-center justify-content-center text-muted">
                                        <i class="bi <?= e($item['category_icon'] ?? 'bi-box') ?> fs-1"></i>
                                        <small class="mt-1">No Image</small>
                                    </div>
                                <?php endif; ?>
                                <span class="position-absolute top-0 start-0 m-3">
                                    <?= getTypeBadge($item['type']) ?>
                                </span>
                                <span class="position-absolute top-0 end-0 m-3">
                                    <?= getStatusBadge($item['status']) ?>
                                </span>
                            </div>

                            <div class="card-body p-3.5 d-flex flex-column">
                                <small class="text-primary fw-semibold mb-1">
                                    <i class="bi <?= e($item['category_icon'] ?? 'bi-tag') ?> me-1"></i> <?= e($item['category_name'] ?? 'General') ?>
                                </small>
                                <h6 class="card-title fw-bold text-truncate mb-2" title="<?= e($item['title']) ?>">
                                    <?= e($item['title']) ?>
                                </h6>
                                <p class="card-text small text-muted text-truncate mb-3">
                                    <?= e($item['description']) ?>
                                </p>

                                <div class="mt-auto pt-2 border-top">
                                    <div class="d-flex justify-content-between align-items-center small text-muted mb-2">
                                        <span><i class="bi bi-geo-alt me-1"></i> <?= e($item['location_name'] ?? 'Campus') ?></span>
                                        <span><i class="bi bi-calendar3 me-1"></i> <?= formatDate($item['date_lost'] ?? $item['date_found'] ?? $item['created_at']) ?></span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <code class="text-muted small"><?= e($item['reference_code']) ?></code>
                                        <a href="<?= APP_URL ?>/item-details.php?ref=<?= e($item['reference_code']) ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                                            Details <i class="bi bi-chevron-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
