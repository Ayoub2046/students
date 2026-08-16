<?php
/**
 * Physical Storage and Unclaimed Item Management
 */
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/AuditLog.php';

class Storage {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function getLocations(): array {
        $sql = "SELECT s.*, (SELECT COUNT(*) FROM item_storage WHERE storage_location_id = s.id) AS current_items 
                FROM storage_locations s ORDER BY s.room, s.shelf, s.box";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function assignItem(int $itemId, int $storageLocationId, int $officerId, ?string $notes = null): bool {
        try {
            $this->pdo->beginTransaction();

            $chk = $this->pdo->prepare("SELECT id FROM item_storage WHERE item_id = :id LIMIT 1");
            $chk->execute(['id' => $itemId]);
            $exists = $chk->fetchColumn();

            if ($exists) {
                $sql = "UPDATE item_storage SET storage_location_id = :slid, received_by = :off, received_at = NOW(), notes = :notes WHERE item_id = :id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'slid'  => $storageLocationId,
                    'off'   => $officerId,
                    'notes' => $notes,
                    'id'    => $itemId
                ]);
            } else {
                $sql = "INSERT INTO item_storage (item_id, storage_location_id, received_by, received_at, notes) 
                        VALUES (:id, :slid, :off, NOW(), :notes)";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'id'    => $itemId,
                    'slid'  => $storageLocationId,
                    'off'   => $officerId,
                    'notes' => $notes
                ]);
            }

            AuditLog::log($officerId, 'assign_storage', 'item_storage', $itemId, null, "Assigned item to storage location ID {$storageLocationId}");

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return false;
        }
    }

    public function getUnclaimedItems(int $daysThreshold = 90): array {
        $sql = "SELECT i.*, c.name AS category_name, l.name AS location_name, sl.name AS storage_name, sl.room, sl.shelf, sl.box,
                       DATEDIFF(NOW(), i.created_at) AS days_in_system
                FROM items i
                LEFT JOIN categories c ON i.category_id = c.id
                LEFT JOIN locations l ON i.location_id = l.id
                LEFT JOIN item_storage ist ON i.id = ist.item_id
                LEFT JOIN storage_locations sl ON ist.storage_location_id = sl.id
                WHERE i.type = 'found' 
                  AND i.status IN ('approved', 'available')
                  AND DATEDIFF(NOW(), i.created_at) >= :days
                ORDER BY i.created_at ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':days', $daysThreshold, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function processDisposal(int $itemId, int $officerId, string $actionType, string $notes): bool {
        $newStatus = ($actionType === 'donate') ? 'unclaimed' : 'disposed';
        $stmt = $this->pdo->prepare("UPDATE items SET status = :status, rejection_reason = :notes WHERE id = :id");
        $success = $stmt->execute(['status' => $newStatus, 'notes' => $notes, 'id' => $itemId]);

        if ($success) {
            AuditLog::log($officerId, 'unclaimed_disposal', 'item', $itemId, null, "Item {$actionType}: {$notes}");
        }
        return $success;
    }
}
