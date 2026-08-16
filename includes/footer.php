<?php
/**
 * Global Footer
 */
?>
<footer class="bg-white border-top mt-auto py-3">
    <div class="container-fluid px-lg-4">
        <div class="row align-items-center gy-2">
            <div class="col-md-6 text-center text-md-start">
                <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2 mb-1">
                    <span class="bg-indigo-900 text-white rounded p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px; font-size: 0.7rem; background-color: #312e81;">
                        <i class="bi bi-compass"></i>
                    </span>
                    <span class="fw-bold text-dark small"><?= e(APP_NAME) ?></span>
                    <span class="badge bg-light text-secondary border font-monospace" style="font-size: 0.65rem;">v2.4.1</span>
                </div>
                <p class="text-muted small mb-0" style="font-size: 0.75rem;">"<?= e(APP_SLOGAN) ?>" &bull; Student Center Desk, Room 104 &bull; Contact: lostfound@university.edu</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <div class="d-flex justify-content-center justify-content-md-end gap-3 small text-muted mb-1" style="font-size: 0.75rem;">
                    <a href="<?= APP_URL ?>/index.php" class="text-decoration-none text-muted">Home</a>
                    <a href="<?= APP_URL ?>/items.php" class="text-decoration-none text-muted">Inventory</a>
                    <a href="<?= APP_URL ?>/map.php" class="text-decoration-none text-muted">Map</a>
                    <a href="<?= APP_URL ?>/report-lost.php" class="text-decoration-none text-muted">Report Lost</a>
                    <a href="<?= APP_URL ?>/report-found.php" class="text-decoration-none text-muted">Report Found</a>
                </div>
                <small class="text-muted d-block" style="font-size: 0.7rem;">&copy; <?= date('Y') ?> Metropolitan State University. All rights reserved.</small>
            </div>
        </div>
    </div>
    <!-- Technical High Density Status Bar -->
    <div class="system-status-bar d-flex flex-wrap justify-content-between align-items-center mt-2 border-top pt-1 px-4">
        <span>CONNECTED TO: MySQL @ localhost:3306 [utf8mb4_unicode_ci]</span>
        <span>MEMORY: 14.2MB / 128MB &bull; PHP 8.2 &bull; HIGH DENSITY RECOVERY DESK</span>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Leaflet JS for Campus Map -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Chart.js for Analytics -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- QRCode.js for QR Generation -->
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

<!-- Custom App JS -->
<script src="<?= APP_URL ?>/assets/js/app.js"></script>
</body>
</html>
