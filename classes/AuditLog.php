<?php
/**
 * Audit Logging System
 */
require_once __DIR__ . '/Database.php';

class AuditLog {
    public static function log(?int $userId, string $action, string $entityType, ?int $entityId = null, $oldData = null, $newData = null): bool {
        try {
            $pdo = Database::getInstance()->getConnection();
            $sql = "INSERT INTO audit_logs (user_id, action, entity_type, entity_id, old_data, new_data, ip_address, user_agent)
                    VALUES (:user_id, :action, :entity_type, :entity_id, :old_data, :new_data, :ip_address, :user_agent)";
            
            $stmt = $pdo->prepare($sql);
            
            $oldJson = is_array($oldData) || is_object($oldData) ? json_encode($oldData) : (string)$oldData;
            $newJson = is_array($newData) || is_object($newData) ? json_encode($newData) : (string)$newData;
            
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'CLI/Unknown', 0, 255);

            return $stmt->execute([
                'user_id'     => $userId,
                'action'      => $action,
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
                'old_data'    => $oldJson ?: null,
                'new_data'    => $newJson ?: null,
                'ip_address'  => $ip,
                'user_agent'  => $ua
            ]);
        } catch (Exception $e) {
            error_log("AuditLog failure: " . $e->getMessage());
            return false;
        }
    }

    public static function getRecent(int $limit = 50, int $offset = 0): array {
        try {
            $pdo = Database::getInstance()->getConnection();
            $sql = "SELECT a.*, u.full_name AS user_name, u.role AS user_role, u.university_id 
                    FROM audit_logs a
                    LEFT JOIN users u ON a.user_id = u.id
                    ORDER BY a.created_at DESC
                    LIMIT :limit OFFSET :offset";
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
}
