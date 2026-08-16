<?php
/**
 * User's Reported Items List
 */
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/classes/Item.php';

$pageTitle = "My Reported Items";
$userId = (int)$currentUser['id'];

$itemModel = new Item();
$type = $_GET['type'] ?? '';
$filters = ['reported_by' => $userId];
if (!empty($type)) {
    $filters['type'] = $type;
}

$items = $itemModel->getItems($filters, 50, 0);

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
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h4 class="fw-bold mb-1">My Reported Items</h4>
                        <p class="text-muted small mb-0">Manage and track all lost and found items you registered on the campus network.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= APP_URL ?>/report-lost.php" class="btn btn-outline-danger btn-sm rounded-pill">
                            <i class="bi bi-plus-lg me-1"></i> Report Lost
                        </a>
                        <a href="<?= APP_URL ?>/report-found.php" class="btn btn-outline-success btn-sm rounded-pill">
                            <i class="bi bi-plus-lg me-1"></i> Report Found
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <ul class="nav nav-pills mb-3 gap-2">
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 <?= empty($type) ? 'active' : 'bg-white text-muted border' ?>" href="<?= APP_URL ?>/my-reports.php">All Reports</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 <?= ($type === 'lost') ? 'active' : 'bg-white text-muted border' ?>" href="<?= APP_URL ?>/my-reports.php?type=lost">Lost Only</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link rounded-pill px-3 <?= ($type === 'found') ? 'active' : 'bg-white text-muted border' ?>" href="<?= APP_URL ?>/my-reports.php?type=found">Found Only</a>
                </li>
            </ul>

            <!-- Reports List Table -->
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Item Details</th>
                                <th>Type</th>
                                <th>Category & Location</th>
                                <th>Date Reported</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No items recorded under this filter.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $it): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-3 bg-light d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; overflow: hidden;">
                                                    <?php if (!empty($it['primary_image'])): ?>
                                                        <img src="<?= APP_URL ?>/uploads/items/<?= e($it['primary_image']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                                    <?php else: ?>
                                                        <i class="bi <?= e($it['category_icon'] ?? 'bi-box') ?> fs-4 text-muted"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <a href="<?= APP_URL ?>/item-details.php?ref=<?= e($it['reference_code']) ?>" class="fw-bold text-dark text-decoration-none d-block">
                                                        <?= e($it['title']) ?>
                                                    </a>
                                                    <code class="small text-muted"><?= e($it['reference_code']) ?></code>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= getTypeBadge($it['type']) ?></td>
                                        <td>
                                            <span class="d-block small fw-semibold"><?= e($it['category_name']) ?></span>
                                            <small class="text-muted"><i class="bi bi-geo-alt"></i> <?= e($it['location_name']) ?></small>
                                        </td>
                                        <td><span class="small text-muted"><?= formatDate($it['created_at']) ?></span></td>
                                        <td><?= getStatusBadge($it['status']) ?></td>
                                        <td class="text-end pe-4">
                                            <a href="<?= APP_URL ?>/item-details.php?ref=<?= e($it['reference_code']) ?>" class="btn btn-light btn-sm rounded-pill">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
