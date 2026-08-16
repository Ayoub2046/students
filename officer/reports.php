<?php
/**
 * Officer Item Reports Management
 */
require_once __DIR__ . '/../includes/role-check.php';
requireRole(['officer', 'admin']);

require_once __DIR__ . '/../classes/Item.php';
require_once __DIR__ . '/../classes/Database.php';

$pageTitle = "Review Item Reports";
$itemModel = new Item();

$status = $_GET['status'] ?? 'pending';
$type = $_GET['type'] ?? '';

$filters = [];
if (!empty($status) && $status !== 'all') {
    $filters['status'] = $status;
}
if (!empty($type)) {
    $filters['type'] = $type;
}

$items = $itemModel->getItems($filters, 50, 0);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid px-lg-4 py-4">
    <?= renderFlash() ?>

    <div class="row g-4">
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h4 class="fw-bold mb-1">Incoming Item Reports</h4>
                        <p class="text-muted small mb-0">Approve, reject, or assign physical storage to reported lost and found items.</p>
                    </div>
                </div>
            </div>

            <!-- Filter Badges -->
            <div class="d-flex flex-wrap gap-2 mb-3">
                <a href="?status=pending" class="btn btn-sm rounded-pill <?= ($status === 'pending') ? 'btn-warning fw-bold' : 'btn-outline-secondary bg-white' ?>">
                    Pending Review
                </a>
                <a href="?status=approved" class="btn btn-sm rounded-pill <?= ($status === 'approved') ? 'btn-success fw-bold' : 'btn-outline-secondary bg-white' ?>">
                    Approved
                </a>
                <a href="?status=available" class="btn btn-sm rounded-pill <?= ($status === 'available') ? 'btn-primary fw-bold' : 'btn-outline-secondary bg-white' ?>">
                    Available / In Storage
                </a>
                <a href="?status=returned" class="btn btn-sm rounded-pill <?= ($status === 'returned') ? 'btn-dark fw-bold' : 'btn-outline-secondary bg-white' ?>">
                    Returned
                </a>
                <a href="?status=all" class="btn btn-sm rounded-pill <?= ($status === 'all') ? 'btn-secondary fw-bold' : 'btn-outline-secondary bg-white' ?>">
                    All Records
                </a>
            </div>

            <!-- Reports Table -->
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Item & Code</th>
                                <th>Type</th>
                                <th>Category & Location</th>
                                <th>Reporter</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No item reports match this filter.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($items as $it): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-bold text-dark d-block"><?= e($it['title']) ?></span>
                                            <code><?= e($it['reference_code']) ?></code>
                                        </td>
                                        <td><?= getTypeBadge($it['type']) ?></td>
                                        <td>
                                            <span class="small d-block fw-semibold"><?= e($it['category_name']) ?></span>
                                            <small class="text-muted"><i class="bi bi-geo-alt"></i> <?= e($it['location_name']) ?></small>
                                        </td>
                                        <td>
                                            <span class="small fw-semibold d-block"><?= e($it['reporter_name'] ?? 'User') ?></span>
                                            <small class="text-muted"><?= e($it['reporter_uid'] ?? '') ?></small>
                                        </td>
                                        <td><span class="small text-muted"><?= formatDate($it['created_at']) ?></span></td>
                                        <td><?= getStatusBadge($it['status']) ?></td>
                                        <td class="text-end pe-4">
                                            <a href="<?= APP_URL ?>/officer/report-view.php?id=<?= $it['id'] ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                                                Inspect
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
