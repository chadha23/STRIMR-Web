<?php
require_once 'E:\Xampp\htdocs\badgemarket\model\marketmodel.php';

class UserController
{
    private $conn;

    public function __construct()
    {
        $this->conn = Config::getConnexion();
    }

    public function getUserStats($userId)
    {
        $stmt = $this->conn->prepare("SELECT * FROM stats WHERE id = ?");
        $stmt->execute([$userId]);
        $data = $stmt->fetch();

        if ($data) {
            return new Stats(
                $data['id'],
                $data['level'],
                $data['exp'],
                $data['exp_needed'],
                $data['points']
            );
        }

        // Create new stats
        $stmt = $this->conn->prepare(
            "INSERT INTO stats (id, level, exp, exp_needed, points) VALUES (?, 1, 0, 100, 0)"
        );
        $stmt->execute([$userId]);

        return new Stats($userId, 1, 0, 100, 0);
    }

    public function getOwnedBadges($userId)
    {
        $stmt = $this->conn->prepare("SELECT badge_id FROM user_badges WHERE user_id = ?");
        $stmt->execute([$userId]);
        $owned_rows = $stmt->fetchAll();
        return array_column($owned_rows, "badge_id");
    }

    public function getRanking($limit = 20)
    {
        $query = "
        SELECT 
            s.id as user_id,
            s.level,
            s.points,
            s.exp,
            COUNT(ub.badge_id) as badge_count,
            (s.level * 1000 + COUNT(ub.badge_id) * 100 + s.exp / 10) as rank_score
        FROM stats s
        LEFT JOIN user_badges ub ON s.id = ub.user_id
        GROUP BY s.id
        ORDER BY rank_score DESC, s.level DESC, badge_count DESC, s.points DESC
        LIMIT :limit
    ";

        $stmt = $this->conn->prepare($query);
        // Use bindValue with PDO::PARAM_INT for LIMIT
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getUserRank($userId)
    {
        $query = "
            SELECT COUNT(*) + 1 as user_rank 
            FROM (
                SELECT 
                    s.id,
                    (s.level * 1000 + COUNT(ub.badge_id) * 100 + s.exp / 10) as rank_score
                FROM stats s
                LEFT JOIN user_badges ub ON s.id = ub.user_id
                GROUP BY s.id
            ) as ranks
            WHERE rank_score > (
                SELECT 
                    (s2.level * 1000 + COUNT(ub2.badge_id) * 100 + s2.exp / 10) 
                FROM stats s2
                LEFT JOIN user_badges ub2 ON s2.id = ub2.user_id
                WHERE s2.id = ?
                GROUP BY s2.id
            )
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result ? $result['user_rank'] : 1;
    }
}
