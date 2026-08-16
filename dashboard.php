<?php
/**
 * User / Student Dashboard
 */
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/classes/Item.php';
require_once __DIR__ . '/classes/Claim.php';
require_once __DIR__ . '/classes/Matching.php';
require_once __DIR__ . '/classes/Notification.php';
require_once __DIR__ . '/classes/Database.php';

$pageTitle = "My Dashboard";
$userId = (int)$currentUser['id'];

$pdo = Database::getInstance()->getConnection();
$itemModel = new Item();
$claimModel = new Claim();
$matchModel = new Matching();
$notifModel = new Notification();

// User Specific Metrics
$myLostCount  = (int)$pdo->query("SELECT COUNT(*) FROM items WHERE reported_by = {$userId} AND type = 'lost'")->fetchColumn();
$myFoundCount = (int)$pdo->query("SELECT COUNT(*) FROM items WHERE reported_by = {$userId} AND type = 'found'")->fetchColumn();
$myClaimsCount = (int)$pdo->query("SELECT COUNT(*) FROM claims WHERE claimant_id = {$userId}")->fetchColumn();
$myReturnedCount = (int)$pdo->query("SELECT COUNT(*) FROM items WHERE reported_by = {$userId} AND status = 'returned'")->fetchColumn();
$unreadNotifs = $notifModel->getUnreadCount($userId);

// Fetch Potential Matches for this user's lost reports
$potentialMatches = $matchModel->getMatchesForUser($userId);

// Fetch recent reports by this user
$recentReports = $itemModel->getItems(['reported_by' => $userId], 5, 0);

// Fetch user's recent claims
$recentClaims = $claimModel->getAll(['claimant_id' => $userId], 5, 0);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container-fluid px-lg-4 py-4">
    <?= renderFlash() ?>

    <div class="row g-4">
        <!-- Sidebar Navigation -->
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/includes/sidebar.php'; ?>
        </div>

        <!-- Main Content Area -->
        <div class="col-lg-9">
            <!-- Header Welcome Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h4 class="fw-bold text-dark mb-1">Welcome back, <?= e($currentUser['full_name']) ?> 👋</h4>
                        <p class="text-muted small mb-0">Track your reported items, check match suggestions, and manage your claims.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= APP_URL ?>/report-lost.php" class="btn btn-danger btn-sm rounded-pill px-3 fw-semibold">
                            <i class="bi bi-exclamation-octagon me-1"></i> Report Lost
                        </a>
                        <a href="<?= APP_URL ?>/report-found.php" class="btn btn-success btn-sm rounded-pill px-3 fw-semibold">
                            <i class="bi bi-plus-circle me-1"></i> Report Found
                        </a>
                    </div>
                </div>
            </div>

            <!-- Metric Cards Grid -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-danger fs-3"><i class="bi bi-search"></i></span>
                            <span class="badge bg-danger-subtle text-danger rounded-pill">Lost</span>
                        </div>
                        <h3 class="fw-bold mb-0 text-dark"><?= $myLostCount ?></h3>
                        <small class="text-muted fw-semibold">My Lost Reports</small>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-success fs-3"><i class="bi bi-box2-heart"></i></span>
                            <span class="badge bg-success-subtle text-success rounded-pill">Found</span>
                        </div>
                        <h3 class="fw-bold mb-0 text-dark"><?= $myFoundCount ?></h3>
                        <small class="text-muted fw-semibold">My Found Reports</small>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-primary fs-3"><i class="bi bi-clipboard-check"></i></span>
                            <span class="badge bg-primary-subtle text-primary rounded-pill">Claims</span>
                        </div>
                        <h3 class="fw-bold mb-0 text-dark"><?= $myClaimsCount ?></h3>
                        <small class="text-muted fw-semibold">My Claims</small>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-warning fs-3"><i class="bi bi-bell"></i></span>
                            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill">Alerts</span>
                        </div>
                        <h3 class="fw-bold mb-0 text-dark"><?= $unreadNotifs ?></h3>
                        <small class="text-muted fw-semibold">Unread Notifications</small>
                    </div>
                </div>
            </div>

            <!-- Potential Matches Alert Banner (if any) -->
            <?php if (!empty($potentialMatches)): ?>
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-stars fs-3 text-warning"></i>
                            <h5 class="fw-bold mb-0 text-white">Rule-Based Potential Matches Detected!</h5>
                        </div>
                        <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill fw-bold">
                            <?= count($potentialMatches) ?> Potential Match(es)
                        </span>
                    </div>
                    <div class="row g-3">
                        <?php foreach ($potentialMatches as $match): ?>
                            <div class="col-md-6">
                                <div class="bg-white text-dark rounded-3 p-3 shadow-sm">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold mb-0 text-truncate" style="max-width: 70%;"><?= e($match['found_title']) ?></h6>
                                        <span class="badge bg-success rounded-pill fw-bold">
                                            <?= round($match['match_score']) ?>% Match
                                        </span>
                                    </div>
                                    <small class="text-muted d-block mb-2">
                                        Matches your lost item: <strong><?= e($match['lost_title']) ?></strong>
                                    </small>
                                    <p class="small text-muted mb-2 border-start border-3 border-primary ps-2" style="font-size: 0.78rem;">
                                        <?= e($match['matched_factors']) ?>
                                    </p>
                                    <a href="<?= APP_URL ?>/item-details.php?ref=<?= e($match['found_ref']) ?>" class="btn btn-outline-primary btn-sm w-100 rounded-pill fw-semibold">
                                        Inspect & Submit Claim <i class="bi bi-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- My Recent Reports Table -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
                <div class="card-header bg-white border-0 p-3.5 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">My Reported Items</h5>
                    <a href="<?= APP_URL ?>/my-reports.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        View All (<?= $myLostCount + $myFoundCount ?>)
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Item</th>
                                <th>Type</th>
                                <th>Category</th>
                                <th>Reported Date</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentReports)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-1"></i>
                                        You have not reported any items yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentReports as $rep): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; overflow: hidden;">
                                                    <?php if (!empty($rep['primary_image'])): ?>
                                                        <img src="<?= APP_URL ?>/uploads/items/<?= e($rep['primary_image']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                                    <?php else: ?>
                                                        <i class="bi <?= e($rep['category_icon'] ?? 'bi-box') ?> text-muted"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark"><?= e($rep['title']) ?></div>
                                                    <code class="small text-muted"><?= e($rep['reference_code']) ?></code>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= getTypeBadge($rep['type']) ?></td>
                                        <td><span class="small"><?= e($rep['category_name']) ?></span></td>
                                        <td><span class="small text-muted"><?= formatDate($rep['created_at']) ?></span></td>
                                        <td><?= getStatusBadge($rep['status']) ?></td>
                                        <td class="text-end pe-4">
                                            <a href="<?= APP_URL ?>/item-details.php?ref=<?= e($rep['reference_code']) ?>" class="btn btn-light btn-sm rounded-pill">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- My Recent Claims -->
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="card-header bg-white border-0 p-3.5 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark">My Active Claims</h5>
                    <a href="<?= APP_URL ?>/my-claims.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        View All Claims (<?= $myClaimsCount ?>)
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Claim Code</th>
                                <th>Target Item</th>
                                <th>Submitted On</th>
                                <th>Verification Status</th>
                                <th class="text-end pe-4">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentClaims)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">
                                        <i class="bi bi-patch-question fs-2 d-block mb-1"></i>
                                        No claims submitted yet.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentClaims as $clm): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><code><?= e($clm['claim_code']) ?></code></td>
                                        <td>
                                            <span class="fw-semibold text-dark"><?= e($clm['item_title']) ?></span>
                                            <small class="text-muted d-block"><?= e($clm['item_ref']) ?></small>
                                        </td>
                                        <td><span class="small text-muted"><?= formatDate($clm['created_at']) ?></span></td>
                                        <td><?= getStatusBadge($clm['status']) ?></td>
                                        <td class="text-end pe-4">
                                            <a href="<?= APP_URL ?>/item-details.php?ref=<?= e($clm['item_ref']) ?>" class="btn btn-light btn-sm rounded-pill">
                                                View Item
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
