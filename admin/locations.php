<?php
/**
 * Admin Campus Locations Management
 */
require_once __DIR__ . '/../includes/role-check.php';
requireRole('admin');

require_once __DIR__ . '/../classes/AuditLog.php';
require_once __DIR__ . '/../classes/Database.php';

$pageTitle = "Campus Locations & Geo Points";
$pdo = Database::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        if (isset($_POST['add_location'])) {
            $ins = $pdo->prepare("INSERT INTO locations (name, building, campus, latitude, longitude, description) VALUES (:n, :b, :c, :lat, :lng, :d)");
            $ins->execute([
                'n'   => $_POST['name'],
                'b'   => $_POST['building'],
                'c'   => $_POST['campus'] ?: 'Main Campus',
                'lat' => !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null,
                'lng' => !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null,
                'd'   => $_POST['description'] ?? ''
            ]);
            AuditLog::log('location_created', 'locations', $pdo->lastInsertId(), "Admin registered location: {$_POST['name']}");
            setFlash('success', 'Campus location registered successfully.');
        } elseif (isset($_POST['edit_location'])) {
            $locId = (int)$_POST['location_id'];
            $upd = $pdo->prepare("UPDATE locations SET name = :n, building = :b, campus = :c, latitude = :lat, longitude = :lng, description = :d, status = :s WHERE id = :id");
            $upd->execute([
                'n'   => $_POST['name'],
                'b'   => $_POST['building'],
                'c'   => $_POST['campus'],
                'lat' => !empty($_POST['latitude']) ? (float)$_POST['latitude'] : null,
                'lng' => !empty($_POST['longitude']) ? (float)$_POST['longitude'] : null,
                'd'   => $_POST['description'] ?? '',
                's'   => $_POST['status'],
                'id'  => $locId
            ]);
            AuditLog::log('location_updated', 'locations', $locId, "Admin updated location #{$locId}");
            setFlash('success', 'Location details updated.');
        }
        header('Location: ' . APP_URL . '/admin/locations.php');
        exit;
    }
}

$locations = $pdo->query("SELECT l.*, COUNT(i.id) AS item_count 
                          FROM locations l 
                          LEFT JOIN items i ON l.id = i.location_id 
                          GROUP BY l.id 
                          ORDER BY l.name ASC")->fetchAll();

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
                        <h4 class="fw-bold mb-1">Campus Locations & Geo Points</h4>
                        <p class="text-muted small mb-0">Manage buildings, halls, GPS pins for interactive mapping and lost/found intake.</p>
                    </div>
                    <button class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#addLocModal">
                        <i class="bi bi-plus-lg me-1"></i> Add Location
                    </button>
                </div>
            </div>

            <!-- Locations Table -->
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Location & Building</th>
                                <th>Campus</th>
                                <th>GPS Coordinates</th>
                                <th>Associated Items</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($locations as $loc): ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-bold text-dark d-block"><?= e($loc['name']) ?></span>
                                        <small class="text-muted"><?= e($loc['building']) ?></small>
                                    </td>
                                    <td><span class="small"><?= e($loc['campus']) ?></span></td>
                                    <td>
                                        <?php if ($loc['latitude'] && $loc['longitude']): ?>
                                            <code class="small"><?= number_format($loc['latitude'], 4) ?>, <?= number_format($loc['longitude'], 4) ?></code>
                                        <?php else: ?>
                                            <span class="text-muted small">Not Pinned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-light text-primary border rounded-pill"><?= $loc['item_count'] ?> items</span></td>
                                    <td>
                                        <span class="badge <?= ($loc['status'] === 'active') ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?> border">
                                            <?= ucfirst($loc['status']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-light btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#editLocModal<?= $loc['id'] ?>">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </button>
                                    </td>
                                </tr>

                                <!-- Edit Location Modal -->
                                <div class="modal fade" id="editLocModal<?= $loc['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content rounded-4 border-0">
                                            <div class="modal-header bg-primary text-white p-3.5">
                                                <h5 class="modal-title fw-bold">Edit Location: <?= e($loc['name']) ?></h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="">
                                                <?= csrfField() ?>
                                                <input type="hidden" name="edit_location" value="1">
                                                <input type="hidden" name="location_id" value="<?= $loc['id'] ?>">

                                                <div class="modal-body p-4">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-muted">Location Name *</label>
                                                        <input type="text" name="name" class="form-control bg-light" value="<?= e($loc['name']) ?>" required>
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label fw-semibold small text-muted">Building *</label>
                                                            <input type="text" name="building" class="form-control bg-light" value="<?= e($loc['building']) ?>" required>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label fw-semibold small text-muted">Campus</label>
                                                            <input type="text" name="campus" class="form-control bg-light" value="<?= e($loc['campus']) ?>" required>
                                                        </div>
                                                    </div>
                                                    <div class="row g-2 mb-3">
                                                        <div class="col-6">
                                                            <label class="form-label fw-semibold small text-muted">Latitude</label>
                                                            <input type="text" name="latitude" class="form-control bg-light" value="<?= e($loc['latitude'] ?? '') ?>">
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label fw-semibold small text-muted">Longitude</label>
                                                            <input type="text" name="longitude" class="form-control bg-light" value="<?= e($loc['longitude'] ?? '') ?>">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold small text-muted">Status</label>
                                                        <select name="status" class="form-select bg-light">
                                                            <option value="active" <?= ($loc['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                                                            <option value="inactive" <?= ($loc['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer p-3 bg-light border-0">
                                                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Location -->
<div class="modal fade" id="addLocModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header bg-primary text-white p-3.5">
                <h5 class="modal-title fw-bold">Register Campus Location</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <?= csrfField() ?>
                <input type="hidden" name="add_location" value="1">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Location Name *</label>
                        <input type="text" name="name" class="form-control bg-light" placeholder="e.g. Science Complex Atrium" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">Building Name *</label>
                            <input type="text" name="building" class="form-control bg-light" placeholder="e.g. Franklin Science Hall" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">Campus</label>
                            <input type="text" name="campus" class="form-control bg-light" value="Main Campus" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">Latitude</label>
                            <input type="text" name="latitude" class="form-control bg-light" placeholder="37.7749">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-muted">Longitude</label>
                            <input type="text" name="longitude" class="form-control bg-light" placeholder="-122.4194">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Location Notes</label>
                        <textarea name="description" rows="2" class="form-control bg-light" placeholder="Nearby landmarks, security desk position..."></textarea>
                    </div>
                </div>
                <div class="modal-footer p-3 bg-light border-0">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Register Location</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
