<?php
/**
 * In-App Notification System
 */
require_once __DIR__ . '/Database.php';

class Notification {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public static function send(int $userId, string $title, string $message, string $type = 'system', ?string $link = null): bool {
        try {
            $pdo = Database::getInstance()->getConnection();
            $sql = "INSERT INTO notifications (user_id, title, message, type, link, is_read) 
                    VALUES (:user_id, :title, :message, :type, :link, 0)";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                'user_id' => $userId,
                'title'   => trim($title),
                'message' => trim($message),
                'type'    => $type,
                'link'    => $link
            ]);
        } catch (Exception $e) {
            error_log("Notification error: " . $e->getMessage());
            return false;
        }
    }

    public function getUserNotifications(int $userId, int $limit = 20, int $offset = 0): array {
        $sql = "SELECT * FROM notifications WHERE user_id = :uid ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getUnreadCount(int $userId): int {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0");
        $stmt->execute(['uid' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    public function markAsRead(int $notificationId, int $userId): bool {
        $stmt = $this->pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :uid");
        return $stmt->execute(['id' => $notificationId, 'uid' => $userId]);
    }

    public function markAllAsRead(int $userId): bool {
        $stmt = $this->pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :uid");
        return $stmt->execute(['uid' => $userId]);
    }
}
