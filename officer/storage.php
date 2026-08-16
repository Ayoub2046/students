<?php
/**
 * Officer Physical Storage & Inventory Tracker
 */
require_once __DIR__ . '/../includes/role-check.php';
requireRole(['officer', 'admin']);

require_once __DIR__ . '/../classes/Storage.php';
require_once __DIR__ . '/../classes/Database.php';

$pageTitle = "Physical Storage & Custody";
$storageModel = new Storage();
$pdo = Database::getInstance()->getConnection();

$locations = $storageModel->getAllLocations();
$filterLocId = (int)($_GET['location_id'] ?? 0);

// Add new storage location
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_location'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $ins = $pdo->prepare("INSERT INTO storage_locations (name, building, room_number, capacity, description) VALUES (:n, :b, :r, :c, :d)");
        $ins->execute([
            'n' => $_POST['name'],
            'b' => $_POST['building'],
            'r' => $_POST['room_number'],
            'c' => (int)$_POST['capacity'],
            'd' => $_POST['description'] ?? ''
        ]);
        setFlash('success', 'New storage facility registered.');
        header('Location: ' . APP_URL . '/officer/storage.php');
        exit;
    }
}

// Fetch stored items query
$itemsSql = "SELECT s.*, i.title AS item_title, i.reference_code, i.status AS item_status, 
                    sl.name AS storage_name, sl.building, sl.room_number
             FROM item_storage s
             JOIN items i ON s.item_id = i.id
             JOIN storage_locations sl ON s.storage_location_id = sl.id
             WHERE 1=1";
if ($filterLocId > 0) {
    $itemsSql .= " AND s.storage_location_id = {$filterLocId}";
}
$itemsSql .= " ORDER BY s.assigned_at DESC";
$storedItems = $pdo->query($itemsSql)->fetchAll();

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
                        <h4 class="fw-bold mb-1">Physical Custody & Storage Tracking</h4>
                        <p class="text-muted small mb-0">Track shelf, box, and room assignments for all verified found inventory items.</p>
                    </div>
                    <button class="btn btn-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addStorageModal">
                        <i class="bi bi-plus-lg me-1"></i> Register Storage Room
                    </button>
                </div>
            </div>

            <!-- Storage Hubs Cards -->
            <div class="row g-3 mb-4">
                <?php foreach ($locations as $loc): ?>
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 <?= ($filterLocId == $loc['id']) ? 'border border-2 border-primary' : '' ?>">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-0 text-dark"><?= e($loc['name']) ?></h6>
                                <span class="badge bg-light text-primary border rounded-pill">Room <?= e($loc['room_number']) ?></span>
                            </div>
                            <small class="text-muted d-block mb-3"><?= e($loc['building']) ?></small>
                            <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                                <span class="small text-muted">Occupancy: <strong><?= $loc['occupied_count'] ?> / <?= $loc['capacity'] ?></strong></span>
                                <a href="?location_id=<?= $loc['id'] ?>" class="btn btn-outline-primary btn-sm rounded-pill" style="font-size: 0.75rem;">
                                    View Items
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Stored Items Table -->
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white border-0 p-3.5 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">Stored Items Inventory (<?= count($storedItems) ?>)</h5>
                    <?php if ($filterLocId > 0): ?>
                        <a href="<?= APP_URL ?>/officer/storage.php" class="btn btn-outline-secondary btn-sm rounded-pill">Clear Filter</a>
                    <?php endif; ?>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Item Code</th>
                                <th>Title</th>
                                <th>Room & Facility</th>
                                <th>Shelf #</th>
                                <th>Box #</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($storedItems)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-archive fs-1 d-block mb-2"></i>
                                        No items registered in this storage facility.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($storedItems as $it): ?>
                                    <tr>
                                        <td class="ps-4"><code><?= e($it['reference_code']) ?></code></td>
                                        <td class="fw-bold text-dark"><?= e($it['item_title']) ?></td>
                                        <td>
                                            <span class="small d-block fw-semibold"><?= e($it['storage_name']) ?></span>
                                            <small class="text-muted">Room <?= e($it['room_number']) ?></small>
                                        </td>
                                        <td><span class="badge bg-light text-dark border"><?= e($it['shelf_number'] ?: 'N/A') ?></span></td>
                                        <td><span class="badge bg-light text-dark border"><?= e($it['box_number'] ?: 'N/A') ?></span></td>
                                        <td><?= getStatusBadge($it['item_status']) ?></td>
                                        <td class="text-end pe-4">
                                            <a href="<?= APP_URL ?>/officer/report-view.php?id=<?= $it['item_id'] ?>" class="btn btn-light btn-sm rounded-pill">
                                                <i class="bi bi-pencil me-1"></i> Edit Location
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

<!-- Modal: Add Storage Location -->
<div class="modal fade" id="addStorageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header bg-primary text-white p-3.5">
                <h5 class="modal-title fw-bold">Register Storage Facility</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <?= csrfField() ?>
                <input type="hidden" name="add_location" value="1">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Facility Name *</label>
                        <input type="text" name="name" class="form-control bg-light" placeholder="e.g. Student Center Vault A" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">Building *</label>
                            <input type="text" name="building" class="form-control bg-light" placeholder="Student Center" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">Room Number *</label>
                            <input type="text" name="room_number" class="form-control bg-light" placeholder="Room 104" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Holding Capacity</label>
                        <input type="number" name="capacity" class="form-control bg-light" value="100" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Notes / Access Instructions</label>
                        <textarea name="description" rows="2" class="form-control bg-light" placeholder="Security key code, lockbox combinations..."></textarea>
                    </div>
                </div>
                <div class="modal-footer p-3 bg-light border-0">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Save Facility</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
