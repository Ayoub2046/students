<?php
/**
 * Officer Detailed Claim Verification & Decision Panel
 */
require_once __DIR__ . '/../includes/role-check.php';
requireRole(['officer', 'admin']);

require_once __DIR__ . '/../classes/Claim.php';
require_once __DIR__ . '/../classes/Item.php';
require_once __DIR__ . '/../classes/Database.php';

$claimId = (int)($_GET['id'] ?? 0);
$claimModel = new Claim();
$itemModel = new Item();

$claim = $claimModel->findById($claimId);

if (!$claim) {
    setFlash('danger', 'Claim record not found.');
    header('Location: ' . APP_URL . '/officer/claims.php');
    exit;
}

$item = $itemModel->findById($claim['item_id'], true);
$pageTitle = "Verify Claim: " . $claim['claim_code'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security token invalid.';
    } else {
        $decision = $_POST['decision'] ?? '';
        $notes = $_POST['officer_notes'] ?? '';
        $officerId = (int)$currentUser['id'];

        if ($decision === 'approve') {
            if ($claimModel->approve($claimId, $officerId, $notes)) {
                setFlash('success', "Claim {$claim['claim_code']} approved! The item is now set to READY FOR HANDOVER.");
                header('Location: ' . APP_URL . '/officer/claim-view.php?id=' . $claimId);
                exit;
            } else {
                $error = 'Failed to approve claim.';
            }
        } elseif ($decision === 'reject') {
            $reason = trim($_POST['rejection_reason'] ?? '');
            if (empty($reason)) {
                $error = 'Rejection reason is required when rejecting a claim.';
            } else {
                if ($claimModel->reject($claimId, $officerId, $reason, $notes)) {
                    setFlash('warning', "Claim {$claim['claim_code']} has been rejected and claimant notified.");
                    header('Location: ' . APP_URL . '/officer/claim-view.php?id=' . $claimId);
                    exit;
                } else {
                    $error = 'Failed to reject claim.';
                }
            }
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid px-lg-4 py-4">
    <?= renderFlash() ?>

    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>/officer/dashboard.php">Officer Console</a></li>
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>/officer/claims.php">Claims</a></li>
            <li class="breadcrumb-item active"><?= e($claim['claim_code']) ?></li>
        </ol>
    </nav>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger shadow-sm rounded-3">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= e($error) ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Verification Answers Evaluation Column -->
        <div class="col-lg-7">
            <!-- Confidential Intake Comparison Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-danger-subtle border border-danger-subtle mb-4">
                <h5 class="fw-bold text-danger mb-2"><i class="bi bi-shield-lock-fill me-2"></i> Official Confidential Records (Intake Ground Truth)</h5>
                <p class="small text-muted mb-3">Compare these intake secrets against the answers provided below by the applicant.</p>
                <div class="row g-3 small">
                    <div class="col-md-6">
                        <strong>Confidential Serial Number:</strong><br>
                        <code><?= e($item['serial_number'] ?: 'None Specified') ?></code>
                    </div>
                    <div class="col-md-6">
                        <strong>Secret Markings & Clues:</strong><br>
                        <span><?= e($item['identification_details'] ?: 'None Specified') ?></span>
                    </div>
                    <div class="col-md-6">
                        <strong>Intake Location:</strong><br>
                        <span><?= e($item['location_name']) ?> (<?= e($item['building']) ?>)</span>
                    </div>
                    <div class="col-md-6">
                        <strong>Date Found:</strong><br>
                        <span><?= formatDate($item['date_found'] ?? $item['created_at']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Claimant's Submitted Questionnaire -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-person-lines-fill text-primary me-2"></i> Claimant's Verification Responses</h5>
                    <span class="badge bg-light text-muted border"><?= formatDate($claim['created_at']) ?></span>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold small text-muted text-uppercase mb-1">Reason for Claim</label>
                    <p class="p-3 bg-light rounded-3 text-dark mb-0"><?= nl2br(e($claim['reason'])) ?></p>
                </div>

                <?php if (!empty($claim['questions'])): ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($claim['questions'] as $idx => $qa): ?>
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="fw-bold small text-primary d-block mb-1">Q<?= $idx + 1 ?>: <?= e($qa['question']) ?></span>
                                <p class="mb-0 text-dark"><?= nl2br(e($qa['answer'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Claimant Profile Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <h6 class="fw-bold text-muted small text-uppercase mb-3">Claimant Identification</h6>
                <div class="row g-2 small">
                    <div class="col-sm-6">
                        <strong>Full Name:</strong> <?= e($claim['claimant_name']) ?><br>
                        <strong>University ID:</strong> <code><?= e($claim['claimant_uid']) ?></code>
                    </div>
                    <div class="col-sm-6">
                        <strong>Email:</strong> <?= e($claim['claimant_email']) ?><br>
                        <strong>Phone:</strong> <?= e($claim['claimant_phone'] ?: 'N/A') ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Decision / Handover Column -->
        <div class="col-lg-5">
            <!-- Target Item Summary -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-3 bg-light d-flex align-items-center justify-content-center" style="width: 55px; height: 55px; overflow: hidden;">
                        <?php if (!empty($item['images']) && !empty($item['images'][0]['image_path'])): ?>
                            <img src="<?= APP_URL ?>/uploads/items/<?= e($item['images'][0]['image_path']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <i class="bi bi-box fs-2 text-muted"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark"><?= e($item['title']) ?></h6>
                        <code><?= e($item['reference_code']) ?></code>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <span class="small text-muted">Claim Status:</span>
                    <span><?= getStatusBadge($claim['status']) ?></span>
                </div>
            </div>

            <!-- Officer Decision Form -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-gavel text-primary me-2"></i> Verification Decision</h5>

                <form method="POST" action="">
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Internal Officer Notes</label>
                        <textarea name="officer_notes" rows="2" class="form-control bg-light" placeholder="Verification notes, question validation rationale..."><?= e($claim['officer_notes'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold small text-muted">Rejection Reason (Mandatory if Rejecting)</label>
                        <textarea name="rejection_reason" rows="2" class="form-control bg-light" placeholder="Explain discrepancy to student/staff..."><?= e($claim['rejection_reason'] ?? '') ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" name="decision" value="approve" class="btn btn-success flex-grow-1 py-2.5 rounded-pill fw-bold shadow-sm">
                            <i class="bi bi-check2-circle me-1"></i> Approve Claim
                        </button>
                        <button type="submit" name="decision" value="reject" class="btn btn-danger flex-grow-1 py-2.5 rounded-pill fw-bold shadow-sm">
                            <i class="bi bi-x-circle me-1"></i> Reject Claim
                        </button>
                    </div>
                </form>
            </div>

            <!-- Handover Launcher if Claim Approved -->
            <?php if ($claim['status'] === 'approved'): ?>
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-success text-white">
                    <h5 class="fw-bold mb-1"><i class="bi bi-hand-thumbs-up me-1"></i> Claim Approved</h5>
                    <p class="small text-white-75 mb-3">The applicant may now visit the physical office. Launch the official handover signing console when claimant arrives.</p>
                    <a href="<?= APP_URL ?>/officer/handover.php?item_id=<?= $item['id'] ?>&claim_id=<?= $claim['id'] ?>" class="btn btn-light text-success fw-bold rounded-pill w-100">
                        <i class="bi bi-pen-fill me-1"></i> Launch ID Verification & Signature
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
