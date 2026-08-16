<?php
/**
 * Claim Management and Verification
 */
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/AuditLog.php';
require_once __DIR__ . '/Notification.php';

class Claim {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public static function generateClaimCode(): string {
        $pdo = Database::getInstance()->getConnection();
        $prefix = 'CLM-' . date('Y') . '-';
        $stmt = $pdo->prepare("SELECT claim_code FROM claims WHERE claim_code LIKE :prefix ORDER BY id DESC LIMIT 1");
        $stmt->execute(['prefix' => $prefix . '%']);
        $lastCode = $stmt->fetchColumn();

        if ($lastCode) {
            $num = (int)substr($lastCode, strlen($prefix));
            $next = str_pad((string)($num + 1), 6, '0', STR_PAD_LEFT);
        } else {
            $next = '000001';
        }
        return $prefix . $next;
    }

    public function submit(int $itemId, int $claimantId, string $reason, array $qaPairs = []): array {
        try {
            $this->pdo->beginTransaction();

            // Check if item exists and is claimable
            $itemStmt = $this->pdo->prepare("SELECT * FROM items WHERE id = :id FOR UPDATE");
            $itemStmt->execute(['id' => $itemId]);
            $item = $itemStmt->fetch();

            if (!$item) {
                throw new Exception("Item not found.");
            }

            if (in_array($item['status'], ['returned', 'disposed', 'cancelled', 'rejected'])) {
                throw new Exception("This item is no longer available for claims.");
            }

            // Check if claimant already has a pending/approved claim for this item
            $chkStmt = $this->pdo->prepare("SELECT id FROM claims WHERE item_id = :item_id AND claimant_id = :claimant_id AND status IN ('pending', 'under_review', 'approved') LIMIT 1");
            $chkStmt->execute(['item_id' => $itemId, 'claimant_id' => $claimantId]);
            if ($chkStmt->fetch()) {
                throw new Exception("You already have an active claim submitted for this item.");
            }

            $claimCode = self::generateClaimCode();

            $sql = "INSERT INTO claims (claim_code, item_id, claimant_id, reason, status) 
                    VALUES (:claim_code, :item_id, :claimant_id, :reason, 'pending')";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'claim_code'  => $claimCode,
                'item_id'     => $itemId,
                'claimant_id' => $claimantId,
                'reason'      => trim($reason)
            ]);

            $claimId = (int)$this->pdo->lastInsertId();

            // Insert Verification Answers
            if (!empty($qaPairs)) {
                $qaStmt = $this->pdo->prepare("INSERT INTO claim_answers (claim_id, question, answer) VALUES (:claim_id, :question, :answer)");
                foreach ($qaPairs as $qa) {
                    if (!empty($qa['question']) && !empty($qa['answer'])) {
                        $qaStmt->execute([
                            'claim_id' => $claimId,
                            'question' => trim($qa['question']),
                            'answer'   => trim($qa['answer'])
                        ]);
                    }
                }
            }

            // Update item status to 'claimed' or 'under_verification'
            $updItem = $this->pdo->prepare("UPDATE items SET status = 'claimed' WHERE id = :id");
            $updItem->execute(['id' => $itemId]);

            AuditLog::log($claimantId, 'submit_claim', 'claim', $claimId, null, "Submitted claim {$claimCode} for item {$item['reference_code']}");

            $this->pdo->commit();

            // Notify claimant
            Notification::send($claimantId, "Claim Submitted: {$claimCode}", "Your claim for '{$item['title']}' has been registered. An officer will review your verification details.", 'claim_status', 'my-claims.php');

            // Notify Officers / Admin
            $officers = $this->pdo->query("SELECT id FROM users WHERE role IN ('officer', 'admin') AND status = 'active'")->fetchAll();
            foreach ($officers as $off) {
                Notification::send((int)$off['id'], "New Claim Submitted: {$claimCode}", "New claim on item '{$item['reference_code']}' awaiting review.", 'officer_alert', "officer/claim-view.php?id={$claimId}");
            }

            return ['success' => true, 'claim_id' => $claimId, 'claim_code' => $claimCode];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function findById(int $id): ?array {
        $sql = "SELECT c.*, i.reference_code AS item_ref, i.title AS item_title, i.type AS item_type, i.status AS item_status,
                       i.brand, i.model, i.color, i.serial_number, i.identification_details,
                       cat.name AS category_name, loc.name AS location_name,
                       u.full_name AS claimant_name, u.university_id AS claimant_uid, u.email AS claimant_email, u.phone AS claimant_phone,
                       rev.full_name AS reviewer_name
                FROM claims c
                JOIN items i ON c.item_id = i.id
                LEFT JOIN categories cat ON i.category_id = cat.id
                LEFT JOIN locations loc ON i.location_id = loc.id
                JOIN users u ON c.claimant_id = u.id
                LEFT JOIN users rev ON c.reviewed_by = rev.id
                WHERE c.id = :id LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $claim = $stmt->fetch();

        if (!$claim) return null;

        // Fetch Answers
        $ansStmt = $this->pdo->prepare("SELECT * FROM claim_answers WHERE claim_id = :id");
        $ansStmt->execute(['id' => $id]);
        $claim['answers'] = $ansStmt->fetchAll();

        // Primary Image
        $imgStmt = $this->pdo->prepare("SELECT image_path FROM item_images WHERE item_id = :item_id ORDER BY is_primary DESC LIMIT 1");
        $imgStmt->execute(['item_id' => $claim['item_id']]);
        $claim['item_image'] = $imgStmt->fetchColumn();

        return $claim;
    }

    public function getAll(array $filters = [], int $limit = 20, int $offset = 0): array {
        $conditions = ["1=1"];
        $params = [];

        if (!empty($filters['claimant_id'])) {
            $conditions[] = "c.claimant_id = :claimant_id";
            $params['claimant_id'] = (int)$filters['claimant_id'];
        }

        if (!empty($filters['item_id'])) {
            $conditions[] = "c.item_id = :item_id";
            $params['item_id'] = (int)$filters['item_id'];
        }

        if (!empty($filters['status'])) {
            $conditions[] = "c.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = "(c.claim_code LIKE :search OR i.title LIKE :search OR i.reference_code LIKE :search OR u.full_name LIKE :search OR u.university_id LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = implode(' AND ', $conditions);
        $sql = "SELECT c.*, i.reference_code AS item_ref, i.title AS item_title, i.type AS item_type, i.status AS item_status,
                       u.full_name AS claimant_name, u.university_id AS claimant_uid, u.email AS claimant_email,
                       (SELECT image_path FROM item_images WHERE item_id = i.id ORDER BY is_primary DESC LIMIT 1) AS primary_image
                FROM claims c
                JOIN items i ON c.item_id = i.id
                JOIN users u ON c.claimant_id = u.id
                WHERE {$whereClause}
                ORDER BY c.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function count(array $filters = []): int {
        $conditions = ["1=1"];
        $params = [];

        if (!empty($filters['claimant_id'])) {
            $conditions[] = "c.claimant_id = :claimant_id";
            $params['claimant_id'] = (int)$filters['claimant_id'];
        }
        if (!empty($filters['status'])) {
            $conditions[] = "c.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $conditions[] = "(c.claim_code LIKE :search OR i.title LIKE :search OR u.full_name LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = implode(' AND ', $conditions);
        $sql = "SELECT COUNT(*) FROM claims c JOIN items i ON c.item_id = i.id JOIN users u ON c.claimant_id = u.id WHERE {$whereClause}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function review(int $claimId, int $officerId, string $status, ?string $reason = null): bool {
        $claim = $this->findById($claimId);
        if (!$claim) return false;

        $sql = "UPDATE claims SET status = :status, reviewed_by = :officer_id, reviewed_at = NOW(), rejection_reason = :reason WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $success = $stmt->execute([
            'status'     => $status,
            'officer_id' => $officerId,
            'reason'     => $reason,
            'id'         => $claimId
        ]);

        if ($success) {
            AuditLog::log($officerId, 'review_claim', 'claim', $claimId, ['old_status' => $claim['status']], ['new_status' => $status, 'reason' => $reason]);

            // If approved, update item to 'ready_for_handover'
            if ($status === 'approved') {
                $this->pdo->prepare("UPDATE items SET status = 'ready_for_handover' WHERE id = :id")->execute(['id' => $claim['item_id']]);
                Notification::send($claim['claimant_id'], "Claim APPROVED: {$claim['claim_code']}", "Your claim for '{$claim['item_title']}' has been approved! Visit the Lost & Found office to verify ID and complete collection.", 'claim_approved', 'my-claims.php');
            } elseif ($status === 'rejected') {
                // If no other active claims, restore item status to available
                $this->pdo->prepare("UPDATE items SET status = 'available' WHERE id = :id")->execute(['id' => $claim['item_id']]);
                Notification::send($claim['claimant_id'], "Claim Update: {$claim['claim_code']}", "Your claim for '{$claim['item_title']}' was rejected." . ($reason ? " Reason: {$reason}" : ""), 'claim_rejected', 'my-claims.php');
            }
        }

        return $success;
    }
}
