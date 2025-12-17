<?php $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Go Live - <?= htmlspecialchars($_SESSION['username']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .copy-btn{background:#9147ff;color:white;border:none;padding:10px 16px;border-radius:8px;cursor:pointer;margin-left:10px;}
        .copy-btn:hover{background:#772ce8;}
        code{background:#000;padding:12px;border-radius:8px;display:block;margin:10px 0;word-break:break-all;}
    </style>
</head>
<body>
<div class="container">
    <h1 style="color:#9147ff;text-align:center;margin-bottom:30px;">You're Going Live!</h1>

    <div class="obs">
        <h2>OBS Settings</h2>
        <p><strong>Server:</strong> <code>rtmp://localhost:1935/live</code></p>
        <p><strong>Stream Key:</strong>
            <div style="display:flex;align-items:center;gap:10px;">
                <code id="key"><?= htmlspecialchars($channel['stream_key']) ?></code>
                <button class="copy-btn" onclick="copy('key')">Copy</button>
                <button class="copy-btn" onclick="regenerateKey()" style="background:#ff6b6b;">🔄 Nouveau Key</button>
            </div>
        </p>
        
        <!-- Vérification automatique du key -->
        <div id="keyStatus" style="margin-top:15px;padding:10px;border-radius:5px;display:none;"></div>
    </div>

    <div style="background:#111;padding:35px;border-radius:16px;text-align:center;margin:40px 0;">
        <h2>Your Stream URL</h2>
        <div style="display:flex;align-items:center;justify-content:center;gap:15px;flex-wrap:wrap;">
            <a href="<?= $base_url ?>/watch/<?= $channel['slug'] ?>" 
               id="link" target="_blank" 
               style="font-size:22px;color:#9147ff;">
                <?= $base_url ?>/watch/<?= $channel['slug'] ?>
            </a>
            <button class="copy-btn" onclick="copy('link')">Copy Link</button>
        </div>
        <br><br>
        <strong style="color:#51cf66;font-size:18px;">Unlimited viewers ready!</strong>
        
        <div style="margin-top:30px;">
            <div id="streamStatus" style="background:#333;padding:20px;border-radius:10px;margin:20px 0;">
                <div id="statusIndicator" style="display:inline-block;padding:10px 20px;border-radius:20px;background:#666;color:white;margin:10px;">
                    ⚫ VÉRIFICATION...
                </div>
                <div id="statusMessage" style="margin:15px 0;color:#ffa500;">
                    Vérification du stream key et de l'état...
                </div>
                
                <div id="connectionInfo" style="font-size:12px;color:#888;margin-top:10px;"></div>
            </div>
            
            <button onclick="forceCheck()" style="background:#9147ff;color:white;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;margin:5px;">
                🔄 Vérifier Stream
            </button>
            <button onclick="forceLive()" style="background:#51cf66;color:white;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;margin:5px;">
                ✅ Forcer LIVE
            </button>
            <button onclick="openTestPage()" style="background:#333;color:white;border:none;padding:10px 20px;border-radius:8px;cursor:pointer;margin:5px;">
                🔧 Diagnostics
            </button>
        </div>
    </div>

    <p style="text-align:center;">
        <a href="<?= $base_url ?>/dashboard">← Back to Dashboard</a>
    </p>
</div>

<script>
function copy(id) {
    navigator.clipboard.writeText(document.getElementById(id).innerText);
    alert('Copied to clipboard!');
}

// Vérifier automatiquement l'état du stream
let streamCheckInterval;
let isChecking = false;

function startStreamMonitoring() {
    const streamKey = document.getElementById('key').textContent;
    const statusDiv = document.createElement('div');
    statusDiv.id = 'streamStatus';
    statusDiv.style.cssText = `
        background: #18181b;
        padding: 20px;
        border-radius: 12px;
        margin: 20px 0;
        text-align: center;
        border: 2px solid #333;
    `;
    statusDiv.innerHTML = `
        <h3 style="margin:0 0 10px 0;color:#fff;">État du Stream</h3>
        <div id="statusIndicator" style="font-size:18px;margin:10px 0;">
            🟡 En attente du stream...
        </div>
        <div id="statusMessage" style="color:#aaa;font-size:14px;">
            Lancez OBS avec les paramètres ci-dessus
        </div>
    `;
    
    // Insérer après la configuration OBS
    const obsSection = document.querySelector('.obs');
    obsSection.after(statusDiv);
    
    checkStreamStatus();
    streamCheckInterval = setInterval(checkStreamStatus, 3000);
}

async function checkStreamStatus() {
    if (isChecking) return;
    isChecking = true;
    
    const streamKey = document.getElementById('key').textContent;
    const indicator = document.getElementById('statusIndicator');
    const message = document.getElementById('statusMessage');
    
    try {
        // Vérifier via notre webhook
        const response = await fetch(`stream-webhook.php?stream_key=${streamKey}`);
        const data = await response.json();
        
        if (data.success && data.is_live) {
            indicator.style.backgroundColor = '#51cf66';
            indicator.textContent = '🔴 EN DIRECT';
            message.textContent = 'Votre stream est actif ! Visible sur le dashboard.';
            message.style.color = '#51cf66';
        } else {
            // Fallback : tester directement les URLs HLS
            const ports = [8888, 8000, 8080, 8889];
            let streamFound = false;
            
            for (const port of ports) {
                try {
                    const hlsResponse = await fetch(`http://localhost:${port}/live/${streamKey}/index.m3u8`, {method: 'HEAD'});
                    if (hlsResponse.ok) {
                        streamFound = true;
                        indicator.style.backgroundColor = '#51cf66';
                        indicator.textContent = '🔴 EN DIRECT';
                        message.textContent = `Stream détecté sur port ${port} ! Synchronisation...`;
                        message.style.color = '#51cf66';
                        
                        // Notifier le serveur
                        fetch('stream-webhook.php', {
                            method: 'POST',
                            headers: {'Content-Type': 'application/json'},
                            body: JSON.stringify({action: 'start', stream_key: streamKey})
                        });
                        break;
                    }
                } catch (e) {
                    // Continue vers le port suivant
                }
            }
            
            if (!streamFound) {
                indicator.style.backgroundColor = '#ff6b6b';
                indicator.textContent = '⭕ EN ATTENTE';
                message.textContent = 'Démarrez votre stream dans OBS pour aller en direct.';
                message.style.color = '#ffa500';
            }
        }
        const response = await fetch(`stream-webhook.php?action=check&stream_key=${encodeURIComponent(streamKey)}`);
        const data = await response.json();
        
        if (data.success) {
            if (data.is_live) {
                indicator.innerHTML = '🟢 STREAM ACTIF !';
                indicator.style.color = '#51cf66';
                message.innerHTML = `
                    Votre stream est maintenant en direct !<br>
                    <a href="watch/<?= $channel['slug'] ?>" style="color:#9147ff;text-decoration:none;font-weight:bold;">
                        👀 Voir votre stream
                    </a>
                `;
                
                // Arrêter la vérification une fois que le stream est actif
                if (streamCheckInterval) {
                    clearInterval(streamCheckInterval);
                }
            } else {
                indicator.innerHTML = '🟡 En attente...';
                indicator.style.color = '#ffd43b';
                message.innerHTML = 'Démarrez votre stream dans OBS pour qu\'il apparaisse en direct';
            }
        }
    } catch (error) {
        indicator.innerHTML = '🔴 Erreur de vérification';
        indicator.style.color = '#ff6b6b';
        message.innerHTML = 'Impossible de vérifier l\'état du stream';
    }
    
    isChecking = false;
}

// Démarrer la surveillance automatique
document.addEventListener('DOMContentLoaded', function() {
    // Vérifier d'abord si le stream key est valide
    checkStreamKey();
    
    // Puis démarrer la surveillance
    startStreamMonitoring();
});

// Vérifier si le stream key est valide
function checkStreamKey() {
    const streamKey = document.getElementById('key').textContent;
    const keyStatus = document.getElementById('keyStatus');
    
    // Un stream key valide doit être hexadécimal de 40 caractères
    const isValidKey = /^[a-f0-9]{40}$/.test(streamKey);
    
    if (!isValidKey) {
        keyStatus.style.display = 'block';
        keyStatus.style.background = '#ff6b6b';
        keyStatus.innerHTML = '⚠️ Stream key invalide détecté ! Cliquez sur "🔄 Nouveau Key" pour corriger.';
    } else {
        keyStatus.style.display = 'block';
        keyStatus.style.background = '#51cf66';
        keyStatus.innerHTML = '✅ Stream key valide';
        
        // Masquer après 3 secondes si valide
        setTimeout(() => {
            keyStatus.style.display = 'none';
        }, 3000);
    }
}

// Régénérer un nouveau stream key
async function regenerateKey() {
    if (!confirm('Générer un nouveau stream key ? Vous devrez mettre à jour OBS.')) return;
    
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'regenerate_key'})
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('key').textContent = data.new_key;
            alert('Nouveau stream key généré ! Copiez-le dans OBS.');
            checkStreamKey();
        } else {
            alert('Erreur: ' + data.error);
        }
    } catch (error) {
        alert('Erreur: ' + error.message);
    }
}

let streamCheckInterval;
let isChecking = false;

function startStreamMonitoring() {
    updateStreamStatus();
    streamCheckInterval = setInterval(updateStreamStatus, 3000);
}

// Mettre à jour le statut du stream avec détection temps réel
async function updateStreamStatus() {
    if (isChecking) return;
    isChecking = true;
    
    const streamKey = document.getElementById('key').textContent;
    const indicator = document.getElementById('statusIndicator');
    const message = document.getElementById('statusMessage');
    const connectionInfo = document.getElementById('connectionInfo');
    
    try {
        // 1. Vérifier les connexions RTMP actives
        const rtmpActive = await checkRTMPConnections();
        
        // 2. Vérifier le manifest HLS
        const hlsActive = await checkHLSManifest(streamKey);
        
        // 3. Mettre à jour l'interface
        if (rtmpActive || hlsActive) {
            indicator.style.backgroundColor = '#51cf66';
            indicator.textContent = '🔴 EN DIRECT';
            message.textContent = 'Votre stream est actif ! Visible sur le dashboard.';
            message.style.color = '#51cf66';
            
            connectionInfo.innerHTML = `
                RTMP: ${rtmpActive ? '✅ Connecté' : '❌'} | 
                HLS: ${hlsActive ? '✅ Manifest trouvé' : '❌'}
            `;
            
            // Notifier le serveur
            fetch('stream-webhook.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({action: 'start', stream_key: streamKey})
            });
        } else {
            indicator.style.backgroundColor = '#ff6b6b';
            indicator.textContent = '⚫ EN ATTENTE';
            message.textContent = 'Démarrez votre stream dans OBS pour aller en direct.';
            message.style.color = '#ffa500';
            
            connectionInfo.innerHTML = 'RTMP: ❌ | HLS: ❌';
        }
    } catch (error) {
        console.error('Erreur surveillance:', error);
        indicator.style.backgroundColor = '#666';
        indicator.textContent = '⚠️ ERREUR';
        message.textContent = 'Erreur de surveillance: ' + error.message;
        message.style.color = '#ff6b6b';
    }
    
    isChecking = false;
}

// Vérifier les connexions RTMP actives
async function checkRTMPConnections() {
    try {
        const response = await fetch('api.php?action=check_rtmp_connections');
        const data = await response.json();
        return data.has_connections || false;
    } catch {
        return false;
    }
}

// Vérifier le manifest HLS
async function checkHLSManifest(streamKey) {
    const ports = [8888, 8000, 8080];
    
    for (const port of ports) {
        try {
            const response = await fetch(`http://localhost:${port}/live/${streamKey}/index.m3u8`, {method: 'HEAD'});
            if (response.ok) {
                return true;
            }
        } catch {
            // Continue vers le port suivant
        }
    }
    
    return false;
}

// Fonction pour forcer une vérification
async function forceCheck() {
    const streamKey = document.getElementById('key').textContent;
    
    try {
        const response = await fetch(`force-sync.php?action=force_check&stream_key=${streamKey}`);
        const data = await response.json();
        
        alert(`Vérification terminée: ${data.message}\nStatut: ${data.is_live ? 'LIVE' : 'OFFLINE'}`);
        checkStreamStatus(); // Rafraîchir l'affichage
    } catch (error) {
        alert('Erreur lors de la vérification: ' + error.message);
    }
}

// Fonction pour forcer en LIVE
async function forceLive() {
    if (!confirm('Forcer ce stream en LIVE ? (pour debug seulement)')) return;
    
    const streamKey = document.getElementById('key').textContent;
    
    try {
        const response = await fetch(`force-sync.php?action=force_live&stream_key=${streamKey}`);
        const data = await response.json();
        
        alert(`Stream forcé en LIVE: ${data.message}`);
        checkStreamStatus();
    } catch (error) {
        alert('Erreur: ' + error.message);
    }
}

function openTestPage() {
    window.open('test-stream.php', '_blank');
}
</script>
</body>
</html>