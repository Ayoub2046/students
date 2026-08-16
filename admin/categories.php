<?php
/**
 * Admin Category Management
 */
require_once __DIR__ . '/../includes/role-check.php';
requireRole('admin');

require_once __DIR__ . '/../classes/AuditLog.php';
require_once __DIR__ . '/../classes/Database.php';

$pageTitle = "Item Categories Management";
$pdo = Database::getInstance()->getConnection();

// Handle Add / Edit Category
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        if (isset($_POST['add_category'])) {
            $ins = $pdo->prepare("INSERT INTO categories (name, description, icon) VALUES (:n, :d, :i)");
            $ins->execute([
                'n' => $_POST['name'],
                'd' => $_POST['description'] ?? '',
                'i' => $_POST['icon'] ?: 'bi-box'
            ]);
            AuditLog::log('category_created', 'categories', $pdo->lastInsertId(), "Admin added category: {$_POST['name']}");
            setFlash('success', 'New item category registered.');
        } elseif (isset($_POST['edit_category'])) {
            $catId = (int)$_POST['category_id'];
            $upd = $pdo->prepare("UPDATE categories SET name = :n, description = :d, icon = :i, status = :s WHERE id = :id");
            $upd->execute([
                'n'  => $_POST['name'],
                'd'  => $_POST['description'] ?? '',
                'i'  => $_POST['icon'],
                's'  => $_POST['status'],
                'id' => $catId
            ]);
            AuditLog::log('category_updated', 'categories', $catId, "Admin updated category #{$catId}");
            setFlash('success', 'Category updated successfully.');
        }
        header('Location: ' . APP_URL . '/admin/categories.php');
        exit;
    }
}

$categories = $pdo->query("SELECT c.*, COUNT(i.id) AS item_count 
                           FROM categories c 
                           LEFT JOIN items i ON c.id = i.category_id 
                           GROUP BY c.id 
                           ORDER BY c.name ASC")->fetchAll();

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
                        <h4 class="fw-bold mb-1">Item Categories Taxonomy</h4>
                        <p class="text-muted small mb-0">Define catalog classifications, icons, and intake guidelines.</p>
                    </div>
                    <button class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        <i class="bi bi-plus-lg me-1"></i> Add Category
                    </button>
                </div>
            </div>

            <!-- Categories Grid -->
            <div class="row g-3">
                <?php foreach ($categories as $c): ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-white h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle p-2 bg-light text-primary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                            <i class="bi <?= e($c['icon'] ?: 'bi-tag') ?> fs-5"></i>
                                        </div>
                                        <h6 class="fw-bold mb-0 text-dark"><?= e($c['name']) ?></h6>
                                    </div>
                                    <span class="badge <?= ($c['status'] === 'active') ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?> border rounded-pill">
                                        <?= ucfirst($c['status']) ?>
                                    </span>
                                </div>
                                <p class="small text-muted mb-3" style="font-size: 0.8rem;"><?= e($c['description']) ?></p>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <small class="text-muted fw-semibold"><?= $c['item_count'] ?> registered items</small>
                                <button class="btn btn-outline-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#editCatModal<?= $c['id'] ?>">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editCatModal<?= $c['id'] ?>" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content rounded-4 border-0">
                                <div class="modal-header bg-primary text-white p-3.5">
                                    <h5 class="modal-title fw-bold">Edit Category: <?= e($c['name']) ?></h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <form method="POST" action="">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="edit_category" value="1">
                                    <input type="hidden" name="category_id" value="<?= $c['id'] ?>">

                                    <div class="modal-body p-4">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold small text-muted">Category Name *</label>
                                            <input type="text" name="name" class="form-control bg-light" value="<?= e($c['name']) ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold small text-muted">Bootstrap Icon Class</label>
                                            <input type="text" name="icon" class="form-control bg-light" value="<?= e($c['icon']) ?>" placeholder="bi-laptop, bi-wallet2...">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold small text-muted">Status</label>
                                            <select name="status" class="form-select bg-light">
                                                <option value="active" <?= ($c['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                                                <option value="inactive" <?= ($c['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold small text-muted">Description</label>
                                            <textarea name="description" rows="2" class="form-control bg-light"><?= e($c['description']) ?></textarea>
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
            </div>
        </div>
    </div>
</div>

<!-- Modal: Add Category -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header bg-primary text-white p-3.5">
                <h5 class="modal-title fw-bold">Create Item Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <?= csrfField() ?>
                <input type="hidden" name="add_category" value="1">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Category Name *</label>
                        <input type="text" name="name" class="form-control bg-light" placeholder="e.g. Eyewear & Optics" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Bootstrap Icon Class</label>
                        <input type="text" name="icon" class="form-control bg-light" placeholder="bi-eyeglasses" value="bi-box">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-muted">Description</label>
                        <textarea name="description" rows="2" class="form-control bg-light" placeholder="Intake description guidelines..."></textarea>
                    </div>
                </div>
                <div class="modal-footer p-3 bg-light border-0">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Register Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
