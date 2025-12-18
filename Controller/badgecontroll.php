<?php
require_once 'E:\Xampp\htdocs\badgemarket\model\marketmodel.php';

class BadgeController
{
    private $conn;

    public function __construct()
    {
        $this->conn = Config::getConnexion();
    }

    public function getAllBadges()
    {
        $stmt = $this->conn->query("SELECT * FROM badges");
        $badges = [];
        while ($row = $stmt->fetch()) {
            $badges[] = new Badge(
                $row['id'],
                $row['name'],
                $row['icon'],
                $row['description'],
                $row['cost'],
                $row['exp_reward'],
                $row['image'] ?? null
            );
        }
        return $badges;
    }

    public function searchBadges($search_term)
    {
        if (is_numeric($search_term)) {
            $stmt = $this->conn->prepare("SELECT * FROM badges WHERE id = ?");
            $stmt->execute([$search_term]);
        } else {
            $stmt = $this->conn->prepare("SELECT * FROM badges WHERE name LIKE ? OR description LIKE ?");
            $search_param = "%" . $search_term . "%";
            $stmt->execute([$search_param, $search_param]);
        }

        $badges = [];
        while ($row = $stmt->fetch()) {
            $badges[] = new Badge(
                $row['id'],
                $row['name'],
                $row['icon'],
                $row['description'],
                $row['cost'],
                $row['exp_reward'],
                $row['image'] ?? null
            );
        }
        return $badges;
    }

    public function getBadge($id)
    {
        $stmt = $this->conn->prepare("SELECT * FROM badges WHERE id = ?");
        $stmt->execute([$id]);

        if ($row = $stmt->fetch()) {
            return new Badge(
                $row['id'],
                $row['name'],
                $row['icon'],
                $row['description'],
                $row['cost'],
                $row['exp_reward'],
                $row['image'] ?? null
            );
        }
        return null;
    }
}
