<?php
/**
 * Automatic Rule-Based Matching Engine
 * Compares lost vs found items and computes weighted scores (0 - 100)
 */
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Notification.php';

class Matching {
    private PDO $pdo;

    public function __construct() {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public static function findAndNotifyMatches(int $targetItemId): array {
        $matcher = new self();
        return $matcher->runForItem($targetItemId);
    }

    public function runForItem(int $itemId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM items WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $itemId]);
        $target = $stmt->fetch();
        if (!$target) return [];

        $oppositeType = ($target['type'] === 'lost') ? 'found' : 'lost';

        // Fetch candidate items of opposite type
        $cStmt = $this->pdo->prepare("SELECT * FROM items WHERE type = :type AND status IN ('pending', 'approved', 'available', 'claimed', 'under_verification') AND id != :id");
        $cStmt->execute(['type' => $oppositeType, 'id' => $itemId]);
        $candidates = $cStmt->fetchAll();

        $results = [];

        foreach ($candidates as $candidate) {
            $lostItem  = ($target['type'] === 'lost') ? $target : $candidate;
            $foundItem = ($target['type'] === 'found') ? $target : $candidate;

            $scoreData = $this->calculateScore($lostItem, $foundItem);

            if ($scoreData['score'] >= 40.0) { // Record meaningful matches
                $this->saveMatch($lostItem['id'], $foundItem['id'], $scoreData['score'], $scoreData['factors']);
                $results[] = [
                    'lost_id'  => $lostItem['id'],
                    'found_id' => $foundItem['id'],
                    'score'    => $scoreData['score'],
                    'factors'  => $scoreData['factors']
                ];

                // If score >= 70%, trigger notification to lost item owner
                if ($scoreData['score'] >= 70.0) {
                    Notification::send(
                        (int)$lostItem['reported_by'],
                        "Possible Match Found! (" . round($scoreData['score']) . "% Match)",
                        "A newly registered found item '{$foundItem['title']}' matches your lost report '{$lostItem['title']}'.",
                        'match',
                        "item-details.php?ref={$foundItem['reference_code']}"
                    );
                }
            }
        }

        return $results;
    }

    public function calculateScore(array $lost, array $found): array {
        $score = 0.0;
        $factors = [];

        // 1. Category Weight = 20
        if ($lost['category_id'] == $found['category_id']) {
            $score += 20.0;
            $factors[] = "Category match (+20)";
        }

        // 2. Brand Weight = 15
        if (!empty($lost['brand']) && !empty($found['brand'])) {
            if (strcasecmp(trim($lost['brand']), trim($found['brand'])) === 0) {
                $score += 15.0;
                $factors[] = "Exact brand match: {$lost['brand']} (+15)";
            } elseif (stripos($lost['brand'], $found['brand']) !== false || stripos($found['brand'], $lost['brand']) !== false) {
                $score += 10.0;
                $factors[] = "Partial brand match (+10)";
            }
        }

        // 3. Model Weight = 15
        if (!empty($lost['model']) && !empty($found['model'])) {
            if (strcasecmp(trim($lost['model']), trim($found['model'])) === 0) {
                $score += 15.0;
                $factors[] = "Exact model match: {$lost['model']} (+15)";
            } elseif (stripos($lost['model'], $found['model']) !== false || stripos($found['model'], $lost['model']) !== false) {
                $score += 8.0;
                $factors[] = "Partial model match (+8)";
            }
        }

        // 4. Color Weight = 10
        if (!empty($lost['color']) && !empty($found['color'])) {
            if (strcasecmp(trim($lost['color']), trim($found['color'])) === 0) {
                $score += 10.0;
                $factors[] = "Exact color match: {$lost['color']} (+10)";
            } elseif (stripos($lost['color'], $found['color']) !== false || stripos($found['color'], $lost['color']) !== false) {
                $score += 6.0;
                $factors[] = "Similar color shade (+6)";
            }
        }

        // 5. Location Weight = 15
        if ($lost['location_id'] == $found['location_id']) {
            $score += 15.0;
            $factors[] = "Same campus location (+15)";
        }

        // 6. Date Proximity Weight = 15 (Lost date vs Found date within 7 days)
        $dateLost  = !empty($lost['date_lost']) ? strtotime($lost['date_lost']) : null;
        $dateFound = !empty($found['date_found']) ? strtotime($found['date_found']) : null;
        if ($dateLost && $dateFound) {
            $diffDays = abs($dateFound - $dateLost) / (60 * 60 * 24);
            if ($diffDays <= 1) {
                $score += 15.0;
                $factors[] = "Found on or adjacent date (+15)";
            } elseif ($diffDays <= 3) {
                $score += 10.0;
                $factors[] = "Found within 3 days (+10)";
            } elseif ($diffDays <= 7) {
                $score += 5.0;
                $factors[] = "Found within 7 days (+5)";
            }
        }

        // 7. Title & Description Keyword Overlap = 10
        $wordsLost = array_filter(explode(' ', strtolower($lost['title'] . ' ' . $lost['description'])));
        $wordsFound = array_filter(explode(' ', strtolower($found['title'] . ' ' . $found['description'])));
        $intersect = array_intersect($wordsLost, $wordsFound);
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'in', 'on', 'at', 'with', 'for', 'to', 'of', 'my', 'is', 'was'];
        $meaningfulMatches = array_diff($intersect, $stopWords);

        if (count($meaningfulMatches) >= 3) {
            $score += 10.0;
            $factors[] = "Multiple description keyword matches (+10)";
        } elseif (count($meaningfulMatches) >= 1) {
            $score += 5.0;
            $factors[] = "Keyword overlap (+5)";
        }

        return [
            'score'   => min(100.0, round($score, 2)),
            'factors' => implode(' | ', $factors)
        ];
    }

    private function saveMatch(int $lostId, int $foundId, float $score, string $factors): void {
        $chk = $this->pdo->prepare("SELECT id FROM potential_matches WHERE lost_item_id = :l AND found_item_id = :f LIMIT 1");
        $chk->execute(['l' => $lostId, 'f' => $foundId]);
        $existingId = $chk->fetchColumn();

        if ($existingId) {
            $upd = $this->pdo->prepare("UPDATE potential_matches SET match_score = :s, matched_factors = :fac, updated_at = NOW() WHERE id = :id");
            $upd->execute(['s' => $score, 'fac' => $factors, 'id' => $existingId]);
        } else {
            $ins = $this->pdo->prepare("INSERT INTO potential_matches (lost_item_id, found_item_id, match_score, matched_factors, status) VALUES (:l, :f, :s, :fac, 'pending')");
            $ins->execute(['l' => $lostId, 'f' => $foundId, 's' => $score, 'fac' => $factors]);
        }
    }

    public function getMatchesForUser(int $userId): array {
        $sql = "SELECT m.*, l.reference_code AS lost_ref, l.title AS lost_title, 
                       f.reference_code AS found_ref, f.title AS found_title, f.status AS found_status,
                       (SELECT image_path FROM item_images WHERE item_id = f.id ORDER BY is_primary DESC LIMIT 1) AS found_image
                FROM potential_matches m
                JOIN items l ON m.lost_item_id = l.id
                JOIN items f ON m.found_item_id = f.id
                WHERE l.reported_by = :uid
                ORDER BY m.match_score DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll();
    }
}
