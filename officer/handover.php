<?php
/**
 * Officer Physical Handover & Digital Signature Console
 */
require_once __DIR__ . '/../includes/role-check.php';
requireRole(['officer', 'admin']);

require_once __DIR__ . '/../classes/Item.php';
require_once __DIR__ . '/../classes/Claim.php';
require_once __DIR__ . '/../classes/AuditLog.php';
require_once __DIR__ . '/../classes/Notification.php';
require_once __DIR__ . '/../classes/Database.php';

$pageTitle = "Physical Handover & Verification";
$pdo = Database::getInstance()->getConnection();
$itemModel = new Item();
$claimModel = new Claim();

$itemId = (int)($_GET['item_id'] ?? 0);
$claimId = (int)($_GET['claim_id'] ?? 0);

$item = null;
$claim = null;

if ($itemId > 0) {
    $item = $itemModel->findById($itemId, true);
    if ($claimId <= 0) {
        $stmt = $pdo->prepare("SELECT id FROM claims WHERE item_id = :iid AND status IN ('approved', 'pending') ORDER BY id DESC LIMIT 1");
        $stmt->execute(['iid' => $itemId]);
        $claimId = (int)$stmt->fetchColumn();
    }
}

if ($claimId > 0) {
    $claim = $claimModel->findById($claimId);
    if (!$item && $claim) {
        $item = $itemModel->findById($claim['item_id'], true);
    }
}

$error = '';
$receipt = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complete_handover'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please reload.';
    } elseif (empty($_POST['recipient_name']) || empty($_POST['id_type']) || empty($_POST['id_number'])) {
        $error = 'Recipient verification fields are mandatory.';
    } else {
        $postItemId = (int)$_POST['item_id'];
        $postClaimId = !empty($_POST['claim_id']) ? (int)$_POST['claim_id'] : null;
        $sigData = $_POST['signature_data'] ?? '';

        // Save digital signature image if present
        $sigFilename = null;
        if (!empty($sigData) && str_starts_with($sigData, 'data:image')) {
            $data = explode(',', $sigData);
            $decoded = base64_decode($data[1] ?? '');
            if ($decoded) {
                $sigFilename = 'sig_' . time() . '_' . uniqid() . '.png';
                file_put_contents(UPLOAD_PATH_ITEMS . '/' . $sigFilename, $decoded);
            }
        }

        $handoverSql = "INSERT INTO handovers (item_id, claim_id, officer_id, recipient_name, recipient_id_type, recipient_id_number, recipient_phone, signature_image, notes)
                        VALUES (:it, :cl, :of, :rn, :itp, :inm, :rp, :sig, :nt)";
        $hStmt = $pdo->prepare($handoverSql);
        $hStmt->execute([
            'it'  => $postItemId,
            'cl'  => $postClaimId,
            'of'  => $currentUser['id'],
            'rn'  => $_POST['recipient_name'],
            'itp' => $_POST['id_type'],
            'inm' => $_POST['id_number'],
            'rp'  => $_POST['recipient_phone'] ?? '',
            'sig' => $sigFilename,
            'nt'  => $_POST['handover_notes'] ?? ''
        ]);
        $handoverId = $pdo->lastInsertId();

        // Update item status to 'returned'
        $itemModel->updateStatus($postItemId, 'returned', (int)$currentUser['id'], 'Item physically returned to verified recipient: ' . $_POST['recipient_name']);

        // Update claim status to 'completed' if exists
        if ($postClaimId) {
            $pdo->prepare("UPDATE claims SET status = 'completed', updated_at = NOW() WHERE id = :id")->execute(['id' => $postClaimId]);
            
            // Notify claimant
            if ($claim) {
                $notif = new Notification();
                $notif->send(
                    (int)$claim['claimant_id'],
                    'Item Handover Completed',
                    "Physical handover for {$item['title']} ({$item['reference_code']}) has been successfully completed and recorded in the university registry.",
                    'completed',
                    "/item-details.php?ref={$item['reference_code']}"
                );
            }
        }

        // Release storage
        $pdo->prepare("DELETE FROM item_storage WHERE item_id = :id")->execute(['id' => $postItemId]);

        // Audit Log
        AuditLog::log('item_handed_over', 'items', $postItemId, "Officer handed item to {$_POST['recipient_name']} (ID: {$_POST['id_number']})");

        setFlash('success', 'Handover successfully recorded! Official handover receipt generated.');
        header('Location: ' . APP_URL . '/officer/handover.php?receipt_id=' . $handoverId);
        exit;
    }
}

// Fetch receipt if viewing
if (isset($_GET['receipt_id'])) {
    $rStmt = $pdo->prepare("SELECT h.*, i.title AS item_title, i.reference_code, i.brand, i.model, i.color, 
                                   u.full_name AS officer_name, u.university_id AS officer_uid
                            FROM handovers h
                            JOIN items i ON h.item_id = i.id
                            JOIN users u ON h.officer_id = u.id
                            WHERE h.id = :id");
    $rStmt->execute(['id' => (int)$_GET['receipt_id']]);
    $receipt = $rStmt->fetch();
}

// Fetch recent handovers
$recentHandovers = $pdo->query("SELECT h.*, i.title AS item_title, i.reference_code, u.full_name AS officer_name
                                FROM handovers h
                                JOIN items i ON h.item_id = i.id
                                JOIN users u ON h.officer_id = u.id
                                ORDER BY h.created_at DESC LIMIT 10")->fetchAll();

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
            <?php if ($receipt): ?>
                <!-- Official Printable Handover Receipt -->
                <div class="card border-0 shadow-lg rounded-4 p-4 p-md-5 bg-white mb-4" id="receiptPrintArea">
                    <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                        <div>
                            <span class="badge bg-success-subtle text-success border px-3 py-1 rounded-pill fw-bold text-uppercase mb-2">
                                Official Transfer Certificate
                            </span>
                            <h3 class="fw-bold text-dark mb-0">University Lost & Found Handover Receipt</h3>
                            <p class="text-muted small mb-0">Receipt ID: #HO-<?= str_pad($receipt['id'], 6, '0', STR_PAD_LEFT) ?> &bull; Issued <?= formatDateTime($receipt['created_at']) ?></p>
                        </div>
                        <button class="btn btn-outline-primary rounded-pill px-4" onclick="window.print();">
                            <i class="bi bi-printer me-1"></i> Print Receipt
                        </button>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <h6 class="fw-bold text-primary mb-2">Item Information</h6>
                                <p class="mb-1"><strong>Title:</strong> <?= e($receipt['item_title']) ?></p>
                                <p class="mb-1"><strong>Reference Code:</strong> <code><?= e($receipt['reference_code']) ?></code></p>
                                <p class="mb-0"><strong>Brand & Color:</strong> <?= e($receipt['brand']) ?> &bull; <?= e($receipt['color']) ?></p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <h6 class="fw-bold text-primary mb-2">Recipient Verification</h6>
                                <p class="mb-1"><strong>Full Name:</strong> <?= e($receipt['recipient_name']) ?></p>
                                <p class="mb-1"><strong>ID Type:</strong> <?= e($receipt['recipient_id_type']) ?> (<?= e($receipt['recipient_id_number']) ?>)</p>
                                <p class="mb-0"><strong>Phone:</strong> <?= e($receipt['recipient_phone'] ?: 'N/A') ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <h6 class="fw-bold text-muted small text-uppercase mb-1">Authorizing Officer</h6>
                                <p class="fw-bold mb-0"><?= e($receipt['officer_name']) ?> (<?= e($receipt['officer_uid']) ?>)</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <h6 class="fw-bold text-muted small text-uppercase mb-2">Recipient Digital Signature</h6>
                                <?php if (!empty($receipt['signature_image'])): ?>
                                    <img src="<?= APP_URL ?>/uploads/items/<?= e($receipt['signature_image']) ?>" style="max-height: 70px; max-width: 220px;" class="border-bottom pb-1">
                                <?php else: ?>
                                    <span class="text-muted small">Signature on Physical Paper File</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-secondary small text-muted mb-0">
                        <i class="bi bi-info-circle me-1"></i> By receiving this item, the recipient confirms that the described property has been returned in satisfactory condition and releases the University Lost & Found Office from further custodial liability.
                    </div>
                </div>
            <?php endif; ?>

            <!-- New Handover Form -->
            <?php if ($item): ?>
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                    <div class="bg-success p-4 text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h4 class="fw-bold mb-1">Execute Physical Handover</h4>
                                <p class="small text-white-75 mb-0">Verify recipient identity and collect their digital signature</p>
                            </div>
                            <span class="badge bg-white text-success fs-6 fw-bold px-3 py-1.5 rounded-pill">
                                <?= e($item['reference_code']) ?>
                            </span>
                        </div>
                    </div>

                    <div class="card-body p-4 p-md-4.5 bg-white">
                        <form method="POST" action="<?= APP_URL ?>/officer/handover.php">
                            <?= csrfField() ?>
                            <input type="hidden" name="complete_handover" value="1">
                            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                            <input type="hidden" name="claim_id" value="<?= $claim ? $claim['id'] : '' ?>">
                            <input type="hidden" name="signature_data" id="signatureDataInput">

                            <div class="row g-3 mb-4">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-muted">Recipient Full Name *</label>
                                    <input type="text" name="recipient_name" class="form-control bg-light" required value="<?= e($claim['claimant_name'] ?? '') ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small text-muted">ID Document Type *</label>
                                    <select name="id_type" class="form-select bg-light" required>
                                        <option value="Student ID Card">Student ID Card</option>
                                        <option value="Staff ID Card">Staff ID Card</option>
                                        <option value="National Driver License">Driver's License</option>
                                        <option value="Passport">Passport / Govt ID</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold small text-muted">ID / Card Number *</label>
                                    <input type="text" name="id_number" class="form-control bg-light" required placeholder="STU-2026-..." value="<?= e($claim['claimant_uid'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-muted">Contact Phone Number</label>
                                    <input type="tel" name="recipient_phone" class="form-control bg-light" placeholder="+1 (555) 000-0000" value="<?= e($claim['claimant_phone'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-muted">Handover Remarks</label>
                                    <input type="text" name="handover_notes" class="form-control bg-light" placeholder="Handed over in Room 104 without damages...">
                                </div>
                            </div>

                            <!-- Signature Canvas -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold small text-muted d-flex justify-content-between">
                                    <span>Recipient Digital Signature * (Draw on touchscreen or with mouse)</span>
                                    <button type="button" class="btn btn-link btn-sm text-danger p-0" id="clearSignatureBtn">Clear Canvas</button>
                                </label>
                                <div class="border rounded-4 bg-light p-2" style="max-width: 480px;">
                                    <canvas id="signatureCanvas" width="460" height="150" class="bg-white rounded-3 w-100"></canvas>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                                <a href="<?= APP_URL ?>/officer/dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                                <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm" id="submitHandoverBtn">
                                    <i class="bi bi-check2-circle me-1"></i> Finalize Handover & Issue Receipt
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Recent Handovers Registry -->
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white border-0 p-3.5">
                    <h5 class="fw-bold mb-0 text-dark">Recent Handover Records (Audit Trail)</h5>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Receipt #</th>
                                <th>Item Title</th>
                                <th>Recipient</th>
                                <th>Handed Over By</th>
                                <th>Date & Time</th>
                                <th class="text-end pe-4">Receipt</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentHandovers)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        No handover records logged yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentHandovers as $ho): ?>
                                    <tr>
                                        <td class="ps-4"><code>#HO-<?= str_pad($ho['id'], 6, '0', STR_PAD_LEFT) ?></code></td>
                                        <td>
                                            <span class="fw-bold text-dark d-block"><?= e($ho['item_title']) ?></span>
                                            <code class="small text-muted"><?= e($ho['reference_code']) ?></code>
                                        </td>
                                        <td>
                                            <span class="small fw-semibold d-block"><?= e($ho['recipient_name']) ?></span>
                                            <small class="text-muted"><?= e($ho['recipient_id_type']) ?> (<?= e($ho['recipient_id_number']) ?>)</small>
                                        </td>
                                        <td><span class="small"><?= e($ho['officer_name']) ?></span></td>
                                        <td><span class="small text-muted"><?= formatDateTime($ho['created_at']) ?></span></td>
                                        <td class="text-end pe-4">
                                            <a href="?receipt_id=<?= $ho['id'] ?>" class="btn btn-outline-primary btn-sm rounded-pill">
                                                <i class="bi bi-receipt me-1"></i> View Receipt
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('signatureCanvas');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    let isDrawing = false;

    ctx.strokeStyle = "#1e293b";
    ctx.lineWidth = 2;
    ctx.lineCap = "round";

    function getPos(e) {
        const rect = canvas.getBoundingClientRect();
        const clientX = e.clientX || (e.touches && e.touches[0].clientX);
        const clientY = e.clientY || (e.touches && e.touches[0].clientY);
        return {
            x: (clientX - rect.left) * (canvas.width / rect.width),
            y: (clientY - rect.top) * (canvas.height / rect.height)
        };
    }

    function startDraw(e) {
        isDrawing = true;
        const pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
        e.preventDefault();
    }

    function draw(e) {
        if (!isDrawing) return;
        const pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        e.preventDefault();
    }

    function stopDraw() {
        if (isDrawing) {
            isDrawing = false;
            document.getElementById('signatureDataInput').value = canvas.toDataURL('image/png');
        }
    }

    canvas.addEventListener('mousedown', startDraw);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDraw);
    canvas.addEventListener('mouseleave', stopDraw);

    canvas.addEventListener('touchstart', startDraw);
    canvas.addEventListener('touchmove', draw);
    canvas.addEventListener('touchend', stopDraw);

    document.getElementById('clearSignatureBtn')?.addEventListener('click', function () {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        document.getElementById('signatureDataInput').value = '';
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
