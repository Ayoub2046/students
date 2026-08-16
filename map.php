<?php
/**
 * Interactive Campus Map (Leaflet.js + OpenStreetMap)
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Database.php';

$pageTitle = "Campus Lost & Found Map";
$pdo = Database::getInstance()->getConnection();

// Fetch active campus locations with coordinates
$locations = $pdo->query("SELECT * FROM locations WHERE status = 'active'")->fetchAll();

// Fetch approved items with their location details
$items = $pdo->query("SELECT i.id, i.reference_code, i.title, i.type, i.status, l.name AS location_name, l.latitude, l.longitude
                      FROM items i
                      JOIN locations l ON i.location_id = l.id
                      WHERE i.status IN ('approved', 'available', 'claimed', 'under_verification')
                        AND l.latitude IS NOT NULL")->fetchAll();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container-fluid px-lg-5 py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i> Interactive Campus Map</h3>
            <p class="text-muted small mb-0">Explore item report densities and campus recovery collection hubs</p>
        </div>
        <div class="d-flex gap-2">
            <span class="badge bg-danger-subtle text-danger border p-2 rounded-pill"><i class="bi bi-circle-fill me-1"></i> Lost Report Zone</span>
            <span class="badge bg-success-subtle text-success border p-2 rounded-pill"><i class="bi bi-circle-fill me-1"></i> Found Item Zone</span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Interactive Map Container -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white p-2">
                <div id="campusMap"></div>
            </div>
        </div>

        <!-- Campus Locations Sidebar -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white" style="max-height: 540px; overflow-y: auto;">
                <h6 class="fw-bold text-primary mb-3"><i class="bi bi-building me-1"></i> Campus Lost & Found Hubs</h6>
                <div class="list-group list-group-flush">
                    <?php foreach ($locations as $loc): ?>
                        <div class="list-group-item px-0 py-2.5 border-bottom">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark"><?= e($loc['name']) ?></h6>
                                    <small class="text-muted"><?= e($loc['building']) ?> &bull; <?= e($loc['campus']) ?></small>
                                </div>
                                <span class="badge bg-light text-primary border rounded-pill">
                                    <i class="bi bi-geo-alt"></i> Pin
                                </span>
                            </div>
                            <p class="small text-muted mt-1 mb-0" style="font-size: 0.78rem;"><?= e($loc['description']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Center around the main campus coordinates
    const map = L.map('campusMap').setView([37.7750, -122.4200], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Render Campus Locations
    const locations = <?= json_encode($locations) ?>;
    locations.forEach(loc => {
        if (loc.latitude && loc.longitude) {
            const marker = L.circleMarker([loc.latitude, loc.longitude], {
                radius: 8,
                fillColor: '#1e40af',
                color: '#ffffff',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.85
            }).addTo(map);

            marker.bindPopup(`
                <strong>${loc.name}</strong><br>
                <small>${loc.building || ''} (${loc.campus || ''})</small><br>
                <p class="mt-1 mb-0" style="font-size:12px;">${loc.description || ''}</p>
            `);
        }
    });

    // Render Items
    const items = <?= json_encode($items) ?>;
    items.forEach(it => {
        if (it.latitude && it.longitude) {
            // slight random jitter to prevent exact overlapping markers on same building
            const jitterLat = (Math.random() - 0.5) * 0.0003;
            const jitterLng = (Math.random() - 0.5) * 0.0003;
            const isFound = it.type === 'found';

            const marker = L.circleMarker([parseFloat(it.latitude) + jitterLat, parseFloat(it.longitude) + jitterLng], {
                radius: 6,
                fillColor: isFound ? '#16a34a' : '#dc2626',
                color: '#ffffff',
                weight: 1.5,
                opacity: 1,
                fillOpacity: 0.9
            }).addTo(map);

            marker.bindPopup(`
                <span class="badge ${isFound ? 'bg-success' : 'bg-danger'}">${it.type.toUpperCase()}</span><br>
                <strong>${it.title}</strong><br>
                <code>${it.reference_code}</code><br>
                <a href="<?= APP_URL ?>/item-details.php?ref=${it.reference_code}" class="btn btn-primary btn-sm mt-1 text-white" style="font-size:11px; padding:2px 8px;">View Item</a>
            `);
        }
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
