<?php
/**
 * Detailed Officer Item Review & Storage Assignment Panel
 */
require_once __DIR__ . '/../includes/role-check.php';
requireRole(['officer', 'admin']);

require_once __DIR__ . '/../classes/Item.php';
require_once __DIR__ . '/../classes/Storage.php';
require_once __DIR__ . '/../classes/Matching.php';
require_once __DIR__ . '/../classes/Database.php';

$itemId = (int)($_GET['id'] ?? 0);
$itemModel = new Item();
$storageModel = new Storage();
$matchModel = new Matching();

$item = $itemModel->findById($itemId, true);

if (!$item) {
    setFlash('danger', 'Report not found.');
    header('Location: ' . APP_URL . '/officer/reports.php');
    exit;
}

$pageTitle = "Review: " . $item['reference_code'];
$error = '';
$storageLocations = $storageModel->getAllLocations();
$currentStorage = $storageModel->getItemStorage($itemId);

// Form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security token invalid.';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'update_status') {
            $newStatus = $_POST['status'] ?? '';
            $notes = $_POST['officer_notes'] ?? '';
            
            if ($itemModel->updateStatus($itemId, $newStatus, (int)$currentUser['id'], $notes)) {
                // If approving a found item, assign storage if requested
                if (!empty($_POST['storage_location_id'])) {
                    $storageModel->assignItem(
                        $itemId,
                        (int)$_POST['storage_location_id'],
                        $_POST['shelf_number'] ?? '',
                        $_POST['box_number'] ?? '',
                        $_POST['position_notes'] ?? '',
                        (int)$currentUser['id']
                    );
                }

                // Trigger rule-based matching
                $matchModel->findMatches($itemId);

                setFlash('success', "Report status updated to '" . strtoupper($newStatus) . "'.");
                header('Location: ' . APP_URL . '/officer/report-view.php?id=' . $itemId);
                exit;
            } else {
                $error = 'Failed to update report status.';
            }
        } elseif ($action === 'assign_storage') {
            $storageLocId = (int)($_POST['storage_location_id'] ?? 0);
            if ($storageLocId > 0) {
                $storageModel->assignItem(
                    $itemId,
                    $storageLocId,
                    $_POST['shelf_number'] ?? '',
                    $_POST['box_number'] ?? '',
                    $_POST['position_notes'] ?? '',
                    (int)$currentUser['id']
                );
                setFlash('success', 'Physical storage location recorded.');
                header('Location: ' . APP_URL . '/officer/report-view.php?id=' . $itemId);
                exit;
            }
        } elseif ($action === 'run_matching') {
            $matchedCount = $matchModel->findMatches($itemId);
            setFlash('info', "Matching engine executed! Identified {$matchedCount} potential matching item(s).");
            header('Location: ' . APP_URL . '/officer/report-view.php?id=' . $itemId);
            exit;
        }
    }
}

// Fetch matches for this item
$matches = $matchModel->getMatchesForItem($itemId);

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/navbar.php';
?>

<div class="container-fluid px-lg-4 py-4">
    <?= renderFlash() ?>

    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>/officer/dashboard.php">Officer Console</a></li>
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>/officer/reports.php">Reports</a></li>
            <li class="breadcrumb-item active"><?= e($item['reference_code']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Item Overview Column -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-primary-subtle text-primary mb-1">
                            <i class="bi <?= e($item['category_icon'] ?? 'bi-box') ?> me-1"></i> <?= e($item['category_name']) ?>
                        </span>
                        <h4 class="fw-bold mb-0 text-dark"><?= e($item['title']) ?></h4>
                        <code><?= e($item['reference_code']) ?></code>
                    </div>
                    <div class="d-flex gap-2">
                        <?= getTypeBadge($item['type']) ?>
                        <?= getStatusBadge($item['status']) ?>
                    </div>
                </div>

                <!-- Photos -->
                <?php if (!empty($item['images'])): ?>
                    <div class="d-flex gap-2 mb-4 overflow-auto pb-2">
                        <?php foreach ($item['images'] as $img): ?>
                            <img src="<?= APP_URL ?>/uploads/items/<?= e($img['image_path']) ?>" class="rounded-3 border" style="width: 120px; height: 120px; object-fit: cover;" onerror="this.src='https://images.unsplash.com/photo-1584438784894-089d6a62b8fa?w=500&auto=format&fit=crop&q=60'">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <h6 class="fw-bold text-muted small text-uppercase">Public Description</h6>
                <p class="text-secondary bg-light p-3 rounded-3 mb-4"><?= nl2br(e($item['description'])) ?></p>

                <!-- Confidential Details -->
                <div class="p-3 bg-danger-subtle border border-danger-subtle rounded-3 mb-4">
                    <h6 class="fw-bold text-danger mb-2"><i class="bi bi-shield-lock-fill me-1"></i> Confidential Verification Clues (Private)</h6>
                    <div class="row g-2 small">
                        <div class="col-sm-6">
                            <strong>Serial / IMEI / Tag:</strong><br>
                            <code><?= e($item['serial_number'] ?: 'None Specified') ?></code>
                        </div>
                        <div class="col-sm-6">
                            <strong>Secret Markings / Details:</strong><br>
                            <span><?= e($item['identification_details'] ?: 'None Specified') ?></span>
                        </div>
                    </div>
                </div>

                <!-- Attributes -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded-3">
                            <small class="text-muted d-block fw-semibold">Brand & Model</small>
                            <span class="fw-bold"><?= e($item['brand'] ?: 'N/A') ?> / <?= e($item['model'] ?: 'N/A') ?></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded-3">
                            <small class="text-muted d-block fw-semibold">Color</small>
                            <span class="fw-bold"><?= e($item['color'] ?: 'N/A') ?></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded-3">
                            <small class="text-muted d-block fw-semibold">Location</small>
                            <span class="fw-bold"><i class="bi bi-geo-alt me-1"></i> <?= e($item['location_name']) ?> (<?= e($item['building']) ?>)</span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded-3">
                            <small class="text-muted d-block fw-semibold">Date <?= ($item['type'] === 'lost') ? 'Lost' : 'Found' ?></small>
                            <span class="fw-bold"><?= formatDate($item['date_lost'] ?? $item['date_found'] ?? $item['created_at']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Reporter Contact Info -->
                <h6 class="fw-bold text-muted small text-uppercase mb-2">Reporter Information</h6>
                <div class="p-3 bg-light rounded-3">
                    <div class="row g-2 small">
                        <div class="col-sm-6">
                            <strong>Name:</strong> <?= e($item['reporter_name'] ?? 'N/A') ?><br>
                            <strong>University ID:</strong> <?= e($item['reporter_uid'] ?? 'N/A') ?>
                        </div>
                        <div class="col-sm-6">
                            <strong>Email:</strong> <?= e($item['reporter_email'] ?? 'N/A') ?><br>
                            <strong>Phone:</strong> <?= e($item['reporter_phone'] ?? 'N/A') ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Matches Found for this item -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-stars text-warning me-1"></i> Rule-Based Similarity Matches</h5>
                    <form method="POST" action="">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="run_matching">
                        <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill">
                            <i class="bi bi-arrow-repeat me-1"></i> Run Matching Engine
                        </button>
                    </form>
                </div>

                <?php if (empty($matches)): ?>
                    <p class="text-muted small mb-0">No similarity matches above 50% threshold computed yet.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($matches as $m): ?>
                            <div class="list-group-item px-0 py-2.5 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="fw-bold mb-0 text-dark"><?= e($m['candidate_title']) ?></h6>
                                    <span class="badge bg-success rounded-pill"><?= round($m['match_score']) ?>% Match</span>
                                </div>
                                <small class="text-muted d-block mb-1">Code: <code><?= e($m['candidate_ref']) ?></code> &bull; <?= e($m['matched_factors']) ?></small>
                                <a href="<?= APP_URL ?>/officer/report-view.php?id=<?= $m['candidate_id'] ?>" class="btn btn-link btn-sm p-0 text-decoration-none">
                                    Inspect candidate record <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action / Duty Column -->
        <div class="col-lg-5">
            <!-- Review Decision Form -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-pencil-square text-primary me-1"></i> Officer Decision Form</h5>

                <form method="POST" action="">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="update_status">

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Update Item Status</label>
                        <select name="status" class="form-select bg-light" required>
                            <option value="pending" <?= ($item['status'] === 'pending') ? 'selected' : '' ?>>Pending Review</option>
                            <option value="approved" <?= ($item['status'] === 'approved') ? 'selected' : '' ?>>Approved (Publish to Directory)</option>
                            <option value="available" <?= ($item['status'] === 'available') ? 'selected' : '' ?>>Available in Office Storage</option>
                            <option value="ready_for_handover" <?= ($item['status'] === 'ready_for_handover') ? 'selected' : '' ?>>Ready for Physical Handover</option>
                            <option value="returned" <?= ($item['status'] === 'returned') ? 'selected' : '' ?>>Returned to Owner</option>
                            <option value="rejected" <?= ($item['status'] === 'rejected') ? 'selected' : '' ?>>Rejected / Spam</option>
                            <option value="disposed" <?= ($item['status'] === 'disposed') ? 'selected' : '' ?>>Disposed / Donated</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Officer Notes & Justification</label>
                        <textarea name="officer_notes" rows="3" class="form-control bg-light" placeholder="Internal notes, reason for status change, remarks..."><?= e($item['officer_notes'] ?? '') ?></textarea>
                    </div>

                    <!-- Storage allocation for found items -->
                    <?php if ($item['type'] === 'found'): ?>
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-archive text-info me-1"></i> Assign Physical Storage Location</h6>
                            <div class="mb-2">
                                <label class="form-label small text-muted mb-0">Storage Hub</label>
                                <select name="storage_location_id" class="form-select form-select-sm bg-white">
                                    <option value="">No physical storage assigned</option>
                                    <?php foreach ($storageLocations as $sl): ?>
                                        <option value="<?= $sl['id'] ?>" <?= ($currentStorage && $currentStorage['storage_location_id'] == $sl['id']) ? 'selected' : '' ?>>
                                            <?= e($sl['name']) ?> (<?= e($sl['room_number']) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="text" name="shelf_number" class="form-control form-control-sm bg-white" placeholder="Shelf (e.g. S-02)" value="<?= e($currentStorage['shelf_number'] ?? '') ?>">
                                </div>
                                <div class="col-6">
                                    <input type="text" name="box_number" class="form-control form-control-sm bg-white" placeholder="Box (e.g. B-14)" value="<?= e($currentStorage['box_number'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <input type="text" name="position_notes" class="form-control form-control-sm bg-white" placeholder="Position notes..." value="<?= e($currentStorage['position_notes'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-3 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Save Officer Action
                    </button>
                </form>
            </div>

            <!-- Quick Handover Link if ready -->
            <?php if ($item['status'] === 'ready_for_handover'): ?>
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-success text-white">
                    <h5 class="fw-bold mb-1"><i class="bi bi-hand-thumbs-up me-1"></i> Ready for Handover</h5>
                    <p class="small text-white-75 mb-3">Owner claim has been approved. Proceed to ID verification and signature.</p>
                    <a href="<?= APP_URL ?>/officer/handover.php?item_id=<?= $item['id'] ?>" class="btn btn-light text-success fw-bold rounded-pill w-100">
                        Launch Handover Form <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
