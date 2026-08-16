<?php
/**
 * Officer Claims Verification Hub
 */
require_once __DIR__ . '/../includes/role-check.php';
requireRole(['officer', 'admin']);

require_once __DIR__ . '/../classes/Claim.php';
require_once __DIR__ . '/../classes/Database.php';

$pageTitle = "Verify Ownership Claims";
$claimModel = new Claim();

$status = $_GET['status'] ?? 'pending';
$filters = [];
if (!empty($status) && $status !== 'all') {
    $filters['status'] = $status;
}

$claims = $claimModel->getAll($filters, 50, 0);

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
                        <h4 class="fw-bold mb-1">Ownership Claims Verification Desk</h4>
                        <p class="text-muted small mb-0">Evaluate student/staff claims against confidential item intake records.</p>
                    </div>
                </div>
            </div>

            <!-- Filter Badges -->
            <div class="d-flex flex-wrap gap-2 mb-3">
                <a href="?status=pending" class="btn btn-sm rounded-pill <?= ($status === 'pending') ? 'btn-warning fw-bold' : 'btn-outline-secondary bg-white' ?>">
                    Pending Review
                </a>
                <a href="?status=under_review" class="btn btn-sm rounded-pill <?= ($status === 'under_review') ? 'btn-info text-white fw-bold' : 'btn-outline-secondary bg-white' ?>">
                    Under Review
                </a>
                <a href="?status=approved" class="btn btn-sm rounded-pill <?= ($status === 'approved') ? 'btn-success fw-bold' : 'btn-outline-secondary bg-white' ?>">
                    Approved
                </a>
                <a href="?status=rejected" class="btn btn-sm rounded-pill <?= ($status === 'rejected') ? 'btn-danger fw-bold' : 'btn-outline-secondary bg-white' ?>">
                    Rejected
                </a>
                <a href="?status=completed" class="btn btn-sm rounded-pill <?= ($status === 'completed') ? 'btn-dark fw-bold' : 'btn-outline-secondary bg-white' ?>">
                    Completed / Handed Over
                </a>
                <a href="?status=all" class="btn btn-sm rounded-pill <?= ($status === 'all') ? 'btn-secondary fw-bold' : 'btn-outline-secondary bg-white' ?>">
                    All Claims
                </a>
            </div>

            <!-- Claims Table -->
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Claim Code</th>
                                <th>Target Item</th>
                                <th>Claimant Details</th>
                                <th>Date Submitted</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Verification</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($claims)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-patch-question fs-1 d-block mb-2"></i>
                                        No claims match this status.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($claims as $c): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <code class="fw-bold fs-6 text-primary"><?= e($c['claim_code']) ?></code>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark d-block"><?= e($c['item_title']) ?></span>
                                            <code class="small text-muted"><?= e($c['item_ref']) ?></code>
                                        </td>
                                        <td>
                                            <span class="small fw-semibold d-block"><?= e($c['claimant_name']) ?></span>
                                            <small class="text-muted"><?= e($c['claimant_uid']) ?> &bull; <?= e($c['claimant_email']) ?></small>
                                        </td>
                                        <td><span class="small text-muted"><?= formatDate($c['created_at']) ?></span></td>
                                        <td><?= getStatusBadge($c['status']) ?></td>
                                        <td class="text-end pe-4">
                                            <a href="<?= APP_URL ?>/officer/claim-view.php?id=<?= $c['id'] ?>" class="btn btn-warning btn-sm rounded-pill px-3 fw-semibold">
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
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
