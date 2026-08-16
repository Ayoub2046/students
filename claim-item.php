<?php
/**
 * Item Ownership Claim Gateway
 */
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/classes/Item.php';
require_once __DIR__ . '/classes/Claim.php';

$itemId = (int)($_GET['item_id'] ?? 0);
$itemModel = new Item();
$item = $itemModel->findById($itemId);

if (!$item || $item['type'] !== 'found') {
    setFlash('danger', 'Invalid item for claim submission.');
    header('Location: ' . APP_URL . '/items.php');
    exit;
}

$pageTitle = "Claim Item - " . $item['title'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid security token. Please submit again.';
    } elseif (empty($_POST['reason']) || empty($_POST['ans_location']) || empty($_POST['ans_secret'])) {
        $error = 'Please answer all required verification questions marked with an asterisk (*).';
    } else {
        $claimModel = new Claim();
        $qaPairs = [
            [
                'question' => 'Where did you lose this item?',
                'answer'   => $_POST['ans_location'] ?? ''
            ],
            [
                'question' => 'When did you lose this item?',
                'answer'   => $_POST['ans_date'] ?? ''
            ],
            [
                'question' => 'Describe unique features, markings, or contents not publicly visible:',
                'answer'   => $_POST['ans_secret'] ?? ''
            ],
            [
                'question' => 'Describe accessories, cases, or companion items:',
                'answer'   => $_POST['ans_accessories'] ?? ''
            ]
        ];

        $res = $claimModel->submit($itemId, (int)$currentUser['id'], $_POST['reason'], $qaPairs);

        if ($res['success']) {
            setFlash('success', "Your ownership claim has been submitted under code {$res['claim_code']}! An authorized officer will inspect your answers.");
            header('Location: ' . APP_URL . '/my-claims.php');
            exit;
        } else {
            $error = $res['message'];
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?= APP_URL ?>/item-details.php?ref=<?= e($item['reference_code']) ?>"><?= e($item['reference_code']) ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page">Submit Ownership Claim</li>
                </ol>
            </nav>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger shadow-sm rounded-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= e($error) ?>
                </div>
            <?php endif; ?>

            <!-- Target Item Summary Card -->
            <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 bg-light d-flex align-items-center justify-content-center" style="width: 70px; height: 70px; overflow: hidden;">
                        <?php if (!empty($item['images']) && !empty($item['images'][0]['image_path'])): ?>
                            <img src="<?= APP_URL ?>/uploads/items/<?= e($item['images'][0]['image_path']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <i class="bi bi-box fs-2 text-muted"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="badge bg-success-subtle text-success">FOUND ITEM</span>
                        <h5 class="fw-bold mb-0 text-dark"><?= e($item['title']) ?></h5>
                        <code class="small text-muted"><?= e($item['reference_code']) ?> &bull; Found at <?= e($item['location_name']) ?></code>
                    </div>
                </div>
            </div>

            <!-- Claim Submission Form -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="bg-primary p-4 text-white">
                    <h5 class="fw-bold mb-1"><i class="bi bi-shield-check me-2"></i> Ownership Verification Questionnaire</h5>
                    <p class="small text-white-75 mb-0">To prevent fraudulent claims, answer these verification questions with specifics.</p>
                </div>

                <div class="card-body p-4 p-md-4.5 bg-white">
                    <form method="POST" action="<?= APP_URL ?>/claim-item.php?item_id=<?= $item['id'] ?>">
                        <?= csrfField() ?>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">1. Why do you believe this item is yours? *</label>
                            <textarea name="reason" rows="2" class="form-control bg-light" placeholder="e.g. I lost my wallet right after paying at the cafeteria snack bar..." required><?= e($_POST['reason'] ?? '') ?></textarea>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">2. Where did you lose it? *</label>
                                <input type="text" name="ans_location" class="form-control bg-light" placeholder="e.g. Alan Turing Lab 3, 2nd row" required value="<?= e($_POST['ans_location'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">3. When did you lose it? *</label>
                                <input type="text" name="ans_date" class="form-control bg-light" placeholder="e.g. Yesterday morning around 10:30 AM" required value="<?= e($_POST['ans_date'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">4. Describe unique features / markings not publicly visible *</label>
                            <textarea name="ans_secret" rows="3" class="form-control bg-light" placeholder="e.g. Scratches, stickers, lock screen wallpaper, specific cards inside, serial number..." required><?= e($_POST['ans_secret'] ?? '') ?></textarea>
                            <small class="text-muted">This is evaluated against the confidential item notes stored by the intake officer.</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">5. Describe any accessories, cases, or markings</label>
                            <input type="text" name="ans_accessories" class="form-control bg-light" placeholder="e.g. Red silicone case, orange charging cable, key chain..." value="<?= e($_POST['ans_accessories'] ?? '') ?>">
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <a href="<?= APP_URL ?>/item-details.php?ref=<?= e($item['reference_code']) ?>" class="btn btn-outline-secondary rounded-pill px-4">Cancel</a>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                                <i class="bi bi-send-check-fill me-1"></i> Submit Official Claim
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
