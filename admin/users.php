<?php
/**
 * Admin User Management (RBAC & Account Provisioning)
 */
require_once __DIR__ . '/../includes/role-check.php';
requireRole('admin');

require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/AuditLog.php';
require_once __DIR__ . '/../classes/Database.php';

$pageTitle = "User & Access Management";
$userModel = new User();
$pdo = Database::getInstance()->getConnection();

$faculties = $pdo->query("SELECT * FROM faculties ORDER BY name ASC")->fetchAll();
$departments = $pdo->query("SELECT * FROM departments ORDER BY name ASC")->fetchAll();

$error = '';
$search = $_GET['search'] ?? '';
$roleFilter = $_GET['role'] ?? '';

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security token invalid.';
    } else {
        $auth = new Auth();
        $res = $auth->register([
            'full_name'     => $_POST['full_name'],
            'university_id' => $_POST['university_id'],
            'email'         => $_POST['email'],
            'phone'         => $_POST['phone'] ?? '',
            'faculty_id'    => $_POST['faculty_id'] ?: null,
            'department_id' => $_POST['department_id'] ?: null,
            'role'          => $_POST['role'],
            'password'      => $_POST['password']
        ]);

        if ($res['success']) {
            AuditLog::log('user_created', 'users', $res['user_id'], "Admin created account for {$_POST['email']} with role {$_POST['role']}");
            setFlash('success', 'User account successfully provisioned.');
            header('Location: ' . APP_URL . '/admin/users.php');
            exit;
        } else {
            $error = $res['message'];
        }
    }
}

// Handle Update Role / Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $targetUserId = (int)$_POST['user_id'];
        $newRole = $_POST['role'];
        $newStatus = $_POST['status'];

        $upd = $pdo->prepare("UPDATE users SET role = :r, status = :s, updated_at = NOW() WHERE id = :id");
        $upd->execute(['r' => $newRole, 's' => $newStatus, 'id' => $targetUserId]);

        // If password reset requested
        if (!empty($_POST['reset_password'])) {
            $userModel->resetPassword($targetUserId, $_POST['reset_password']);
        }

        AuditLog::log('user_updated', 'users', $targetUserId, "Admin updated user #{$targetUserId} to role: {$newRole}, status: {$newStatus}");
        setFlash('success', 'User permissions and status updated.');
        header('Location: ' . APP_URL . '/admin/users.php');
        exit;
    }
}

// Query Users
$usersSql = "SELECT u.*, f.name AS faculty_name, d.name AS department_name
             FROM users u
             LEFT JOIN faculties f ON u.faculty_id = f.id
             LEFT JOIN departments d ON u.department_id = d.id
             WHERE 1=1";
$params = [];

if (!empty($search)) {
    $usersSql .= " AND (u.full_name LIKE :s OR u.email LIKE :s OR u.university_id LIKE :s)";
    $params['s'] = "%{$search}%";
}
if (!empty($roleFilter)) {
    $usersSql .= " AND u.role = :r";
    $params['r'] = $roleFilter;
}
$usersSql .= " ORDER BY u.created_at DESC";

$stmt = $pdo->prepare($usersSql);
$stmt->execute($params);
$users = $stmt->fetchAll();

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
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger shadow-sm rounded-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= e($error) ?>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <h4 class="fw-bold mb-1">User & Access Management</h4>
                        <p class="text-muted small mb-0">Manage roles, permissions, department affiliations, and account statuses.</p>
                    </div>
                    <button class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#createUserModal">
                        <i class="bi bi-person-plus-fill me-1"></i> Provision New User
                    </button>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
                <form method="GET" action="" class="row g-2">
                    <div class="col-md-7">
                        <input type="text" name="search" class="form-control bg-light" placeholder="Search by name, email, student ID..." value="<?= e($search) ?>">
                    </div>
                    <div class="col-md-3">
                        <select name="role" class="form-select bg-light">
                            <option value="">All Roles</option>
                            <option value="student" <?= ($roleFilter === 'student') ? 'selected' : '' ?>>Student</option>
                            <option value="staff" <?= ($roleFilter === 'staff') ? 'selected' : '' ?>>Staff</option>
                            <option value="officer" <?= ($roleFilter === 'officer') ? 'selected' : '' ?>>Officer</option>
                            <option value="admin" <?= ($roleFilter === 'admin') ? 'selected' : '' ?>>Administrator</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100 rounded-3">Filter</button>
                        <a href="<?= APP_URL ?>/admin/users.php" class="btn btn-light border rounded-3"><i class="bi bi-arrow-counterclockwise"></i></a>
                    </div>
                </form>
            </div>

            <!-- Users Table -->
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                <div class="table-responsive">
                    <table class="table align-middle mb-0 table-hover">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-4">User</th>
                                <th>University ID</th>
                                <th>Role</th>
                                <th>Faculty & Dept</th>
                                <th>Status</th>
                                <th>Joined</th>
                                <th class="text-end pe-4">Manage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">No users match your criteria.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($users as $u): ?>
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-bold text-dark d-block"><?= e($u['full_name']) ?></span>
                                            <small class="text-muted"><?= e($u['email']) ?></small>
                                        </td>
                                        <td><code><?= e($u['university_id']) ?></code></td>
                                        <td>
                                            <?php if ($u['role'] === 'admin'): ?>
                                                <span class="badge bg-danger">ADMIN</span>
                                            <?php elseif ($u['role'] === 'officer'): ?>
                                                <span class="badge bg-primary">OFFICER</span>
                                            <?php elseif ($u['role'] === 'staff'): ?>
                                                <span class="badge bg-info text-dark">STAFF</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">STUDENT</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="small d-block fw-semibold"><?= e($u['faculty_name'] ?? 'General') ?></span>
                                            <small class="text-muted"><?= e($u['department_name'] ?? '-') ?></small>
                                        </td>
                                        <td>
                                            <?php if ($u['status'] === 'active'): ?>
                                                <span class="badge bg-success-subtle text-success border">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger border"><?= ucfirst($u['status']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="small text-muted"><?= formatDate($u['created_at']) ?></span></td>
                                        <td class="text-end pe-4">
                                            <button class="btn btn-light btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#editUserModal<?= $u['id'] ?>">
                                                <i class="bi bi-pencil-square me-1"></i> Edit
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Edit User Modal -->
                                    <div class="modal fade" id="editUserModal<?= $u['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content rounded-4 border-0">
                                                <div class="modal-header bg-primary text-white p-3.5">
                                                    <h5 class="modal-title fw-bold">Edit User Permissions: <?= e($u['full_name']) ?></h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="">
                                                    <?= csrfField() ?>
                                                    <input type="hidden" name="update_user" value="1">
                                                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">

                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold small text-muted">Role Permission</label>
                                                            <select name="role" class="form-select bg-light">
                                                                <option value="student" <?= ($u['role'] === 'student') ? 'selected' : '' ?>>Student</option>
                                                                <option value="staff" <?= ($u['role'] === 'staff') ? 'selected' : '' ?>>Staff</option>
                                                                <option value="officer" <?= ($u['role'] === 'officer') ? 'selected' : '' ?>>Lost & Found Officer</option>
                                                                <option value="admin" <?= ($u['role'] === 'admin') ? 'selected' : '' ?>>System Administrator</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold small text-muted">Account Status</label>
                                                            <select name="status" class="form-select bg-light">
                                                                <option value="active" <?= ($u['status'] === 'active') ? 'selected' : '' ?>>Active</option>
                                                                <option value="suspended" <?= ($u['status'] === 'suspended') ? 'selected' : '' ?>>Suspended</option>
                                                                <option value="inactive" <?= ($u['status'] === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                                                            </select>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold small text-muted">Reset Password (Leave blank to keep current)</label>
                                                            <input type="password" name="reset_password" class="form-control bg-light" placeholder="New temporary password">
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer p-3 bg-light border-0">
                                                        <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Create New User -->
<div class="modal fade" id="createUserModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header bg-primary text-white p-3.5">
                <h5 class="modal-title fw-bold">Provision New User Account</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <?= csrfField() ?>
                <input type="hidden" name="create_user" value="1">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Full Name *</label>
                            <input type="text" name="full_name" class="form-control bg-light" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">University ID *</label>
                            <input type="text" name="university_id" class="form-control bg-light" placeholder="STU-2026-..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">University Email *</label>
                            <input type="email" name="email" class="form-control bg-light" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Phone Number</label>
                            <input type="tel" name="phone" class="form-control bg-light">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Faculty</label>
                            <select name="faculty_id" class="form-select bg-light">
                                <option value="">Select Faculty</option>
                                <?php foreach ($faculties as $f): ?>
                                    <option value="<?= $f['id'] ?>"><?= e($f['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Department</label>
                            <select name="department_id" class="form-select bg-light">
                                <option value="">Select Department</option>
                                <?php foreach ($departments as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= e($d['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Role *</label>
                            <select name="role" class="form-select bg-light" required>
                                <option value="student">Student</option>
                                <option value="staff">Staff</option>
                                <option value="officer">Lost & Found Officer</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small text-muted">Initial Password *</label>
                            <input type="password" name="password" class="form-control bg-light" required minlength="6">
                        </div>
                    </div>
                </div>
                <div class="modal-footer p-3 bg-light border-0">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
