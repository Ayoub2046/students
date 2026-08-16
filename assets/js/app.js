/**
 * University Lost & Found App JavaScript
 */

document.addEventListener('DOMContentLoaded', function () {
    // Enable Bootstrap tooltips and popovers
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-dismiss standard alerts after 6 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 6000);
    });

    // Dynamic Image Preview for file inputs
    const fileInputs = document.querySelectorAll('input[type="file"][data-preview-target]');
    fileInputs.forEach(function (input) {
        input.addEventListener('change', function (e) {
            const targetId = this.getAttribute('data-preview-target');
            const target = document.getElementById(targetId);
            if (!target) return;

            target.innerHTML = '';
            if (this.files) {
                Array.from(this.files).forEach(function (file) {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function (event) {
                            const imgWrap = document.createElement('div');
                            imgWrap.className = 'position-relative d-inline-block m-1';
                            imgWrap.innerHTML = `
                                <img src="${event.target.result}" class="rounded border shadow-sm" style="width: 80px; height: 80px; object-fit: cover;">
                            `;
                            target.appendChild(imgWrap);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    });

    // Confirmation dialogs
    document.querySelectorAll('[data-confirm]').forEach(function (element) {
        element.addEventListener('click', function (e) {
            const message = this.getAttribute('data-confirm') || 'Are you sure you want to proceed?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
});

// Helper to initialize QR code on element
function generateItemQR(elementId, text) {
    const el = document.getElementById(elementId);
    if (!el || typeof QRCode === 'undefined') return;
    el.innerHTML = '';
    new QRCode(el, {
        text: text,
        width: 140,
        height: 140,
        colorDark: "#1e40af",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.H
    });
}
