<?php
/**
 * User's Claims Tracking Page
 */
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/classes/Claim.php';

$pageTitle = "My Submitted Claims";
$userId = (int)$currentUser['id'];

$claimModel = new Claim();
$claims = $claimModel->getAll(['claimant_id' => $userId], 50, 0);

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
                <h4 class="fw-bold mb-1">My Submitted Claims</h4>
                <p class="text-muted small mb-0">Track verification progress, review officer decisions, and access handover instructions.</p>
            </div>

            <!-- Claims List -->
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Claim Code</th>
                                <th>Item Details</th>
                                <th>Date Submitted</th>
                                <th>Verification Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($claims)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-shield-question fs-1 d-block mb-2"></i>
                                        You have not submitted any claims yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($claims as $clm): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <code class="fw-bold fs-6 text-primary"><?= e($clm['claim_code']) ?></code>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="rounded-3 bg-light d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; overflow: hidden;">
                                                    <?php if (!empty($clm['primary_image'])): ?>
                                                        <img src="<?= APP_URL ?>/uploads/items/<?= e($clm['primary_image']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                                    <?php else: ?>
                                                        <i class="bi bi-box fs-4 text-muted"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <span class="fw-bold text-dark d-block"><?= e($clm['item_title']) ?></span>
                                                    <code class="small text-muted"><?= e($clm['item_ref']) ?></code>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="small text-muted"><?= formatDate($clm['created_at']) ?></span></td>
                                        <td>
                                            <?= getStatusBadge($clm['status']) ?>
                                            <?php if ($clm['status'] === 'approved'): ?>
                                                <small class="d-block text-success fw-bold mt-1"><i class="bi bi-geo-alt"></i> Visit Room 104 with ID</small>
                                            <?php elseif ($clm['status'] === 'rejected' && !empty($clm['rejection_reason'])): ?>
                                                <small class="d-block text-danger mt-1"><?= e($clm['rejection_reason']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end pe-4">
                                            <a href="<?= APP_URL ?>/item-details.php?ref=<?= e($clm['item_ref']) ?>" class="btn btn-light btn-sm rounded-pill">
                                                <i class="bi bi-eye"></i> View Item
                                            </a>
                                            <a href="<?= APP_URL ?>/messages.php?item_id=<?= $clm['item_id'] ?>" class="btn btn-outline-primary btn-sm rounded-pill" title="Message Officer">
                                                <i class="bi bi-chat-dots"></i>
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
