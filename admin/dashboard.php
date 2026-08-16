<?php
/**
 * Administrator Executive Dashboard & Analytics
 */
require_once __DIR__ . '/../includes/role-check.php';
requireRole('admin');

require_once __DIR__ . '/../classes/Database.php';

$pageTitle = "Executive Admin Dashboard";
$pdo = Database::getInstance()->getConnection();

// System High-Level KPIs
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalItems = (int)$pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
$totalLost = (int)$pdo->query("SELECT COUNT(*) FROM items WHERE type = 'lost'")->fetchColumn();
$totalFound = (int)$pdo->query("SELECT COUNT(*) FROM items WHERE type = 'found'")->fetchColumn();
$totalClaims = (int)$pdo->query("SELECT COUNT(*) FROM claims")->fetchColumn();
$totalReturned = (int)$pdo->query("SELECT COUNT(*) FROM items WHERE status = 'returned'")->fetchColumn();
$recoveryRate = ($totalLost + $totalFound > 0) ? round(($totalReturned / ($totalLost + $totalFound)) * 100, 1) : 0;

// Monthly Trend Stats (Last 6 Months)
$monthlySql = "SELECT DATE_FORMAT(created_at, '%b %Y') AS month_label,
                      SUM(CASE WHEN type = 'lost' THEN 1 ELSE 0 END) AS lost_cnt,
                      SUM(CASE WHEN type = 'found' THEN 1 ELSE 0 END) AS found_cnt,
                      SUM(CASE WHEN status = 'returned' THEN 1 ELSE 0 END) AS returned_cnt
               FROM items
               WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
               GROUP BY DATE_FORMAT(created_at, '%Y-%m'), DATE_FORMAT(created_at, '%b %Y')
               ORDER BY MIN(created_at) ASC";
$monthlyTrends = $pdo->query($monthlySql)->fetchAll();

// Category Distribution
$catDist = $pdo->query("SELECT c.name, COUNT(i.id) AS item_count
                        FROM categories c
                        LEFT JOIN items i ON c.id = i.category_id
                        GROUP BY c.id, c.name
                        ORDER BY item_count DESC
                        LIMIT 6")->fetchAll();

// Recent Audit Trail
$recentAudits = $pdo->query("SELECT a.*, u.full_name AS user_name, u.role AS user_role
                            FROM audit_logs a
                            LEFT JOIN users u ON a.user_id = u.id
                            ORDER BY a.created_at DESC
                            LIMIT 8")->fetchAll();

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
            <!-- Header Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <span class="badge bg-primary-subtle text-primary px-3 py-1 rounded-pill fw-bold text-uppercase mb-1">
                            System Administration Console
                        </span>
                        <h4 class="fw-bold text-dark mb-0">University System Analytics & Governance</h4>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= APP_URL ?>/admin/reports.php" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold">
                            <i class="bi bi-file-earmark-bar-graph me-1"></i> Full Analytics & CSV
                        </a>
                    </div>
                </div>
            </div>

            <!-- Executive KPI Cards -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100">
                        <span class="text-primary fs-3 mb-1"><i class="bi bi-people-fill"></i></span>
                        <h3 class="fw-bold mb-0 text-dark"><?= $totalUsers ?></h3>
                        <small class="text-muted fw-semibold" style="font-size: 0.75rem;">Total Accounts</small>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100">
                        <span class="text-danger fs-3 mb-1"><i class="bi bi-search"></i></span>
                        <h3 class="fw-bold mb-0 text-dark"><?= $totalLost ?></h3>
                        <small class="text-muted fw-semibold" style="font-size: 0.75rem;">Lost Reports</small>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100">
                        <span class="text-success fs-3 mb-1"><i class="bi bi-box2-heart"></i></span>
                        <h3 class="fw-bold mb-0 text-dark"><?= $totalFound ?></h3>
                        <small class="text-muted fw-semibold" style="font-size: 0.75rem;">Found Items</small>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100">
                        <span class="text-warning fs-3 mb-1"><i class="bi bi-clipboard-check"></i></span>
                        <h3 class="fw-bold mb-0 text-dark"><?= $totalClaims ?></h3>
                        <small class="text-muted fw-semibold" style="font-size: 0.75rem;">Claims Submitted</small>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100">
                        <span class="text-info fs-3 mb-1"><i class="bi bi-check2-circle"></i></span>
                        <h3 class="fw-bold mb-0 text-dark"><?= $totalReturned ?></h3>
                        <small class="text-muted fw-semibold" style="font-size: 0.75rem;">Items Returned</small>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center h-100">
                        <span class="text-success fs-3 mb-1"><i class="bi bi-graph-up-arrow"></i></span>
                        <h3 class="fw-bold mb-0 text-dark"><?= $recoveryRate ?>%</h3>
                        <small class="text-muted fw-semibold" style="font-size: 0.75rem;">Recovery Rate</small>
                    </div>
                </div>
            </div>

            <!-- Chart Analytics Row -->
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-activity text-primary me-2"></i> Monthly Registration & Recovery Trends</h5>
                        <div style="height: 280px;">
                            <canvas id="monthlyTrendChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-pie-chart text-primary me-2"></i> Category Breakdown</h5>
                        <div style="height: 280px;">
                            <canvas id="categoryPieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent System Audit Trail -->
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                <div class="card-header bg-white border-0 p-3.5 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-journal-text text-primary me-2"></i> System Activity & Security Audit Trail</h5>
                    <a href="<?= APP_URL ?>/admin/audit-logs.php" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        Full Audit Log
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">Action</th>
                                <th>Performed By</th>
                                <th>Target Entity</th>
                                <th>Description / Details</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentAudits)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No audit events logged yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($recentAudits as $a): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <span class="badge bg-light text-dark border font-monospace"><?= e($a['action']) ?></span>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark small d-block"><?= e($a['user_name'] ?? 'System / Anonymous') ?></span>
                                            <small class="text-muted"><?= e($a['user_role'] ?? 'guest') ?></small>
                                        </td>
                                        <td><code><?= e($a['entity_type'] ?? '-') ?> #<?= e($a['entity_id'] ?? '-') ?></code></td>
                                        <td><span class="small text-secondary"><?= e($a['details'] ?? '') ?></span></td>
                                        <td><span class="small text-muted"><?= formatDateTime($a['created_at']) ?></span></td>
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
    // Monthly Trend Chart
    const trendCtx = document.getElementById('monthlyTrendChart');
    if (trendCtx) {
        const trendData = <?= json_encode($monthlyTrends) ?>;
        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: trendData.map(d => d.month_label),
                datasets: [
                    {
                        label: 'Lost Reports',
                        data: trendData.map(d => d.lost_cnt),
                        backgroundColor: '#ef4444',
                        borderRadius: 6
                    },
                    {
                        label: 'Found Items',
                        data: trendData.map(d => d.found_cnt),
                        backgroundColor: '#22c55e',
                        borderRadius: 6
                    },
                    {
                        label: 'Returned to Owner',
                        data: trendData.map(d => d.returned_cnt),
                        backgroundColor: '#3b82f6',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'top' } }
            }
        });
    }

    // Category Pie Chart
    const catCtx = document.getElementById('categoryPieChart');
    if (catCtx) {
        const catData = <?= json_encode($catDist) ?>;
        new Chart(catCtx, {
            type: 'doughnut',
            data: {
                labels: catData.map(c => c.name),
                datasets: [{
                    data: catData.map(c => c.item_count),
                    backgroundColor: ['#1e40af', '#0284c7', '#059669', '#d97706', '#dc2626', '#7c3aed']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
