<?php
session_start();
require_once 'E:\Xampp\htdocs\badgemarket\model\marketmodel.php';
require_once 'E:\Xampp\htdocs\badgemarket\controll\badgecontroll.php';
require_once 'E:\Xampp\htdocs\badgemarket\controll\statcontroll.php';
require_once 'E:\Xampp\htdocs\badgemarket\controll\cartcontroll.php';
require_once 'E:\Xampp\htdocs\badgemarket\controll\buycontroll.php';
include 'E:\Xampp\htdocs\badgemarket\view\header.php';

$user_id = 1;

// Initialize controllers
$badgeController = new BadgeController();
$userController = new UserController();
$cartController = new CartController();
$purchaseController = new PurchaseController();

// Initialize cart in session if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle adding/removing from cart USING CONTROLLER
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart'])) {
        $badge_id = intval($_POST['badge_id']);
        $cartController->addToCart($badge_id);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_POST['remove_from_cart'])) {
        $badge_id = intval($_POST['badge_id']);
        $cartController->removeFromCart($badge_id);
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_POST['clear_cart'])) {
        $cartController->clearCart();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Handle bulk purchase USING CONTROLLER
if (isset($_POST['buy_all_cart'])) {
    $cartIds = $cartController->getCartItems();
    if (!empty($cartIds)) {
        try {
            $purchaseController->purchaseCart($user_id, $cartIds);
            $cartController->clearCart();
            $_SESSION['purchase_success'] = true;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } catch (Exception $e) {
            $_SESSION['purchase_error'] = $e->getMessage();
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        }
    }
}

// ==================== USING CONTROLLERS ====================

// Get user stats USING CONTROLLER
$user = $userController->getUserStats($user_id);

// Get badges owned by user USING CONTROLLER
$owned = $userController->getOwnedBadges($user_id);

// Get cart badges USING CONTROLLER
$cart_badges = $cartController->getCartBadges();
$cart_total = 0;
foreach ($cart_badges as $badge) {
    $cart_total += $badge->getCost();
}

// Calculate experience percentage using object getters
$exp_percent = ($user->getExp() / $user->getExpNeeded()) * 100;

// Handle search functionality for badges USING CONTROLLER
$badge_search_query = "";
$badge_search_results = [];
$is_searching_badges = false;

if (isset($_GET['badge_search']) && !empty(trim($_GET['badge_search']))) {
    $badge_search_term = trim($_GET['badge_search']);
    $is_searching_badges = true;
    $badge_search_results = $badgeController->searchBadges($badge_search_term);
    $badge_search_query = htmlspecialchars($badge_search_term);
}

// Handle search functionality for leaderboard
$leaderboard_search_query = "";
$leaderboard_search_results = [];
$is_searching_leaderboard = false;
$searched_user_rank = null;
$searched_user_stats = null;

if (isset($_GET['leaderboard_search']) && !empty(trim($_GET['leaderboard_search']))) {
    $leaderboard_search_term = trim($_GET['leaderboard_search']);
    $is_searching_leaderboard = true;

    // Try to search by user ID
    if (is_numeric($leaderboard_search_term)) {
        $searched_user_id = intval($leaderboard_search_term);
        $searched_user_stats = $userController->getUserStats($searched_user_id);
        if ($searched_user_stats) {
            $searched_user_rank = $userController->getUserRank($searched_user_id);
        }
    }
    $leaderboard_search_query = htmlspecialchars($leaderboard_search_term);
}

// Get ranking data USING CONTROLLER (limited to 20 for normal view)
$ranking_data = $userController->getRanking(20);
$user_rank = $userController->getUserRank($user_id);

// Get all badges for regular display USING CONTROLLER
$all_badges = $badgeController->getAllBadges();

// Get all badges for achievements section
$achievement_badges = $badgeController->getAllBadges();
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="style.css">
    <style>
        /* ALL YOUR CSS REMAINS EXACTLY THE SAME - NO CHANGES */
        .search-container {
            text-align: center;
            margin: 20px 0 30px 0;
        }

        .search-box {
            position: relative;
            max-width: 600px;
            margin: 0 auto;
        }

        .search-input {
            width: 100%;
            padding: 15px 50px 15px 20px;
            background: rgba(30, 30, 30, 0.9);
            border: 2px solid #444;
            border-radius: 50px;
            color: #ffffff;
            font-size: 1.1rem;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .search-input:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.3);
            background: rgba(40, 40, 40, 0.9);
        }

        .search-input::placeholder {
            color: #888;
        }

        .search-button {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: linear-gradient(135deg, #0088cc, #00d4ff);
            border: none;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .search-button:hover {
            background: linear-gradient(135deg, #00d4ff, #0088cc);
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.5);
        }

        .search-results-header {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: rgba(0, 136, 204, 0.1);
            border-radius: 10px;
            border-left: 4px solid #00d4ff;
        }

        .search-results-header h3 {
            color: #00d4ff;
            margin-bottom: 5px;
            font-size: 1.3rem;
        }

        .search-results-count {
            color: #aaa;
            font-size: 0.9em;
        }

        .clear-search-btn {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 20px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid #444;
            border-radius: 20px;
            color: #aaa;
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        .clear-search-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            border-color: #00d4ff;
        }

        .no-results {
            text-align: center;
            padding: 40px;
            color: #888;
            font-style: italic;
            background: rgba(30, 30, 30, 0.5);
            border-radius: 10px;
            margin: 20px 0;
            grid-column: 1 / -1;
        }

        .no-results h3 {
            color: #aaa;
            margin-bottom: 10px;
        }

        .search-tips {
            text-align: center;
            margin-top: 10px;
            color: #666;
            font-size: 0.9em;
        }

        .marketplace-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }

        .marketplace-header h2 {
            color: #ffffff;
            font-size: 1.8rem;
            margin: 0;
        }

        /* Cart styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #0f0f0f, #1a1a1a, #2d2d2d);
            color: #ffffff;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
            border: 1px solid #333;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            color: #ffffff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        }

        .user-stats {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin: 20px 0;
            gap: 20px;
        }

        .stat-card {
            background: rgba(30, 30, 30, 0.8);
            padding: 15px;
            border-radius: 10px;
            min-width: 200px;
            text-align: center;
            backdrop-filter: blur(5px);
            border: 1px solid #444;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            margin-top: 5px;
            color: #00d4ff;
        }

        .progress-bar {
            height: 20px;
            background: rgba(0, 0, 0, 0.6);
            border-radius: 10px;
            margin-top: 10px;
            overflow: hidden;
            border: 1px solid #444;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #0088cc, #00d4ff);
            border-radius: 10px;
            transition: width 0.5s ease;
        }

        .marketplace {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-top: 10px;
        }

        .badge-card {
            background: rgba(30, 30, 30, 0.9);
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s;
            border: 1px solid #444;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .badge-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 132, 255, 0.2);
            border-color: #0088cc;
        }

        .badge-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #ffffff;
        }

        .badge-name {
            font-size: 1.4rem;
            margin-bottom: 10px;
            color: #ffffff;
        }

        .badge-description {
            font-size: 0.9rem;
            margin-bottom: 15px;
            color: #aaaaaa;
            line-height: 1.4;
        }

        .badge-cost {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-weight: bold;
            color: #ffffff;
            padding: 10px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 8px;
        }

        .badge-cost span:first-child {
            color: #00d4ff;
        }

        .badge-cost span:last-child {
            color: #00ff88;
        }

        .buy-btn {
            background: linear-gradient(135deg, #0088cc, #00d4ff);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            width: 100%;
            font-size: 1rem;
            letter-spacing: 0.5px;
        }

        .buy-btn:hover {
            background: linear-gradient(135deg, #00d4ff, #0088cc);
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(0, 212, 255, 0.4);
        }

        .buy-btn:disabled {
            background: linear-gradient(135deg, #333, #444);
            color: #888;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            background: rgba(0, 0, 0, 0.9);
            border-radius: 10px;
            transform: translateX(150%);
            transition: transform 0.5s;
            z-index: 1000;
            border: 1px solid #0088cc;
            color: #00d4ff;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.5);
        }

        .notification.show {
            transform: translateX(0);
        }

        .achievements {
            margin-top: 40px;
            background: rgba(20, 20, 20, 0.9);
            padding: 25px;
            border-radius: 15px;
            border: 1px solid #333;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .achievements h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #ffffff;
            font-size: 1.8rem;
        }

        .achievement-list {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
        }

        .achievement-badge {
            width: 80px;
            height: 80px;
            background: rgba(40, 40, 40, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            position: relative;
            border: 2px solid #444;
            transition: all 0.3s;
        }

        .achievement-badge:hover {
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.5);
        }

        .achievement-badge.owned {
            background: linear-gradient(135deg, #0088cc, #00d4ff);
            border-color: #00d4ff;
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.7);
        }

        .achievement-badge.locked {
            background: linear-gradient(135deg, #333, #444);
            filter: grayscale(1);
            opacity: 0.7;
        }

        .achievement-badge.locked::after {
            content: "🔒";
            position: absolute;
            bottom: -5px;
            right: -5px;
            font-size: 1rem;
            background: rgba(0, 0, 0, 0.8);
            border-radius: 50%;
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .badge-tooltip {
            position: absolute;
            bottom: -40px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 0, 0, 0.9);
            color: #00d4ff;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 0.8em;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            z-index: 100;
            border: 1px solid #0088cc;
            pointer-events: none;
        }

        .achievement-badge:hover .badge-tooltip {
            opacity: 1;
            visibility: visible;
            bottom: -35px;
        }

        .reset-btn {
            background: linear-gradient(135deg, #222, #333);
            color: #ffffff;
            border: 1px solid #444;
            padding: 12px 30px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .reset-btn:hover {
            background: linear-gradient(135deg, #333, #444);
            transform: scale(1.05);
            border-color: #0088cc;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
        }

        /* Cart styles update for black theme */
        .cart-section {
            background: rgba(30, 30, 30, 0.9);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
            border: 1px solid #333;
        }

        .cart-section h2 {
            color: #ffffff;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .cart-count {
            color: #aaaaaa;
            font-size: 1rem;
            margin-bottom: 20px;
        }

        .cart-item {
            background: rgba(40, 40, 40, 0.9);
            border-radius: 10px;
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            border: 1px solid #444;
            transition: all 0.3s;
        }

        .cart-item:hover {
            background: rgba(50, 50, 50, 0.9);
            border-color: #0088cc;
        }

        .cart-total {
            text-align: right;
            font-size: 1.3em;
            font-weight: bold;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #444;
            color: #ffffff;
        }

        .cart-total span {
            color: #00d4ff;
            font-size: 1.4em;
        }

        .cart-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .cart-action-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            font-size: 1em;
            min-width: 120px;
        }

        .clear-cart-btn {
            background: linear-gradient(135deg, #cc0000, #ff3333);
            color: white;
        }

        .clear-cart-btn:hover {
            background: linear-gradient(135deg, #ff3333, #cc0000);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 51, 51, 0.4);
        }

        .buy-all-btn {
            background: linear-gradient(135deg, #008800, #00cc00);
            color: white;
        }

        .buy-all-btn:hover:not(:disabled) {
            background: linear-gradient(135deg, #00cc00, #008800);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 204, 0, 0.4);
        }

        .buy-all-btn:disabled {
            background: linear-gradient(135deg, #333, #444);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .badge-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .add-to-cart-btn,
        .remove-from-cart-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            font-size: 0.95em;
        }

        .add-to-cart-btn {
            background: linear-gradient(135deg, #0088cc, #00aaff);
            color: white;
        }

        .add-to-cart-btn:hover:not(:disabled) {
            background: linear-gradient(135deg, #00aaff, #0088cc);
            transform: translateY(-2px);
        }

        .add-to-cart-btn:disabled {
            background: linear-gradient(135deg, #333, #444);
            cursor: not-allowed;
            color: #888;
        }

        .remove-from-cart-btn {
            background: linear-gradient(135deg, #ff8800, #ffaa00);
            color: white;
        }

        .remove-from-cart-btn:hover {
            background: linear-gradient(135deg, #ffaa00, #ff8800);
            transform: translateY(-2px);
        }

        .message {
            padding: 18px 25px;
            margin: 20px 0;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 1.1em;
            border: 2px solid transparent;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .success {
            background: linear-gradient(135deg, #003300, #006600);
            color: #00ff88;
            border-color: #00ff88;
        }

        .error {
            background: linear-gradient(135deg, #330000, #660000);
            color: #ff4444;
            border-color: #ff4444;
        }

        .in-cart {
            background: rgba(0, 68, 136, 0.2);
            border: 2px solid #0088cc;
            position: relative;
        }

        .in-cart::before {
            content: "🛒 In Cart";
            position: absolute;
            top: 10px;
            right: 10px;
            background: #0088cc;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.7em;
            font-weight: bold;
        }

        /* Ranking System Styles */
        .ranking-section {
            margin-top: 40px;
            background: rgba(30, 30, 30, 0.9);
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
            border: 1px solid #333;
        }

        .ranking-section h2 {
            text-align: center;
            color: #ffffff;
            font-size: 1.8rem;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #0088cc;
        }

        .user-rank-info {
            text-align: center;
            background: rgba(0, 136, 204, 0.2);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 25px;
            border: 1px solid #0088cc;
        }

        .user-rank-info span {
            color: #00d4ff;
            font-weight: bold;
            font-size: 1.2em;
        }

        .ranking-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .ranking-table th {
            background: rgba(0, 136, 204, 0.3);
            padding: 15px;
            text-align: left;
            font-weight: bold;
            color: #00d4ff;
            border-bottom: 2px solid #0088cc;
        }

        .ranking-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #444;
        }

        .ranking-table tr:hover {
            background: rgba(0, 136, 204, 0.1);
        }

        .ranking-table tr.current-user {
            background: rgba(0, 212, 255, 0.2);
            border-left: 4px solid #00d4ff;
        }

        .rank-medal {
            font-size: 1.5em;
            text-align: center;
            width: 50px;
        }

        .rank-1 .rank-medal {
            color: gold;
        }

        .rank-2 .rank-medal {
            color: silver;
        }

        .rank-3 .rank-medal {
            color: #cd7f32;
        }

        /* bronze */

        .rank-number {
            font-weight: bold;
            color: #00d4ff;
            font-size: 1.1em;
        }

        .rank-level {
            color: #00ff88;
            font-weight: bold;
        }

        .rank-badges {
            color: #ffaa00;
            font-weight: bold;
        }

        .rank-points {
            color: #ff5555;
            font-weight: bold;
        }

        .rank-score {
            color: #d4a0ff;
            font-weight: bold;
        }

        .rank-progress {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .rank-progress-bar {
            flex-grow: 1;
            height: 8px;
            background: rgba(0, 0, 0, 0.3);
            border-radius: 4px;
            overflow: hidden;
        }

        .rank-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #0088cc, #00d4ff);
            border-radius: 4px;
        }

        .leaderboard-search-container {
            margin-bottom: 25px;
            padding: 15px;
            background: rgba(40, 40, 40, 0.7);
            border-radius: 10px;
            border: 1px solid #444;
        }

        .leaderboard-search-box {
            display: flex;
            gap: 10px;
            max-width: 500px;
            margin: 0 auto;
        }

        .leaderboard-search-input {
            flex: 1;
            padding: 12px 20px;
            background: rgba(30, 30, 30, 0.9);
            border: 2px solid #444;
            border-radius: 25px;
            color: #ffffff;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .leaderboard-search-input:focus {
            outline: none;
            border-color: #00d4ff;
            box-shadow: 0 0 15px rgba(0, 212, 255, 0.3);
        }

        .leaderboard-search-button {
            background: linear-gradient(135deg, #0088cc, #00d4ff);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            min-width: 100px;
        }

        .leaderboard-search-button:hover {
            background: linear-gradient(135deg, #00d4ff, #0088cc);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 212, 255, 0.4);
        }

        .user-search-result {
            background: rgba(0, 136, 204, 0.2);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 2px solid #00d4ff;
            text-align: center;
        }

        .user-search-result h3 {
            color: #00d4ff;
            margin-bottom: 10px;
            font-size: 1.4rem;
        }

        .user-search-stats {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 15px;
        }

        .user-search-stat {
            background: rgba(30, 30, 30, 0.8);
            padding: 10px 20px;
            border-radius: 8px;
            border: 1px solid #444;
        }

        .user-search-stat-label {
            color: #aaa;
            font-size: 0.9em;
            margin-bottom: 5px;
        }

        .user-search-stat-value {
            color: #00d4ff;
            font-weight: bold;
            font-size: 1.2em;
        }

        .search-not-found {
            text-align: center;
            padding: 30px;
            color: #888;
            font-style: italic;
            background: rgba(30, 30, 30, 0.5);
            border-radius: 10px;
            margin: 20px 0;
        }

        @media (max-width: 768px) {
            .user-stats {
                flex-direction: column;
                align-items: center;
            }

            .stat-card {
                width: 100%;
                max-width: 300px;
            }

            .marketplace {
                grid-template-columns: 1fr;
            }

            .cart-actions {
                justify-content: center;
            }

            .cart-action-btn {
                width: 100%;
            }

            .ranking-table {
                font-size: 0.9em;
            }

            .ranking-table th,
            .ranking-table td {
                padding: 8px 10px;
            }

            .leaderboard-search-box {
                flex-direction: column;
            }

            .leaderboard-search-button {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <?php if (isset($_SESSION['purchase_success']) && $_SESSION['purchase_success']): ?>
            <div class="message success">
                ✅ Purchase successful! All badges have been added to your collection.
            </div>
            <?php unset($_SESSION['purchase_success']); ?>
        <?php elseif (isset($_SESSION['purchase_error'])): ?>
            <div class="message error">
                ❌ Error: <?= htmlspecialchars($_SESSION['purchase_error']) ?>
            </div>
            <?php unset($_SESSION['purchase_error']); ?>
        <?php endif; ?>

        <header>
            <h1>Badge Marketplace</h1>
        </header>

        <!-- Shopping Cart -->
        <div class="cart-section">
            <h2>🛒 Shopping Cart (<?= count($_SESSION['cart']) ?> items)</h2>

            <?php if (!empty($cart_badges)): ?>
                <div class="cart-items">
                    <?php foreach ($cart_badges as $badge): ?>
                        <div class="cart-item">
                            <span class="badge-icon"><?= htmlspecialchars($badge->getIcon()) ?></span>
                            <span class="badge-name"><?= htmlspecialchars($badge->getName()) ?></span>
                            <span class="badge-cost"><?= $badge->getCost() ?> points</span>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="badge_id" value="<?= $badge->getId() ?>">
                                <button type="submit" name="remove_from_cart" class="remove-from-cart-btn">Remove</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-total">
                    Total: <?= $cart_total ?> points
                </div>

                <div class="cart-actions">
                    <form method="POST">
                        <button type="submit" name="clear_cart" class="cart-action-btn clear-cart-btn"
                            onclick="return confirm('Clear your entire cart?')">Clear Cart</button>
                    </form>

                    <form method="POST">
                        <button type="submit" name="buy_all_cart" class="cart-action-btn buy-all-btn"
                            <?= $user->getPoints() < $cart_total ? 'disabled' : '' ?>>
                            Buy All (<?= $cart_total ?> points)
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="cart-empty">
                    Your cart is empty. Add badges from the marketplace below!
                </div>
            <?php endif; ?>
        </div>

        <!-- User Stats -->
        <div class="user-stats">
            <div class="stat-card">
                <div>Level</div>
                <div class="stat-value"><?= $user->getLevel() ?></div>
            </div>

            <div class="stat-card">
                <div>Experience</div>
                <div class="stat-value"><?= $user->getExp() ?> / <?= $user->getExpNeeded() ?></div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?= $exp_percent ?>%;"></div>
                </div>
            </div>

            <div class="stat-card">
                <div>Points</div>
                <div class="stat-value"><?= $user->getPoints() ?></div>
            </div>
        </div>

        <!-- Ranking System -->
        <div class="ranking-section">
            <h2>🏆 Global Ranking</h2>

            <div class="user-rank-info">
                Your Current Rank: <span>#<?= $user_rank ?></span>
                • Level: <span><?= $user->getLevel() ?></span>
                • Badges: <span><?= count($owned) ?></span>
                • Points: <span><?= $user->getPoints() ?></span>
            </div>

            <!-- Leaderboard Search -->
            <div class="leaderboard-search-container">
                <form method="GET" action="" class="leaderboard-search-box">
                    <input type="text"
                        name="leaderboard_search"
                        class="leaderboard-search-input"
                        placeholder="🔍 Search player by User ID..."
                        value="<?= $leaderboard_search_query ?>"
                        autocomplete="off">
                    <button type="submit" class="leaderboard-search-button">Search Player</button>
                </form>
            </div>

            <!-- Show searched user results -->
            <?php if ($is_searching_leaderboard && $searched_user_stats): ?>
                <div class="user-search-result">
                    <h3>Player #<?= $leaderboard_search_query ?> Found!</h3>
                    <div class="user-search-stats">
                        <div class="user-search-stat">
                            <div class="user-search-stat-label">Rank</div>
                            <div class="user-search-stat-value">#<?= $searched_user_rank ?></div>
                        </div>
                        <div class="user-search-stat">
                            <div class="user-search-stat-label">Level</div>
                            <div class="user-search-stat-value"><?= $searched_user_stats->getLevel() ?></div>
                        </div>
                        <div class="user-search-stat">
                            <div class="user-search-stat-label">Points</div>
                            <div class="user-search-stat-value"><?= $searched_user_stats->getPoints() ?></div>
                        </div>
                        <div class="user-search-stat">
                            <div class="user-search-stat-label">Experience</div>
                            <div class="user-search-stat-value"><?= $searched_user_stats->getExp() ?> / <?= $searched_user_stats->getExpNeeded() ?></div>
                        </div>
                    </div>
                </div>
            <?php elseif ($is_searching_leaderboard): ?>
                <div class="search-not-found">
                    <h3>Player not found</h3>
                    <p>No player found with ID "<?= $leaderboard_search_query ?>"</p>
                    <p>Try searching with a different User ID</p>
                </div>
            <?php endif; ?>

            <!-- Leaderboard Table -->
            <table class="ranking-table">
                <thead>
                    <tr>
                        <th width="60">Rank</th>
                        <th>Player</th>
                        <th width="80">Level</th>
                        <th width="100">Badges</th>
                        <th width="120">Points</th>
                        <th width="120">Rank Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ranking_data as $index => $row):
                        $rank = $index + 1;
                        $is_current_user = ($row['user_id'] == $user_id);
                        $is_searched_user = ($is_searching_leaderboard && $row['user_id'] == $leaderboard_search_query);
                    ?>
                        <tr class="rank-<?= $rank <= 3 ? $rank : 'normal' ?> <?= $is_current_user ? 'current-user' : '' ?> <?= $is_searched_user ? 'current-user' : '' ?>">
                            <td class="rank-number">
                                <?php if ($rank <= 3): ?>
                                    <div class="rank-medal">
                                        <?= $rank == 1 ? '🥇' : ($rank == 2 ? '🥈' : '🥉') ?>
                                    </div>
                                <?php else: ?>
                                    #<?= $rank ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $is_current_user ? 'You' : 'Player ' . htmlspecialchars($row['user_id']) ?>
                                <?php if ($is_current_user): ?>
                                    <span style="color: #00d4ff; font-size: 0.8em;">(YOU)</span>
                                <?php elseif ($is_searched_user): ?>
                                    <span style="color: #ffaa00; font-size: 0.8em;">(SEARCHED)</span>
                                <?php endif; ?>
                            </td>
                            <td class="rank-level"><?= htmlspecialchars($row['level']) ?></td>
                            <td class="rank-badges"><?= htmlspecialchars($row['badge_count']) ?></td>
                            <td class="rank-points"><?= number_format($row['points']) ?></td>
                            <td class="rank-score"><?= number_format($row['rank_score']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Badge Search Section -->
        <div class="search-container">
            <form method="GET" action="" class="search-box">
                <input type="text"
                    name="badge_search"
                    class="search-input"
                    placeholder="🔍 Search badges by ID, name, or description..."
                    value="<?= $badge_search_query ?>"
                    autocomplete="off">
                <button type="submit" class="search-button">Go</button>
            </form>

            <?php if (!empty($badge_search_query)): ?>
                <div class="search-results-header">
                    <h3>Search Results for "<?= $badge_search_query ?>"</h3>
                    <div class="search-results-count">
                        Found <?= count($badge_search_results) ?> badge(s)
                    </div>
                    <a href="?" class="clear-search-btn">Clear Search & Show All Badges</a>
                </div>
            <?php else: ?>
                <div class="search-tips">
                    💡 Tip: Search by badge ID (number) or name/description (text)
                </div>
            <?php endif; ?>
        </div>

        <!-- Marketplace -->
        <?php if ($is_searching_badges): ?>
            <!-- Show search results -->
            <div class="marketplace">
                <?php if (count($badge_search_results) > 0): ?>
                    <?php foreach ($badge_search_results as $badge):
                        $is_owned = in_array($badge->getId(), $owned);
                        $in_cart = in_array($badge->getId(), $_SESSION['cart']);
                    ?>
                        <div class="badge-card <?= $in_cart ? 'in-cart' : '' ?>">
                            <div class="badge-icon"><?= htmlspecialchars($badge->getIcon()) ?></div>
                            <div class="badge-name"><?= htmlspecialchars($badge->getName()) ?></div>
                            <div class="badge-description"><?= htmlspecialchars($badge->getDescription()) ?></div>

                            <div class="badge-cost">
                                <span>Cost: <?= $badge->getCost() ?> points</span>
                                <span>EXP: +<?= $badge->getExpReward() ?></span>
                            </div>

                            <div class="badge-actions">
                                <?php if ($is_owned): ?>
                                    <button class="buy-btn" disabled>Owned ✔</button>
                                <?php elseif ($in_cart): ?>
                                    <button class="buy-btn" disabled>In Cart 🛒</button>
                                    <form method="POST">
                                        <input type="hidden" name="badge_id" value="<?= $badge->getId() ?>">
                                        <button type="submit" name="remove_from_cart" class="remove-from-cart-btn">Remove</button>
                                    </form>
                                <?php elseif ($user->getPoints() < $badge->getCost()): ?>
                                    <button class="add-to-cart-btn" disabled>Not enough points</button>
                                <?php else: ?>
                                    <form method="POST">
                                        <input type="hidden" name="badge_id" value="<?= $badge->getId() ?>">
                                        <button type="submit" name="add_to_cart" class="add-to-cart-btn">Add to Cart</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Show all badges (normal view) -->
            <div class="marketplace">
                <?php foreach ($all_badges as $badge):
                    $is_owned = in_array($badge->getId(), $owned);
                    $in_cart = in_array($badge->getId(), $_SESSION['cart']);
                ?>
                    <div class="badge-card <?= $in_cart ? 'in-cart' : '' ?>">
                        <div class="badge-icon"><?= htmlspecialchars($badge->getIcon()) ?></div>
                        <div class="badge-name"><?= htmlspecialchars($badge->getName()) ?></div>
                        <div class="badge-description"><?= htmlspecialchars($badge->getDescription()) ?></div>

                        <div class="badge-cost">
                            <span>Cost: <?= $badge->getCost() ?> points</span>
                            <span>EXP: +<?= $badge->getExpReward() ?></span>
                        </div>

                        <div class="badge-actions">
                            <?php if ($is_owned): ?>
                                <button class="buy-btn" disabled>Owned ✔</button>
                            <?php elseif ($in_cart): ?>
                                <button class="buy-btn" disabled>In Cart 🛒</button>
                                <form method="POST">
                                    <input type="hidden" name="badge_id" value="<?= $badge->getId() ?>">
                                    <button type="submit" name="remove_from_cart" class="remove-from-cart-btn">Remove</button>
                                </form>
                            <?php elseif ($user->getPoints() < $badge->getCost()): ?>
                                <button class="add-to-cart-btn" disabled>Not enough points</button>
                            <?php else: ?>
                                <form method="POST">
                                    <input type="hidden" name="badge_id" value="<?= $badge->getId() ?>">
                                    <button type="submit" name="add_to_cart" class="add-to-cart-btn">Add to Cart</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Your Badges -->
        <div class="achievements">
            <h2>Your Badges</h2>
            <div class="achievement-list">
                <?php foreach ($achievement_badges as $badge):
                    $own = in_array($badge->getId(), $owned);
                ?>
                    <div class="achievement-badge <?= $own ? 'owned' : 'locked' ?>">
                        <?= htmlspecialchars($badge->getIcon()) ?>
                        <?php if ($own): ?>
                            <div class="badge-tooltip"><?= htmlspecialchars($badge->getName()) ?> ✓</div>
                        <?php else: ?>
                            <div class="badge-tooltip"><?= htmlspecialchars($badge->getName()) ?> 🔒</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</body>

</html>