<?php
/**
 * User Entity and Model Operations
 */
require_once __DIR__ . '/Database.php';

class User {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function findById(int $id): ?array {
        $sql = "SELECT u.*, f.name AS faculty_name, d.name AS department_name 
                FROM users u
                LEFT JOIN faculties f ON u.faculty_id = f.id
                LEFT JOIN departments d ON u.department_id = d.id
                WHERE u.id = :id LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function getAll(array $filters = [], int $limit = 50, int $offset = 0): array {
        $conditions = ["1=1"];
        $params = [];

        if (!empty($filters['role'])) {
            $conditions[] = "u.role = :role";
            $params['role'] = $filters['role'];
        }
        if (!empty($filters['status'])) {
            $conditions[] = "u.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $conditions[] = "(u.full_name LIKE :search OR u.email LIKE :search OR u.university_id LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = implode(' AND ', $conditions);
        $sql = "SELECT u.*, f.name AS faculty_name, d.name AS department_name 
                FROM users u
                LEFT JOIN faculties f ON u.faculty_id = f.id
                LEFT JOIN departments d ON u.department_id = d.id
                WHERE {$whereClause}
                ORDER BY u.created_at DESC
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

        if (!empty($filters['role'])) {
            $conditions[] = "role = :role";
            $params['role'] = $filters['role'];
        }
        if (!empty($filters['status'])) {
            $conditions[] = "status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $conditions[] = "(full_name LIKE :search OR email LIKE :search OR university_id LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = implode(' AND ', $conditions);
        $sql = "SELECT COUNT(*) FROM users WHERE {$whereClause}";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function updateProfile(int $id, array $data): bool {
        $fields = ["full_name = :full_name", "phone = :phone"];
        $params = [
            'id' => $id,
            'full_name' => $data['full_name'],
            'phone' => $data['phone'] ?? null
        ];

        if (!empty($data['password'])) {
            $fields[] = "password = :password";
            $params['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (!empty($data['profile_image'])) {
            $fields[] = "profile_image = :profile_image";
            $params['profile_image'] = $data['profile_image'];
        }

        $setSql = implode(', ', $fields);
        $sql = "UPDATE users SET {$setSql} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function updateStatus(int $id, string $status): bool {
        $stmt = $this->pdo->prepare("UPDATE users SET status = :status WHERE id = :id");
        return $stmt->execute(['status' => $status, 'id' => $id]);
    }

    public function updateRole(int $id, string $role): bool {
        $stmt = $this->pdo->prepare("UPDATE users SET role = :role WHERE id = :id");
        return $stmt->execute(['role' => $role, 'id' => $id]);
    }
}
