<?php $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
</head>
<body>
<div class="container">
    <div style="text-align:right;padding:20px;">
        <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> | 
        <a href="<?= $base_url ?>/go-live">Go Live</a> | 
        <a href="<?= $base_url ?>/logout">Logout</a>
    </div>

    <h1 style="text-align:center;color:#9147ff;">Live Streams</h1>

    <?php if (empty($channels)): ?>
        <p style="text-align:center;color:#888;">No one is live right now.</p>
    <?php else: ?>
        <div style="display:flex;flex-wrap:wrap;gap:20px;justify-content:center;">
            <?php foreach ($channels as $ch): ?>
                <div class="card">
                    <h3>LIVE - <?= htmlspecialchars($ch['username']) ?></h3>
                    <p>Viewers: <?= $ch['viewer_count'] ?></p>
                    <a href="<?= $base_url ?>/watch/<?= $ch['slug'] ?>" class="btn">Watch</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div style="text-align:center;margin:50px;">
        <a href="<?= $base_url ?>/go-live" style="font-size:24px;background:#ff0000;color:white;padding:20px 40px;border-radius:50px;">
            GO LIVE NOW
        </a>
    </div>
</div>
</body>
</html>