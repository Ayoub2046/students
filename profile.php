<?php
/**
 * User Profile & Password Management
 */
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/classes/User.php';

$pageTitle = "My Account Profile";
$userId = (int)$currentUser['id'];
$userModel = new User();
$userProfile = $userModel->findById($userId);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security token invalid. Please reload.';
    } else {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $newPass = $_POST['new_password'] ?? '';
        $confPass = $_POST['confirm_password'] ?? '';

        if (empty($fullName)) {
            $error = 'Full name cannot be empty.';
        } elseif (!empty($newPass) && ($newPass !== $confPass)) {
            $error = 'New passwords do not match.';
        } else {
            $updateData = [
                'full_name' => $fullName,
                'phone'     => $phone
            ];

            if (!empty($newPass)) {
                $updateData['password'] = $newPass;
            }

            if ($userModel->updateProfile($userId, $updateData)) {
                $_SESSION['user_name'] = $fullName;
                setFlash('success', 'Profile updated successfully.');
                header('Location: ' . APP_URL . '/profile.php');
                exit;
            } else {
                $error = 'Failed to update profile details.';
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container-fluid px-lg-4 py-4">
    <?= renderFlash() ?>

    <div class="row g-4">
        <div class="col-lg-3">
            <?php require_once __DIR__ . '/includes/sidebar.php'; ?>
        </div>

        <div class="col-lg-9">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger shadow-sm rounded-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= e($error) ?>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="bg-primary p-4 text-white">
                    <h4 class="fw-bold mb-1">Account & Security Profile</h4>
                    <p class="small text-white-75 mb-0">Manage your university contact information and login credentials</p>
                </div>

                <div class="card-body p-4 p-md-4.5 bg-white">
                    <form method="POST" action="<?= APP_URL ?>/profile.php">
                        <?= csrfField() ?>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Full Name *</label>
                                <input type="text" name="full_name" class="form-control bg-light" required value="<?= e($userProfile['full_name'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">University ID (Locked)</label>
                                <input type="text" class="form-control bg-light text-muted" readonly value="<?= e($userProfile['university_id'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Registered Email Address</label>
                                <input type="email" class="form-control bg-light text-muted" readonly value="<?= e($userProfile['email'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Phone Number</label>
                                <input type="tel" name="phone" class="form-control bg-light" placeholder="+1 (555) 000-0000" value="<?= e($userProfile['phone'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Faculty</label>
                                <input type="text" class="form-control bg-light text-muted" readonly value="<?= e($userProfile['faculty_name'] ?? 'General') ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Role Permission</label>
                                <input type="text" class="form-control bg-light text-muted text-uppercase" readonly value="<?= e($userProfile['role'] ?? 'student') ?>">
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="fw-bold text-primary mb-3"><i class="bi bi-shield-lock me-1"></i> Change Password (Leave blank to keep unchanged)</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">New Password</label>
                                <input type="password" name="new_password" class="form-control bg-light" placeholder="Minimum 6 characters">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control bg-light" placeholder="Repeat new password">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                                <i class="bi bi-save me-1"></i> Save Profile Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
