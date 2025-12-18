<?php
class CartController
{
    public function __construct()
    {
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function addToCart($badgeId)
    {
        if (!in_array($badgeId, $_SESSION['cart'])) {
            $_SESSION['cart'][] = $badgeId;
        }
    }

    public function removeFromCart($badgeId)
    {
        $key = array_search($badgeId, $_SESSION['cart']);
        if ($key !== false) {
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
    }

    public function clearCart()
    {
        $_SESSION['cart'] = [];
    }

    public function getCartItems()
    {
        return $_SESSION['cart'];
    }

    public function getCartBadges()
    {
        require_once 'E:\Xampp\htdocs\badgemarket\model\marketmodel.php';
        $conn = Config::getConnexion();
        $badges = [];

        if (!empty($_SESSION['cart'])) {
            $placeholders = implode(',', array_fill(0, count($_SESSION['cart']), '?'));
            $stmt = $conn->prepare("SELECT * FROM badges WHERE id IN ($placeholders)");
            $stmt->execute($_SESSION['cart']);
            $cart_data = $stmt->fetchAll();

            foreach ($cart_data as $badge_data) {
                $badges[] = new Badge(
                    $badge_data['id'],
                    $badge_data['name'],
                    $badge_data['icon'],
                    $badge_data['description'],
                    $badge_data['cost'],
                    $badge_data['exp_reward'],
                    $badge_data['image'] ?? null
                );
            }
        }
        return $badges;
    }
}
