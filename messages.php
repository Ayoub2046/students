<?php
/**
 * In-App Item Messaging & Inquiries
 */
require_once __DIR__ . '/includes/auth-check.php';
require_once __DIR__ . '/classes/Item.php';
require_once __DIR__ . '/classes/Database.php';

$pageTitle = "Item Inquiries & Messages";
$userId = (int)$currentUser['id'];
$pdo = Database::getInstance()->getConnection();

$selectedItemId = (int)($_GET['item_id'] ?? 0);

// If sending a message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $itemId = (int)$_POST['item_id'];
        $receiverId = (int)$_POST['receiver_id'];
        $msgText = trim($_POST['message']);

        // Default receiver to an active officer if not designated
        if ($receiverId <= 0) {
            $receiverId = (int)$pdo->query("SELECT id FROM users WHERE role IN ('officer', 'admin') AND status = 'active' LIMIT 1")->fetchColumn();
        }

        $ins = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, item_id, message) VALUES (:s, :r, :i, :m)");
        $ins->execute([
            's' => $userId,
            'r' => $receiverId,
            'i' => $itemId,
            'm' => $msgText
        ]);

        header("Location: " . APP_URL . "/messages.php?item_id={$itemId}");
        exit;
    }
}

// Fetch user's active conversations grouped by item
$threadsSql = "SELECT DISTINCT m.item_id, i.reference_code, i.title, i.type,
                      (SELECT message FROM messages WHERE item_id = m.item_id ORDER BY created_at DESC LIMIT 1) AS last_message,
                      (SELECT created_at FROM messages WHERE item_id = m.item_id ORDER BY created_at DESC LIMIT 1) AS last_time
               FROM messages m
               JOIN items i ON m.item_id = i.id
               WHERE m.sender_id = :uid OR m.receiver_id = :uid
               ORDER BY last_time DESC";
$tStmt = $pdo->prepare($threadsSql);
$tStmt->execute(['uid' => $userId]);
$threads = $tStmt->fetchAll();

// If no thread selected, pick the first
if ($selectedItemId <= 0 && !empty($threads)) {
    $selectedItemId = (int)$threads[0]['item_id'];
}

// Fetch active thread messages
$activeMessages = [];
$activeItem = null;
if ($selectedItemId > 0) {
    $itemModel = new Item();
    $activeItem = $itemModel->findById($selectedItemId, true);

    $msgSql = "SELECT m.*, u.full_name AS sender_name, u.role AS sender_role 
               FROM messages m
               JOIN users u ON m.sender_id = u.id
               WHERE m.item_id = :item_id AND (m.sender_id = :uid OR m.receiver_id = :uid OR :is_staff = 1)
               ORDER BY m.created_at ASC";
    $mStmt = $pdo->prepare($msgSql);
    $mStmt->execute([
        'item_id'  => $selectedItemId,
        'uid'      => $userId,
        'is_staff' => in_array($currentUser['role'], ['admin', 'officer']) ? 1 : 0
    ]);
    $activeMessages = $mStmt->fetchAll();

    // Mark as read
    $upd = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE item_id = :item_id AND receiver_id = :uid");
    $upd->execute(['item_id' => $selectedItemId, 'uid' => $userId]);
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
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden" style="min-height: 600px;">
                <div class="row g-0 h-100">
                    <!-- Conversations List Column -->
                    <div class="col-md-4 border-end">
                        <div class="p-3 border-bottom bg-light">
                            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-chat-left-text me-1 text-primary"></i> Inquiries by Item</h6>
                        </div>
                        <div class="list-group list-group-flush overflow-auto" style="max-height: 520px;">
                            <?php if (empty($threads)): ?>
                                <div class="p-4 text-center text-muted small">
                                    <i class="bi bi-chat-square-dots fs-3 d-block mb-1"></i>
                                    No item conversations yet.
                                </div>
                            <?php else: ?>
                                <?php foreach ($threads as $th): ?>
                                    <a href="<?= APP_URL ?>/messages.php?item_id=<?= $th['item_id'] ?>" class="list-group-item list-group-item-action p-3 border-bottom <?= ($selectedItemId == $th['item_id']) ? 'active' : '' ?>">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="fw-bold text-truncate" style="max-width: 140px;"><?= e($th['title']) ?></span>
                                            <small class="text-muted" style="font-size: 0.7rem;"><?= timeAgo($th['last_time']) ?></small>
                                        </div>
                                        <code class="small d-block mb-1"><?= e($th['reference_code']) ?></code>
                                        <p class="small text-muted text-truncate mb-0" style="font-size: 0.78rem;">
                                            <?= e($th['last_message']) ?>
                                        </p>
                                    </a>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Chat Box Area -->
                    <div class="col-md-8 d-flex flex-column justify-content-between">
                        <?php if ($activeItem): ?>
                            <!-- Chat Header -->
                            <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark"><?= e($activeItem['title']) ?></h6>
                                    <code class="small text-primary"><?= e($activeItem['reference_code']) ?></code>
                                </div>
                                <a href="<?= APP_URL ?>/item-details.php?ref=<?= e($activeItem['reference_code']) ?>" class="btn btn-outline-primary btn-sm rounded-pill">
                                    <i class="bi bi-box-arrow-up-right me-1"></i> View Item
                                </a>
                            </div>

                            <!-- Chat Messages Feed -->
                            <div class="p-4 overflow-auto flex-grow-1" style="max-height: 420px; min-height: 350px;">
                                <?php if (empty($activeMessages)): ?>
                                    <div class="text-center text-muted py-5">
                                        <i class="bi bi-chat-dots fs-2 d-block mb-2"></i>
                                        Send your first question or inquiry regarding this item.
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($activeMessages as $m): ?>
                                        <?php $isMe = ($m['sender_id'] == $userId); ?>
                                        <div class="d-flex flex-column <?= $isMe ? 'align-items-end' : 'align-items-start' ?> mb-3">
                                            <div class="small fw-semibold text-muted mb-1">
                                                <?= e($m['sender_name']) ?> <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.65rem;"><?= e($m['sender_role']) ?></span>
                                            </div>
                                            <div class="p-3 rounded-4 shadow-sm <?= $isMe ? 'bg-primary text-white' : 'bg-light text-dark border' ?>" style="max-width: 80%;">
                                                <p class="mb-0 small"><?= nl2br(e($m['message'])) ?></p>
                                            </div>
                                            <small class="text-muted mt-1" style="font-size: 0.7rem;"><?= timeAgo($m['created_at']) ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>

                            <!-- Chat Input Form -->
                            <div class="p-3 border-top bg-white">
                                <form method="POST" action="<?= APP_URL ?>/messages.php">
                                    <?= csrfField() ?>
                                    <input type="hidden" name="item_id" value="<?= $activeItem['id'] ?>">
                                    <input type="hidden" name="receiver_id" value="<?= ($userId == $activeItem['reported_by']) ? 2 : $activeItem['reported_by'] ?>">

                                    <div class="input-group">
                                        <input type="text" name="message" class="form-control bg-light" placeholder="Type an inquiry or handover question..." required autocomplete="off">
                                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                                            <i class="bi bi-send-fill me-1"></i> Send
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="d-flex align-items-center justify-content-center h-100 text-muted p-5">
                                <div class="text-center">
                                    <i class="bi bi-chat-left-dots display-3 mb-2 d-block"></i>
                                    <h5>Select an Item Conversation</h5>
                                    <p class="small">Choose an item from the left panel or click "Send Officer Inquiry" on any item page.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
