<?php
// Le channel est déjà récupéré et passé par le StreamController
// Pas besoin de session_start() car déjà fait dans index.php
if (!isset($channel) || !$channel) {
    http_response_code(404);
    echo "<h1 style='color:#fff;background:#000;text-align:center;padding:100px;'>Channel introuvable</h1>";
    exit;
}

// Le channel est disponible directement avec toutes ses données
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($channel['username']) ?> - EN DIRECT</title>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .watch-container {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        .video-section {
            background: #18181b;
            border-radius: 12px;
            padding: 20px;
        }
        .chat-section {
            background: #18181b;
            border-radius: 12px;
            padding: 20px;
            height: 600px;
            display: flex;
            flex-direction: column;
        }
        .stream-info {
            margin: 15px 0;
            padding: 15px;
            background: #0e0e10;
            border-radius: 8px;
        }
        .donation-form {
            background: #1f1f23;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .donation-input {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: none;
            border-radius: 6px;
            background: #2c2c2c;
            color: white;
        }
        .quick-amounts {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 5px;
            margin: 10px 0;
        }
        .quick-amount {
            padding: 8px;
            text-align: center;
            background: #9147ff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .quick-amount:hover {
            background: #772ce8;
        }
        @media (max-width: 768px) {
            .watch-container {
                grid-template-columns: 1fr;
            }
        }
        @keyframes slideInFromRight {
            from {
                opacity: 0;
                transform: translateX(20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>

<div class="watch-container">
    <div class="video-section">
        <video id="video" controls autoplay playsinline></video>
        <div id="video-status" style="display:none;position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(0,0,0,0.8);color:white;padding:20px;border-radius:8px;text-align:center;font-size:18px;z-index:10;"></div>
        
        <div class="stream-info">
            <h2><?= htmlspecialchars($channel['username']) ?> <span id="stream-status" style="color:#ff0000;">● LIVE</span></h2>
            <div>👁️ <span id="viewerCount"><?= $channel['viewer_count'] ?></span> viewers</div>
            <div style="margin-top:10px;">
                <a href="dashboard" style="color:#9147ff;">← Retour au Dashboard</a>
            </div>
        </div>
    </div>

    <div class="chat-section">
        <h3>💎 Faire une Donation</h3>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="donation-form">
                <div>Mes points: <strong id="userPoints">Loading...</strong> 💎</div>
                
                <div id="donationMessages"></div>
                
                <input type="number" id="donationAmount" class="donation-input" 
                       placeholder="Montant (min. 10 points)" min="10">
                
                <div class="quick-amounts">
                    <button class="quick-amount" onclick="setAmount(10)">10💎</button>
                    <button class="quick-amount" onclick="setAmount(50)">50💎</button>
                    <button class="quick-amount" onclick="setAmount(100)">100💎</button>
                    <button class="quick-amount" onclick="setAmount(250)">250💎</button>
                    <button class="quick-amount" onclick="setAmount(500)">500💎</button>
                    <button class="quick-amount" onclick="setAmount(1000)">1000💎</button>
                </div>
                
                <textarea id="donationMessage" class="donation-input" 
                          placeholder="Message optionnel..." rows="2"></textarea>
                
                <button onclick="sendDonation()" id="donateBtn"
                        style="width:100%;background:#51cf66;color:white;border:none;padding:12px;border-radius:6px;font-weight:bold;">
                    💸 Envoyer la Donation
                </button>
            </div>
        <?php else: ?>
            <div style="text-align:center;padding:20px;color:#888;">
                <a href="login" style="color:#9147ff;">Connectez-vous</a> pour faire des donations
            </div>
        <?php endif; ?>

        <div style="flex:1;overflow-y:auto;background:#0e0e10;border-radius:8px;padding:10px;">
            <h4 style="color:#fff;margin-bottom:10px;">💝 Donations récentes</h4>
            <div id="donationFeed"></div>
        </div>
    </div>
</div>

<script src="assets/js/validation.js"></script>
<script>
const video = document.getElementById('video');
const streamKey = "<?= $channel['stream_key'] ?>";
const channelId = <?= $channel['id'] ?>;
const hlsUrl = `http://localhost:8888/live/${streamKey}/index.m3u8`;

let isStreamOnline = false;
let hls = null;
let streamCheckAttempts = 0;

// Setup du stream vidéo avec détection intelligente
function setupVideo() {
    console.log('🎥 Configuration du stream...', streamKey);
    console.log('📺 URL HLS:', hlsUrl);
    
    // Réinitialiser l'état
    isStreamOnline = false;
    document.getElementById('video-status').innerHTML = '⏳ Connexion au stream...';
    document.getElementById('video-status').style.display = 'block';
    document.getElementById('stream-status').innerHTML = '🔍 Vérification...';
    document.getElementById('stream-status').style.color = '#ffa500';
    
    // Essayer d'initialiser le player
    initializePlayer();
    
    // Vérifier périodiquement si le stream devient disponible
    setTimeout(() => {
        if (!isStreamOnline) {
            console.log('🔄 Nouvelle tentative de connexion...');
            setupVideo();
        }
    }, 10000); // Réessayer toutes les 10 secondes
}

// Vérifier si le stream existe via test direct MediaMTX
async function checkStreamExists() {
    try {
        // Test direct du manifest HLS
        const response = await fetch(hlsUrl, { 
            method: 'HEAD', 
            cache: 'no-cache',
            headers: {
                'Cache-Control': 'no-cache'
            }
        });
        
        if (response.ok) {
            console.log('Stream manifest disponible sur MediaMTX');
            return true;
        }
        
        // Fallback: vérifier via notre API
        const apiResponse = await fetch(`stream-webhook.php?stream_key=${streamKey}`);
        const data = await apiResponse.json();
        return data.success && data.is_live;
        
    } catch (error) {
        console.log('Erreur vérification stream:', error);
        return false;
    }
}

// Initialiser le lecteur HLS
function initializePlayer() {
    console.log('🚀 Initialisation du lecteur vidéo...');
    
    if (Hls.isSupported()) {
        // Nettoyer l'ancien player
        if (hls) {
            try {
                hls.destroy();
            } catch (e) {
                console.log('Erreur destruction HLS:', e);
            }
            hls = null;
        }
        
        // Créer un nouveau player HLS
        hls = new Hls({
            lowLatencyMode: true,
            backBufferLength: 10,
            maxBufferLength: 30,
            liveSyncDuration: 1,
            liveMaxLatencyDuration: 4,
            enableWorker: false,
            autoStartLoad: true,
            startPosition: -1,
            debug: false
        });
        
        hls.loadSource(hlsUrl);
        hls.attachMedia(video);
        
        // Événement : Manifest parsé avec succès
        hls.on(Hls.Events.MANIFEST_PARSED, function(event, data) {
            console.log('✅ Stream trouvé ! Démarrage...', data);
            isStreamOnline = true;
            document.getElementById('video-status').style.display = 'none';
            document.getElementById('stream-status').innerHTML = '🔴 EN DIRECT';
            document.getElementById('stream-status').style.color = '#ff0000';
            
            // Démarrer la lecture automatiquement
            video.play().then(() => {
                console.log('🎬 Lecture démarrée');
            }).catch(e => {
                console.log('⚠️ Autoplay bloqué:', e.message);
                showPlayButton();
            });
        });
        
        // Événement : Premier fragment chargé
        hls.on(Hls.Events.FRAG_LOADED, function() {
            console.log('📦 Données reçues');
            if (!isStreamOnline) {
                isStreamOnline = true;
                document.getElementById('video-status').style.display = 'none';
                document.getElementById('stream-status').innerHTML = '🔴 EN DIRECT';
                document.getElementById('stream-status').style.color = '#ff0000';
            }
        });
        
        // Gestion des erreurs
        hls.on(Hls.Events.ERROR, function(event, data) {
            console.log('⚠️ Erreur HLS:', data.type, data.details, data);
            
            if (data.fatal) {
                switch(data.type) {
                    case Hls.ErrorTypes.NETWORK_ERROR:
                        console.log('🌐 Erreur réseau - reconnexion...');
                        setTimeout(() => {
                            if (hls) {
                                hls.startLoad();
                            }
                        }, 2000);
                        break;
                        
                    case Hls.ErrorTypes.MEDIA_ERROR:
                        console.log('🎥 Erreur média - récupération...');
                        try {
                            hls.recoverMediaError();
                        } catch (e) {
                            console.log('❌ Récupération impossible');
                            showStreamOffline('Erreur de lecture');
                        }
                        break;
                        
                    default:
                        console.log('❌ Erreur fatale:', data.details);
                        showStreamOffline('Stream indisponible');
                        break;
                }
            }
        });
        
    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
        // Fallback pour Safari
        console.log('🍎 Utilisation player natif Safari');
        video.src = hlsUrl;
        video.addEventListener('loadedmetadata', () => {
            console.log('✅ Métadonnées chargées');
            isStreamOnline = true;
            document.getElementById('video-status').style.display = 'none';
            document.getElementById('stream-status').innerHTML = '🔴 EN DIRECT';
            video.play();
        });
        
        video.addEventListener('error', (e) => {
            console.log('❌ Erreur video native:', e);
            showStreamOffline('Erreur de lecture');
        });
        
    } else {
        console.log('❌ HLS non supporté');
        showStreamOffline('Navigateur non compatible');
    }
}

// Afficher un bouton de lecture manuel
function showPlayButton() {
    const playBtn = document.createElement('button');
    playBtn.innerHTML = '▶️ Cliquez pour lire le stream';
    playBtn.style.cssText = `
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #9147ff;
        color: white;
        border: none;
        padding: 15px 30px;
        border-radius: 25px;
        font-size: 16px;
        cursor: pointer;
        z-index: 100;
    `;
    
    playBtn.onclick = () => {
        video.play();
        playBtn.remove();
    };
    
    video.parentElement.appendChild(playBtn);
}

// Afficher le stream comme hors ligne avec debug
function showStreamOffline(reason = 'Stream hors ligne') {
    isStreamOnline = false;
    
    const statusEl = document.getElementById('video-status');
    const streamStatusEl = document.getElementById('stream-status');
    
    statusEl.innerHTML = `
        <div style="text-align: center; padding: 20px;">
            <div style="font-size: 48px; margin-bottom: 10px;">📺</div>
            <div style="font-size: 18px; margin-bottom: 10px;">${reason}</div>
            <div style="font-size: 14px; opacity: 0.7; margin-bottom: 15px;">
                Stream Key: <code style="background:#333;padding:2px 6px;border-radius:3px;">${streamKey}</code>
            </div>
            <div style="font-size: 12px; opacity: 0.6;">
                URL: <code style="background:#333;padding:2px 6px;border-radius:3px;font-size:10px;">${hlsUrl}</code>
            </div>
            <button onclick="setupVideo()" style="margin-top:15px;background:#9147ff;color:white;border:none;padding:8px 16px;border-radius:5px;cursor:pointer;">
                🔄 Réessayer
            </button>
        </div>
    `;
    statusEl.style.display = 'block';
    
    streamStatusEl.innerHTML = '⚫ HORS LIGNE';
    streamStatusEl.style.color = '#888';
    
    if (hls) {
        try {
            hls.destroy();
        } catch (e) {
            console.log('Erreur destruction HLS:', e);
        }
        hls = null;
    }
}
                        }
                        break;
                    case Hls.ErrorTypes.MEDIA_ERROR:
                        console.log('Erreur media, recovery...');
                        try {
                            hls.recoverMediaError();
                        } catch (e) {
                            console.log('Recovery impossible, restart...');
                            setTimeout(setupVideo, 5000);
                        }
                        break;
                    default:
                        console.log('Erreur fatale, restart dans 10 secondes');
                        if (hls) {
                            hls.destroy();
                            hls = null;
                        }
                        setTimeout(setupVideo, 10000);
                        break;
                }
            }
        });
        
        hls.on(Hls.Events.FRAG_LOAD_ERROR, function(event, data) {
            if (!streamLoaded) {
                console.log('Stream pas disponible pour le moment...');
                document.getElementById('video-status').innerHTML = '⏳ En attente du stream...';
                document.getElementById('video-status').style.display = 'block';
                // Réessayer automatiquement
                setTimeout(() => {
                    hls.startLoad();
                }, 3000);
            }
        });
        
    } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
        // Safari natif
        video.src = hlsUrl;
        video.addEventListener('loadstart', () => {
            document.getElementById('video-status').style.display = 'none';
        });
    } else {
        document.getElementById('video-status').innerHTML = '❌ Votre navigateur ne supporte pas le streaming';
        document.getElementById('video-status').style.display = 'block';
    }
}

// Initialiser le video
setupVideo();

// Variables globales pour donation et sessions
let currentUserPoints = 0;
let donationPollingInterval;
let lastDonationId = 0;
let lastDonationCheck = new Date().toISOString();

// Fonctions pour les donations
function setAmount(amount) {
    document.getElementById('donationAmount').value = amount;
    
    // Validation immédiate
    const result = formValidators.validateAmount(amount, 10, currentUserPoints);
    if (!result.valid) {
        validator.setInputState('donationAmount', 'warning');
        validator.showMessage('donationMessages', result.message, 'warning', 3000);
    } else {
        validator.setInputState('donationAmount', 'success');
        validator.clearMessage('donationMessages');
    }
}

async function sendDonation() {
    const amount = parseInt(document.getElementById('donationAmount').value);
    const message = document.getElementById('donationMessage').value;
    
    // Validation complète
    let hasErrors = false;
    
    // Vérification champ vide d'abord
    const amountEmptyResult = formValidators.validateEmpty(document.getElementById('donationAmount').value, 'Montant');
    
    if (!amountEmptyResult.valid) {
        validator.setInputState('donationAmount', 'error');
        validator.shakeElement('donationAmount');
        validator.showToast(amountEmptyResult.message, 'error');
        hasErrors = true;
    } else if (!amount || amount < 10) {
        validator.setInputState('donationAmount', 'error');
        validator.shakeElement('donationAmount');
        validator.showToast('Montant minimum : 10 points', 'error');
        hasErrors = true;
    } else if (amount > currentUserPoints) {
        validator.setInputState('donationAmount', 'error');
        validator.shakeElement('donationAmount');
        validator.showToast(`Points insuffisants (vous avez ${currentUserPoints} points)`, 'error');
        hasErrors = true;
    }
    
    if (hasErrors) {
        const donateBtn = document.getElementById('donateBtn');
        donateBtn.style.transform = 'scale(0.95)';
        setTimeout(() => {
            donateBtn.style.transform = 'scale(1)';
        }, 150);
        return;
    }
    
    // Animation de chargement
    const donateBtn = document.getElementById('donateBtn');
    const originalText = donateBtn.innerHTML;
    donateBtn.innerHTML = '⏳ Envoi en cours...';
    donateBtn.disabled = true;
    
    try {
        const formData = new FormData();
        formData.append('to_user_id', <?= $channel['user_id'] ?>);
        formData.append('amount', amount);
        formData.append('message', message);
        
        const response = await fetch('donate', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            validator.showToast(`Donation de ${amount} points envoyée ! 🎉`, 'success', 4000);
            document.getElementById('donationAmount').value = '';
            document.getElementById('donationMessage').value = '';
            validator.setInputState('donationAmount', '');
            loadUserPoints(); // Recharger les points
        } else {
            validator.setInputState('donationAmount', 'error');
            validator.showToast('Erreur : ' + result.error, 'error');
        }
    } catch (error) {
        validator.showMessage('donationMessages', 'Erreur de connexion');
    } finally {
        donateBtn.innerHTML = originalText;
        donateBtn.disabled = false;
    }
}

// Validation en temps réel du montant
document.addEventListener('DOMContentLoaded', () => {
    const amountInput = document.getElementById('donationAmount');
    if (amountInput) {
        amountInput.addEventListener('input', (e) => {
            const amount = parseInt(e.target.value);
            if (amount > 0) {
                const result = formValidators.validateAmount(amount, 10, currentUserPoints || 99999);
                if (!result.valid) {
                    validator.setInputState('donationAmount', amount > (currentUserPoints || 0) ? 'error' : 'warning');
                } else {
                    validator.setInputState('donationAmount', 'success');
                }
            } else {
                validator.setInputState('donationAmount', '');
            }
        });
    }
});

// Charger les points de l'utilisateur
async function loadUserPoints() {
    try {
        const response = await fetch('api.php?action=user_points');
        const data = await response.json();
        const points = data.points || 0;
        document.getElementById('userPoints').textContent = points.toLocaleString();
        currentUserPoints = points;
    } catch (error) {
        console.error('Erreur chargement points:', error);
    }
}

// Variables globales pour le tracking de session
let sessionId = '<?= session_id() ?: "guest_" . time() ?>';
let viewerUpdateInterval, streamCheckInterval, sessionPingInterval;

// Fonction pour mettre à jour le statut du stream
async function checkStreamStatus() {
    try {
        // Vérifier via le webhook amélioré
        let streamActive = false;
        
        try {
            const response = await fetch(`stream-webhook.php?stream_key=${streamKey}`);
            const data = await response.json();
            streamActive = data.success && data.is_live;
        } catch (e) {
            console.log('Webhook indisponible, test direct...');
        }
        
        // Si webhook ne confirme pas, tester directement plusieurs ports
        if (!streamActive) {
            const ports = [8888, 8000, 8080, 8889];
            
            for (const port of ports) {
                try {
                    const hlsResponse = await fetch(`http://localhost:${port}/live/${streamKey}/index.m3u8`, {method: 'HEAD'});
                    if (hlsResponse.ok) {
                        streamActive = true;
                        console.log(`Stream trouvé sur port ${port}`);
                        
                        // Notifier le serveur que le stream est actif
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
        }
        
        if (streamActive) {
            if (!isStreamOnline) {
                isStreamOnline = true;
                document.getElementById('stream-status').innerHTML = '🔴 EN DIRECT';
                document.getElementById('stream-status').style.color = '#51cf66';
                document.getElementById('video-status').style.display = 'none';
                setupVideo(); // Réinitialiser la vidéo
            }
            
            // Mettre à jour les viewers
            const viewerResponse = await fetch(`api.php?action=stream_status&channel_id=${channelId}`);
            const viewerData = await viewerResponse.json();
            if (viewerData.viewer_count !== undefined) {
                document.getElementById('viewerCount').textContent = viewerData.viewer_count;
            }
        } else {
            if (isStreamOnline) {
                isStreamOnline = false;
                
                // Stream hors ligne
                document.getElementById('stream-status').innerHTML = '⚫ HORS LIGNE';
                document.getElementById('stream-status').style.color = '#ff6b6b';
                document.getElementById('video-status').innerHTML = '📡 Le streamer n\'est pas en direct';
                document.getElementById('video-status').style.display = 'block';
                
                if (hls) {
                    hls.destroy();
                    hls = null;
                }
                video.src = '';
            }
        }
    } catch (error) {
        console.error('Erreur vérification stream:', error);
    }
}

// Fonction pour maintenir la session active
function pingSession() {
    if (isStreamOnline) {
        fetch('api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'ping_session',
                channel_id: channelId,
                session_id: sessionId
            })
        }).catch(console.error);
    }
}

// Fonction pour quitter proprement
function leaveStream() {
    fetch('decrement.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            channel_id: channelId,
            session_id: sessionId
        })
    });
}

// Initialiser les mises à jour temps réel
viewerUpdateInterval = setInterval(checkStreamStatus, 5000);
streamCheckInterval = setInterval(checkStreamStatus, 10000);
sessionPingInterval = setInterval(pingSession, 30000);

// Gérer la fermeture de la page
window.addEventListener('beforeunload', leaveStream);
window.addEventListener('unload', leaveStream);

// Charger les points au démarrage si connecté
<?php if (isset($_SESSION['user_id'])): ?>
loadUserPoints();
loadRecentDonations();
startDonationPolling();
<?php endif; ?>

// Ajouter une donation au feed
// Améliorer les donations temps réel avec tracking précis
function addDonationToFeed(donation) {
    const feed = document.getElementById('donationFeed');
    const donationEl = document.createElement('div');
    donationEl.style.cssText = `
        background: linear-gradient(90deg, #9147ff22, #18181b);
        margin: 8px 0;
        padding: 12px;
        border-radius: 8px;
        border-left: 4px solid #51cf66;
        animation: slideInFromRight 0.5s ease;
        transition: all 0.3s ease;
    `;
    
    const timeAgo = timeAgoText(donation.created_at);
    donationEl.innerHTML = `
        <div style="color:#51cf66;font-weight:bold;display:flex;align-items:center;gap:5px;">
            💎 ${donation.from_username || 'Anonyme'} 
            <span style="background:#51cf66;color:white;padding:2px 6px;border-radius:10px;font-size:11px;">${donation.amount}💎</span>
        </div>
        ${donation.message ? `<div style="color:#fff;margin:5px 0;font-size:13px;font-style:italic;">"${donation.message}"</div>` : ''}
        <div style="color:#666;font-size:10px;">${timeAgo}</div>
    `;
    
    feed.prepend(donationEl);
    
    // Mettre à jour le lastDonationId pour le polling
    if (donation.id > lastDonationId) {
        lastDonationId = donation.id;
    }
    
    // Limiter à 15 donations affichées
    while (feed.children.length > 15) {
        feed.removeChild(feed.lastChild);
    }
    
    // Animation flash pour nouveaux dons
    donationEl.style.background = 'linear-gradient(90deg, #51cf6644, #18181b)';
    setTimeout(() => {
        donationEl.style.background = 'linear-gradient(90deg, #9147ff22, #18181b)';
    }, 2000);
    
    // Alerte de notification pour nouveaux dons
    showDonationAlert(donation);
}

// Fonction pour calculer le temps écoulé
function timeAgoText(dateString) {
    const now = new Date();
    const date = new Date(dateString);
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return 'Il y a quelques secondes';
    if (seconds < 3600) return `Il y a ${Math.floor(seconds / 60)} min`;
    if (seconds < 86400) return `Il y a ${Math.floor(seconds / 3600)}h`;
    return `Il y a ${Math.floor(seconds / 86400)} jours`;
}

// Alerte popup pour nouvelles donations
function showDonationAlert(donation) {
    const alert = document.createElement('div');
    alert.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: linear-gradient(45deg, #51cf66, #40c057);
        color: white;
        padding: 15px 20px;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(81, 207, 102, 0.4);
        z-index: 10000;
        animation: slideInAlert 0.5s ease;
        max-width: 300px;
        border: 2px solid rgba(255,255,255,0.2);
    `;
    
    alert.innerHTML = `
        <div style="font-weight: bold; font-size: 14px; margin-bottom: 5px;">🎉 Nouvelle donation !</div>
        <div style="font-size: 13px;">${donation.from_username || 'Anonyme'} - 💎 ${donation.amount} points</div>
        ${donation.message ? `<div style="font-size: 11px; opacity: 0.9; margin-top: 3px; font-style: italic;">"${donation.message}"</div>` : ''}
    `;
    
    document.body.appendChild(alert);
    
    // Supprimer après 4 secondes
    setTimeout(() => {
        alert.style.animation = 'slideOutAlert 0.5s ease';
        setTimeout(() => document.body.removeChild(alert), 500);
    }, 4000);
}

// Charger les donations récentes au démarrage
async function loadRecentDonations() {
    try {
        const response = await fetch(`api.php?action=recent_donations&channel_id=${channelId}&limit=10`);
        const data = await response.json();
        
        if (Array.isArray(data) && data.length > 0) {
            // Trier par ID pour s'assurer de l'ordre correct
            data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            
            data.forEach(donation => {
                addDonationToFeed(donation);
            });
            
            console.log(`Chargé ${data.length} donations récentes`);
        }
    } catch (error) {
        console.error('Erreur chargement donations:', error);
    }
}

// Écouter les nouvelles donations en temps réel avec meilleure détection
function startDonationPolling() {
    donationPollingInterval = setInterval(async () => {
        try {
            // Utiliser l'ID de la dernière donation pour éviter les doublons
            const url = `api.php?action=latest_donation&channel_id=${channelId}&after_id=${lastDonationId}`;
            const response = await fetch(url);
            const result = await response.json();
            
            if (result.success && result.donation && result.donation.id > lastDonationId) {
                console.log('💰 Nouvelle donation détectée:', result.donation);
                addDonationToFeed(result.donation);
                
                // Animation spéciale pour nouvelle donation
                flashNewDonation();
                
                // Mettre à jour les points si c'est l'utilisateur connecté qui reçoit
                <?php if (isset($_SESSION['user_id'])): ?>
                if (result.donation.to_user_id == <?= $_SESSION['user_id'] ?? 'null' ?>) {
                    loadUserPoints(); // Rafraîchir les points
                }
                <?php endif; ?>
            }
        } catch (error) {
            console.error('⚠️ Erreur polling donations:', error);
        }
    }, 2000); // Vérifier toutes les 2 secondes pour réactivité maximale
}

// Animation flash pour nouvelle donation
function flashNewDonation() {
    const donationSection = document.querySelector('.chat-section');
    if (donationSection) {
        donationSection.style.animation = 'donationFlash 1s ease';
        setTimeout(() => {
            donationSection.style.animation = '';
        }, 1000);
    }
}

let lastDonationCheck = new Date().toISOString();

// Gestion des sessions et viewers
let sessionId = '<?= session_id() ?: "guest_" . time() ?>';
let viewerUpdateInterval, sessionPingInterval;

// Initialiser la page complète
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initialisation de la page watch...');
    
    // Charger les données utilisateur si connecté
    <?php if (isset($_SESSION['user_id'])): ?>
    loadUserPoints();
    loadRecentDonations();
    startDonationPolling();
    console.log('Système de donations activé');
    <?php else: ?>
    console.log('Utilisateur non connecté - donations désactivées');
    <?php endif; ?>
    
    // Démarrer les mises à jour de viewers
    updateViewerCount();
    viewerUpdateInterval = setInterval(updateViewerCount, 15000);
    sessionPingInterval = setInterval(pingSession, 30000);
    
    // Ajouter les styles d'animation
    addAnimationStyles();
});

// Mettre à jour le nombre de viewers
function updateViewerCount() {
    fetch(`api.php?action=viewers&channel_id=${channelId}`)
        .then(response => response.json())
        .then(data => {
            if (data.viewers !== undefined) {
                document.getElementById('viewerCount').textContent = data.viewers;
            }
        })
        .catch(error => console.log('Erreur viewers:', error));
}

// Maintenir la session active
function pingSession() {
    fetch('api.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            action: 'ping_session',
            channel_id: channelId,
            session_id: sessionId
        })
    }).catch(() => {});
}

// Quitter proprement à la fermeture
function leaveStream() {
    fetch('decrement.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            channel_id: channelId,
            session_id: sessionId
        })
    }).catch(() => {});
    
    // Arrêter les intervals
    if (viewerUpdateInterval) clearInterval(viewerUpdateInterval);
    if (sessionPingInterval) clearInterval(sessionPingInterval);
    if (donationPollingInterval) clearInterval(donationPollingInterval);
}

// Ajouter les styles pour les animations
function addAnimationStyles() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInFromRight {
            from { 
                opacity: 0; 
                transform: translateX(100px); 
            }
            to { 
                opacity: 1; 
                transform: translateX(0); 
            }
        }
        @keyframes slideInAlert {
            from { 
                opacity: 0; 
                transform: translateX(350px) scale(0.8); 
            }
            to { 
                opacity: 1; 
                transform: translateX(0) scale(1); 
            }
        }
        @keyframes slideOutAlert {
            from { 
                opacity: 1; 
                transform: translateX(0) scale(1); 
            }
            to { 
                opacity: 0; 
                transform: translateX(350px) scale(0.8); 
            }
        }
        .donation-feed {
            max-height: 450px;
            overflow-y: auto;
            scroll-behavior: smooth;
        }
        .donation-feed::-webkit-scrollbar {
            width: 8px;
        }
        .donation-feed::-webkit-scrollbar-track {
            background: #18181b;
            border-radius: 4px;
        }
        .donation-feed::-webkit-scrollbar-thumb {
            background: #9147ff;
            border-radius: 4px;
        }
        .donation-feed::-webkit-scrollbar-thumb:hover {
            background: #772ce8;
        }
        @keyframes donationFlash {
            0%, 100% { background: #18181b; }
            50% { background: #51cf6622; }
        }
    `;
    document.head.appendChild(style);
}

// Gérer la fermeture de page
window.addEventListener('beforeunload', leaveStream);
window.addEventListener('pagehide', leaveStream);
</script>

</body>
</html>