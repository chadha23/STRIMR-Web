<?php $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mon Profil - STRIMR</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .points-display {
            background: #9147ff;
            color: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            margin: 20px 0;
            font-size: 24px;
        }
        .transaction {
            background: #18181b;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-left: 4px solid #9147ff;
        }
        .transaction.incoming {
            border-left-color: #51cf66;
        }
        .transaction.outgoing {
            border-left-color: #ff6b6b;
        }
    </style>
</head>
<body>
<div class="container">
    <div style="text-align:right;padding:20px;">
        <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> | 
        <a href="<?= $base_url ?>/dashboard">Dashboard</a> | 
        <a href="<?= $base_url ?>/logout">Logout</a>
    </div>

    <h1 style="text-align:center;color:#9147ff;">Mon Profil</h1>

    <?php if (isset($_SESSION['purchase_success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (window.validator) {
                    validator.showToast('<?= addslashes($_SESSION['purchase_success']) ?>', 'success', 5000);
                }
            });
        </script>
        <?php unset($_SESSION['purchase_success']); ?>
    <?php endif; ?>

    <div class="points-display">
        💎 <?= number_format($points) ?> Points
        <div style="font-size: 16px; margin-top: 10px;">
            <a href="<?= $base_url ?>/purchase" style="background: #fff; color: #9147ff; padding: 10px 20px; border-radius: 20px; text-decoration: none;">
                💳 Acheter des Points
            </a>
        </div>
    </div>

    <h2>Historique des Transactions</h2>

    <?php if (empty($transactions)): ?>
        <p style="text-align:center;color:#888;">Aucune transaction pour le moment.</p>
    <?php else: ?>
        <?php foreach ($transactions as $t): ?>
            <?php 
            $isIncoming = $t['to_user_id'] == $_SESSION['user_id'];
            $class = $isIncoming ? 'incoming' : 'outgoing';
            $symbol = $isIncoming ? '+' : '-';
            $otherUser = $isIncoming ? $t['from_username'] : $t['to_username'];
            ?>
            <div class="transaction <?= $class ?>">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <strong><?= $symbol . number_format($t['amount']) ?> points</strong>
                        <?php if ($t['type'] === 'donation'): ?>
                            <br><?= $isIncoming ? "de" : "à" ?> <strong><?= htmlspecialchars($otherUser) ?></strong>
                            <?php if ($t['message']): ?>
                                <br><em>"<?= htmlspecialchars($t['message']) ?>"</em>
                            <?php endif; ?>
                        <?php else: ?>
                            <br>Achat de points
                        <?php endif; ?>
                    </div>
                    <small><?= date('d/m/Y H:i', strtotime($t['created_at'])) ?></small>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <div style="text-align:center;margin-top:30px;">
        <a href="<?= $base_url ?>/dashboard">← Retour au Dashboard</a>
    </div>
</div>

<script src="assets/js/validation.js"></script>
</body>
</html>