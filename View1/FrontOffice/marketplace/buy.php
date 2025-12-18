<?php
session_start();
require_once 'E:\Xampp\htdocs\badgemarket\model\marketmodel.php';

$conn = Config::getConnexion();
$user_id = 1;
$badge_id = intval($_POST['badge_id']);

// Start transaction
$conn->beginTransaction();

try {
    // Fetch user stats - using FOR UPDATE to lock the row
    $stmt = $conn->prepare("SELECT * FROM stats WHERE id = ? FOR UPDATE");
    $stmt->execute([$user_id]);
    $user_data = $stmt->fetch();

    if (!$user_data) {
        // If no row exists, create one
        $stmt = $conn->prepare("INSERT INTO stats (id, level, exp, exp_needed, points) VALUES (?, 1, 0, 100, 0)");
        $stmt->execute([$user_id]);

        // Fetch it again
        $stmt = $conn->prepare("SELECT * FROM stats WHERE id = ? FOR UPDATE");
        $stmt->execute([$user_id]);
        $user_data = $stmt->fetch();
    }

    // Create Stats object
    $user = new Stats(
        $user_data['id'],
        $user_data['level'],
        $user_data['exp'],
        $user_data['exp_needed'],
        $user_data['points']
    );

    // Fetch badge
    $stmt = $conn->prepare("SELECT * FROM badges WHERE id = ?");
    $stmt->execute([$badge_id]);
    $badge_data = $stmt->fetch();

    if (!$badge_data) throw new Exception("Invalid badge");

    // Create Badge object
    $badge = new Badge(
        $badge_data['id'],
        $badge_data['name'],
        $badge_data['icon'],
        $badge_data['description'],
        $badge_data['cost'],
        $badge_data['exp_reward'],
        $badge_data['image'] ?? null
    );

    // Validations using model getters
    if ($user->getPoints() < $badge->getCost()) {
        throw new Exception("Not enough points");
    }

    // Check if already owned
    $stmt = $conn->prepare("SELECT * FROM user_badges WHERE user_id = ? AND badge_id = ?");
    $stmt->execute([$user_id, $badge_id]);
    if ($stmt->fetch()) throw new Exception("Already owned");

    // Calculate new values
    $new_points = $user->getPoints() - $badge->getCost();
    $total_exp = $user->getExp() + $badge->getExpReward();

    // Calculate level from total exp
    $level = 1;
    $exp_needed = 100;

    // Distribute ALL exp
    while ($total_exp >= $exp_needed) {
        $total_exp -= $exp_needed;
        $level++;
        $exp_needed = (int)($exp_needed * 1.5);

        // Safety
        if ($level > 1000) break;
    }

    // Update user object with new values
    $user->setPoints($new_points);
    $user->setExp($total_exp);
    $user->setLevel($level);
    $user->setExpNeeded($exp_needed);

    // Update database
    $stmt = $conn->prepare(
        "UPDATE stats 
         SET points = ?, exp = ?, level = ?, exp_needed = ?
         WHERE id = ?"
    );

    $stmt->execute([
        $user->getPoints(),
        $user->getExp(),
        $user->getLevel(),
        $user->getExpNeeded(),
        $user->getId()
    ]);

    // Add badge (create UserBadge object if needed later)
    $stmt = $conn->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $badge_id]);

    $conn->commit();

    header("Location: index.php");
    exit;
} catch (Exception $e) {
    $conn->rollBack();
    die("Error: " . $e->getMessage());
}
