<?php
/**
 * Items Directory with Multi-Filter Search & Pagination
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Item.php';
require_once __DIR__ . '/classes/Database.php';

$pageTitle = "Browse Lost & Found Items";

$pdo = Database::getInstance()->getConnection();
$categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll();
$locations = $pdo->query("SELECT * FROM locations WHERE status = 'active' ORDER BY name ASC")->fetchAll();

// Filter parameters
$filters = [
    'public_only' => true,
    'search'      => $_GET['search'] ?? '',
    'type'        => $_GET['type'] ?? '',
    'category_id' => $_GET['category_id'] ?? '',
    'location_id' => $_GET['location_id'] ?? '',
    'color'       => $_GET['color'] ?? '',
    'date_from'   => $_GET['date_from'] ?? '',
    'date_to'     => $_GET['date_to'] ?? ''
];

// Clean empty filter keys
$activeFilters = array_filter($filters, function($v) { return $v !== '' && $v !== null; });

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

$itemModel = new Item();
$totalItems = $itemModel->count($activeFilters);
$totalPages = ceil($totalItems / $limit);
$items = $itemModel->getItems($activeFilters, $limit, $offset);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container-fluid px-lg-5 py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Campus Items Directory</h3>
            <p class="text-muted small mb-0">Search and filter verified lost reports and found items</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= APP_URL ?>/report-lost.php" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                <i class="bi bi-exclamation-octagon me-1"></i> Report Lost
            </a>
            <a href="<?= APP_URL ?>/report-found.php" class="btn btn-outline-success btn-sm rounded-pill px-3">
                <i class="bi bi-plus-circle me-1"></i> Report Found
            </a>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
        <form method="GET" action="<?= APP_URL ?>/items.php" class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold small text-muted">Search Keyword</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Title, brand, model, reference..." value="<?= e($filters['search']) ?>">
                </div>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold small text-muted">Item Type</label>
                <select name="type" class="form-select bg-light">
                    <option value="">All Types</option>
                    <option value="found" <?= ($filters['type'] === 'found') ? 'selected' : '' ?>>Found Items</option>
                    <option value="lost" <?= ($filters['type'] === 'lost') ? 'selected' : '' ?>>Lost Reports</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold small text-muted">Category</label>
                <select name="category_id" class="form-select bg-light">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold small text-muted">Campus Location</label>
                <select name="location_id" class="form-select bg-light">
                    <option value="">All Locations</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?= $loc['id'] ?>" <?= ($filters['location_id'] == $loc['id']) ? 'selected' : '' ?>>
                            <?= e($loc['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold small text-muted">Color</label>
                <input type="text" name="color" class="form-control bg-light" placeholder="e.g. Black, Blue, Silver" value="<?= e($filters['color']) ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold small text-muted">Date From</label>
                <input type="date" name="date_from" class="form-control bg-light" value="<?= e($filters['date_from']) ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label fw-semibold small text-muted">Date To</label>
                <input type="date" name="date_to" class="form-control bg-light" value="<?= e($filters['date_to']) ?>">
            </div>

            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100 rounded-3 fw-semibold">
                    <i class="bi bi-funnel-fill me-1"></i> Apply Filters
                </button>
                <a href="<?= APP_URL ?>/items.php" class="btn btn-light border rounded-3" title="Reset Filters">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Results Counter -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <small class="text-muted fw-semibold">Showing <strong><?= count($items) ?></strong> of <strong><?= $totalItems ?></strong> item records</small>
    </div>

    <!-- Items Grid -->
    <div class="row g-4 mb-4">
        <?php if (empty($items)): ?>
            <div class="col-12 text-center py-5 bg-white rounded-4 shadow-sm">
                <i class="bi bi-search display-3 text-muted mb-2 d-block"></i>
                <h5 class="fw-bold text-dark">No matching items found</h5>
                <p class="text-muted small">Try broadening your search query or adjusting the selected filters.</p>
                <a href="<?= APP_URL ?>/items.php" class="btn btn-outline-primary btn-sm rounded-pill">Reset All Filters</a>
            </div>
        <?php else: ?>
            <?php foreach ($items as $item): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 card-hover bg-white">
                        <div class="item-img-wrap">
                            <?php if (!empty($item['primary_image'])): ?>
                                <img src="<?= APP_URL ?>/uploads/items/<?= e($item['primary_image']) ?>" alt="<?= e($item['title']) ?>" onerror="this.src='https://images.unsplash.com/photo-1584438784894-089d6a62b8fa?w=500&auto=format&fit=crop&q=60'">
                            <?php else: ?>
                                <div class="text-center text-muted">
                                    <i class="bi <?= e($item['category_icon'] ?? 'bi-box') ?> fs-1"></i>
                                    <small class="d-block mt-1">No Image</small>
                                </div>
                            <?php endif; ?>
                            <span class="position-absolute top-0 start-0 m-2.5">
                                <?= getTypeBadge($item['type']) ?>
                            </span>
                            <span class="position-absolute top-0 end-0 m-2.5">
                                <?= getStatusBadge($item['status']) ?>
                            </span>
                        </div>

                        <div class="card-body p-3.5 d-flex flex-column">
                            <small class="text-primary fw-semibold mb-1">
                                <i class="bi <?= e($item['category_icon'] ?? 'bi-tag') ?> me-1"></i> <?= e($item['category_name'] ?? 'General') ?>
                            </small>
                            <h6 class="card-title fw-bold text-truncate mb-1" title="<?= e($item['title']) ?>">
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
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="d-flex justify-content-center">
            <ul class="pagination">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link rounded-pill me-1" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Previous</a>
                </li>
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                        <a class="page-link rounded-circle mx-1" href="?<?= http_build_query(array_merge($_GET, ['page' => $p])) ?>"><?= $p ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link rounded-pill ms-1" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Next</a>
                </li>
            </ul>
        </nav>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
