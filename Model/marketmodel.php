<?php
// File: models/Models.php

// ==================== DATABASE CONNECTION ====================
class Config
{
    private static $pdo = null;

    public static function getConnexion()
    {
        if (!isset(self::$pdo)) {
            $servername = "localhost";
            $username   = "root";
            $password   = "";
            $dbname     = "badgemarket";

            try {
                self::$pdo = new PDO(
                    "mysql:host=$servername;dbname=$dbname;charset=utf8",
                    $username,
                    $password
                );

                self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                die('Connection error: ' . $e->getMessage());
            }
        }

        return self::$pdo;
    }
}

// ==================== STATS MODEL ====================
class Stats
{
    private $id;
    private $level;
    private $exp;
    private $exp_needed;
    private $points;

    public function __construct($id, $level, $exp, $exp_needed, $points)
    {
        $this->id = $id;
        $this->level = $level;
        $this->exp = $exp;
        $this->exp_needed = $exp_needed;
        $this->points = $points;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }
    public function getLevel()
    {
        return $this->level;
    }
    public function getExp()
    {
        return $this->exp;
    }
    public function getExpNeeded()
    {
        return $this->exp_needed;
    }
    public function getPoints()
    {
        return $this->points;
    }

    // Setters
    public function setId($id)
    {
        $this->id = $id;
    }
    public function setLevel($level)
    {
        $this->level = $level;
    }
    public function setExp($exp)
    {
        $this->exp = $exp;
    }
    public function setExpNeeded($exp_needed)
    {
        $this->exp_needed = $exp_needed;
    }
    public function setPoints($points)
    {
        $this->points = $points;
    }

    // Check if user can afford an item based on points
    public function canAfford($price)
    {
        return $this->points >= $price;
    }

    // Deduct points from user's balance
    public function deductPoints($amount)
    {
        if ($this->points >= $amount) {
            $this->points -= $amount;
            return true;
        }
        return false;
    }

    // Add points to user's balance
    public function addPoints($amount)
    {
        $this->points += $amount;
    }

    // Add experience points and handle level up
    public function addExp($amount)
    {
        $this->exp += $amount;

        // Check for level up
        while ($this->exp >= $this->exp_needed) {
            $this->levelUp();
        }
    }

    // Level up the user
    private function levelUp()
    {
        // Subtract the needed exp for current level
        $this->exp -= $this->exp_needed;

        // Increase level
        $this->level++;

        // Calculate new exp needed for next level (you can customize this formula)
        $this->exp_needed = $this->calculateNextLevelExp();
    }

    // Calculate experience needed for next level
    private function calculateNextLevelExp()
    {
        // Example formula: base * (level^1.5)
        // You can adjust this formula to your game's needs
        $baseExp = 100;
        return (int)($baseExp * pow($this->level, 1.5));
    }
}

// ==================== BADGE MODEL ====================
class Badge
{
    private $id;
    private $name;
    private $icon;
    private $description;
    private $cost;
    private $exp_reward;
    private $image;

    public function __construct($id, $name, $icon, $description, $cost, $exp_reward, $image = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->icon = $icon;
        $this->description = $description;
        $this->cost = $cost;
        $this->exp_reward = $exp_reward;
        $this->image = $image;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getIcon()
    {
        return $this->icon;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function getCost()
    {
        return $this->cost;
    }
    public function getExpReward()
    {
        return $this->exp_reward;
    }
    public function getImage()
    {
        return $this->image;
    }

    // Setters
    public function setId($id)
    {
        $this->id = $id;
    }
    public function setName($name)
    {
        $this->name = $name;
    }
    public function setIcon($icon)
    {
        $this->icon = $icon;
    }
    public function setDescription($description)
    {
        $this->description = $description;
    }
    public function setCost($cost)
    {
        $this->cost = $cost;
    }
    public function setExpReward($exp_reward)
    {
        $this->exp_reward = $exp_reward;
    }
    public function setImage($image)
    {
        $this->image = $image;
    }
}

// ==================== USER BADGE MODEL ====================
class UserBadge
{
    private $user_id;
    private $badge_id;
    private $acquired_at;

    public function __construct($user_id, $badge_id, $acquired_at)
    {
        $this->user_id = $user_id;
        $this->badge_id = $badge_id;
        $this->acquired_at = $acquired_at;
    }

    // Getters
    public function getUserId()
    {
        return $this->user_id;
    }
    public function getBadgeId()
    {
        return $this->badge_id;
    }
    public function getAcquiredAt()
    {
        return $this->acquired_at;
    }

    // Setters
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }
    public function setBadgeId($badge_id)
    {
        $this->badge_id = $badge_id;
    }
    public function setAcquiredAt($acquired_at)
    {
        $this->acquired_at = $acquired_at;
    }
}
