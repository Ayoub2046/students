<?php
/**
 * Unclaimed Items (90+ Days Holding Period Expiration) Management
 */
require_once __DIR__ . '/../includes/role-check.php';
requireRole(['officer', 'admin']);

require_once __DIR__ . '/../classes/Item.php';
require_once __DIR__ . '/../classes/AuditLog.php';
require_once __DIR__ . '/../classes/Database.php';

$pageTitle = "Unclaimed Items Management (90d+)";
$pdo = Database::getInstance()->getConnection();
$itemModel = new Item();

// Handle Disposition Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['disposition_action'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $itemId = (int)$_POST['item_id'];
        $action = $_POST['disposition_type'];
        $notes = $_POST['disposition_notes'] ?? '';

        $newStatus = ($action === 'extend') ? 'available' : 'disposed';
        $auditAction = "unclaimed_{$action}";

        $upd = $pdo->prepare("UPDATE items SET status = :st, officer_notes = CONCAT(COALESCE(officer_notes,''), '\n[', NOW(), '] Disposition: ', :n), updated_at = NOW() WHERE id = :id");
        $upd->execute([
            'st' => $newStatus,
            'n'  => "{$action} - {$notes}",
            'id' => $itemId
        ]);

        // If disposed/donated, clear storage
        if ($newStatus === 'disposed') {
            $pdo->prepare("DELETE FROM item_storage WHERE item_id = :id")->execute(['id' => $itemId]);
        }

        AuditLog::log($auditAction, 'items', $itemId, "Officer executed {$action} on expired unclaimed item. Notes: {$notes}");

        setFlash('success', "Item disposition recorded: " . strtoupper($action));
        header('Location: ' . APP_URL . '/officer/unclaimed.php');
        exit;
    }
}

// Fetch items older than 90 days that are found and still unreturned
$unclaimedSql = "SELECT i.*, c.name AS category_name, l.name AS location_name,
                        DATEDIFF(NOW(), i.created_at) AS days_elapsed,
                        sl.name AS storage_name, sl.room_number, s.shelf_number, s.box_number
                 FROM items i
                 LEFT JOIN categories c ON i.category_id = c.id
                 LEFT JOIN locations l ON i.location_id = l.id
                 LEFT JOIN item_storage s ON i.id = s.item_id
                 LEFT JOIN storage_locations sl ON s.storage_location_id = sl.id
                 WHERE i.type = 'found' 
                   AND i.status IN ('available', 'approved')
                   AND DATEDIFF(NOW(), i.created_at) >= 90
                 ORDER BY days_elapsed DESC";
$unclaimedItems = $pdo->query($unclaimedSql)->fetchAll();

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
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-danger-subtle text-danger border px-3 py-1 rounded-pill fw-bold text-uppercase mb-1">
                            90-Day Retention Expiry Policy
                        </span>
                        <h4 class="fw-bold text-dark mb-0">Unclaimed Items Disposition Desk</h4>
                    </div>
                    <span class="badge bg-dark fs-6 px-3 py-2 rounded-pill">
                        <?= count($unclaimedItems) ?> Items Expired
                    </span>
                </div>
            </div>

            <!-- Unclaimed Items Table -->
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Item & Code</th>
                                <th>Category</th>
                                <th>Holding Location</th>
                                <th>Days in Custody</th>
                                <th>Date Logged</th>
                                <th class="text-end pe-4">Disposition Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($unclaimedItems)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-check2-circle fs-1 text-success d-block mb-2"></i>
                                        No items currently exceed the 90-day retention threshold.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($unclaimedItems as $ui): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-bold text-dark d-block"><?= e($ui['title']) ?></span>
                                            <code><?= e($ui['reference_code']) ?></code>
                                        </td>
                                        <td><span class="small"><?= e($ui['category_name']) ?></span></td>
                                        <td>
                                            <?php if (!empty($ui['storage_name'])): ?>
                                                <small class="d-block fw-semibold"><?= e($ui['storage_name']) ?> (<?= e($ui['room_number']) ?>)</small>
                                                <small class="text-muted">Shelf: <?= e($ui['shelf_number']) ?> | Box: <?= e($ui['box_number']) ?></small>
                                            <?php else: ?>
                                                <span class="text-muted small">Not assigned</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-danger-subtle text-danger fw-bold rounded-pill px-2.5 py-1">
                                                <?= $ui['days_elapsed'] ?> Days
                                            </span>
                                        </td>
                                        <td><span class="small text-muted"><?= formatDate($ui['created_at']) ?></span></td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-outline-danger btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#disposeModal<?= $ui['id'] ?>">
                                                <i class="bi bi-gear-fill me-1"></i> Resolve Item
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Disposition Modal for this item -->
                                    <div class="modal fade" id="disposeModal<?= $ui['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content rounded-4 border-0">
                                                <div class="modal-header bg-danger text-white p-3.5">
                                                    <h5 class="modal-title fw-bold">Unclaimed Item Resolution</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="">
                                                    <?= csrfField() ?>
                                                    <input type="hidden" name="disposition_action" value="1">
                                                    <input type="hidden" name="item_id" value="<?= $ui['id'] ?>">

                                                    <div class="modal-body p-4">
                                                        <div class="p-3 bg-light rounded-3 mb-3">
                                                            <strong><?= e($ui['title']) ?></strong> (<code><?= e($ui['reference_code']) ?></code>)<br>
                                                            <small class="text-muted">Held in storage for <?= $ui['days_elapsed'] ?> consecutive days without verified claim.</small>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold small text-muted">Select Resolution Disposition *</label>
                                                            <select name="disposition_type" class="form-select bg-light" required>
                                                                <option value="donated">Donate to Campus Student Charity / Foundation</option>
                                                                <option value="auctioned">Transfer to Annual University Surplus Auction</option>
                                                                <option value="recycled">Eco-Recycle / Electronic Waste Disposal</option>
                                                                <option value="destroyed">Destroy / Secure Disposal (For ID Cards, Keys)</option>
                                                                <option value="extend">Extend Holding Window (+30 Days Grace)</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold small text-muted">Officer Justification / Tracking Reference</label>
                                                            <textarea name="disposition_notes" rows="2" class="form-control bg-light" placeholder="Charity receipt #, surplus batch tag, authorization notes..."></textarea>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer p-3 bg-light border-0">
                                                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold">Authorize Action</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
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
