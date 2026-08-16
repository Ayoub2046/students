<?php
/**
 * Report Found Item Page
 */
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/classes/Item.php';
require_once __DIR__ . '/classes/Database.php';

$pageTitle = "Report Found Item";
$error = '';

$pdo = Database::getInstance()->getConnection();
$categories = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name ASC")->fetchAll();
$locations = $pdo->query("SELECT * FROM locations WHERE status = 'active' ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please submit again.';
    } elseif (empty($_POST['title']) || empty($_POST['category_id']) || empty($_POST['location_id']) || empty($_POST['description']) || empty($_POST['date_found'])) {
        $error = 'Please fill in all mandatory fields marked with an asterisk (*).';
    } else {
        // Handle image uploads
        $uploadedImages = [];
        if (!empty($_FILES['images']['name'][0])) {
            $fileCount = count($_FILES['images']['name']);
            for ($i = 0; $i < min($fileCount, 5); $i++) {
                $file = [
                    'name'     => $_FILES['images']['name'][$i],
                    'type'     => $_FILES['images']['type'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error'    => $_FILES['images']['error'][$i],
                    'size'     => $_FILES['images']['size'][$i]
                ];
                $savedName = uploadFile($file, UPLOAD_PATH_ITEMS);
                if ($savedName) {
                    $uploadedImages[] = $savedName;
                }
            }
        }

        $itemModel = new Item();
        $result = $itemModel->create([
            'type'                    => 'found',
            'title'                   => $_POST['title'],
            'category_id'             => $_POST['category_id'],
            'location_id'             => $_POST['location_id'],
            'description'             => $_POST['description'],
            'brand'                   => $_POST['brand'] ?? '',
            'model'                   => $_POST['model'] ?? '',
            'color'                   => $_POST['color'] ?? '',
            'serial_number'           => $_POST['serial_number'] ?? '',
            'identification_details'  => $_POST['identification_details'] ?? '',
            'date_found'              => $_POST['date_found'],
            'time_found'              => $_POST['time_found'] ?? null,
            'reported_by'             => $currentUser['id'],
            'has_physical_possession' => !empty($_POST['has_physical_possession']) ? 1 : 0,
            'privacy_level'           => 'public'
        ], $uploadedImages);

        if ($result['success']) {
            setFlash('success', "Found item report submitted successfully! Reference: {$result['reference_code']}. Please surrender the physical item to Student Center Room 104 if in your possession.");
            header('Location: ' . APP_URL . '/my-reports.php');
            exit;
        } else {
            $error = $result['message'];
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-9">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/dashboard.php">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Report Found Item</li>
                </ol>
            </nav>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger shadow-sm rounded-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= e($error) ?>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="bg-success p-4 text-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white text-success rounded-circle p-2.5 d-inline-flex align-items-center justify-content-center shadow-sm">
                            <i class="bi bi-box2-heart fs-3"></i>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-1 text-white">Report a Found Item</h4>
                            <p class="small text-white-75 mb-0">Help a fellow student or colleague recover their lost possession</p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 p-md-4.5 bg-white">
                    <form method="POST" action="<?= APP_URL ?>/report-found.php" enctype="multipart/form-data">
                        <?= csrfField() ?>

                        <!-- Physical Item Possession Question -->
                        <div class="card bg-success-subtle border-success-subtle p-3 mb-4 rounded-3">
                            <label class="form-label fw-bold text-success-emphasis mb-1">
                                <i class="bi bi-hand-index-thumb me-1"></i> Do you currently have physical possession of this item? *
                            </label>
                            <div class="d-flex gap-4 mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="has_physical_possession" id="possession_yes" value="1" checked>
                                    <label class="form-check-label fw-semibold" for="possession_yes">
                                        Yes, I have it with me (Will hand it over to Lost & Found Office Room 104)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="has_physical_possession" id="possession_no" value="0">
                                    <label class="form-check-label" for="possession_no">
                                        No, I left it at the location / with local security
                                    </label>
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle me-1"></i> Found Item Information</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold small text-muted">Item Title / Description Headline *</label>
                                <input type="text" name="title" class="form-control bg-light" placeholder="e.g. Blue Hydroflask Water Bottle" required value="<?= e($_POST['title'] ?? '') ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-muted">Category *</label>
                                <select name="category_id" class="form-select bg-light" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= $cat['id'] ?>" <?= (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                            <?= e($cat['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-muted">Brand / Manufacturer</label>
                                <input type="text" name="brand" class="form-control bg-light" placeholder="e.g. Sony, Hydroflask" value="<?= e($_POST['brand'] ?? '') ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-muted">Model / Edition</label>
                                <input type="text" name="model" class="form-control bg-light" placeholder="e.g. 32oz Wide Mouth" value="<?= e($_POST['model'] ?? '') ?>">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-semibold small text-muted">Color</label>
                                <input type="text" name="color" class="form-control bg-light" placeholder="e.g. Cobalt Blue" value="<?= e($_POST['color'] ?? '') ?>">
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold small text-muted">Public Description *</label>
                                <textarea name="description" rows="3" class="form-control bg-light" placeholder="General description of where it was spotted and general condition..." required><?= e($_POST['description'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-geo-alt me-1"></i> Found Location & Date</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Campus Location Where Found *</label>
                                <select name="location_id" class="form-select bg-light" required>
                                    <option value="">Select Location</option>
                                    <?php foreach ($locations as $loc): ?>
                                        <option value="<?= $loc['id'] ?>" <?= (($_POST['location_id'] ?? '') == $loc['id']) ? 'selected' : '' ?>>
                                            <?= e($loc['name']) ?> (<?= e($loc['building'] ?? 'Main') ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold small text-muted">Date Found *</label>
                                <input type="date" name="date_found" class="form-control bg-light" required max="<?= date('Y-m-d') ?>" value="<?= e($_POST['date_found'] ?? date('Y-m-d')) ?>">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-semibold small text-muted">Time Found</label>
                                <input type="time" name="time_found" class="form-control bg-light" value="<?= e($_POST['time_found'] ?? '') ?>">
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-shield-lock me-1"></i> Confidential Details (For Claim Verification)</h6>
                        <div class="p-3 bg-light rounded-3 mb-4 border">
                            <p class="small text-muted mb-3">
                                <i class="bi bi-info-circle-fill text-primary me-1"></i> Keep sensitive identifiers hidden from the public view so true owners can verify ownership.
                            </p>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-muted">Serial Number / IMEI (Optional)</label>
                                    <input type="text" name="serial_number" class="form-control bg-white" placeholder="Visible serial number" value="<?= e($_POST['serial_number'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small text-muted">Secret Identification Markings</label>
                                    <input type="text" name="identification_details" class="form-control bg-white" placeholder="Specific sticker, item inside pocket, card initials" value="<?= e($_POST['identification_details'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-images me-1"></i> Item Photos (Max 5 Images)</h6>
                        <div class="mb-4">
                            <input type="file" name="images[]" class="form-control bg-light" multiple accept="image/*" data-preview-target="foundImagePreview">
                            <small class="text-muted d-block mt-1">Allowed formats: JPG, PNG, WEBP. Max size: 5MB per file.</small>
                            <div id="foundImagePreview" class="d-flex flex-wrap gap-2 mt-2"></div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="<?= APP_URL ?>/dashboard.php" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                            <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold">
                                <i class="bi bi-send me-1"></i> Submit Found Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
