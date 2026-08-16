<?php
/**
 * Item Entity and Business Logic
 */
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/AuditLog.php';
require_once __DIR__ . '/Notification.php';
require_once __DIR__ . '/Matching.php';

class Item {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public static function generateReference(string $type): string {
        $pdo = Database::getInstance()->getConnection();
        $prefix = strtoupper($type) . '-' . date('Y') . '-';
        $stmt = $pdo->prepare("SELECT reference_code FROM items WHERE reference_code LIKE :prefix ORDER BY id DESC LIMIT 1");
        $stmt->execute(['prefix' => $prefix . '%']);
        $lastRef = $stmt->fetchColumn();

        if ($lastRef) {
            $numPart = (int)substr($lastRef, strlen($prefix));
            $nextNum = str_pad((string)($numPart + 1), 6, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '000001';
        }
        return $prefix . $nextNum;
    }

    public function create(array $data, array $imageFiles = []): array {
        try {
            $this->pdo->beginTransaction();

            $type = in_array($data['type'] ?? '', ['lost', 'found']) ? $data['type'] : 'lost';
            $refCode = self::generateReference($type);

            // Default approval requirement
            $requiresApproval = true;
            $status = $requiresApproval ? 'pending' : 'available';

            $sql = "INSERT INTO items (
                reference_code, type, title, description, category_id, brand, model, color,
                serial_number, identification_details, date_lost, date_found, time_lost, time_found,
                location_id, reported_by, has_physical_possession, status, privacy_level
            ) VALUES (
                :reference_code, :type, :title, :description, :category_id, :brand, :model, :color,
                :serial_number, :identification_details, :date_lost, :date_found, :time_lost, :time_found,
                :location_id, :reported_by, :has_physical_possession, :status, :privacy_level
            )";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'reference_code'           => $refCode,
                'type'                     => $type,
                'title'                    => trim($data['title']),
                'description'              => trim($data['description']),
                'category_id'              => (int)$data['category_id'],
                'brand'                    => trim($data['brand'] ?? '') ?: null,
                'model'                    => trim($data['model'] ?? '') ?: null,
                'color'                    => trim($data['color'] ?? '') ?: null,
                'serial_number'            => trim($data['serial_number'] ?? '') ?: null,
                'identification_details'   => trim($data['identification_details'] ?? '') ?: null,
                'date_lost'                => !empty($data['date_lost']) ? $data['date_lost'] : null,
                'date_found'               => !empty($data['date_found']) ? $data['date_found'] : null,
                'time_lost'                => !empty($data['time_lost']) ? $data['time_lost'] : null,
                'time_found'               => !empty($data['time_found']) ? $data['time_found'] : null,
                'location_id'              => (int)$data['location_id'],
                'reported_by'              => (int)$data['reported_by'],
                'has_physical_possession'  => !empty($data['has_physical_possession']) ? 1 : 0,
                'status'                   => $status,
                'privacy_level'            => $data['privacy_level'] ?? 'public'
            ]);

            $itemId = (int)$this->pdo->lastInsertId();

            // Handle images
            if (!empty($imageFiles) && is_array($imageFiles)) {
                $isPrimary = 1;
                foreach ($imageFiles as $fileName) {
                    if ($fileName) {
                        $imgStmt = $this->pdo->prepare("INSERT INTO item_images (item_id, image_path, is_primary) VALUES (:item_id, :image_path, :is_primary)");
                        $imgStmt->execute([
                            'item_id'    => $itemId,
                            'image_path' => $fileName,
                            'is_primary' => $isPrimary
                        ]);
                        $isPrimary = 0;
                    }
                }
            }

            AuditLog::log($data['reported_by'], 'create_report', 'item', $itemId, null, "Reported {$type} item: {$refCode}");

            $this->pdo->commit();

            // Run matching engine asynchronously/subsequently
            Matching::findAndNotifyMatches($itemId);

            return ['success' => true, 'item_id' => $itemId, 'reference_code' => $refCode];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Item creation error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function findById(int $id, bool $includePrivate = false): ?array {
        $sql = "SELECT i.*, c.name AS category_name, c.icon AS category_icon, 
                       l.name AS location_name, l.building, l.campus, l.latitude, l.longitude,
                       u.full_name AS reporter_name, u.university_id AS reporter_uid, u.email AS reporter_email, u.phone AS reporter_phone,
                       s.id AS storage_id, s.storage_location_id, sl.name AS storage_name, sl.room AS storage_room, sl.shelf AS storage_shelf, sl.box AS storage_box, sl.position AS storage_position
                FROM items i
                LEFT JOIN categories c ON i.category_id = c.id
                LEFT JOIN locations l ON i.location_id = l.id
                LEFT JOIN users u ON i.reported_by = u.id
                LEFT JOIN item_storage s ON i.id = s.item_id
                LEFT JOIN storage_locations sl ON s.storage_location_id = sl.id
                WHERE i.id = :id LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $item = $stmt->fetch();

        if (!$item) return null;

        // Fetch images
        $imgStmt = $this->pdo->prepare("SELECT * FROM item_images WHERE item_id = :id ORDER BY is_primary DESC, id ASC");
        $imgStmt->execute(['id' => $id]);
        $item['images'] = $imgStmt->fetchAll();

        // Privacy filter for non-officers/non-reporters
        if (!$includePrivate) {
            unset($item['reporter_phone'], $item['reporter_email'], $item['reporter_uid'], $item['storage_room'], $item['storage_shelf'], $item['storage_box'], $item['storage_position']);
            // Mask sensitive serial numbers partially for public view
            if (!empty($item['serial_number'])) {
                $len = strlen($item['serial_number']);
                $item['serial_number_public'] = $len > 4 ? substr($item['serial_number'], 0, 2) . str_repeat('*', $len - 4) . substr($item['serial_number'], -2) : '****';
            }
        }

        return $item;
    }

    public function findByReference(string $ref, bool $includePrivate = false): ?array {
        $stmt = $this->pdo->prepare("SELECT id FROM items WHERE reference_code = :ref LIMIT 1");
        $stmt->execute(['ref' => trim($ref)]);
        $id = $stmt->fetchColumn();
        return $id ? $this->findById((int)$id, $includePrivate) : null;
    }

    public function getItems(array $filters = [], int $limit = 12, int $offset = 0): array {
        $conditions = ["1=1"];
        $params = [];

        // Public visibility default: only approved/available/claimed items
        if (isset($filters['public_only']) && $filters['public_only']) {
            $conditions[] = "i.status IN ('approved', 'available', 'claimed', 'under_verification', 'ready_for_handover', 'returned')";
        } elseif (!empty($filters['status'])) {
            $conditions[] = "i.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $conditions[] = "i.type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['category_id'])) {
            $conditions[] = "i.category_id = :category_id";
            $params['category_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['location_id'])) {
            $conditions[] = "i.location_id = :location_id";
            $params['location_id'] = (int)$filters['location_id'];
        }

        if (!empty($filters['reported_by'])) {
            $conditions[] = "i.reported_by = :reported_by";
            $params['reported_by'] = (int)$filters['reported_by'];
        }

        if (!empty($filters['color'])) {
            $conditions[] = "i.color LIKE :color";
            $params['color'] = '%' . $filters['color'] . '%';
        }

        if (!empty($filters['date_from'])) {
            $conditions[] = "(i.date_lost >= :date_from OR i.date_found >= :date_from)";
            $params['date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $conditions[] = "(i.date_lost <= :date_to OR i.date_found <= :date_to)";
            $params['date_to'] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = "(i.title LIKE :search OR i.description LIKE :search OR i.brand LIKE :search OR i.model LIKE :search OR i.reference_code LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = implode(' AND ', $conditions);
        $sql = "SELECT i.*, c.name AS category_name, c.icon AS category_icon, 
                       l.name AS location_name, l.building, l.campus,
                       (SELECT image_path FROM item_images WHERE item_id = i.id ORDER BY is_primary DESC LIMIT 1) AS primary_image
                FROM items i
                LEFT JOIN categories c ON i.category_id = c.id
                LEFT JOIN locations l ON i.location_id = l.id
                WHERE {$whereClause}
                ORDER BY i.created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function count(array $filters = []): int {
        $conditions = ["1=1"];
        $params = [];

        if (isset($filters['public_only']) && $filters['public_only']) {
            $conditions[] = "status IN ('approved', 'available', 'claimed', 'under_verification', 'ready_for_handover', 'returned')";
        } elseif (!empty($filters['status'])) {
            $conditions[] = "status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['type'])) {
            $conditions[] = "type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['category_id'])) {
            $conditions[] = "category_id = :category_id";
            $params['category_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['location_id'])) {
            $conditions[] = "location_id = :location_id";
            $params['location_id'] = (int)$filters['location_id'];
        }

        if (!empty($filters['reported_by'])) {
            $conditions[] = "reported_by = :reported_by";
            $params['reported_by'] = (int)$filters['reported_by'];
        }

        if (!empty($filters['search'])) {
            $conditions[] = "(title LIKE :search OR description LIKE :search OR brand LIKE :search OR model LIKE :search OR reference_code LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = implode(' AND ', $conditions);
        $sql = "SELECT COUNT(*) FROM items WHERE {$whereClause}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function updateStatus(int $id, string $status, ?int $officerId = null, ?string $reason = null): bool {
        $item = $this->findById($id, true);
        if (!$item) return false;

        $fields = ["status = :status"];
        $params = ['status' => $status, 'id' => $id];

        if ($status === 'approved' || $status === 'available') {
            $fields[] = "approved_by = :officer_id";
            $fields[] = "approved_at = NOW()";
            $params['officer_id'] = $officerId;
        }

        if ($status === 'rejected') {
            $fields[] = "rejection_reason = :reason";
            $params['reason'] = $reason;
        }

        $setSql = implode(', ', $fields);
        $stmt = $this->pdo->prepare("UPDATE items SET {$setSql} WHERE id = :id");
        $success = $stmt->execute($params);

        if ($success) {
            AuditLog::log($officerId, 'update_item_status', 'item', $id, ['old_status' => $item['status']], ['new_status' => $status, 'reason' => $reason]);

            // Notify reporter
            $notificationTitle = "Report Status Update: {$item['reference_code']}";
            $notificationMsg = "Your {$item['type']} item '{$item['title']}' has been updated to status: " . strtoupper($status);
            if ($reason) {
                $notificationMsg .= ". Reason: " . $reason;
            }
            Notification::send($item['reported_by'], $notificationTitle, $notificationMsg, 'report_status', "item-details.php?ref={$item['reference_code']}");
        }

        return $success;
    }
}
