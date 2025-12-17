<?php $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="container">
    <div style="text-align:right;padding:20px;">
        <span style="background:#9147ff;color:white;padding:5px 10px;border-radius:15px;margin-right:10px;">
            💎 <?= number_format($userPoints ?? 0) ?> points
        </span>
        <strong><?= htmlspecialchars($_SESSION['username']) ?></strong> | 
        <a href="<?= $base_url ?>/profile">💎 Profil</a> | 
        <a href="<?= $base_url ?>/go-live">Go Live</a> | 
        <a href="<?= $base_url ?>/logout">Logout</a>
    </div>

    <h1 style="text-align:center;color:#9147ff;">Live Streams</h1>

    <?php if (empty($channels)): ?>
        <p style="text-align:center;color:#888;">No one is live right now.</p>
    <?php else: ?>
        <div style="display:flex;flex-wrap:wrap;gap:20px;justify-content:center;">
            <?php foreach ($channels as $ch): ?>
                <div class="card" style="overflow:hidden;">
                    <!-- Toujours afficher une preview avec fallback -->
                    <div style="width:100%;height:160px;background:linear-gradient(135deg, #9147ff, #772ce8);background-size:cover;background-position:center;border-radius:8px;margin-bottom:15px;position:relative;display:flex;align-items:center;justify-content:center;">
                        <?php if (!empty($ch['preview_image']) && $ch['preview_image'] !== 'assets/img/default-stream.svg'): ?>
                            <div style="width:100%;height:100%;background-image:url('<?= htmlspecialchars($ch['preview_image']) ?>');background-size:cover;background-position:center;border-radius:8px;"></div>
                        <?php else: ?>
                            <!-- Affichage par défaut plus attractif -->
                            <div style="text-align:center;color:white;">
                                <div style="font-size:48px;margin-bottom:10px;">📹</div>
                                <div style="font-size:16px;font-weight:bold;">Stream Live</div>
                            </div>
                        <?php endif; ?>
                        <div style="position:absolute;top:10px;right:10px;background:rgba(255,0,0,0.9);color:white;padding:4px 8px;border-radius:12px;font-size:12px;font-weight:bold;">
                            🔴 LIVE
                        </div>
                    </div>
                    <h3 style="margin:0 0 8px 0;color:#fff;"><?= htmlspecialchars($ch['username']) ?></h3>
                    <p style="margin:0 0 15px 0;color:#aaa;font-size:14px;">👀 <?= $ch['viewer_count'] ?> viewers</p>
                    <a href="<?= $base_url ?>/watch/<?= htmlspecialchars($ch['username']) ?>" class="btn" style="display:block;text-align:center;">Watch</a>
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

<script>
// Vérification intelligente de l'état des streams (améliorée)
async function checkAllStreams() {
    try {
        // Vérification détaillée des streams
        const response = await fetch('/STRIMR/STRIMR-Web/api.php?action=verify_all_streams', {
            method: 'POST'
        });
        const data = await response.json();
        
        if (data.success) {
            console.log('✅ Vérification streams:', data.results);
            
            // Si des changements détectés, recharger
            const hasChanges = data.results.some(r => r.was_marked_live !== r.actually_live);
            if (hasChanges) {
                console.log('🔄 Changements détectés, rechargement...');
                setTimeout(() => location.reload(), 2000);
            }
        }
    } catch (error) {
        console.log('⚠️ Erreur vérification:', error.message);
    }
}

// Mise à jour automatique des streams en direct et viewers
function updateLiveStreams() {
    fetch('/STRIMR/STRIMR-Web/api.php?action=live_streams')
        .then(response => response.json())
        .then(streams => {
            console.log('Streams actuels:', streams);
            if (streams.length > 0) {
                location.reload(); // Recharger si des streams sont trouvés
            }
        })
        .catch(console.error);
}

// Vérification initiale
checkAllStreams();

// Vérifier et mettre à jour toutes les 15 secondes
setInterval(checkAllStreams, 15000);

// Nettoyer les streams inactifs toutes les minutes
setInterval(() => {
    fetch('/STRIMR/STRIMR-Web/api.php', {method: 'POST', body: new FormData().append('action', 'cleanup')});
}, 60000);
</script>
</body>
</html>