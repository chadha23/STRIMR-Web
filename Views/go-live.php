<?php 
// Configuration CSP pour permettre les scripts nécessaires
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; connect-src 'self' http://localhost:* https:; img-src 'self' data:; font-src 'self';");

$base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); 
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; connect-src 'self' http://localhost:* https:; img-src 'self' data:;">
    <title>Go Live - <?= htmlspecialchars($_SESSION['username']) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .copy-btn{background:#9147ff;color:white;border:none;padding:10px 16px;border-radius:8px;cursor:pointer;margin-left:10px;}
        .copy-btn:hover{background:#772ce8;}
        code{background:#000;padding:12px;border-radius:8px;display:block;margin:10px 0;word-break:break-all;}
        
        /* Animation pulse pour l'indicateur EN DIRECT */
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        /* Style amélioré pour les messages de statut */
        .status-live {
            color: #51cf66 !important;
            font-weight: bold;
            text-shadow: 0 0 10px rgba(81, 207, 102, 0.3);
        }
        
        .status-waiting {
            color: #ffa500 !important;
            font-weight: normal;
        }
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
                <button class="copy-btn" id="copyKeyBtn">Copy</button>
                <button class="copy-btn" id="regenerateKeyBtn" style="background:#ff6b6b;">🔄 Nouveau Key</button>
            </div>
        </p>
        
        <!-- Vérification automatique du key -->
        <div id="keyStatus" style="margin-top:15px;padding:10px;border-radius:5px;display:none;"></div>
        
        <!-- Message de sécurité automatique -->
        <div id="securityNotice" style="margin-top:15px;padding:15px;background:linear-gradient(135deg, rgba(145, 71, 255, 0.1), rgba(145, 71, 255, 0.05));border-radius:8px;border-left:4px solid #9147ff;display:block;">
            <div style="color:#9147ff;font-weight:bold;margin-bottom:8px;font-size:14px;">🛡️ Sécurité Automatique Activée</div>
            <div style="color:#ccc;font-size:12px;line-height:1.4;">
                • 🔑 Votre clé de stream change automatiquement après chaque session<br>
                • 🔒 Cela empêche la confusion entre les sessions<br>
                • 🛡️ Protection contre les accès non autorisés à votre stream<br>
                • ⚡ La nouvelle clé est générée instantanément à l'arrêt
            </div>
        </div>
    </div>

    <div style="background:#111;padding:35px;border-radius:16px;text-align:center;margin:40px 0;">
        <h2>Your Stream URL</h2>
        <div style="display:flex;align-items:center;justify-content:center;gap:15px;flex-wrap:wrap;">
            <a href="../watch/<?= htmlspecialchars($channel['username']) ?>" 
               id="link" target="_blank" 
               style="font-size:22px;color:#9147ff;">
                <?= $_SERVER['HTTP_HOST'] ?>/STRIMR/STRIMR-Web/watch/<?= htmlspecialchars($channel['username']) ?>
            </a>
            <button class="copy-btn" id="copyLinkBtn">Copy Link</button>
        </div>
        
        <!-- Bouton de redirection automatique -->
        <div id="goLiveSection" style="margin:20px 0;">
            <button id="goToStreamBtn" class="copy-btn" 
                    style="background:#51cf66;font-size:16px;padding:15px 30px;display:none;" 
                    onclick="goToStream()">
                🚀 Aller sur votre stream EN DIRECT
            </button>
            <button id="stopStreamBtn" class="copy-btn" 
                    style="background:#ff6b6b;font-size:16px;padding:15px 30px;display:none;margin-left:10px;" 
                    onclick="stopStream()">
                🛑 Arrêter le Stream
            </button>
            <div id="streamInstructions" style="color:#888;margin-top:15px;">
                💡 Démarrez votre stream dans OBS, puis cliquez sur le bouton vert qui apparaîtra ici
            </div>
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
        </div>
    </div>

    <p style="text-align:center;">
        <a href="<?= $base_url ?>/dashboard">← Back to Dashboard</a>
    </p>
</div>

<script>
// Variables globales
let streamCheckInterval;
let isChecking = false;
let lastWorkingPort = null;
let consecutiveFailures = 0;
let detectionMode = 'minimal'; // Commencer en mode minimal pour éviter le spam
let silentMode = true; // Mode silencieux pour éviter les erreurs console

// Démarrer au chargement
document.addEventListener('DOMContentLoaded', function() {
    // Attacher les event listeners
    document.getElementById('copyKeyBtn').addEventListener('click', copyKey);
    document.getElementById('regenerateKeyBtn').addEventListener('click', regenerateKey);
    document.getElementById('copyLinkBtn').addEventListener('click', copyLink);
    
    // Initialiser
    checkStreamKey();
    startStreamMonitoring();
});

// Fonctions de copie
function copyKey() {
    navigator.clipboard.writeText(document.getElementById('key').textContent);
    alert('Stream key copied!');
}

function copyLink() {
    navigator.clipboard.writeText(document.getElementById('link').textContent);
    alert('Stream URL copied!');
}

// Vérifier si le stream key est valide
function checkStreamKey() {
    const streamKey = document.getElementById('key').textContent;
    const keyStatus = document.getElementById('keyStatus');
    
    const isValidKey = /^[a-f0-9]{40}$/.test(streamKey);
    
    if (!isValidKey) {
        keyStatus.style.display = 'block';
        keyStatus.style.background = '#ff6b6b';
        keyStatus.innerHTML = '⚠️ Stream key invalide ! Cliquez sur "🔄 Nouveau Key" pour corriger.';
    } else {
        keyStatus.style.display = 'block';
        keyStatus.style.background = '#51cf66';
        keyStatus.innerHTML = '✅ Stream key valide';
        setTimeout(() => keyStatus.style.display = 'none', 3000);
    }
}

// Régénérer stream key
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

// Surveillance temps réel
function startStreamMonitoring() {
    updateStreamStatus();
    // Intervalle de 3 secondes comme demandé
    streamCheckInterval = setInterval(updateStreamStatus, 3000);
}

async function updateStreamStatus() {
    if (isChecking) return;
    isChecking = true;
    
    const streamKey = document.getElementById('key').textContent;
    const indicator = document.getElementById('statusIndicator');
    const message = document.getElementById('statusMessage');
    const connectionInfo = document.getElementById('connectionInfo');
    
    try {
        // Mode détection améliorée : vérifier RTMP ET HLS
        let rtmpActive = false;
        let hlsActive = false;
        
        // 1. Vérifier RTMP (connexions actives)
        rtmpActive = await checkRTMPConnections();
        
        // 2. Vérifier HLS directement
        hlsActive = await checkHLSStatus(streamKey);
        
        // 3. Le stream est actif si RTMP OU HLS fonctionne
        const streamActive = rtmpActive || hlsActive;
        
        // 4. Mettre à jour l'interface
        const timestamp = new Date().toLocaleTimeString();
        
        if (streamActive) {
            consecutiveFailures = 0;
            
            // Enregistrer le timestamp de début de stream si ce n'est pas déjà fait
            if (!window.streamStartTime) {
                window.streamStartTime = new Date().toISOString();
                console.log('🎬 DÉBUT DE STREAM DÉTECTÉ:', window.streamStartTime);
                
                // Notifier le serveur du début de stream avec timestamp
                fetch('api.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        action: 'start_stream_session',
                        stream_key: streamKey,
                        start_time: window.streamStartTime
                    })
                }).catch(error => console.log('Erreur start session:', error));
            }
            
            indicator.style.backgroundColor = '#51cf66';
            indicator.style.animation = 'pulse 2s infinite';
            indicator.textContent = '🔴 EN DIRECT';
            message.textContent = `🎥 STREAMING EN COURS - ${timestamp}`;
            message.style.color = '#51cf66';
            message.style.fontWeight = 'bold';
            connectionInfo.innerHTML = `RTMP: ${rtmpActive ? '✅ ACTIF' : '❌ INACTIF'} | HLS: ${hlsActive ? '✅ ACTIF' : '❌ INACTIF'} | Début: ${window.streamStartTime ? new Date(window.streamStartTime).toLocaleTimeString() : 'N/A'}`;
            
            // Afficher le bouton "Aller sur votre stream"
            document.getElementById('goToStreamBtn').style.display = 'block';
            document.getElementById('stopStreamBtn').style.display = 'block';
            document.getElementById('streamInstructions').style.display = 'none';
            
        } else {
            // Si le stream s'arrête, réinitialiser le timestamp
            if (window.streamStartTime) {
                console.log('🛑 FIN DE STREAM DÉTECTÉE');
                window.streamStartTime = null;
                
                // Notifier le serveur de la fin de stream
                fetch('api.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        action: 'end_stream_session',
                        stream_key: streamKey
                    })
                }).catch(() => {});
            }
            
            consecutiveFailures++;
            
            indicator.style.backgroundColor = '#ff6b6b';
            indicator.style.animation = 'none';
            indicator.textContent = '⚫ EN ATTENTE';
            message.textContent = `⏳ En attente de stream... (${consecutiveFailures} échecs)`;
            message.style.color = '#ffa500';
            message.style.fontWeight = 'normal';
            connectionInfo.innerHTML = `RTMP: ❌ | HLS: ❌ | Échecs consécutifs: ${consecutiveFailures} | Dernière vérif: ${timestamp}`;
            
            // Cacher le bouton
            document.getElementById('goToStreamBtn').style.display = 'none';
            document.getElementById('stopStreamBtn').style.display = 'none';
            document.getElementById('streamInstructions').style.display = 'block';
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

// Vérifications
async function checkRTMPConnections() {
    try {
        console.log('🔍 Vérification RTMP...');
        const response = await fetch('api.php?action=check_rtmp_connections');
        const data = await response.json();
        
        console.log('RTMP Response:', data);
        
        if (data && typeof data.has_connections !== 'undefined') {
            const hasConnections = data.has_connections;
            const activeClients = data.active_clients || 0;
            
            console.log(`🔍 RTMP: ${hasConnections ? '✅ ACTIF' : '❌ INACTIF'} (${activeClients} streams actifs)`);
            
            if (data.debug_info && data.debug_info.active_streams) {
                console.log('📺 Streams actifs:', data.debug_info.active_streams);
            }
            
            if (data.debug_info && data.debug_info.mediamtx_api) {
                console.log('🔌 MediaMTX API:', data.debug_info.mediamtx_api);
            }
            
            return hasConnections;
        }
        return false;
    } catch (error) {
        console.error('❌ Erreur RTMP check:', error);
        return false;
    }
}

// Nouvelle fonction pour vérifier HLS directement via l'API
async function checkHLSStatus(streamKey) {
    try {
        console.log('🔍 Vérification HLS pour:', streamKey);
        const response = await fetch(`api.php?action=check_hls_status&stream_key=${encodeURIComponent(streamKey)}`);
        const data = await response.json();
        
        console.log('HLS Response:', data);
        
        if (data && data.success) {
            const hlsWorking = data.hls_working;
            
            console.log(`🎬 HLS: ${hlsWorking ? '✅ ACTIF' : '❌ INACTIF'} (${data.attempts} tentatives)`);
            
            if (data.debug_info && data.debug_info.curl_attempts) {
                const lastAttempt = data.debug_info.curl_attempts[data.debug_info.curl_attempts.length - 1];
                console.log(`📡 Dernière tentative: HTTP ${lastAttempt.http_code}, M3U8: ${lastAttempt.has_m3u8_content ? 'OUI' : 'NON'}`);
            }
            
            return hlsWorking;
        }
        return false;
    } catch (error) {
        console.error('❌ Erreur HLS check:', error);
        return false;
    }
}

// Vérification HLS silencieuse (sans erreurs console)
async function checkHLSManifestSilent(streamKey) {
    // Ne faire aucune requête HLS si aucune connexion RTMP
    const rtmpConnections = await checkRTMPConnections();
    if (!rtmpConnections) {
        return false; // Pas de stream RTMP = pas de HLS
    }
    
    // Seulement si RTMP actif, tester HLS sur port principal
    try {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 2000);
        
        const response = await fetch(`http://localhost:8888/live/${streamKey}/index.m3u8`, {
            method: 'HEAD',
            signal: controller.signal
        });
        
        clearTimeout(timeoutId);
        
        if (response.ok) {
            lastWorkingPort = 8888;
            return true;
        }
    } catch {
        // Ignorer silencieusement toutes les erreurs
    }
    
    return false;
}

async function checkHLSManifestSmart(streamKey) {
    // Tester d'abord le dernier port qui a fonctionné
    if (lastWorkingPort) {
        try {
            const response = await fetch(`http://localhost:${lastWorkingPort}/live/${streamKey}/index.m3u8`, {method: 'HEAD'});
            if (response.ok) return true;
        } catch {}
        // Si le dernier port ne fonctionne plus, l'oublier
        lastWorkingPort = null;
    }
    
    // Tester seulement le port 8888 (MediaMTX principal)
    try {
        const response = await fetch(`http://localhost:8888/live/${streamKey}/index.m3u8`, {method: 'HEAD'});
        if (response.ok) {
            lastWorkingPort = 8888;
            return true;
        }
    } catch {}
    
    return false;
}

async function checkLastWorkingPort(streamKey) {
    if (!lastWorkingPort) return false;
    
    try {
        const response = await fetch(`http://localhost:${lastWorkingPort}/live/${streamKey}/index.m3u8`, {method: 'HEAD'});
        if (response.ok) return true;
        lastWorkingPort = null; // Port plus actif
    } catch {
        lastWorkingPort = null;
    }
    
    return false;
}

// Version complète pour les tests manuels
async function checkHLSManifest(streamKey) {
    const ports = [8888, 8000, 8080];
    
    for (const port of ports) {
        try {
            const response = await fetch(`http://localhost:${port}/live/${streamKey}/index.m3u8`, {method: 'HEAD'});
            if (response.ok) {
                lastWorkingPort = port;
                return true;
            }
        } catch {}
    }
    return false;
}

// Fonctions de contrôle
async function forceCheck() {
    const streamKey = document.getElementById('key').textContent;
    
    try {
        const response = await fetch(`force-sync.php?action=force_check&stream_key=${streamKey}`);
        const data = await response.json();
        alert(`Vérification: ${data.message}\nStatut: ${data.is_live ? 'LIVE' : 'OFFLINE'}`);
        updateStreamStatus();
    } catch (error) {
        alert('Erreur: ' + error.message);
    }
}

async function forceLive() {
    if (!confirm('Forcer ce stream en LIVE ? (debug seulement)')) return;
    
    const streamKey = document.getElementById('key').textContent;
    
    try {
        const response = await fetch(`force-sync.php?action=force_live&stream_key=${streamKey}`);
        const data = await response.json();
        alert(`Stream forcé: ${data.message}`);
        updateStreamStatus();
    } catch (error) {
        alert('Erreur: ' + error.message);
    }
}

function openTestPage() {
    window.open('test-stream.php', '_blank');
}

// Basculer la surveillance on/off
function toggleMonitoring() {
    if (streamCheckInterval) {
        clearInterval(streamCheckInterval);
        streamCheckInterval = null;
        
        const btn = document.getElementById('toggleMonitoringBtn');
        btn.textContent = '▶️ Reprendre';
        btn.style.background = '#51cf66';
        
        // Mettre à jour l'interface
        const indicator = document.getElementById('statusIndicator');
        const message = document.getElementById('statusMessage');
        const connectionInfo = document.getElementById('connectionInfo');
        
        indicator.style.backgroundColor = '#666';
        indicator.textContent = '⏸️ ARRÊTÉ';
        message.textContent = 'Surveillance arrêtée';
        message.style.color = '#888';
        connectionInfo.innerHTML = 'Surveillance désactivée';
        
        alert('Surveillance arrêtée - plus de requêtes réseau');
    } else {
        startStreamMonitoring();
        
        const btn = document.getElementById('toggleMonitoringBtn');
        btn.textContent = '⏸️ Arrêter';
        btn.style.background = '#ff6b6b';
        
        alert('Surveillance reprise');
    }
}

// Basculer le mode de détection
function toggleDetectionMode() {
    if (detectionMode === 'minimal') {
        detectionMode = 'smart';
        consecutiveFailures = 0;
        lastWorkingPort = null;
        // Surveillance normale mais modérée
        clearInterval(streamCheckInterval);
        streamCheckInterval = setInterval(updateStreamStatus, 6000);
        alert('Mode SMART activé - surveillance complète (6s)');
    } else {
        detectionMode = 'minimal';
        // Mode très réduit
        clearInterval(streamCheckInterval);
        streamCheckInterval = setInterval(updateStreamStatus, 15000);
        alert('Mode MINIMAL activé - surveillance réduite (15s)');
    }
    
    // Mettre à jour le bouton
    const btn = document.getElementById('toggleModeBtn');
    btn.textContent = detectionMode === 'minimal' ? '🎯 Mode Smart' : '🎯 Mode Minimal';
    btn.style.background = detectionMode === 'minimal' ? '#51cf66' : '#ffa500';
}

// Fonction pour aller sur le stream EN DIRECT
function goToStream() {
    const streamUrl = `../watch/<?= htmlspecialchars($channel['username']) ?>`;
    window.open(streamUrl, '_blank');
}

// Fonction pour arrêter manuellement le stream
async function stopStream() {
    if (!confirm('Êtes-vous sûr de vouloir arrêter le stream ?')) {
        return;
    }
    
    try {
        const response = await fetch('api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'stop_stream'})
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Afficher les informations de sécurité
            let securityMessage = '✅ Stream arrêté avec succès !\n\n';
            
            if (data.security_info) {
                securityMessage += '🛡️ MESURES DE SÉCURITÉ AUTOMATIQUES :\n';
                if (data.security_info.key_changed) {
                    securityMessage += '🔑 Nouvelle clé de stream générée\n';
                }
                if (data.security_info.session_closed) {
                    securityMessage += '🔒 Session fermée et sécurisée\n';
                }
                securityMessage += '\n⚠️ Vous devez récupérer votre nouvelle clé ci-dessous pour streamer à nouveau.';
            }
            
            alert(securityMessage);
            
            // Actualiser la page pour montrer la nouvelle clé
            setTimeout(() => {
                window.location.reload();
            }, 2000);
            
            // Réinitialiser l'interface temporairement
            window.streamStartTime = null;
            document.getElementById('statusIndicator').style.backgroundColor = '#ff6b6b';
            document.getElementById('statusIndicator').textContent = '🔒 SÉCURISÉ';
            document.getElementById('statusMessage').innerHTML = 'Stream arrêté et sécurisé.<br>🔄 Actualisation pour nouvelle clé...';
            document.getElementById('goToStreamBtn').style.display = 'none';
            document.getElementById('stopStreamBtn').style.display = 'none';
            document.getElementById('streamInstructions').innerHTML = '🔐 Nouvelle clé de sécurité en cours de génération...';
            document.getElementById('streamInstructions').style.color = '#ffa500';
        } else {
            alert('❌ Erreur: ' + (data.error || 'Échec arrêt stream'));
        }
    } catch (error) {
        console.error('Erreur arrêt stream:', error);
        alert('❌ Erreur de connexion');
    }
}

// Modifier updateStreamStatus pour afficher le bouton de redirection
const originalUpdateStreamStatus = updateStreamStatus;
updateStreamStatus = async function() {
    await originalUpdateStreamStatus();
    
    // Vérifier si le stream est EN DIRECT et afficher le bouton
    const statusElement = document.getElementById('streamStatus');
    const goToStreamBtn = document.getElementById('goToStreamBtn');
    const streamInstructions = document.getElementById('streamInstructions');
    
    if (statusElement && statusElement.textContent.includes('🔴 EN DIRECT')) {
        // Stream EN DIRECT - afficher le bouton de redirection
        if (goToStreamBtn) {
            goToStreamBtn.style.display = 'inline-block';
            goToStreamBtn.style.animation = 'pulse 1.5s infinite';
        }
        if (streamInstructions) {
            streamInstructions.innerHTML = '🎉 Votre stream est EN DIRECT ! Cliquez sur le bouton vert pour voir votre stream.';
            streamInstructions.style.color = '#51cf66';
        }
    } else {
        // Stream hors ligne - masquer le bouton
        if (goToStreamBtn) {
            goToStreamBtn.style.display = 'none';
        }
        if (streamInstructions) {
            streamInstructions.innerHTML = '💡 Démarrez votre stream dans OBS, puis cliquez sur le bouton vert qui apparaîtra ici';
            streamInstructions.style.color = '#888';
        }
    }
};

// Ajouter l'animation pulse pour le bouton
const style = document.createElement('style');
style.textContent = `
    @keyframes pulse {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(81, 207, 102, 0.7); }
        50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(81, 207, 102, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(81, 207, 102, 0); }
    }
`;
document.head.appendChild(style);
</script>
</body>
</html>