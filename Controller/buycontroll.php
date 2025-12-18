<?php
require_once 'E:\Xampp\htdocs\badgemarket\model\marketmodel.php';
require_once 'E:\Xampp\htdocs\badgemarket\controll\badgecontroll.php'; // Need BadgeController

class PurchaseController
{
    private $conn;

    public function __construct()
    {
        $this->conn = Config::getConnexion();
    }

    // For single badge purchase
    public function purchaseBadge($userId, $badgeId)
    {
        $this->conn->beginTransaction();

        try {
            // Get badge
            $badgeController = new BadgeController();
            $badge = $badgeController->getBadge($badgeId);
            if (!$badge) throw new Exception("Badge not found");

            // Get user stats
            $user = $this->getUserStats($userId);

            // Check if can afford
            if (!$user->canAfford($badge->getCost())) {
                throw new Exception("Not enough points");
            }

            // Check if already owned
            if ($this->ownsBadge($userId, $badgeId)) {
                throw new Exception("Already owned");
            }

            // Process purchase
            $user->deductPoints($badge->getCost());
            $user->addExp($badge->getExpReward());
            $this->updateUserStats($user);

            // Add to user_badges
            $stmt = $this->conn->prepare(
                "INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)"
            );
            $stmt->execute([$userId, $badgeId]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    // NEW METHOD: For cart purchase (multiple badges)
    public function purchaseCart($userId, $cartIds)
    {
        if (empty($cartIds)) {
            throw new Exception("Cart is empty");
        }

        $this->conn->beginTransaction();

        try {
            $badgeController = new BadgeController();
            $totalCost = 0;
            $totalExp = 0;

            // Calculate totals and check availability
            foreach ($cartIds as $badgeId) {
                $badge = $badgeController->getBadge($badgeId);
                if (!$badge) {
                    throw new Exception("Badge ID $badgeId not found");
                }

                // Check if already owned
                if ($this->ownsBadge($userId, $badgeId)) {
                    throw new Exception("You already own badge: " . $badge->getName());
                }

                $totalCost += $badge->getCost();
                $totalExp += $badge->getExpReward();
            }

            // Get user stats
            $user = $this->getUserStats($userId);

            // Check if can afford total
            if (!$user->canAfford($totalCost)) {
                throw new Exception("Not enough points. Need $totalCost, have " . $user->getPoints());
            }

            // Process each badge
            foreach ($cartIds as $badgeId) {
                // Add to user_badges (skip if already owned, though we checked above)
                if (!$this->ownsBadge($userId, $badgeId)) {
                    $stmt = $this->conn->prepare(
                        "INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)"
                    );
                    $stmt->execute([$userId, $badgeId]);
                }
            }

            // Update user stats
            $user->deductPoints($totalCost);
            $user->addExp($totalExp);
            $this->updateUserStats($user);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    private function getUserStats($userId)
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

    private function updateUserStats($user)
    {
        $stmt = $this->conn->prepare(
            "UPDATE stats SET level = ?, exp = ?, exp_needed = ?, points = ? WHERE id = ?"
        );
        return $stmt->execute([
            $user->getLevel(),
            $user->getExp(),
            $user->getExpNeeded(),
            $user->getPoints(),
            $user->getId()
        ]);
    }

    private function ownsBadge($userId, $badgeId)
    {
        $stmt = $this->conn->prepare(
            "SELECT 1 FROM user_badges WHERE user_id = ? AND badge_id = ?"
        );
        $stmt->execute([$userId, $badgeId]);
        return $stmt->fetch() !== false;
    }
}
