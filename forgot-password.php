<?php
/**
 * Forgot Password Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Database.php';

$pageTitle = "Forgot Password";
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pdo = Database::getInstance()->getConnection();
    $stmt = $pdo->prepare("SELECT id, full_name FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $u = $stmt->fetch();

    if ($u) {
        $token = bin2hex(random_bytes(24));
        $expiry = date('Y-m-d H:i:s', time() + 3600);
        $upd = $pdo->prepare("UPDATE users SET reset_token = :tok, reset_token_expiry = :exp WHERE id = :id");
        $upd->execute(['tok' => $token, 'exp' => $expiry, 'id' => $u['id']]);
        
        $resetLink = APP_URL . "/reset-password.php?token=" . $token;
        $msg = "A password reset link has been generated: <a href='{$resetLink}' class='fw-bold'>Click here to reset your password</a> (Valid for 1 hour).";
    } else {
        $msg = "If this email exists in our system, reset instructions have been dispatched.";
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <?php if (!empty($msg)): ?>
                <div class="alert alert-info shadow-sm rounded-3">
                    <i class="bi bi-info-circle-fill me-2"></i> <?= $msg ?>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="bg-primary p-4 text-white text-center">
                    <i class="bi bi-key-fill fs-2 mb-2 d-block"></i>
                    <h4 class="fw-bold mb-1">Reset Password</h4>
                    <p class="small text-white-75 mb-0">Enter your university email to recover your account</p>
                </div>
                <div class="card-body p-4 bg-white">
                    <form method="POST" action="<?= APP_URL ?>/forgot-password.php">
                        <?= csrfField() ?>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">University Email Address</label>
                            <input type="email" name="email" class="form-control bg-light" placeholder="user@university.edu" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-3 fw-semibold">
                            <i class="bi bi-send me-1"></i> Send Reset Link
                        </button>
                    </form>
                </div>
                <div class="card-footer bg-light p-3 text-center border-0">
                    <a href="<?= APP_URL ?>/login.php" class="small fw-semibold text-primary text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i> Back to Sign In
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
