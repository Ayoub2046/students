<?php
/**
 * Item Details View with QR Code and Claim Gateway
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Item.php';
require_once __DIR__ . '/classes/Auth.php';

$ref = $_GET['ref'] ?? '';
$id = (int)($_GET['id'] ?? 0);

$itemModel = new Item();
$isStaff = Auth::check() && in_array(Auth::role(), ['admin', 'officer']);
$currentUserId = Auth::id();

$item = null;
if (!empty($ref)) {
    $item = $itemModel->findByReference($ref, $isStaff);
} elseif ($id > 0) {
    $item = $itemModel->findById($id, $isStaff);
}

if (!$item) {
    setFlash('danger', 'Item not found.');
    header('Location: ' . APP_URL . '/items.php');
    exit;
}

$pageTitle = $item['title'] . " (" . $item['reference_code'] . ")";
$isOwner = Auth::check() && ($currentUserId == $item['reported_by']);

// Can claim? Item must be found, available/claimed status, and not reported by the current user
$canClaim = Auth::check() && ($item['type'] === 'found') && in_array($item['status'], ['available', 'claimed', 'under_verification']) && !$isOwner;

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-4">
    <?= renderFlash() ?>

    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>/index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="<?= APP_URL ?>/items.php">Browse Items</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= e($item['reference_code']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Left Column: Images and QR Code -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
                <!-- Main Image Preview -->
                <div class="position-relative bg-light text-center p-2" style="min-height: 280px; display: flex; align-items: center; justify-content: center;">
                    <?php if (!empty($item['images']) && !empty($item['images'][0]['image_path'])): ?>
                        <img id="mainItemImage" src="<?= APP_URL ?>/uploads/items/<?= e($item['images'][0]['image_path']) ?>" class="img-fluid rounded-3 shadow-sm" style="max-height: 340px; object-fit: contain;" onerror="this.src='https://images.unsplash.com/photo-1584438784894-089d6a62b8fa?w=500&auto=format&fit=crop&q=60'">
                    <?php else: ?>
                        <div class="text-muted p-5">
                            <i class="bi <?= e($item['category_icon'] ?? 'bi-box') ?> display-1"></i>
                            <p class="mt-2 mb-0">No Photographs Provided</p>
                        </div>
                    <?php endif; ?>
                    <span class="position-absolute top-0 start-0 m-3">
                        <?= getTypeBadge($item['type']) ?>
                    </span>
                    <span class="position-absolute top-0 end-0 m-3">
                        <?= getStatusBadge($item['status']) ?>
                    </span>
                </div>

                <!-- Image Thumbnails (if multiple) -->
                <?php if (!empty($item['images']) && count($item['images']) > 1): ?>
                    <div class="card-footer bg-white border-0 p-3 d-flex gap-2 overflow-auto">
                        <?php foreach ($item['images'] as $img): ?>
                            <img src="<?= APP_URL ?>/uploads/items/<?= e($img['image_path']) ?>" class="rounded border p-1 cursor-pointer" style="width: 60px; height: 60px; object-fit: cover;" onclick="document.getElementById('mainItemImage').src = this.src;">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- QR Code Tag Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white text-center">
                <h6 class="fw-bold mb-2"><i class="bi bi-qr-code me-1 text-primary"></i> Official Verification QR Code</h6>
                <p class="small text-muted mb-3">Scan with any mobile camera to view item status and claim details directly</p>
                <div id="itemQRCode" class="d-flex justify-content-center mb-3"></div>
                <code class="fw-bold fs-6 text-primary d-block mb-3"><?= e($item['reference_code']) ?></code>
                <div class="d-flex justify-content-center gap-2">
                    <button class="btn btn-outline-primary btn-sm rounded-pill" onclick="window.print();">
                        <i class="bi bi-printer me-1"></i> Print QR Tag
                    </button>
                </div>
            </div>
        </div>

        <!-- Right Column: Full Specifications & Actions -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-4.5 bg-white mb-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <span class="badge bg-primary-subtle text-primary mb-1">
                            <i class="bi <?= e($item['category_icon'] ?? 'bi-tag') ?> me-1"></i> <?= e($item['category_name']) ?>
                        </span>
                        <h3 class="fw-bold text-dark mb-1"><?= e($item['title']) ?></h3>
                        <small class="text-muted">Registered On <?= formatDateTime($item['created_at']) ?></small>
                    </div>
                </div>

                <hr class="my-3 text-muted opacity-25">

                <!-- Description -->
                <div class="mb-4">
                    <h6 class="fw-bold text-muted small text-uppercase">Public Description</h6>
                    <p class="text-secondary leading-relaxed mb-0"><?= nl2br(e($item['description'])) ?></p>
                </div>

                <!-- Key Attributes Table -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded-3">
                            <small class="text-muted d-block fw-semibold">Brand / Manufacturer</small>
                            <span class="fw-bold text-dark"><?= e($item['brand'] ?: 'Not Specified') ?></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded-3">
                            <small class="text-muted d-block fw-semibold">Model / Edition</small>
                            <span class="fw-bold text-dark"><?= e($item['model'] ?: 'Not Specified') ?></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded-3">
                            <small class="text-muted d-block fw-semibold">Primary Color</small>
                            <span class="fw-bold text-dark"><?= e($item['color'] ?: 'Not Specified') ?></span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 bg-light rounded-3">
                            <small class="text-muted d-block fw-semibold">Date <?= ($item['type'] === 'lost') ? 'Lost' : 'Found' ?></small>
                            <span class="fw-bold text-dark"><?= formatDate($item['date_lost'] ?? $item['date_found'] ?? $item['created_at']) ?></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 bg-light rounded-3">
                            <small class="text-muted d-block fw-semibold">Campus Location</small>
                            <span class="fw-bold text-dark"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= e($item['location_name']) ?> (<?= e($item['building'] ?? 'Main Campus') ?>)</span>
                        </div>
                    </div>
                </div>

                <!-- Authorized Officer / Staff Details -->
                <?php if ($isStaff): ?>
                    <div class="card bg-info-subtle border-info-subtle p-3 mb-4 rounded-3">
                        <h6 class="fw-bold text-info-emphasis mb-2"><i class="bi bi-shield-check me-1"></i> Officer Confidential Metadata</h6>
                        <ul class="list-unstyled small mb-0">
                            <li><strong>Reporter Name:</strong> <?= e($item['reporter_name'] ?? 'N/A') ?> (<?= e($item['reporter_uid'] ?? '') ?>)</li>
                            <li><strong>Reporter Contact:</strong> <?= e($item['reporter_email'] ?? '') ?> | <?= e($item['reporter_phone'] ?? 'N/A') ?></li>
                            <li><strong>Confidential Serial/IMEI:</strong> <code><?= e($item['serial_number'] ?? 'None specified') ?></code></li>
                            <li><strong>Secret Clues / Markings:</strong> <?= e($item['identification_details'] ?? 'None specified') ?></li>
                            <?php if (!empty($item['storage_name'])): ?>
                                <li><strong>Physical Storage:</strong> <?= e($item['storage_name']) ?> &bull; Room: <?= e($item['storage_room']) ?> &bull; Shelf: <?= e($item['storage_shelf']) ?> &bull; Box: <?= e($item['storage_box']) ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Action Gateway -->
                <div class="d-flex flex-wrap gap-2 pt-2">
                    <?php if ($canClaim): ?>
                        <a href="<?= APP_URL ?>/claim-item.php?item_id=<?= $item['id'] ?>" class="btn btn-warning btn-lg px-4 py-2.5 rounded-pill fw-bold text-dark shadow-sm flex-grow-1">
                            <i class="bi bi-shield-fill-check me-2"></i> I THINK THIS IS MINE (Submit Claim)
                        </a>
                    <?php elseif (!Auth::check()): ?>
                        <a href="<?= APP_URL ?>/login.php" class="btn btn-primary btn-lg px-4 py-2.5 rounded-pill fw-bold shadow-sm flex-grow-1">
                            <i class="bi bi-box-arrow-in-right me-2"></i> Sign In to Claim This Item
                        </a>
                    <?php endif; ?>

                    <?php if (Auth::check() && !$isOwner): ?>
                        <a href="<?= APP_URL ?>/messages.php?item_id=<?= $item['id'] ?>" class="btn btn-outline-secondary btn-lg px-4 py-2.5 rounded-pill">
                            <i class="bi bi-chat-dots me-1"></i> Send Officer Inquiry
                        </a>
                    <?php endif; ?>

                    <?php if ($isStaff): ?>
                        <a href="<?= APP_URL ?>/officer/report-view.php?id=<?= $item['id'] ?>" class="btn btn-info btn-lg px-4 py-2.5 rounded-pill text-white fw-semibold">
                            <i class="bi bi-pencil-square me-1"></i> Officer Review Panel
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const itemUrl = "<?= APP_URL ?>/item-details.php?ref=<?= e($item['reference_code']) ?>";
    generateItemQR('itemQRCode', itemUrl);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
