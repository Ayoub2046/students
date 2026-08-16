<?php
/**
 * Reset Password Handler
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Database.php';

$token = $_GET['token'] ?? '';
$pdo = Database::getInstance()->getConnection();
$error = '';

$stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = :tok AND reset_token_expiry > NOW() LIMIT 1");
$stmt->execute(['tok' => $token]);
$user = $stmt->fetch();

if (!$user && empty($_POST)) {
    setFlash('danger', 'Password reset token is invalid or has expired.');
    header('Location: ' . APP_URL . '/forgot-password.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPass = $_POST['password'] ?? '';
    $confPass = $_POST['confirm_password'] ?? '';
    $userId = (int)$_POST['user_id'];

    if ($newPass !== $confPass) {
        $error = 'Passwords do not match.';
    } elseif (strlen($newPass) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        $hash = password_hash($newPass, PASSWORD_BCRYPT);
        $upd = $pdo->prepare("UPDATE users SET password = :p, reset_token = NULL, reset_token_expiry = NULL WHERE id = :id");
        $upd->execute(['p' => $hash, 'id' => $userId]);
        setFlash('success', 'Your password has been reset successfully. Please log in.');
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

$pageTitle = "Set New Password";
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger shadow-sm rounded-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= e($error) ?>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="bg-primary p-4 text-white text-center">
                    <h4 class="fw-bold mb-1">Set New Password</h4>
                    <p class="small text-white-75 mb-0">Create a secure new password for your account</p>
                </div>
                <div class="card-body p-4 bg-white">
                    <form method="POST" action="<?= APP_URL ?>/reset-password.php">
                        <?= csrfField() ?>
                        <input type="hidden" name="user_id" value="<?= e($user['id'] ?? '') ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">New Password</label>
                            <input type="password" name="password" class="form-control bg-light" placeholder="Minimum 6 characters" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control bg-light" placeholder="Re-enter password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-3 fw-semibold">
                            <i class="bi bi-check2-circle me-1"></i> Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
