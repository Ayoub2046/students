<?php
/**
 * Login Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Auth.php';

// Redirect if already logged in
if (Auth::check()) {
    $role = Auth::role();
    if ($role === 'admin') {
        header('Location: ' . APP_URL . '/admin/dashboard.php');
    } elseif ($role === 'officer') {
        header('Location: ' . APP_URL . '/officer/dashboard.php');
    } else {
        header('Location: ' . APP_URL . '/dashboard.php');
    }
    exit;
}

$pageTitle = "Login to Portal";
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security token invalid. Please try again.';
    } else {
        $auth = new Auth();
        $res = $auth->login($_POST['login_query'] ?? '', $_POST['password'] ?? '');

        if ($res['success']) {
            setFlash('success', "Welcome back, " . e($_SESSION['user_name']) . "!");
            if ($res['role'] === 'admin') {
                header('Location: ' . APP_URL . '/admin/dashboard.php');
            } elseif ($res['role'] === 'officer') {
                header('Location: ' . APP_URL . '/officer/dashboard.php');
            } else {
                header('Location: ' . APP_URL . '/dashboard.php');
            }
            exit;
        } else {
            $error = $res['message'];
        }
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <?= renderFlash() ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger shadow-sm rounded-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= e($error) ?>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="bg-primary p-4 text-white text-center">
                    <div class="bg-white text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-2 shadow-sm" style="width: 54px; height: 54px;">
                        <i class="bi bi-person-lock fs-2"></i>
                    </div>
                    <h4 class="fw-bold mb-1">Campus Sign In</h4>
                    <p class="small text-white-75 mb-0">Enter your credentials to access Lost & Found portal</p>
                </div>

                <div class="card-body p-4 p-md-4.5 bg-white">
                    <form method="POST" action="<?= APP_URL ?>/login.php">
                        <?= csrfField() ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-muted">Email or University ID</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                <input type="text" name="login_query" id="login_query" class="form-control bg-light border-start-0" placeholder="e.g. admin@university.local or ADM-2026-001" required value="<?= e($_POST['login_query'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label fw-semibold small text-muted mb-0">Password</label>
                                <a href="<?= APP_URL ?>/forgot-password.php" class="small text-decoration-none">Forgot Password?</a>
                            </div>
                            <div class="input-group mt-1">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                                <input type="password" name="password" id="password" class="form-control bg-light border-start-0" placeholder="Enter your password" required>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="rememberMe" checked>
                            <label class="form-check-label small text-muted" for="rememberMe">
                                Keep me signed in
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-3 fw-semibold shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Portal
                        </button>
                    </form>

                    <!-- Demo Credentials Helper Pills -->
                    <div class="mt-4 pt-3 border-top">
                        <small class="text-muted fw-bold d-block mb-2 text-uppercase" style="font-size: 0.72rem;">Quick Demo Fill:</small>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="fillCreds('admin@university.local', 'Admin@12345')">
                                <i class="bi bi-shield-fill text-danger me-1"></i> Admin
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="fillCreds('officer@university.local', 'Officer@12345')">
                                <i class="bi bi-person-badge-fill text-info me-1"></i> Officer
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="fillCreds('student@university.local', 'Student@12345')">
                                <i class="bi bi-mortarboard-fill text-success me-1"></i> Student
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light p-3 text-center border-0">
                    <span class="small text-muted">Don't have an account?</span>
                    <a href="<?= APP_URL ?>/register.php" class="small fw-bold text-primary text-decoration-none ms-1">Register as Student/Staff</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function fillCreds(login, pass) {
    document.getElementById('login_query').value = login;
    document.getElementById('password').value = pass;
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
