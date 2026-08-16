<?php
/**
 * Registration Page
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Database.php';

if (Auth::check()) {
    header('Location: ' . APP_URL . '/dashboard.php');
    exit;
}

$pageTitle = "Create University Account";
$error = '';
$success = '';

$pdo = Database::getInstance()->getConnection();
$faculties = $pdo->query("SELECT * FROM faculties WHERE status = 'active' ORDER BY name ASC")->fetchAll();
$departments = $pdo->query("SELECT * FROM departments WHERE status = 'active' ORDER BY name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security token invalid. Please reload.';
    } elseif ($_POST['password'] !== $_POST['confirm_password']) {
        $error = 'Passwords do not match.';
    } else {
        $auth = new Auth();
        $res = $auth->register([
            'full_name'     => $_POST['full_name'] ?? '',
            'university_id' => $_POST['university_id'] ?? '',
            'email'         => $_POST['email'] ?? '',
            'phone'         => $_POST['phone'] ?? '',
            'faculty_id'    => $_POST['faculty_id'] ?? null,
            'department_id' => $_POST['department_id'] ?? null,
            'role'          => $_POST['role'] ?? 'student',
            'password'      => $_POST['password'] ?? ''
        ]);

        if ($res['success']) {
            setFlash('success', 'Account registered successfully! Please log in.');
            header('Location: ' . APP_URL . '/login.php');
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
        <div class="col-lg-7">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger shadow-sm rounded-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= e($error) ?>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="bg-primary p-4 text-white">
                    <h4 class="fw-bold mb-1">Student & Staff Registration</h4>
                    <p class="small text-white-75 mb-0">Join the official University Lost & Found system to report items and submit claims</p>
                </div>

                <div class="card-body p-4 p-md-4.5 bg-white">
                    <form method="POST" action="<?= APP_URL ?>/register.php">
                        <?= csrfField() ?>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Full Name *</label>
                                <input type="text" name="full_name" class="form-control bg-light" placeholder="e.g. Alex Mercer" required value="<?= e($_POST['full_name'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">University ID (Student/Staff ID) *</label>
                                <input type="text" name="university_id" class="form-control bg-light" placeholder="e.g. STU-2026-105" required value="<?= e($_POST['university_id'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">University Email Address *</label>
                                <input type="email" name="email" class="form-control bg-light" placeholder="e.g. user@university.edu" required value="<?= e($_POST['email'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Phone Number</label>
                                <input type="tel" name="phone" class="form-control bg-light" placeholder="e.g. +1 (555) 019-2831" value="<?= e($_POST['phone'] ?? '') ?>">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Faculty</label>
                                <select name="faculty_id" class="form-select bg-light">
                                    <option value="">Select Faculty</option>
                                    <?php foreach ($faculties as $fac): ?>
                                        <option value="<?= $fac['id'] ?>"><?= e($fac['name']) ?> (<?= e($fac['code']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Department</label>
                                <select name="department_id" class="form-select bg-light">
                                    <option value="">Select Department</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?= $dept['id'] ?>"><?= e($dept['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Account Role</label>
                                <select name="role" class="form-select bg-light">
                                    <option value="student">Student</option>
                                    <option value="staff">University Staff</option>
                                </select>
                                <small class="text-muted" style="font-size: 0.7rem;">Officer and Admin roles are granted by system administrators.</small>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Password *</label>
                                <input type="password" name="password" class="form-control bg-light" placeholder="Minimum 6 characters" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold small text-muted">Confirm Password *</label>
                                <input type="password" name="confirm_password" class="form-control bg-light" placeholder="Repeat your password" required>
                            </div>

                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-primary w-100 py-2.5 rounded-3 fw-semibold shadow-sm">
                                    <i class="bi bi-person-check-fill me-1"></i> Register Account
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-footer bg-light p-3 text-center border-0">
                    <span class="small text-muted">Already registered?</span>
                    <a href="<?= APP_URL ?>/login.php" class="small fw-bold text-primary text-decoration-none ms-1">Sign In to Account</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
