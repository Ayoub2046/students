<?php
/**
 * Officer Operations Dashboard
 */
require_once __DIR__ . '/../includes/role-check.php';
requireRole(['officer', 'admin']);

require_once __DIR__ . '/../classes/Item.php';
require_once __DIR__ . '/../classes/Claim.php';
require_once __DIR__ . '/../classes/Storage.php';
require_once __DIR__ . '/../classes/Database.php';

$pageTitle = "Officer Operational Console";
$pdo = Database::getInstance()->getConnection();

$stats = [
    'pending_reports'    => (int)$pdo->query("SELECT COUNT(*) FROM items WHERE status = 'pending'")->fetchColumn(),
    'pending_claims'     => (int)$pdo->query("SELECT COUNT(*) FROM claims WHERE status IN ('pending', 'under_review')")->fetchColumn(),
    'in_storage'         => (int)$pdo->query("SELECT COUNT(*) FROM item_storage")->fetchColumn(),
    'ready_for_handover' => (int)$pdo->query("SELECT COUNT(*) FROM items WHERE status = 'ready_for_handover'")->fetchColumn(),
    'unclaimed_count'    => (int)$pdo->query("SELECT COUNT(*) FROM items WHERE type = 'found' AND status IN ('approved', 'available') AND DATEDIFF(NOW(), created_at) >= 90")->fetchColumn(),
    'handovers_today'    => (int)$pdo->query("SELECT COUNT(*) FROM handovers WHERE DATE(created_at) = CURDATE()")->fetchColumn()
];

// Fetch Pending Reports
$itemModel = new Item();
$pendingReports = $itemModel->getItems(['status' => 'pending'], 6, 0);

// Fetch Pending Claims
$claimModel = new Claim();
$pendingClaims = $claimModel->getAll(['status' => 'pending'], 6, 0);

// Fetch Ready for Handover items
$handoverItems = $itemModel->getItems(['status' => 'ready_for_handover'], 6, 0);

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
            <!-- Header Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <span class="badge bg-info-subtle text-info-emphasis px-3 py-1 rounded-pill fw-bold text-uppercase mb-1">
                            <i class="bi bi-shield-check me-1"></i> Duty Officer Desk
                        </span>
                        <h4 class="fw-bold text-dark mb-0">Operational Management Console</h4>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= APP_URL ?>/officer/reports.php" class="btn btn-warning btn-sm rounded-pill px-3 fw-semibold">
                            <i class="bi bi-inbox me-1"></i> Pending Reports (<?= $stats['pending_reports'] ?>)
                        </a>
                        <a href="<?= APP_URL ?>/officer/claims.php" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold">
                            <i class="bi bi-clipboard-check me-1"></i> Pending Claims (<?= $stats['pending_claims'] ?>)
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats Counter Grid -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100">
                        <span class="text-warning fs-3 mb-1"><i class="bi bi-hourglass-split"></i></span>
                        <h3 class="fw-bold mb-0 text-dark"><?= $stats['pending_reports'] ?></h3>
                        <small class="text-muted fw-semibold" style="font-size: 0.75rem;">Pending Reports</small>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100">
                        <span class="text-primary fs-3 mb-1"><i class="bi bi-patch-question"></i></span>
                        <h3 class="fw-bold mb-0 text-dark"><?= $stats['pending_claims'] ?></h3>
                        <small class="text-muted fw-semibold" style="font-size: 0.75rem;">Pending Claims</small>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100">
                        <span class="text-info fs-3 mb-1"><i class="bi bi-archive"></i></span>
                        <h3 class="fw-bold mb-0 text-dark"><?= $stats['in_storage'] ?></h3>
                        <small class="text-muted fw-semibold" style="font-size: 0.75rem;">In Storage</small>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100">
                        <span class="text-success fs-3 mb-1"><i class="bi bi-hand-thumbs-up"></i></span>
                        <h3 class="fw-bold mb-0 text-dark"><?= $stats['ready_for_handover'] ?></h3>
                        <small class="text-muted fw-semibold" style="font-size: 0.75rem;">Ready Handover</small>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100">
                        <span class="text-dark fs-3 mb-1"><i class="bi bi-clock-history"></i></span>
                        <h3 class="fw-bold mb-0 text-dark"><?= $stats['unclaimed_count'] ?></h3>
                        <small class="text-muted fw-semibold" style="font-size: 0.75rem;">Unclaimed 90d+</small>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100">
                        <span class="text-success fs-3 mb-1"><i class="bi bi-check2-circle"></i></span>
                        <h3 class="fw-bold mb-0 text-dark"><?= $stats['handovers_today'] ?></h3>
                        <small class="text-muted fw-semibold" style="font-size: 0.75rem;">Handovers Today</small>
                    </div>
                </div>
            </div>

            <!-- Reports Pending Approval Table -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
                <div class="card-header bg-white border-0 p-3.5 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-inbox me-2 text-warning"></i> Reports Pending Officer Approval</h5>
                    <a href="<?= APP_URL ?>/officer/reports.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        View All (<?= $stats['pending_reports'] ?>)
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Item Reference</th>
                                <th>Type</th>
                                <th>Category & Location</th>
                                <th>Reported By</th>
                                <th>Date Submitted</th>
                                <th class="text-end pe-4">Review Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendingReports)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-check-circle fs-3 text-success d-block mb-1"></i>
                                        All submitted reports have been reviewed!
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pendingReports as $pr): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-bold text-dark d-block"><?= e($pr['title']) ?></span>
                                            <code><?= e($pr['reference_code']) ?></code>
                                        </td>
                                        <td><?= getTypeBadge($pr['type']) ?></td>
                                        <td>
                                            <span class="small d-block fw-semibold"><?= e($pr['category_name']) ?></span>
                                            <small class="text-muted"><i class="bi bi-geo-alt"></i> <?= e($pr['location_name']) ?></small>
                                        </td>
                                        <td><span class="small"><?= e($pr['reporter_name'] ?? 'Student') ?></span></td>
                                        <td><span class="small text-muted"><?= formatDate($pr['created_at']) ?></span></td>
                                        <td class="text-end pe-4">
                                            <a href="<?= APP_URL ?>/officer/report-view.php?id=<?= $pr['id'] ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                                                <i class="bi bi-pencil-square me-1"></i> Inspect & Approve
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Claims Pending Review Table -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
                <div class="card-header bg-white border-0 p-3.5 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-clipboard2-check me-2 text-primary"></i> Claims Awaiting Ownership Verification</h5>
                    <a href="<?= APP_URL ?>/officer/claims.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        View All (<?= $stats['pending_claims'] ?>)
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Claim Code</th>
                                <th>Target Item</th>
                                <th>Claimant</th>
                                <th>Date Submitted</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($pendingClaims)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-check-circle fs-3 text-success d-block mb-1"></i>
                                        No pending claims awaiting review.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($pendingClaims as $pclm): ?>
                                    <tr>
                                        <td class="ps-4"><code><?= e($pclm['claim_code']) ?></code></td>
                                        <td>
                                            <span class="fw-bold text-dark d-block"><?= e($pclm['item_title']) ?></span>
                                            <code class="small text-muted"><?= e($pclm['item_ref']) ?></code>
                                        </td>
                                        <td>
                                            <span class="small fw-semibold text-dark d-block"><?= e($pclm['claimant_name']) ?></span>
                                            <small class="text-muted"><?= e($pclm['claimant_uid']) ?></small>
                                        </td>
                                        <td><span class="small text-muted"><?= formatDate($pclm['created_at']) ?></span></td>
                                        <td><?= getStatusBadge($pclm['status']) ?></td>
                                        <td class="text-end pe-4">
                                            <a href="<?= APP_URL ?>/officer/claim-view.php?id=<?= $pclm['id'] ?>" class="btn btn-warning btn-sm rounded-pill px-3 fw-semibold">
                                                <i class="bi bi-shield-check me-1"></i> Verify Answers
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Ready For Handover Table -->
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white border-0 p-3.5 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-hand-thumbs-up me-2 text-success"></i> Items Approved & Ready for Physical Handover</h5>
                    <a href="<?= APP_URL ?>/officer/handover.php" class="btn btn-outline-success btn-sm rounded-pill px-3">
                        Handover Console
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Item Reference</th>
                                <th>Item Title</th>
                                <th>Current Status</th>
                                <th class="text-end pe-4">Complete Handover</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($handoverItems)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">
                                        No items currently pending physical collection.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($handoverItems as $hIt): ?>
                                    <tr>
                                        <td class="ps-4"><code><?= e($hIt['reference_code']) ?></code></td>
                                        <td class="fw-bold text-dark"><?= e($hIt['title']) ?></td>
                                        <td><?= getStatusBadge($hIt['status']) ?></td>
                                        <td class="text-end pe-4">
                                            <a href="<?= APP_URL ?>/officer/handover.php?item_id=<?= $hIt['id'] ?>" class="btn btn-success btn-sm rounded-pill px-3">
                                                <i class="bi bi-pen me-1"></i> Verify ID & Sign Handover
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
