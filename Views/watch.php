<?php
// Le channel est déjà récupéré et passé par le StreamController
if (!isset($channel) || !$channel) {
    http_response_code(404);
    echo "<h1 style='color:#fff;background:#000;text-align:center;padding:100px;'>Channel introuvable</h1>";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= htmlspecialchars($channel['username']) ?> - EN DIRECT</title>
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <link rel="stylesheet" href="/STRIMR/STRIMR-Web/assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #ffffff;
            min-height: 100vh;
        }
        
        /* Animation pulse pour les indicateurs de sécurité */
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        /* Animation d'apparition pour les overlays de sécurité */
        @keyframes securityFadeIn {
            0% { 
                opacity: 0; 
                transform: translateY(20px) scale(0.9); 
                backdrop-filter: blur(0px);
            }
            100% { 
                opacity: 1; 
                transform: translateY(0) scale(1);
                backdrop-filter: blur(2px);
            }
        }
        
        /* Animation de scintillement pour les éléments sécurisés */
        @keyframes securityBlink {
            0%, 50%, 100% { border-color: rgba(255, 107, 107, 0.3); }
            25%, 75% { border-color: rgba(255, 107, 107, 0.7); }
        }
        
        /* Style spécial pour les éléments sécurisés */
        .security-disabled {
            animation: securityBlink 2s infinite;
            filter: grayscale(1) blur(1px);
            pointer-events: none !important;
        }
        
        /* Style pour l'overlay de sécurité */
        #security-overlay {
            animation: securityFadeIn 0.5s ease-out;
        }
        
        .watch-container {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 24px;
            max-width: 1600px;
            margin: 0 auto;
            padding: 20px;
            min-height: 100vh;
        }
        
        .video-section {
            background: linear-gradient(145deg, #1e1e1e, #252525);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            position: relative;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .chat-section {
            background: linear-gradient(145deg, #1a1a1a, #212121);
            border-radius: 16px;
            padding: 0;
            display: flex;
            flex-direction: column;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            height: 600px;
            min-height: 400px;
            overflow: hidden;
        }
        
        .donations-container {
            background: linear-gradient(135deg, #2a1a2a, #1f1a1f);
            border-radius: 12px;
            margin: 16px;
            border: 1px solid rgba(145, 71, 255, 0.3);
            max-height: 200px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .donations-header {
            padding: 12px 16px;
            background: rgba(145, 71, 255, 0.1);
            border-bottom: 1px solid rgba(145, 71, 255, 0.2);
        }
        
        .donations-header h4 {
            margin: 0;
            color: #d084ff;
            font-size: 14px;
            font-weight: 600;
        }
        
        .donations-feed {
            flex: 1;
            overflow-y: auto;
            padding: 8px 12px;
            max-height: 140px;
        }
        
        .donations-feed::-webkit-scrollbar {
            width: 6px;
        }
        
        .donations-feed::-webkit-scrollbar-track {
            background: rgba(145, 71, 255, 0.1);
            border-radius: 3px;
        }
        
        .donations-feed::-webkit-scrollbar-thumb {
            background: rgba(145, 71, 255, 0.4);
            border-radius: 3px;
        }
        
        .donation-item {
            background: rgba(145, 71, 255, 0.15);
            border: 1px solid rgba(145, 71, 255, 0.3);
            border-radius: 8px;
            padding: 8px 10px;
            margin-bottom: 6px;
            font-size: 12px;
        }
        
        .donation-item .donation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }
        
        .donation-item .donor-name {
            color: #d084ff;
            font-weight: 600;
        }
        
        .donation-item .donation-amount {
            background: #51cf66;
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .donation-item .message-time {
            color: #999;
            font-size: 10px;
        }
        
        .donation-item .message-content {
            color: #ccc;
            font-size: 11px;
            margin-top: 4px;
        }
        
        .stream-info {
            margin: 20px 0;
            padding: 20px;
            background: linear-gradient(135deg, #2a2a2a, #1f1f1f);
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(145, 71, 255, 0.2);
        }
        
        .stream-info h2 {
            font-size: 24px;
            margin-bottom: 8px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        #stream-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .donation-form {
            background: linear-gradient(135deg, #2d2d2d, #232323);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid rgba(145, 71, 255, 0.3);
        }
        
        .donation-form h3 {
            color: #9147ff;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        .donation-input {
            width: 100%;
            padding: 14px;
            margin: 8px 0;
            border: 2px solid #333;
            border-radius: 8px;
            background: #1a1a1a;
            color: white;
            box-sizing: border-box;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .donation-input:focus {
            outline: none;
            border-color: #9147ff;
        }
        
        .quick-amounts {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin: 15px 0;
        }
        
        .quick-amount {
            padding: 12px 8px;
            text-align: center;
            background: linear-gradient(135deg, #9147ff, #772ce8);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .quick-amount:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(145, 71, 255, 0.4);
        }
        
        #video {
            width: 100%;
            height: 500px;
            background: #000;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
        }
        
        #video-status {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, rgba(0,0,0,0.95), rgba(20,20,20,0.95));
            color: white;
            padding: 30px 40px;
            border-radius: 16px;
            text-align: center;
            z-index: 10;
            display: none;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.6);
        }
        
        .chat-live-section {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }
        
        .chat-live-section h4 {
            color: #9147ff;
            margin-bottom: 15px;
            font-size: 18px;
        }
        
        #chatMessages {
            flex: 1;
            overflow-y: auto;
            background: linear-gradient(135deg, #1a1a1a, #0f0f0f);
            border: 1px solid #333;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            max-height: 300px;
        }
        
        #chatMessages::-webkit-scrollbar {
            width: 8px;
        }
        
        #chatMessages::-webkit-scrollbar-track {
            background: #1a1a1a;
            border-radius: 4px;
        }
        
        #chatMessages::-webkit-scrollbar-thumb {
            background: #9147ff;
            border-radius: 4px;
        }
        
        .chat-input-container {
            display: flex;
            gap: 12px;
            align-items: stretch;
        }
        
        #chatInput {
            flex: 1;
            padding: 12px 16px;
            border: 2px solid #333;
            border-radius: 8px;
            background: #1a1a1a;
            color: #fff;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        #chatInput:focus {
            outline: none;
            border-color: #9147ff;
        }
        
        #sendChatBtn {
            background: linear-gradient(135deg, #9147ff, #772ce8);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        #sendChatBtn:hover {
            transform: translateY(-1px);
        }
        
        /* Styles spécifiques pour le chat moderne */
        .live-chat-container {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .chat-messages-feed {
            min-height: 200px;
            max-height: 400px;
        }
        
        .chat-message-item {
            font-family: inherit;
            border-radius: 4px;
            margin-bottom: 4px;
        }
        
        .chat-message-item.chat-type {
            background: rgba(255, 255, 255, 0.03);
            border-left: 3px solid #9147ff;
            padding: 8px 12px;
        }
        
        .chat-message-item.donation-type {
            background: rgba(81, 207, 102, 0.08);
            border-left: 3px solid #51cf66;
            padding: 8px 12px;
        }
        
        .message-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }
        
        .message-username {
            font-weight: 600;
            color: #9147ff;
            font-size: 13px;
        }
        
        .message-time {
            color: #666;
            font-size: 11px;
            margin-left: auto;
        }
        
        .message-content {
            color: #efeff1;
            font-size: 14px;
            line-height: 1.4;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        
        .donation-amount {
            background: #51cf66;
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: bold;
        }
        
        /* Correction spécifique pour les boutons donation */
        .donation-quick-row button {
            flex: none !important;
            min-width: 32px !important;
            max-width: 42px !important;
            padding: 6px 8px !important;
            font-size: 11px !important;
        }
        
        /* Assurer que la zone de chat s'affiche sur tous les appareils */
        .chat-interaction-panel {
            background: #18181b !important;
            border-top: 1px solid #333 !important;
            padding: 12px !important;
            min-height: 60px;
            display: block !important;
            flex-shrink: 0;
        }
        
        .interaction-content {
            display: none;
        }
        
        .interaction-content.active {
            display: block !important;
        }
        
        .chat-input-bottom {
            display: flex !important;
            gap: 8px !important;
            align-items: center !important;
            width: 100% !important;
        }
        
        .chat-input-bottom input {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        
        .chat-input-bottom button {
            display: flex !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        @media (max-width: 1200px) {
            .watch-container {
                grid-template-columns: 1fr;
                gap: 16px;
                padding: 10px;
            }
            
            .chat-section {
                height: 400px;
                min-height: 350px;
            }
        }
        
        @media (max-width: 768px) {
            .watch-container {
                padding: 8px;
                gap: 12px;
            }
            
            .chat-section {
                height: 350px;
                min-height: 300px;
            }
            
            .chat-interaction-panel {
                padding: 8px !important;
                min-height: 50px !important;
            }
            
            .chat-input-bottom input {
                padding: 8px 12px !important;
                font-size: 16px !important; /* Éviter le zoom iOS */
            }
            
            .donation-quick-row {
                gap: 4px !important;
            }
            
            .donation-quick-row button {
                min-width: 28px !important;
                max-width: 36px !important;
                padding: 4px 6px !important;
                font-size: 10px !important;
            }
        }
        
        @media (max-width: 480px) {
            .video-section {
                padding: 16px;
            }
            
            .stream-info {
                padding: 12px;
                margin: 10px 0;
            }
            
            .chat-section {
                height: 300px;
                min-height: 250px;
            }
            
            .chat-messages-feed {
                min-height: 120px !important;
                max-height: 180px !important;
            }
        }
        
        .stream-offline {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #1a0a0a, #2a1a1a);
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            text-align: center;
            padding: 40px;
        }
        
        .offline-content {
            background: linear-gradient(145deg, #2a2a2a, #1f1f1f);
            padding: 60px 80px;
            border-radius: 24px;
            box-shadow: 0 16px 48px rgba(0, 0, 0, 0.6);
            border: 2px solid rgba(255, 100, 100, 0.3);
            max-width: 600px;
        }
        
        .offline-icon {
            font-size: 72px;
            margin-bottom: 20px;
            color: #ff6b6b;
        }
        
        .offline-title {
            font-size: 32px;
            margin-bottom: 16px;
            color: #fff;
        }
        
        .offline-message {
            font-size: 18px;
            color: #ccc;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .back-to-dashboard {
            background: linear-gradient(135deg, #9147ff, #772ce8);
            color: white;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            display: inline-block;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .back-to-dashboard:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(145, 71, 255, 0.4);
        }
    </style>
</head>
<body>

<div class="watch-container">
    <div class="video-section">
        <video id="video" controls autoplay playsinline></video>
        <div id="video-status"></div>
        
        <div class="stream-info">
            <h2><?= htmlspecialchars($channel['username']) ?> <span id="stream-status" style="color:#ffa500;">🔍 Vérification...</span></h2>
            <div>👁️ <span id="viewerCount"><?= $channel['viewer_count'] ?></span> viewers</div>
            <?php if ($channel['user_id'] == $_SESSION['user_id'] ?? null): ?>
            <div style="margin-top:8px;">
                <button onclick="StreamManager.captureThumbnail()" 
                        style="background:#9147ff;color:white;border:none;padding:4px 8px;border-radius:4px;font-size:11px;cursor:pointer;"
                        title="Capturer thumbnail maintenant">📸 Capture</button>
            </div>
            <?php endif; ?>
            <div style="margin-top:10px;">
                <a href="/STRIMR/STRIMR-Web/dashboard" style="color:#9147ff;">← Retour au Dashboard</a>
            </div>
        </div>
    </div>

    <div class="chat-section">
        <!-- Feed unifié : messages ET donations mélangés par ordre chronologique -->
        <div class="live-chat-container">
            <div class="chat-header">
                <h4>💬 Chat en direct & Donations</h4>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <button onclick="ChatManager.forceReload()" style="background: #9147ff; border: none; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; cursor: pointer;" title="Recharger le feed">🔄</button>
                    <button onclick="ChatManager.debugFeed()" style="background: #ff6b6b; border: none; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; cursor: pointer;" title="Debug feed">🐛</button>
                    <span id="viewer-count-chat">👁️ <?= $channel['viewer_count'] ?></span>
                </div>
            </div>
            
            <div class="chat-messages-feed" id="activityFeed">
                <div class="feed-loading">Connexion au chat...</div>
            </div>
        </div>
        
        <!-- Interface d'interaction en bas (si connecté) -->
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="chat-interaction-panel">
                <!-- Onglets compacts -->
                <div class="interaction-tabs">
                    <button class="tab-btn-small active" onclick="switchTab('chat')" id="chatTabBtn">💬</button>
                    <button class="tab-btn-small" onclick="switchTab('donate')" id="donateTabBtn">💎</button>
                </div>
                
                <!-- Zone d'interaction chat -->
                <div id="chatTab" class="interaction-content active">
                    <div class="chat-input-bottom">
                        <input type="text" id="chatInput" placeholder="Discuter en direct..." maxlength="500">
                        <button onclick="sendChatMessage()" id="sendChatBtn">➤</button>
                    </div>
                </div>
                
                <!-- Zone d'interaction donation -->
                <div id="donateTab" class="interaction-content">
                    <div class="donation-compact">
                        <div class="donation-info">
                            <span>Points: <strong id="userPoints">0</strong>💎</span>
                        </div>
                        <div class="donation-quick-row">
                            <input type="number" id="donationAmount" placeholder="€" min="10">
                            <button onclick="setAmount(10)">10</button>
                            <button onclick="setAmount(50)">50</button>
                            <button onclick="setAmount(100)">100</button>
                            <button onclick="setAmount(500)">500</button>
                        </div>
                        <div class="donation-send-row">
                            <input type="text" id="donationMessage" placeholder="Message optionnel...">
                            <button onclick="sendDonation()" id="donateBtn">💸</button>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="chat-login-prompt">
                <p>🔒 <a href="/STRIMR/STRIMR-Web/login">Connectez-vous</a> pour participer au chat</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Overlay Stream Offline -->
<div class="stream-offline" id="streamOfflineOverlay">
    <div class="offline-content">
        <div class="offline-icon">📺</div>
        <h2 class="offline-title">Stream Terminé</h2>
        <p class="offline-message">
            <strong><?= htmlspecialchars($channel['username']) ?></strong> a arrêté le stream.<br>
            Merci d'avoir regardé !
        </p>
        <a href="/STRIMR/STRIMR-Web/" class="back-to-dashboard">
            ← Retour au Dashboard
        </a>
    </div>
</div>

<script src="/STRIMR/STRIMR-Web/assets/js/validation.js"></script>
<script>
// Configuration globale
const CONFIG = {
    video: document.getElementById('video'),
    streamKey: "<?= $channel['stream_key'] ?>",
    channelId: <?= $channel['id'] ?>,
    streamerId: <?= $channel['user_id'] ?>,
    hlsUrl: `http://localhost:8888/live/<?= $channel['stream_key'] ?>/index.m3u8`,
    isLoggedIn: <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>,
    userId: <?= $_SESSION['user_id'] ?? 'null' ?>,
    maxRetries: 2,  // Réduit à 2 pour détection offline plus rapide
    retryCount: 0
};

// État global de l'application
const STATE = {
    isStreamOnline: false,
    hls: null,
    currentUserPoints: 0,
    lastDonationId: 0,
    intervals: {
        viewerUpdate: null,
        sessionPing: null,
        thumbnailCapture: null
    }
};

// === GESTION DU STREAM VIDEO ===
class StreamManager {
    static async initialize() {
        console.log('🎥 Initialisation du stream...', CONFIG.streamKey);
        console.log('📺 URL HLS:', CONFIG.hlsUrl);
        
        this.showStatus('⏳ Connexion au stream...', '#ffa500');
        
        if (Hls.isSupported()) {
            this.initializeHLS();
        } else if (CONFIG.video.canPlayType('application/vnd.apple.mpegurl')) {
            this.initializeSafari();
        } else {
            this.showError('Navigateur non supporté');
        }
        
        // Vérification initiale du statut après 3 secondes
        setTimeout(() => {
            console.log('⚠️ Vérification initiale du statut après 3s');
            this.checkStreamStatus();
        }, 3000);
        
        // Vérification périodique du statut toutes les 10 secondes pour détecter les arrêts
        setInterval(() => {
            if (STATE.isStreamOnline) {
                this.checkStreamStatus();
            }
        }, 10000);
    }
    
    static initializeHLS() {
        // Nettoyer l'ancien player
        if (STATE.hls) {
            try {
                STATE.hls.destroy();
            } catch (e) {
                console.log('Erreur destruction HLS:', e);
            }
            STATE.hls = null;
        }
        
        // Créer le nouveau player avec configuration équilibrée latence/qualité
        STATE.hls = new Hls({
            lowLatencyMode: true,   // Réactiver faible latence
            backBufferLength: 20,   // Buffer modéré pour équilibrer
            maxBufferLength: 40,    // Buffer raisonnable
            liveSyncDuration: 1,    // Latence minimale
            liveMaxLatencyDuration: 3, // Latence max réduite mais stable
            enableWorker: true,     // Worker pour performances
            autoStartLoad: true,
            startPosition: -1,
            debug: false,
            // Configuration optimisée pour qualité + latence
            maxBufferSize: 40 * 1000 * 1000, // 40MB buffer optimisé
            maxBufferHole: 0.2,     // Tolérance réduite
            highBufferWatchdogPeriod: 2, // Surveillance plus fréquente
            nudgeOffset: 0.05,      // Décalage minimal
            nudgeMaxRetry: 3,       // Essais de récupération limités
            maxFragLookUpTolerance: 0.1, // Tolérance précise
            // Options de qualité
            startLevel: -1,         // Auto-sélection du niveau de qualité
            capLevelToPlayerSize: false, // Pas de limitation par taille player
            testBandwidth: true,    // Test de bande passante
            abrEwmaDefaultEstimate: 1000000 // Estimation bande passante initiale 1Mbps
        });
        
        STATE.hls.loadSource(CONFIG.hlsUrl);
        STATE.hls.attachMedia(CONFIG.video);
        
        // Événements HLS
        STATE.hls.on(Hls.Events.MANIFEST_PARSED, (event, data) => {
            console.log('✅ Stream manifest trouvé!', data);
            console.log('📊 Niveaux de qualité disponibles:', data.levels.length);
            this.onStreamFound();
        });
        
        // Événement de changement de niveau de qualité
        STATE.hls.on(Hls.Events.LEVEL_SWITCHED, (event, data) => {
            const level = STATE.hls.levels[data.level];
            if (level) {
                console.log(`📈 Qualité: ${level.width}x${level.height} @${Math.round(level.bitrate/1000)}kbps`);
            }
        });
        
        // Surveillance de la latence
        STATE.hls.on(Hls.Events.FRAG_LOADED, (event, data) => {
            const latency = STATE.hls.latency;
            if (latency && latency > 0) {
                console.log(`⚡ Latence: ${latency.toFixed(1)}s`);
            }
            
            if (!STATE.isStreamOnline) {
                console.log('📦 Premier fragment chargé');
                this.onStreamFound();
            }
        });
        
        STATE.hls.on(Hls.Events.ERROR, (event, data) => {
            // Ignorer les erreurs non-fatales courantes
            if (!data.fatal) {
                if (data.details === 'bufferStalledError' || 
                    data.details === 'bufferNudgeOnStall' ||
                    data.details === 'fragLoadError' && data.response?.code === 404) {
                    // Ces erreurs sont normales dans le streaming live
                    return;
                }
                console.log('⚠️ Erreur HLS non-fatale:', data.type, data.details);
                return;
            }
            
            console.log('⚠️ Erreur HLS fatale:', data.type, data.details);
            
            if (data.fatal) {
                switch(data.type) {
                    case Hls.ErrorTypes.NETWORK_ERROR:
                        console.log('🌐 Erreur réseau fatale:', data.details);
                        
                        // Si c'est une erreur de chargement de manifest = stream offline
                        if (data.details === 'manifestLoadError') {
                            console.log('📺 Manifest introuvable - stream offline');
                            setTimeout(() => this.showStreamEnded(), 1000);
                            return;
                        }
                        
                        // Compter les erreurs réseau
                        if (!this.networkErrorCount) this.networkErrorCount = 0;
                        this.networkErrorCount++;
                        
                        if (this.networkErrorCount >= 2) {
                            console.log('📺 Trop d\'erreurs réseau - stream offline');
                            this.showStreamEnded();
                            return;
                        }
                        break;
                        
                    case Hls.ErrorTypes.MEDIA_ERROR:
                        // Gestion intelligente des erreurs buffer avec faible latence
                        if (data.details === 'bufferStalledError') {
                            console.log('🔄 Buffer stalled - récupération rapide...');
                            // Tentative de récupération douce pour maintenir la latence
                            try {
                                STATE.hls.startLoad();
                            } catch (e) {
                                console.log('⚠️ Récupération buffer échouée');
                            }
                            return;
                        }
                        
                        if (data.details === 'bufferNudgeOnStall') {
                            console.log('🎯 Nudge buffer - normal en faible latence');
                            return; // Normal en low latency
                        }
                        
                        console.log('🎥 Erreur média - tentative de récupération...');
                        
                        // Limiter les tentatives mais permettre récupération qualité
                        if (!this.mediaErrorCount) this.mediaErrorCount = 0;
                        this.mediaErrorCount++;
                        
                        if (this.mediaErrorCount >= 2) { // Plus strict pour garder qualité
                            console.log('❌ Erreurs média répétées - arrêt');
                            setTimeout(() => this.showStreamEnded(), 1000);
                            return;
                        }
                        
                        try {
                            STATE.hls.recoverMediaError();
                        } catch (e) {
                            console.log('❌ Récupération impossible');
                            setTimeout(() => this.showStreamEnded(), 1000);
                        }
                        break;
                        
                    default:
                        console.log('❌ Erreur fatale:', data.details);
                        setTimeout(() => this.showStreamEnded(), 2000);
                        break;
                }
            }
        });
    }
    
    static initializeSafari() {
        console.log('🍎 Utilisation player natif Safari');
        CONFIG.video.src = CONFIG.hlsUrl;
        
        CONFIG.video.addEventListener('loadedmetadata', () => {
            console.log('✅ Métadonnées chargées');
            this.onStreamFound();
        });
        
        CONFIG.video.addEventListener('error', (e) => {
            console.log('❌ Erreur video native:', e);
            this.showError('Erreur de lecture');
        });
    }
    
    static async checkStreamExists() {
        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 3000);
            
            const response = await fetch(CONFIG.hlsUrl, { 
                method: 'HEAD', 
                cache: 'no-cache',
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            
            if (response.ok && !STATE.isStreamOnline) {
                console.log('🔄 Stream détecté, réinitialisation...');
                CONFIG.retryCount = 0;
                this.initialize();
            }
        } catch (error) {
            CONFIG.retryCount++;
            console.log(`❌ Tentative ${CONFIG.retryCount}/${CONFIG.maxRetries} échouée:`, error.name);
            
            // Si c'est une erreur 404 ou réseau répétée = stream offline
            if (CONFIG.retryCount >= CONFIG.maxRetries) {
                console.log('📺 Stream confirmé offline après', CONFIG.retryCount, 'tentatives');
                this.showStreamEnded();
                return;
            }
        }
    }
    
    // Vérifier le statut du stream via l'API
    static async checkStreamStatus() {
        try {
            const response = await fetch(`/STRIMR/STRIMR-Web/api.php?action=stream_status&channel_id=${CONFIG.channelId}`);
            const data = await response.json();
            
            // Si le stream était en ligne mais n'est plus détecté comme live dans la DB
            if (STATE.isStreamOnline && !data.is_live) {
                console.log('📺 Stream arrêté par le streamer - forcer la fin');
                this.showStreamEnded();
            }
        } catch (error) {
            console.error('Erreur vérification statut:', error);
            // En cas d'erreur, vérifier aussi le fichier HLS directement
            this.checkStreamExists();
        }
    }
    
    // Afficher l'overlay de fin de stream
    static showStreamEnded() {
        console.log('🔴 Stream arrêté - Fermeture complète');
        
        // Marquer comme offline immédiatement
        STATE.isStreamOnline = false;
        
        // Fermer la session côté serveur
        this.endStreamSession();
        
        // FORCER LE NETTOYAGE COMPLET DU CHAT
        if (ChatManager) {
            ChatManager.stopSessionPolling();
            ChatManager.reset();
            console.log('🧹 Chat manager complètement réinitialisé');
        }
        
        // Arrêter et nettoyer le player
        if (STATE.hls) {
            try {
                STATE.hls.destroy();
                STATE.hls = null;
                console.log('✅ Player HLS nettoyé');
            } catch (e) {
                console.log('Erreur destruction HLS:', e);
            }
        }
        
        // Masquer le statut de connexion
        const videoStatus = document.getElementById('video-status');
        if (videoStatus) {
            videoStatus.style.display = 'none';
        }
        
        // Mettre à jour le statut dans l'info stream
        const statusEl = document.getElementById('stream-status');
        if (statusEl) {
            statusEl.innerHTML = '🔴 HORS LIGNE';
            statusEl.style.color = '#ff6b6b';
            statusEl.style.background = 'rgba(255, 107, 107, 0.1)';
            statusEl.style.padding = '4px 8px';
            statusEl.style.borderRadius = '12px';
        }
        
        // Remettre l'interface chat en mode attente
        ChatManager.showStreamOfflineMessage();
        
        // Afficher l'overlay offline
        const overlay = document.getElementById('streamOfflineOverlay');
        if (overlay) {
            overlay.style.display = 'flex';
            console.log('✅ Overlay offline affiché');
        } else {
            console.error('❌ Overlay offline introuvable!');
        }
        
        // Nettoyer tous les intervals
        Object.entries(STATE.intervals).forEach(([key, interval]) => {
            if (interval) {
                clearInterval(interval);
                STATE.intervals[key] = null;
                console.log(`🧹 Interval ${key} nettoyé`);
            }
        });
        
        // Arrêter spécifiquement la capture de thumbnails
        if (STATE.intervals.thumbnailCapture) {
            clearInterval(STATE.intervals.thumbnailCapture);
            STATE.intervals.thumbnailCapture = null;
            console.log('📸 Capture automatique de thumbnails arrêtée');
        }
        
        // Supprimer tous les thumbnails de ce channel
        this.cleanupThumbnails();
        
        // Arrêter le chat
        if (ChatManager.chatPollingInterval) {
            clearInterval(ChatManager.chatPollingInterval);
            ChatManager.chatPollingInterval = null;
            console.log('🧹 Chat polling arrêté');
        }
        
        console.log('✅ Nettoyage complet terminé');
    }
    
    // Fermer la session de stream côté serveur
    static async endStreamSession() {
        try {
            console.log('🔒 Fermeture de session côté serveur...');
            
            const response = await fetch(`/STRIMR/STRIMR-Web/api.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'end_stream_session',
                    channel_id: CONFIG.channelId
                })
            });
            
            if (response.ok) {
                const result = await response.json();
                console.log('✅ Session fermée:', result);
                
                // Réinitialiser les variables de chat
                ChatManager.lastActivityTime = null;
                ChatManager.unifiedFeeds = { activity: [] };
                
            } else {
                const errorText = await response.text();
                console.warn('⚠️ Erreur fermeture session:', response.status, errorText);
            }
            
        } catch (error) {
            console.warn('⚠️ Erreur fermeture session:', error);
        }
    }
    
    static onStreamFound() {
        STATE.isStreamOnline = true;
        this.hideStatus();
        document.getElementById('stream-status').innerHTML = '🔴 EN DIRECT';
        document.getElementById('stream-status').style.color = '#ff0000';
        
        // Ajouter un écouteur pour démarrer la capture automatique dès que la vidéo joue
        if (!CONFIG.video.hasAttribute('data-thumbnail-listener')) {
            CONFIG.video.addEventListener('playing', () => {
                // Vérifier si la capture n'est pas déjà active
                if (!STATE.intervals.thumbnailCapture && !CONFIG.video.hasAttribute('data-thumbnail-active')) {
                    console.log('🎬 Vidéo en lecture - démarrage capture automatique...');
                    CONFIG.video.setAttribute('data-thumbnail-active', 'true');
                    setTimeout(() => {
                        this.startThumbnailCapture();
                    }, 3000); // 3 secondes après le début de la lecture
                }
            });
            CONFIG.video.setAttribute('data-thumbnail-listener', 'true');
        }
        
        // Tentative de lecture automatique
        CONFIG.video.play().then(() => {
            console.log('🎬 Lecture démarrée automatiquement');
        }).catch(e => {
            console.log('⚠️ Autoplay bloqué:', e.message);
            this.showPlayButton();
        });
    }
    
    // Capturer automatiquement des thumbnails du stream
    static startThumbnailCapture() {
        console.log('📸 Démarrage capture automatique de thumbnails...');
        
        // Vérifier si la capture n'est pas déjà active
        if (STATE.intervals.thumbnailCapture) {
            console.log('⚠️ Capture automatique déjà active, ignorant le redémarrage');
            return;
        }
        
        // Vérifier d'abord que la vidéo est vraiment prête
        const video = CONFIG.video;
        if (!video || video.readyState < 2 || !video.videoWidth || video.currentTime < 1) {
            console.log('⚠️ Vidéo pas encore stable, report de 3 secondes...', {
                readyState: video?.readyState,
                videoWidth: video?.videoWidth,
                currentTime: video?.currentTime
            });
            setTimeout(() => this.startThumbnailCapture(), 3000);
            return;
        }
        
        console.log('✅ Vidéo stable, démarrage capture automatique');
        
        // Nettoyer SEULEMENT les thumbnails très anciens (plus de 10 minutes)
        this.cleanupOldThumbnails();
        
        // Capturer un thumbnail immédiatement
        this.captureThumbnail();
        
        // Puis capturer toutes les 2 minutes
        STATE.intervals.thumbnailCapture = setInterval(() => {
            if (STATE.isStreamOnline && video && !video.paused && !video.ended) {
                this.captureThumbnail();
            } else {
                console.log('⚠️ Stream offline ou vidéo arrêtée, arrêt capture automatique');
                this.stopThumbnailCapture();
            }
        }, 120000); // 2 minutes
        
        console.log('✅ Capture automatique activée (toutes les 2 minutes)');
    }
    
    // Arrêter la capture automatique
    static stopThumbnailCapture() {
        if (STATE.intervals.thumbnailCapture) {
            clearInterval(STATE.intervals.thumbnailCapture);
            STATE.intervals.thumbnailCapture = null;
            console.log('⏹️ Capture automatique arrêtée');
        }
        // Nettoyer les attributs de protection
        if (CONFIG.video) {
            CONFIG.video.removeAttribute('data-thumbnail-active');
        }
    }
    
    // Capturer une frame du stream comme thumbnail
    static async captureThumbnail() {
        try {
            const video = CONFIG.video;
            
            console.log('📸 Tentative de capture thumbnail...', {
                videoExists: !!video,
                readyState: video?.readyState,
                paused: video?.paused,
                ended: video?.ended,
                videoWidth: video?.videoWidth,
                videoHeight: video?.videoHeight,
                currentTime: video?.currentTime
            });
            
            // Vérifications plus strictes de l'état de la vidéo
            if (!video) {
                console.log('❌ Élément vidéo introuvable');
                return;
            }
            
            if (video.readyState < 2) {
                console.log('⚠️ Vidéo pas encore chargée (readyState:', video.readyState, ')');
                return;
            }
            
            if (video.paused || video.ended) {
                console.log('⚠️ Vidéo en pause ou terminée');
                return;
            }
            
            if (!video.videoWidth || !video.videoHeight) {
                console.log('⚠️ Dimensions vidéo non disponibles');
                return;
            }
            
            if (video.currentTime === 0) {
                console.log('⚠️ Vidéo à 0 secondes, attente...');
                return;
            }
            
            console.log(`✅ Vidéo prête pour capture: ${video.videoWidth}x${video.videoHeight} @ ${video.currentTime.toFixed(1)}s`);
            
            // Créer un canvas pour capturer la frame
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            
            // Définir la taille du thumbnail (16:9 ratio, max 320x180)
            const maxWidth = 320;
            const maxHeight = 180;
            const videoRatio = video.videoWidth / video.videoHeight;
            
            let thumbnailWidth, thumbnailHeight;
            
            if (videoRatio > maxWidth / maxHeight) {
                thumbnailWidth = maxWidth;
                thumbnailHeight = maxWidth / videoRatio;
            } else {
                thumbnailHeight = maxHeight;
                thumbnailWidth = maxHeight * videoRatio;
            }
            
            canvas.width = thumbnailWidth;
            canvas.height = thumbnailHeight;
            
            console.log(`📐 Thumbnail dimensions: ${thumbnailWidth}x${thumbnailHeight}`);
            
            // Capturer la frame actuelle
            ctx.drawImage(video, 0, 0, thumbnailWidth, thumbnailHeight);
            
            // Convertir en blob
            canvas.toBlob(async (blob) => {
                if (blob) {
                    console.log(`📦 Blob généré: ${blob.size} bytes, type: ${blob.type}`);
                    await this.uploadThumbnail(blob);
                } else {
                    console.error('❌ Échec génération blob thumbnail');
                }
            }, 'image/jpeg', 0.8); // Qualité JPEG 80%
            
        } catch (error) {
            console.error('❌ Erreur capture thumbnail:', error);
        }
    }
    
    // Uploader le thumbnail vers le serveur
    static async uploadThumbnail(blob) {
        try {
            console.log('🚀 Début upload thumbnail...', {
                blobSize: blob.size,
                blobType: blob.type,
                channelId: CONFIG.channelId
            });
            
            const formData = new FormData();
            formData.append('action', 'upload_thumbnail');
            formData.append('channel_id', CONFIG.channelId);
            formData.append('thumbnail', blob, `stream_${CONFIG.channelId}_${Date.now()}.jpg`);
            
            // Vérifier le FormData
            console.log('📝 FormData créé:', {
                action: formData.get('action'),
                channel_id: formData.get('channel_id'),
                thumbnailFileName: formData.get('thumbnail')?.name,
                thumbnailSize: formData.get('thumbnail')?.size
            });
            
            const response = await fetch('/STRIMR/STRIMR-Web/api.php', {
                method: 'POST',
                body: formData
            });
            
            console.log('📡 Réponse serveur:', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok,
                headers: Object.fromEntries(response.headers.entries())
            });
            
            if (response.ok) {
                const result = await response.json();
                console.log('✅ Upload réussi:', result);
                
                if (result.success) {
                    console.log(`📸 Thumbnail uploadé: ${result.thumbnail_url}`);
                    
                    // Optionnel: Notifier l'utilisateur
                    this.showThumbnailSuccess(result.thumbnail_url);
                } else {
                    console.warn('⚠️ Échec upload thumbnail:', result.error);
                }
            } else {
                // Lire la réponse d'erreur pour plus de détails
                const errorText = await response.text();
                console.error('❌ Erreur HTTP upload thumbnail:', response.status, errorText);
                
                try {
                    const errorJson = JSON.parse(errorText);
                    console.error('📋 Détails erreur:', errorJson);
                } catch (e) {
                    console.error('📋 Réponse brute:', errorText);
                }
            }
            
        } catch (error) {
            console.error('❌ Erreur upload thumbnail:', error);
        }
    }
    
    // Afficher une notification de succès pour le thumbnail
    static showThumbnailSuccess(thumbnailUrl) {
        // Créer une petite preview dans le coin
        const preview = document.createElement('div');
        preview.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 120px;
            height: 68px;
            background: #000;
            border: 2px solid #9147ff;
            border-radius: 8px;
            z-index: 1000;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            animation: thumbnailPreview 3s ease;
        `;
        
        preview.innerHTML = `
            <img src="${thumbnailUrl}" style="width:100%;height:100%;object-fit:cover;" alt="Thumbnail">
            <div style="position:absolute;top:2px;left:4px;background:rgba(0,0,0,0.7);color:#fff;font-size:10px;padding:1px 4px;border-radius:3px;">📸 Auto</div>
        `;
        
        document.body.appendChild(preview);
        
        // Supprimer après 3 secondes
        setTimeout(() => {
            if (document.body.contains(preview)) {
                preview.style.animation = 'fadeOut 0.5s ease';
                setTimeout(() => preview.remove(), 500);
            }
        }, 3000);
        
        // Ajouter les keyframes CSS si pas déjà présents
        if (!document.getElementById('thumbnail-animations')) {
            const style = document.createElement('style');
            style.id = 'thumbnail-animations';
            style.textContent = `
                @keyframes thumbnailPreview {
                    0% { transform: translateX(100%); opacity: 0; }
                    10% { transform: translateX(0); opacity: 1; }
                    90% { transform: translateX(0); opacity: 1; }
                    100% { transform: translateX(100%); opacity: 0; }
                }
                @keyframes fadeOut {
                    from { opacity: 1; }
                    to { opacity: 0; }
                }
            `;
            document.head.appendChild(style);
        }
    }
    
    // Nettoyer tous les thumbnails de ce channel quand le stream se termine
    static async cleanupThumbnails() {
        try {
            console.log('🗑️ Nettoyage complet des thumbnails...');
            
            const response = await fetch('/STRIMR/STRIMR-Web/api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'cleanup_thumbnails',
                    channel_id: CONFIG.channelId
                })
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    console.log(`✅ ${result.deleted_count || 0} thumbnails supprimés`);
                } else {
                    console.warn('⚠️ Erreur nettoyage thumbnails:', result.error);
                }
            } else {
                console.error('❌ Erreur HTTP nettoyage thumbnails:', response.status);
            }
        } catch (error) {
            console.error('❌ Erreur cleanupThumbnails:', error);
        }
    }

    // Nouveau: Nettoyage sélectif des anciens thumbnails (> 10 minutes)
    static async cleanupOldThumbnails() {
        try {
            console.log('🧹 Nettoyage des anciens thumbnails...');
            
            const response = await fetch('/STRIMR/STRIMR-Web/api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'cleanup_old_thumbnails',
                    channel_id: CONFIG.channelId,
                    max_age_minutes: 10
                })
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    console.log(`🧹 ${result.deleted_count || 0} anciens thumbnails supprimés`);
                } else {
                    console.warn('⚠️ Erreur nettoyage anciens thumbnails:', result.error);
                }
            } else {
                console.error('❌ Erreur HTTP nettoyage anciens thumbnails:', response.status);
            }
        } catch (error) {
            console.error('❌ Erreur cleanupOldThumbnails:', error);
        }
    }

    // Nouveau: Nettoyage sélectif des anciens thumbnails (> 10 minutes)
    static async cleanupOldThumbnails() {
        try {
            console.log('🧹 Nettoyage des anciens thumbnails...');
            
            const response = await fetch('/STRIMR/STRIMR-Web/api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'cleanup_old_thumbnails',
                    channel_id: CONFIG.channelId,
                    max_age_minutes: 10
                })
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    console.log(`🧹 ${result.deleted_count || 0} anciens thumbnails supprimés`);
                } else {
                    console.warn('⚠️ Erreur nettoyage anciens thumbnails:', result.error);
                }
            } else {
                console.error('❌ Erreur HTTP nettoyage anciens thumbnails:', response.status);
            }
            
        } catch (error) {
            console.error('❌ Erreur nettoyage thumbnails:', error);
        }
    }
    
    static showPlayButton() {
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
            CONFIG.video.play().then(() => {
                console.log('🎬 Lecture démarrée manuellement');
                // La capture automatique se démarrera via l'écouteur 'playing'
            }).catch(e => {
                console.error('❌ Erreur lecture manuelle:', e);
            });
            playBtn.remove();
        };
        
        document.querySelector('.video-section').appendChild(playBtn);
    }
    
    static showStatus(message, color = '#ffa500') {
        const statusEl = document.getElementById('video-status');
        statusEl.innerHTML = message;
        statusEl.style.display = 'block';
        document.getElementById('stream-status').innerHTML = '🔍 Vérification...';
        document.getElementById('stream-status').style.color = color;
    }
    
    static hideStatus() {
        document.getElementById('video-status').style.display = 'none';
    }
    
    static showError(message) {
        this.showStatus(`❌ ${message}`, '#ff0000');
    }
    
    // Désactiver complètement les interactions pour la sécurité
    static disableInteractionForSecurity() {
        console.log('🛡️ Désactivation des interactions pour sécurité');
        
        // Désactiver les formulaires de chat et donation
        const chatInput = document.getElementById('chatInput');
        const donationAmount = document.getElementById('donationAmount');
        const donationMessage = document.getElementById('donationMessage');
        const chatForm = document.querySelector('.chat-form');
        const donationForm = document.querySelector('.donation-form');
        
        if (chatInput) {
            chatInput.disabled = true;
            chatInput.placeholder = '🔒 Chat désactivé - Stream terminé';
            chatInput.style.background = 'rgba(255, 107, 107, 0.1)';
            chatInput.style.border = '1px solid #ff6b6b';
            chatInput.style.color = '#666';
        }
        
        if (donationAmount) {
            donationAmount.disabled = true;
            donationAmount.style.background = 'rgba(255, 107, 107, 0.1)';
            donationAmount.style.border = '1px solid #ff6b6b';
        }
        
        if (donationMessage) {
            donationMessage.disabled = true;
            donationMessage.placeholder = '🔒 Donations désactivées - Stream terminé';
            donationMessage.style.background = 'rgba(255, 107, 107, 0.1)';
            donationMessage.style.border = '1px solid #ff6b6b';
            donationMessage.style.color = '#666';
        }
        
        // Désactiver tous les boutons d'interaction
        const interactionButtons = document.querySelectorAll('.chat-form button, .donation-form button, .donation-quick-row button');
        interactionButtons.forEach(btn => {
            btn.disabled = true;
            btn.style.background = '#666';
            btn.style.cursor = 'not-allowed';
            btn.style.opacity = '0.5';
            if (btn.textContent.includes('Envoyer')) {
                btn.textContent = '🔒 Désactivé';
            } else if (btn.textContent.includes('Donner')) {
                btn.textContent = '🔒 Sécurisé';
            }
        });
        
        // Désactiver les formulaires complets
        if (chatForm) {
            chatForm.style.opacity = '0.4';
            chatForm.style.pointerEvents = 'none';
            chatForm.style.filter = 'grayscale(1)';
        }
        
        if (donationForm) {
            donationForm.style.opacity = '0.4';
            donationForm.style.pointerEvents = 'none';
            donationForm.style.filter = 'grayscale(1)';
        }
        
        // Ajouter un overlay de sécurité
        this.addSecurityOverlay();
    }
    
    // Ajouter un overlay de sécurité sur la zone de chat
    static addSecurityOverlay() {
        const chatSection = document.querySelector('.chat-section');
        if (chatSection && !document.getElementById('security-overlay')) {
            const overlay = document.createElement('div');
            overlay.id = 'security-overlay';
            overlay.style.cssText = `
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(45deg, rgba(255, 107, 107, 0.1), rgba(255, 107, 107, 0.05));
                backdrop-filter: blur(2px);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1000;
                border-radius: 12px;
                border: 2px dashed rgba(255, 107, 107, 0.3);
            `;
            overlay.innerHTML = `
                <div style="text-align: center; padding: 20px; background: rgba(0,0,0,0.8); border-radius: 12px; border: 1px solid rgba(255, 107, 107, 0.5);">
                    <div style="font-size: 32px; margin-bottom: 10px;">🛡️</div>
                    <div style="color: #ff6b6b; font-size: 16px; font-weight: bold; margin-bottom: 8px;">ZONE SÉCURISÉE</div>
                    <div style="color: #ffffff; font-size: 12px; line-height: 1.4;">Chat et donations désactivés<br>pour votre protection</div>
                </div>
            `;
            
            chatSection.style.position = 'relative';
            chatSection.appendChild(overlay);
        }
    }
    
    static showOffline() {
        const statusEl = document.getElementById('video-status');
        statusEl.innerHTML = `
            <div style="text-align: center; padding: 30px; background: linear-gradient(135deg, #2d2d2d 0%, #1a1a1a 100%); border-radius: 16px; border: 2px solid #ff6b6b; box-shadow: 0 8px 32px rgba(255, 107, 107, 0.3);">
                <div style="font-size: 64px; margin-bottom: 20px; animation: pulse 2s infinite;">🔒</div>
                <div style="font-size: 24px; margin-bottom: 15px; color: #ff6b6b; font-weight: bold;">STREAM TERMINÉ</div>
                <div style="font-size: 16px; color: #ffffff; margin-bottom: 20px; line-height: 1.5;">
                    ⚠️ <strong>Session de stream fermée pour votre sécurité</strong><br>
                    🔐 La clé de stream a été automatiquement changée<br>
                    📺 Ce stream n'est plus disponible
                </div>
                <div style="background: rgba(255, 107, 107, 0.1); padding: 15px; border-radius: 10px; margin: 15px 0; border-left: 4px solid #ff6b6b;">
                    <div style="font-size: 14px; color: #ffcccc; margin-bottom: 8px;"><strong>🛡️ Mesures de sécurité activées :</strong></div>
                    <div style="font-size: 13px; color: #ffdddd; line-height: 1.4;">• Session fermée automatiquement<br>• Chat et donations désactivés<br>• Nouvelle clé de stream générée</div>
                </div>
                <button onclick="window.location.href='/STRIMR/STRIMR-Web/dashboard'" 
                        style="margin-top:20px;background:linear-gradient(135deg, #9147ff, #7000ff);color:white;border:none;padding:12px 24px;border-radius:8px;cursor:pointer;font-size:16px;font-weight:bold;box-shadow: 0 4px 15px rgba(145, 71, 255, 0.4);transition: all 0.3s ease;">
                    🏠 Retour Dashboard
                </button>
                <button onclick="StreamManager.initialize()" 
                        style="margin-top:10px;margin-left:10px;background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2);padding:10px 20px;border-radius:8px;cursor:pointer;font-size:14px;">
                    🔄 Réessayer la connexion
                </button>
            </div>
        `;
        statusEl.style.display = 'block';
        
        document.getElementById('stream-status').innerHTML = '🔒 SÉCURISÉ';
        document.getElementById('stream-status').style.color = '#ff6b6b';
        document.getElementById('stream-status').style.background = 'rgba(255, 107, 107, 0.1)';
        document.getElementById('stream-status').style.padding = '4px 12px';
        document.getElementById('stream-status').style.borderRadius = '12px';
        document.getElementById('stream-status').style.border = '1px solid rgba(255, 107, 107, 0.3)';
        
        STATE.isStreamOnline = false;
        
        // Désactiver COMPLÈTEMENT les fonctions de chat/donation pour la sécurité
        this.disableInteractionForSecurity();
        
        if (STATE.hls) {
            try {
                STATE.hls.destroy();
            } catch (e) {
                console.log('Erreur destruction HLS:', e);
            }
            STATE.hls = null;
        }
    }
}

// === GESTION DES DONATIONS ===
class DonationManager {
    static async initialize() {
        if (!CONFIG.isLoggedIn) {
            console.log('Utilisateur non connecté - donations désactivées');
            return;
        }
        
        await this.loadUserPoints();
        // await this.loadRecentDonations(); // DÉSACTIVÉ - maintenant géré par le système de sessions
        // this.startPolling(); // DÉSACTIVÉ - maintenant géré par le système de sessions
        
        console.log('💎 Système de donations activé');
    }
    
    static async loadUserPoints() {
        try {
            console.log('🔄 Chargement des points utilisateur...');
            const response = await fetch('/STRIMR/STRIMR-Web/api.php?action=user_points');
            const data = await response.json();
            console.log('📊 Réponse API points:', data);
            
            const points = data.points || 0;
            document.getElementById('userPoints').textContent = points.toLocaleString();
            STATE.currentUserPoints = points;
            console.log(`✅ Points chargés: ${points}`);
        } catch (error) {
            console.error('❌ Erreur chargement points:', error);
            document.getElementById('userPoints').textContent = 'Erreur';
        }
    }
    
    static async loadRecentDonations() {
        try {
            const response = await fetch(`/STRIMR/STRIMR-Web/api.php?action=recent_donations&channel_id=${CONFIG.channelId}&limit=10`);
            const data = await response.json();
            
            if (Array.isArray(data) && data.length > 0) {
                // Ne pas afficher dans le feed séparé si le ChatManager unifié est utilisé
                console.log(`✅ Chargé ${data.length} donations récentes (gérées par ChatManager unifié)`);
            } else {
                console.log('Aucune donation récente trouvée');
            }
        } catch (error) {
            console.error('❌ Erreur chargement donations:', error);
        }
    }
    
    static startPolling() {
        // DÉSACTIVÉ - Le polling des donations est maintenant géré par le système de sessions
        console.log('🔄 Polling donations désactivé - géré par système de sessions');
        // Toute la logique de polling est maintenant dans le système unifié de sessions
    }
    
    static addToFeed(donation, isNew = false) {
        // Utiliser le feed unifié maintenant
        const feed = document.getElementById('activityFeed');
        if (!feed) {
            console.error('❌ Feed d\'activité introuvable');
            return;
        }
        
        const donationEl = document.createElement('div');
        donationEl.className = 'chat-message-item donation-type';
        
        const time = new Date(donation.created_at).toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
        
        donationEl.innerHTML = `
            <div class="message-header">
                <span class="message-username">${escapeHtml(donation.from_username || 'Anonyme')}</span>
                <span class="donation-amount">${donation.amount}💎</span>
                <span class="message-time">${time}</span>
            </div>
            ${donation.message ? `<div class="message-content">${escapeHtml(donation.message)}</div>` : ''}
        `;
        
        // Animation pour nouvelles donations
        if (isNew) {
            donationEl.style.animation = 'slideIn 0.3s ease';
        }
        
        feed.prepend(donationEl);
        
        // Mettre à jour le dernier ID
        if (donation.id > STATE.lastDonationId) {
            STATE.lastDonationId = donation.id;
        }
        
        // Limiter à 30 éléments dans le feed
        while (feed.children.length > 30) {
            feed.removeChild(feed.lastChild);
        }
        
        // Auto-scroll vers le bas pour voir la nouvelle donation
        feed.scrollTop = feed.scrollHeight;
        
        // Animations pour nouvelles donations
        if (isNew) {
            this.showAlert(donation);
            this.flashSection();
        }
    }
    
    static getTimeAgo(dateString) {
        const now = new Date();
        const date = new Date(dateString);
        const seconds = Math.floor((now - date) / 1000);
        
        if (seconds < 60) return 'Il y a quelques secondes';
        if (seconds < 3600) return `Il y a ${Math.floor(seconds / 60)} min`;
        if (seconds < 86400) return `Il y a ${Math.floor(seconds / 3600)}h`;
        return `Il y a ${Math.floor(seconds / 86400)} jours`;
    }
    
    static showAlert(donation) {
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
        
        setTimeout(() => {
            alert.style.animation = 'slideOutAlert 0.5s ease';
            setTimeout(() => {
                if (document.body.contains(alert)) {
                    document.body.removeChild(alert);
                }
            }, 500);
        }, 4000);
    }
    
    static flashSection() {
        const section = document.querySelector('.chat-section');
        if (section) {
            section.style.animation = 'donationFlash 1s ease';
            setTimeout(() => {
                section.style.animation = '';
            }, 1000);
        }
    }
}

// === FONCTIONS GLOBALES ===

// Gestion des onglets d'interaction (Chat/Donation uniquement)
function switchTab(tabName) {
    // Retirer active de tous les onglets et contenus
    document.querySelectorAll('.tab-btn-small').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.interaction-content').forEach(content => content.classList.remove('active'));
    
    // Activer l'onglet sélectionné
    if (event && event.target) {
        event.target.classList.add('active');
    }
    
    // Activer le contenu correspondant
    const tabContent = document.getElementById(tabName + 'Tab');
    if (tabContent) {
        tabContent.classList.add('active');
    }
}

function setAmount(amount) {
    document.getElementById('donationAmount').value = amount;
    
    // Sélection visuelle du bouton dans la donation-quick-row
    document.querySelectorAll('.donation-quick-row button').forEach(btn => {
        btn.style.background = '#2c2c2c';
        btn.style.borderColor = '#444';
        btn.style.color = '#efeff1';
    });
    
    // Mettre en surbrillance le bouton sélectionné
    if (event && event.target) {
        event.target.style.background = '#9147ff';
        event.target.style.borderColor = '#9147ff';
        event.target.style.color = 'white';
    }
    
    // Validation simple
    if (window.validator && window.formValidators) {
        const result = formValidators.validateAmount(amount, 10, STATE.currentUserPoints);
        if (!result.valid) {
            validator.setInputState('donationAmount', 'warning');
        } else {
            validator.setInputState('donationAmount', 'success');
        }
    }
}

// Fonction pour envoyer un message de chat
async function sendChatMessage() {
    const input = document.getElementById('chatInput');
    if (!input) return;
    
    const message = input.value.trim();
    if (!message) return;
    
    // Validation du message
    if (window.formValidators) {
        const result = formValidators.validateChatMessage(message);
        if (!result.valid) {
            window.validator && validator.showError('chatInput', result.message);
            return;
        }
    }
    
    try {
        const response = await fetch('/STRIMR/STRIMR-Web/api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'send_chat_message',
                channel_id: CONFIG.channelId,
                message: message
            })
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.success) {
                input.value = '';
                
                // AJOUTER LE MESSAGE AU FEED EN TEMPS RÉEL POUR L'EXPÉDITEUR
                const newMessage = {
                    id: data.message_id || Date.now(),
                    username: '<?= $_SESSION['username'] ?? 'Vous' ?>',
                    user_username: '<?= $_SESSION['username'] ?? 'Vous' ?>',
                    message: message,
                    timestamp: new Date().toISOString()
                };
                
                // Ajouter à la structure interne
                if (ChatManager && ChatManager.unifiedFeeds) {
                    ChatManager.unifiedFeeds.activity.push({
                        type: 'chat',
                        timestamp: newMessage.timestamp,
                        data: newMessage
                    });
                    
                    // Trier et limiter
                    ChatManager.unifiedFeeds.activity.sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));
                    ChatManager.unifiedFeeds.activity = ChatManager.unifiedFeeds.activity.slice(-30);
                    
                    // Mettre à jour lastActivityTime
                    ChatManager.lastActivityTime = new Date().toISOString();
                    
                    // Afficher immédiatement SANS rafraîchissement
                    ChatManager.addNewActivityToDisplay();
                    console.log('✅ Message ajouté en temps réel sans rafraîchissement');
                }
                
            } else {
                console.error('Erreur envoi message:', data.error);
            }
        }
    } catch (error) {
        console.error('Erreur réseau chat:', error);
    }
}

async function sendDonation() {
    const amount = parseInt(document.getElementById('donationAmount').value);
    const message = document.getElementById('donationMessage').value;
    
    // Validation basique
    if (!amount || amount < 10) {
        alert('Montant minimum : 10 points');
        return;
    }
    
    if (amount > STATE.currentUserPoints) {
        alert(`Points insuffisants (vous avez ${STATE.currentUserPoints} points)`);
        return;
    }
    
    // Animation de chargement
    const donateBtn = document.getElementById('donateBtn');
    const originalText = donateBtn.innerHTML;
    donateBtn.innerHTML = '⏳ Envoi en cours...';
    donateBtn.disabled = true;
    
    try {
        const response = await fetch('/STRIMR/STRIMR-Web/api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'send_donation',
                to_user_id: CONFIG.streamerId,
                channel_id: CONFIG.channelId,
                amount: amount,
                message: message
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(`Donation de ${amount} points envoyée ! 🎉`);
            document.getElementById('donationAmount').value = '';
            document.getElementById('donationMessage').value = '';
            
            // Mettre à jour les points utilisateur
            STATE.currentUserPoints = result.new_balance;
            document.getElementById('userPoints').textContent = result.new_balance;
            
            // AJOUTER LA DONATION AU FEED EN TEMPS RÉEL POUR L'EXPÉDITEUR
            const newDonation = {
                id: result.donation_id || Date.now(),
                from_username: '<?= $_SESSION['username'] ?? 'Vous' ?>',
                amount: amount,
                message: message,
                created_at: new Date().toISOString()
            };
            
            // Ajouter à la structure interne
            if (ChatManager && ChatManager.unifiedFeeds) {
                ChatManager.unifiedFeeds.activity.push({
                    type: 'donation',
                    timestamp: newDonation.created_at,
                    data: newDonation
                });
                
                // Trier et limiter
                ChatManager.unifiedFeeds.activity.sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));
                ChatManager.unifiedFeeds.activity = ChatManager.unifiedFeeds.activity.slice(-30);
                
                // Mettre à jour lastActivityTime
                ChatManager.lastActivityTime = new Date().toISOString();
                
                // Afficher immédiatement SANS rafraîchissement
                ChatManager.addNewActivityToDisplay();
                console.log('✅ Donation ajoutée en temps réel sans rafraîchissement');
            }
        } else {
            console.error('Erreur donation:', result);
            alert('Erreur : ' + (result.error || 'Erreur inconnue'));
        }
    } catch (error) {
        console.error('Erreur de connexion:', error);
        alert('Erreur de connexion : ' + error.message);
    } finally {
        donateBtn.innerHTML = originalText;
        donateBtn.disabled = false;
    }
}

// === GESTION DES VIEWERS ===
function updateViewerCount() {
    fetch(`/STRIMR/STRIMR-Web/api.php?action=viewers&channel_id=${CONFIG.channelId}`)
        .then(response => response.json())
        .then(data => {
            if (data.viewers !== undefined) {
                document.getElementById('viewerCount').textContent = data.viewers;
                // Mettre à jour aussi le compteur dans le chat
                const chatViewerCount = document.getElementById('viewer-count-chat');
                if (chatViewerCount) {
                    chatViewerCount.textContent = `👁️ ${data.viewers}`;
                }
            }
        })
        .catch(error => console.log('Erreur viewers:', error));
}

// === GESTION DES SESSIONS ===
const sessionId = '<?= session_id() ?: "guest_" . time() ?>';

function pingSession() {
    if (STATE.isStreamOnline) {
        fetch('/STRIMR/STRIMR-Web/api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'ping_session',
                channel_id: CONFIG.channelId,
                session_id: sessionId
            })
        }).catch(() => {});
    }
}

// === GESTION DU CHAT ===
class ChatManager {
    static lastMessageTime = null;
    static lastActivityTime = null;
    static chatPollingInterval = null;
    static sessionPollingInterval = null;
    static errorCount = 0;
    static maxErrors = 20; // Augmenté de 5 à 20 pour éviter les arrêts fréquents
    static errorTimeout = null;
    static isInitialized = false; // Nouveau flag pour éviter la double initialisation
    static sessionCreationInProgress = false; // Flag pour éviter les créations multiples
    
    static unifiedFeeds = {
        activity: [],
        chat: [],
        donations: []
    };
    
    static initialize() {
        if (this.isInitialized) {
            console.log('⚠️ Chat manager déjà initialisé, ignorant la nouvelle tentative');
            return;
        }
        
        console.log('🚀 Initialisation chat manager...');
        this.isInitialized = true;
        
        // Nettoyer d'abord les anciens contenus
        this.clearOldContent();
        
        // Charger les messages existants SEULEMENT AU DÉBUT
        this.loadUnifiedActivity();
        
        // Démarrer le polling moderne pour les sessions
        if (!this.sessionPollingInterval) {
            this.startSessionPolling();
        }
        
        // Event listener pour Enter dans le champ de chat
        const chatInput = document.getElementById('chatInput');
        if (chatInput) {
            chatInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    sendChatMessage();
                }
            });
        }
        
        this.isInitialized = true;
        console.log('💬 Chat manager unifié initialisé');
    }
    
    // Forcer le rechargement du feed
    static forceReload() {
        console.log('🔄 Rechargement forcé du feed...');
        this.unifiedFeeds.activity = [];
        this.clearOldContent();
        this.loadUnifiedActivity();
    }
    
    // Debug : afficher les données actuelles du feed
    static debugFeed() {
        console.log('🐛 Debug feed:', {
            activity: this.unifiedFeeds.activity,
            count: this.unifiedFeeds.activity.length
        });
        this.unifiedFeeds.activity.forEach((item, index) => {
            console.log(`${index}: ${item.type} - ${item.timestamp} - `, item.data);
        });
    }
    
    // Méthode pour reset complètement le chat manager
    static reset() {
        console.log('🔄 Reset complet du chat manager...');
        this.cleanup();
        this.isInitialized = false;
        this.sessionCreationInProgress = false;
        this.errorCount = 0;
        this.lastMessageTime = null;
        this.lastActivityTime = null;
        this.unifiedFeeds = { activity: [], chat: [], donations: [] };
        
        // NETTOYER COMPLÈTEMENT L'AFFICHAGE
        const activityContainer = document.getElementById('activityFeed');
        if (activityContainer) {
            activityContainer.innerHTML = '';
            console.log('🧹 Feed d\'activité vidé complètement');
        }
    }

    // Nettoyer les anciens contenus
    static clearOldContent() {
        const activityContainer = document.getElementById('activityFeed');
        if (activityContainer) {
            activityContainer.innerHTML = '<div class="feed-loading" style="text-align:center;padding:20px;color:#666;font-size:14px;">⏳ Chargement de l\'activité...</div>';
        }
        
        // Reset des feeds
        this.unifiedFeeds = {
            activity: [],
            chat: [],
            donations: []
        };
        
        console.log('🧹 Contenus anciens nettoyés, message de chargement temporaire affiché');
    }
    
    // Chargement initial - FORCÉ PROPRE pour éviter l'accumulation d'anciens messages
    static async loadUnifiedActivity() {
        console.log('🚀 Chargement FORCÉ du feed unifié (session propre)...');
        
        try {
            // FORCER SESSION PROPRE: appel API avec flag de session fraîche
            const sessionUrl = `/STRIMR/STRIMR-Web/api.php?action=get_session_activity&channel_id=${CONFIG.channelId}&limit=50&force_fresh=1`;
            console.log('🌐 URL appelée (session fraîche forcée):', sessionUrl);
            
            const sessionResponse = await fetch(sessionUrl);
            
            if (!sessionResponse.ok) {
                throw new Error(`Session activity error: ${sessionResponse.status}`);
            }
            
            const sessionData = await sessionResponse.json();
            console.log('📊 Données de session reçues (fraîche):', {
                sessionSuccess: sessionData.success,
                messages: sessionData.messages?.length || 0,
                donations: sessionData.donations?.length || 0,
                sessionActive: sessionData.session ? true : false,
                sessionStart: sessionData.session?.stream_start || 'N/A',
                sessionId: sessionData.session?.id || 'N/A'
            });
            
            if (sessionData.success && sessionData.session) {
                // RESET COMPLET - Aucune accumulation possible
                this.unifiedFeeds.activity = [];
                
                // Vérifier que la session est vraiment récente (moins de 2 heures)
                const sessionStart = new Date(sessionData.session.stream_start);
                const now = new Date();
                const hoursAgo = (now - sessionStart) / (1000 * 60 * 60);
                
                if (hoursAgo > 2) {
                    console.warn(`⚠️ Session trop ancienne (${hoursAgo.toFixed(1)}h), création d'une nouvelle session`);
                    this.createStreamSession();
                    return;
                }
                
                // Ajouter SEULEMENT les messages de cette session précise
                if (sessionData.messages && sessionData.messages.length > 0) {
                    sessionData.messages.forEach(msg => {
                        // Vérification supplémentaire: message APRÈS le début de session
                        const msgTime = new Date(msg.timestamp);
                        if (msgTime >= sessionStart) {
                            this.unifiedFeeds.activity.push({
                                type: 'chat',
                                timestamp: msg.timestamp,
                                data: msg
                            });
                        } else {
                            console.warn(`⚠️ Message ANCIEN ignoré: ${msg.timestamp} < ${sessionData.session.stream_start}`);
                        }
                    });
                    console.log(`✅ ${sessionData.messages.length} messages de session fraîche ajoutés`);
                }
                
                // Ajouter SEULEMENT les donations de cette session précise
                if (sessionData.donations && sessionData.donations.length > 0) {
                    sessionData.donations.forEach(donation => {
                        // Vérification supplémentaire: donation APRÈS le début de session
                        const donTime = new Date(donation.created_at);
                        if (donTime >= sessionStart) {
                            this.unifiedFeeds.activity.push({
                                type: 'donation',
                                timestamp: donation.created_at,
                                data: donation
                            });
                        } else {
                            console.warn(`⚠️ Donation ANCIENNE ignorée: ${donation.created_at} < ${sessionData.session.stream_start}`);
                        }
                    });
                    console.log(`✅ ${sessionData.donations.length} donations de session fraîche ajoutées`);
                }
                
                // Trier chronologiquement
                this.unifiedFeeds.activity.sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));
                
                // Garder maximum 30 éléments pour éviter l'encombrement
                this.unifiedFeeds.activity = this.unifiedFeeds.activity.slice(-30);
                
                // Définir lastActivityTime basé sur la session
                this.lastActivityTime = sessionData.session.stream_start;
                console.log(`🕐 lastActivityTime: session start ${this.lastActivityTime}`);
                
                // Afficher proprement le feed
                this.displayUnifiedActivity();
                
                console.log(`✅ CHARGEMENT PROPRE TERMINÉ: ${this.unifiedFeeds.activity.length} activités de session ID ${sessionData.session.id}`);
                
                // FORCER la suppression du message de chargement après un court délai
                setTimeout(() => {
                    const loadingMsg = document.querySelector('.feed-loading');
                    if (loadingMsg && loadingMsg.innerHTML.includes('Chargement')) {
                        console.log('🧹 Suppression forcée du message de chargement restant');
                        loadingMsg.remove();
                    }
                }, 500);
                
            } else {
                // Pas de session active - Stream offline
                console.log('📺 Aucune session active - stream hors ligne');
                this.showStreamOfflineMessage();
            }
            
        } catch (error) {
            console.warn('⚠️ Erreur chargement activité:', error);
            this.showStreamOfflineMessage();
        }
    }
    
    static displayUnifiedActivity() {
        const activityContainer = document.getElementById('activityFeed');
        if (!activityContainer) {
            console.error('❌ Container activityFeed introuvable');
            return;
        }
        
        // Vérifier s'il faut faire un affichage initial complet
        const hasLoadingMessage = activityContainer.innerHTML.includes('Chargement') || 
                                 activityContainer.innerHTML.includes('feed-loading') ||
                                 activityContainer.children.length === 0;
        
        if (hasLoadingMessage) {
            // NETTOYER COMPLÈTEMENT le container d'abord
            activityContainer.innerHTML = '';
            
            if (this.unifiedFeeds.activity.length === 0) {
                activityContainer.innerHTML = '<div class="feed-loading">Aucune activité récente</div>';
                return;
            }
            
            // Chargement initial complet - remplacer tout le contenu
            const fragment = document.createDocumentFragment();
            
            this.unifiedFeeds.activity.forEach(activity => {
                const messageEl = this.createActivityElement(activity);
                const id = activity.type === 'chat' ? `chat-${activity.data.id}` : `donation-${activity.data.id}`;
                messageEl.setAttribute('data-activity-id', id);
                fragment.appendChild(messageEl);
            });
            
            // Remplacer COMPLÈTEMENT le contenu
            activityContainer.innerHTML = '';
            activityContainer.appendChild(fragment);
            activityContainer.scrollTop = activityContainer.scrollHeight;
            
            console.log(`✅ Feed initial créé avec ${this.unifiedFeeds.activity.length} activités (message de chargement supprimé)`);
        } else {
            // Utiliser la méthode d'ajout incrémental pour éviter le rafraîchissement
            this.addNewActivityToDisplay();
        }
    }
    
    static createActivityElement(activity) {
        const messageEl = document.createElement('div');
        messageEl.className = `chat-message-item ${activity.type}-type`;
        
        if (activity.type === 'chat') {
            const msg = activity.data;
            const time = new Date(msg.timestamp).toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
            const username = msg.user_username || msg.username;
            
            messageEl.innerHTML = `
                <div class="message-header">
                    <span class="message-username">${escapeHtml(username)}</span>
                    <span class="message-time">${time}</span>
                </div>
                <div class="message-content">${escapeHtml(msg.message)}</div>
            `;
        } else if (activity.type === 'donation') {
            const donation = activity.data;
            const time = new Date(donation.created_at).toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
            const username = donation.from_username || donation.username || 'Anonyme';
            
            messageEl.innerHTML = `
                <div class="message-header">
                    <span class="message-username">💎 ${escapeHtml(username)}</span>
                    <span class="donation-amount">${donation.amount}💎</span>
                    <span class="message-time">${time}</span>
                </div>
                ${donation.message ? `<div class="message-content">${escapeHtml(donation.message)}</div>` : ''}
            `;
        }
        
        return messageEl;
    }
    
    static showStreamOfflineMessage() {
        const activityContainer = document.getElementById('activityFeed');
        if (activityContainer) {
            activityContainer.innerHTML = `
                <div class="stream-offline-message" style="padding: 30px; background: linear-gradient(135deg, #2d2d2d 0%, #1a1a1a 100%); border-radius: 16px; border: 2px solid #ff6b6b; box-shadow: 0 8px 32px rgba(255, 107, 107, 0.2); text-align: center;">
                    <div class="offline-icon" style="font-size: 48px; margin-bottom: 20px; animation: pulse 2s infinite;">🔒</div>
                    <h3 style="color: #ff6b6b; font-size: 20px; margin-bottom: 15px; font-weight: bold;">SESSION TERMINÉE</h3>
                    <div style="background: rgba(255, 107, 107, 0.1); padding: 20px; border-radius: 12px; margin: 20px 0; border-left: 4px solid #ff6b6b;">
                        <p style="color: #ffffff; font-size: 16px; margin-bottom: 15px; line-height: 1.5;">🛡️ <strong>Mesures de sécurité automatiques activées</strong></p>
                        <div style="text-align: left; color: #ffdddd; font-size: 14px; line-height: 1.6;">
                            • 🔐 Session fermée pour votre protection<br>
                            • 💬 Chat désactivé pour empêcher les injections<br>
                            • 💎 Donations sécurisées et verrouillées<br>
                            • 🔑 Nouvelle clé de stream générée automatiquement
                        </div>
                    </div>
                    <div style="background: rgba(145, 71, 255, 0.1); padding: 15px; border-radius: 10px; margin: 15px 0; border-left: 4px solid #9147ff;">
                        <p style="color: #9147ff; font-size: 14px; font-weight: bold; margin-bottom: 8px;">📺 Pour redémarrer un nouveau stream :</p>
                        <div style="text-align: left; color: #ccc; font-size: 13px; line-height: 1.5;">
                            • Le streamer doit aller sur <strong>"Go Live"</strong><br>
                            • Récupérer sa nouvelle clé de sécurité<br>
                            • Configurer OBS avec la nouvelle clé<br>
                            • Une nouvelle session sécurisée se créera automatiquement
                        </div>
                    </div>
                    <div style="margin-top: 25px; padding: 15px; background: rgba(255, 255, 255, 0.05); border-radius: 8px; border: 1px solid rgba(255, 255, 255, 0.1);">
                        <p style="color: #888; font-size: 13px; font-style: italic;">🔒 Votre sécurité est notre priorité - Merci de votre compréhension</p>
                    </div>
                </div>
            `;
        }
        
        // Désactiver COMPLÈTEMENT et SÉCURISER les zones d'envoi pour empêcher les injections
        const chatForm = document.querySelector('.chat-form');
        const donationForm = document.querySelector('.donation-form');
        const chatInput = document.getElementById('chatInput');
        const donationInputs = document.querySelectorAll('#donationAmount, #donationMessage');
        
        if (chatForm) {
            chatForm.style.opacity = '0.3';
            chatForm.style.pointerEvents = 'none';
            chatForm.style.filter = 'grayscale(1) blur(1px)';
            chatForm.style.background = 'rgba(255, 107, 107, 0.1)';
            chatForm.style.border = '2px dashed rgba(255, 107, 107, 0.3)';
            chatForm.style.borderRadius = '8px';
            
            const submitBtn = chatForm.querySelector('button[type="submit"], button');
            if (submitBtn) {
                submitBtn.textContent = '🔒 SÉCURISÉ';
                submitBtn.disabled = true;
                submitBtn.style.background = '#666';
                submitBtn.style.cursor = 'not-allowed';
            }
        }
        
        if (chatInput) {
            chatInput.disabled = true;
            chatInput.value = '';
            chatInput.placeholder = '🔒 Chat sécurisé - Stream terminé';
            chatInput.style.background = 'rgba(255, 107, 107, 0.1)';
            chatInput.style.border = '1px solid #ff6b6b';
            chatInput.style.color = '#666';
            
            // Désactiver tous les event listeners pour empêcher l'injection
            chatInput.onkeydown = null;
            chatInput.onkeypress = null;
            chatInput.onclick = () => alert('🔒 Chat désactivé pour votre sécurité');
        }
        
        if (donationForm) {
            donationForm.style.opacity = '0.3';
            donationForm.style.pointerEvents = 'none';
            donationForm.style.filter = 'grayscale(1) blur(1px)';
            donationForm.style.background = 'rgba(255, 107, 107, 0.1)';
            donationForm.style.border = '2px dashed rgba(255, 107, 107, 0.3)';
            donationForm.style.borderRadius = '8px';
            
            const submitBtn = donationForm.querySelector('button[type="submit"], button');
            if (submitBtn) {
                submitBtn.textContent = '🔒 SÉCURISÉ';
                submitBtn.disabled = true;
                submitBtn.style.background = '#666';
                submitBtn.style.cursor = 'not-allowed';
            }
        }
        
        // Sécuriser tous les inputs de donation
        donationInputs.forEach(input => {
            input.disabled = true;
            input.value = '';
            input.style.background = 'rgba(255, 107, 107, 0.1)';
            input.style.border = '1px solid #ff6b6b';
            input.style.color = '#666';
            if (input.id === 'donationMessage') {
                input.placeholder = '🔒 Donations sécurisées - Stream terminé';
            }
        });
        
        // Sécuriser tous les boutons de montants rapides
        const quickAmountButtons = document.querySelectorAll('.donation-quick-row button');
        quickAmountButtons.forEach(btn => {
            btn.disabled = true;
            btn.style.background = '#666';
            btn.style.cursor = 'not-allowed';
            btn.style.opacity = '0.4';
            btn.onclick = () => alert('🔒 Donations désactivées pour votre sécurité');
        });
    }
    
    // Vérifier si le stream est réellement en direct
    static checkIfStreamIsLive() {
        // Vérifier plusieurs indicateurs que le stream fonctionne
        const hasHls = STATE.hls && STATE.hls.media;
        const hasManifest = STATE.isStreamOnline;
        const videoPlaying = CONFIG.video && !CONFIG.video.paused && !CONFIG.video.ended;
        
        console.log('🔍 Vérification état stream:', {
            hasHls: !!hasHls,
            hasManifest: hasManifest,
            videoPlaying: !!videoPlaying,
            videoReadyState: CONFIG.video ? CONFIG.video.readyState : 'N/A'
        });
        
        return hasManifest || videoPlaying;
    }
    
    // Créer une session de stream automatiquement
    static async createStreamSession() {
        try {
            console.log('🚀 Création automatique de session...');
            
            const response = await fetch(`/STRIMR/STRIMR-Web/api.php?action=create_stream_session&channel_id=${CONFIG.channelId}`, {
                method: 'GET'
            });
            
            if (response.ok) {
                const result = await response.json();
                console.log('✅ Session créée automatiquement:', result);
                
                // Recharger l'activité maintenant qu'une session existe
                this.loadUnifiedActivity();
                
            } else {
                const errorText = await response.text();
                console.warn('⚠️ Erreur création session:', response.status, errorText);
                
                // Essayer avec une approche alternative si erreur 400
                if (response.status === 400) {
                    console.log('🔄 Tentative alternative de création de session...');
                    this.createSessionFallback();
                }
            }
            
        } catch (error) {
            console.warn('⚠️ Erreur création session:', error);
        }
    }
    
    // Méthode fallback pour créer une session 
    static async createSessionFallback() {
        try {
            console.log('🔄 Fallback: création session via webhook simulation');
            
            // Simuler un appel webhook comme si le stream venait de commencer
            const response = await fetch(`/STRIMR/STRIMR-Web/stream-webhook.php?action=start&stream_key=${CONFIG.streamKey}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'start',
                    stream_key: CONFIG.streamKey,
                    title: 'Live Stream Session'
                })
            });
            
            if (response.ok) {
                const result = await response.json();
                console.log('✅ Session créée via fallback:', result);
                
                // Recharger l'activité
                setTimeout(() => this.loadUnifiedActivity(), 1000);
            } else {
                console.warn('⚠️ Échec fallback aussi:', response.status);
                // Dernier recours: activer le chat sans session
                this.enableChatWithoutSession();
            }
            
        } catch (error) {
            console.warn('⚠️ Erreur fallback:', error);
            this.enableChatWithoutSession();
        }
    }
    
    // Activer le chat sans session (mode dégradé)
    static enableChatWithoutSession() {
        console.log('🆘 Mode dégradé: activation du chat sans session');
        
        // Définir un lastActivityTime pour débloquer le polling
        this.lastActivityTime = new Date().toISOString();
        console.log(`🕐 lastActivityTime défini en mode dégradé: ${this.lastActivityTime}`);
        
        // Nettoyer l'affichage du message d'attente
        const activityContainer = document.getElementById('activityFeed');
        if (activityContainer) {
            activityContainer.innerHTML = '<div class="feed-loading">💬 Chat en mode dégradé - Les messages ne seront pas sauvegardés</div>';
        }
        
        // Réactiver les formulaires
        this.enableChatForms();
    }
    
    // Réactiver les formulaires de chat et donation
    static enableChatForms() {
        const chatForm = document.querySelector('.chat-form');
        const donationForm = document.querySelector('.donation-form');
        
        if (chatForm) {
            chatForm.style.opacity = '1';
            chatForm.style.pointerEvents = 'auto';
            const submitBtn = chatForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.textContent = '💬 Envoyer';
                submitBtn.disabled = false;
            }
        }
        
        if (donationForm) {
            donationForm.style.opacity = '1';
            donationForm.style.pointerEvents = 'auto';
            const submitBtn = donationForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.textContent = '💎 Donner';
                submitBtn.disabled = false;
            }
        }
    }
    
    static addNewActivityToDisplay() {
        const activityContainer = document.getElementById('activityFeed');
        if (!activityContainer) {
            console.error('❌ Container activityFeed introuvable');
            return;
        }
        
        // Récupérer les IDs déjà affichés
        const existingIds = new Set();
        const existingItems = activityContainer.querySelectorAll('[data-activity-id]');
        existingItems.forEach(item => {
            existingIds.add(item.getAttribute('data-activity-id'));
        });
        
        // Ajouter seulement les nouveaux éléments dans l'ordre correct
        let newItemsAdded = 0;
        const newElements = [];
        
        // Parcourir les activités dans l'ordre (plus anciennes en premier)
        this.unifiedFeeds.activity.forEach(activity => {
            const id = activity.type === 'chat' ? `chat-${activity.data.id}` : `donation-${activity.data.id}`;
            
            if (!existingIds.has(id)) {
                const element = this.createActivityElement(activity);
                element.setAttribute('data-activity-id', id);
                
                // Animation d'apparition fluide pour nouveaux messages
                element.style.opacity = '0';
                element.style.transform = 'translateY(10px)';
                element.style.transition = 'all 0.3s ease';
                
                newElements.push({element, timestamp: activity.timestamp});
                newItemsAdded++;
            }
        });
        
        // Trier les nouveaux éléments par timestamp et les ajouter dans l'ordre
        newElements.sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));
        
        newElements.forEach(({element}, index) => {
            // Ajouter à la fin pour respecter l'ordre chronologique
            activityContainer.appendChild(element);
            
            // Animation d'entrée avec délai léger pour chaque élément
            setTimeout(() => {
                element.style.opacity = '1';
                element.style.transform = 'translateY(0)';
            }, index * 50); // Délai de 50ms entre chaque élément
        });
        
        // Limiter le nombre d'éléments affichés (supprimer les plus anciens)
        while (activityContainer.children.length > 30) {
            const firstChild = activityContainer.firstChild;
            if (firstChild && firstChild.nodeType === 1) { // S'assurer que c'est un élément
                firstChild.style.transition = 'all 0.2s ease';
                firstChild.style.opacity = '0';
                firstChild.style.transform = 'translateY(-10px)';
                
                setTimeout(() => {
                    if (activityContainer.contains(firstChild)) {
                        activityContainer.removeChild(firstChild);
                    }
                }, 200);
            }
        }
        
        // Scroll fluide vers le bas pour voir les nouveaux messages
        if (newItemsAdded > 0) {
            activityContainer.scrollTo({
                top: activityContainer.scrollHeight,
                behavior: 'smooth'
            });
            console.log(`✅ ${newItemsAdded} nouveaux éléments ajoutés avec animation`);
        }
    }
    
    static async loadNewActivity() {
        // DÉSACTIVÉ - Maintenant géré par le système de sessions
        console.log('🔄 loadNewActivity désactivé - géré par système de sessions');
        return;
    }
    
    static async loadChatMessages() {
        // DÉSACTIVÉ - Maintenant géré par le système de sessions
        console.log('🔄 loadChatMessages désactivé - géré par système de sessions');
        return;
    }

    // NOUVEAU : Session polling SIMPLIFIÉ pour éviter les doublons
    static async loadNewSessionActivity() {
        if (!this.lastActivityTime) {
            console.log('⏭️ lastActivityTime pas encore défini, ignorer le polling');
            return;
        }
        
        try {
            // Polling intelligent: récupérer seulement les nouvelles activités
            const url = `/STRIMR/STRIMR-Web/api.php?action=get_session_activity&channel_id=${CONFIG.channelId}&limit=30`;
            
            const response = await fetch(url);
            
            if (!response.ok) {
                throw new Error(`Session activity API error: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success && data.session) {
                // Créer un Set des IDs actuels pour détecter les nouveaux
                const existingIds = new Set();
                this.unifiedFeeds.activity.forEach(activity => {
                    const id = activity.type === 'chat' ? activity.data.id : activity.data.id;
                    existingIds.add(`${activity.type}-${id}`);
                });
                
                let newItemsAdded = 0;
                const newActivities = [];
                
                // Ajouter SEULEMENT les nouveaux messages
                if (data.messages && data.messages.length > 0) {
                    data.messages.forEach(msg => {
                        const msgId = `chat-${msg.id}`;
                        if (!existingIds.has(msgId)) {
                            newActivities.push({
                                type: 'chat',
                                timestamp: msg.timestamp,
                                data: msg
                            });
                            newItemsAdded++;
                        }
                    });
                }
                
                // Ajouter SEULEMENT les nouvelles donations
                if (data.donations && data.donations.length > 0) {
                    data.donations.forEach(donation => {
                        const donId = `donation-${donation.id}`;
                        if (!existingIds.has(donId)) {
                            newActivities.push({
                                type: 'donation',
                                timestamp: donation.created_at,
                                data: donation
                            });
                            newItemsAdded++;
                        }
                    });
                }
                
                // Trier et limiter seulement s'il y a de nouveaux éléments
                if (newItemsAdded > 0) {
                    // Ajouter les nouvelles activités à la liste existante
                    this.unifiedFeeds.activity = this.unifiedFeeds.activity.concat(newActivities);
                    
                    // Trier par timestamp
                    this.unifiedFeeds.activity.sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));
                    
                    // Garder seulement les 30 dernières
                    this.unifiedFeeds.activity = this.unifiedFeeds.activity.slice(-30);
                    
                    // Mettre à jour lastActivityTime avec le timestamp le plus récent
                    const latestActivity = newActivities[newActivities.length - 1];
                    if (latestActivity) {
                        this.lastActivityTime = latestActivity.timestamp;
                    }
                    
                    // Afficher SEULEMENT les nouveaux éléments
                    this.addNewActivityToDisplay();
                    
                    console.log(`✅ ${newItemsAdded} nouveaux éléments ajoutés via polling`);
                    
                    // Log pour debug: afficher qui a envoyé
                    newActivities.forEach(activity => {
                        if (activity.type === 'chat') {
                            console.log(`📨 Nouveau message de ${activity.data.user_username}: ${activity.data.message}`);
                        } else if (activity.type === 'donation') {
                            console.log(`💎 Nouvelle donation de ${activity.data.from_username}: ${activity.data.amount}💎`);
                        }
                    });
                }
                
            } else {
                // Pas de session active
                this.showStreamOfflineMessage();
            }
            
        } catch (error) {
            console.warn('⚠️ Erreur session polling:', error);
        }
    }
    
    // Démarrer le polling moderne pour les sessions
    static startSessionPolling() {
        // Arrêter tout polling existant d'abord
        if (this.sessionPollingInterval) {
            clearInterval(this.sessionPollingInterval);
            console.log('⏹️ Ancien polling session arrêté');
        }
        
        // Vérifier qu'aucun autre intervalle ne tourne pour éviter les conflits
        if (this.chatPollingInterval) {
            clearInterval(this.chatPollingInterval);
            this.chatPollingInterval = null;
            console.log('⏹️ Ancien chat polling arrêté');
        }
        
        // Nettoyer tous les anciens intervalles possibles
        const possibleIntervals = [this.sessionPollingInterval, this.chatPollingInterval, this.errorTimeout];
        possibleIntervals.forEach(interval => {
            if (interval) {
                clearInterval(interval);
            }
        });
        
        // S'assurer qu'il n'y a qu'UN SEUL intervalle actif
        if (this.sessionPollingInterval) {
            console.log('⚠️ Interval déjà actif, ignorer la création');
            return;
        }
        
        console.log('🚀 Démarrage du polling session moderne (2 secondes)...');
        
        // Polling toutes les 2 secondes pour une synchronisation plus rapide
        this.sessionPollingInterval = setInterval(() => {
            this.loadNewSessionActivity();
        }, 2000); // 2 secondes = 2000ms (plus rapide pour voir les messages des autres)
        
        console.log(`✅ Polling activé (ID: ${this.sessionPollingInterval}, Interval: 2000ms)`);
        
        // Vérification de sécurité: arrêter automatiquement après 30 minutes
        setTimeout(() => {
            if (this.sessionPollingInterval) {
                this.stopSessionPolling();
                console.log('⏰ Arrêt automatique du polling après 30 min');
            }
        }, 30 * 60 * 1000);
    }

    // Arrêter le polling des sessions
    static stopSessionPolling() {
        if (this.sessionPollingInterval) {
            clearInterval(this.sessionPollingInterval);
            this.sessionPollingInterval = null;
            console.log('⏹️ Polling session arrêté');
        }
    }
    
    static async loadNewMessages() {
        // DÉSACTIVÉ - Maintenant géré par le système de sessions
        console.log('🔄 loadNewMessages désactivé - géré par système de sessions');
        return;
    }

    // Gestion centralisée des erreurs - DÉSACTIVÉE
    static handleError(functionName) {
        // DÉSACTIVÉ - Maintenant géré par le système de sessions
        console.log('🔄 handleError désactivé - géré par système de sessions');
        return;
    }
    


    static displayMessages(messages, append = false) {
        const chatContainer = document.getElementById('chatMessages');
        if (!chatContainer) return;
        
        if (!append) {
            chatContainer.innerHTML = '';
        }
        
        messages.forEach(message => {
            const messageEl = document.createElement('div');
            messageEl.style.cssText = 'margin-bottom:8px;padding:6px;border-radius:4px;background:rgba(255,255,255,0.05);';
            
            // Formater le timestamp avec date complète si nécessaire
            const messageDate = new Date(message.timestamp);
            const today = new Date();
            const isToday = messageDate.toDateString() === today.toDateString();
            
            let time;
            if (isToday) {
                // Aujourd'hui : afficher seulement l'heure
                time = messageDate.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
            } else {
                // Autre jour : afficher date et heure
                time = messageDate.toLocaleDateString('fr-FR', {
                    day: '2-digit', 
                    month: '2-digit',
                    hour: '2-digit', 
                    minute: '2-digit'
                });
            }
            
            const username = message.user_username || message.username;
            
            messageEl.innerHTML = `
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2px;">
                    <span style="color:#9147ff;font-weight:bold;font-size:12px;">${escapeHtml(username)}</span>
                    <div>
                        <span style="color:#666;font-size:10px;">${time}</span>
                        <button onclick="reportMessage(${message.id})" 
                                style="margin-left:5px;background:none;border:none;color:#ff6b6b;font-size:10px;cursor:pointer;" 
                                title="Signaler ce message">⚠️</button>
                    </div>
                </div>
                <div style="color:#fff;font-size:13px;word-wrap:break-word;">${escapeHtml(message.message)}</div>
            `;
            
            chatContainer.appendChild(messageEl);
        });
        
        // Auto-scroll vers le bas
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }
    
    static cleanup() {
        if (this.chatPollingInterval) {
            clearInterval(this.chatPollingInterval);
        }
    }
}

async function sendChatMessage() {
    const chatInput = document.getElementById('chatInput');
    const sendBtn = document.getElementById('sendChatBtn');
    
    if (!chatInput || !sendBtn) return;
    
    const message = chatInput.value.trim();
    if (!message) return;
    
    // Désactiver temporairement
    sendBtn.disabled = true;
    sendBtn.textContent = 'Envoi...';
    
    try {
        const response = await fetch('/STRIMR/STRIMR-Web/api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'send_chat_message',
                channel_id: CONFIG.channelId,
                message: message
            })
        });
        
        // Vérifier si la réponse est OK avant de parser JSON
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            throw new Error('Réponse non-JSON reçue du serveur');
        }
        
        const data = await response.json();
        
        if (data.success) {
            chatInput.value = '';
            
            // Ajouter immédiatement le message au feed local pour réactivité
            const currentUser = '<?= $_SESSION["username"] ?? "Vous" ?>';
            const newMessage = {
                id: data.message_id || Date.now(), // Utiliser l'ID réel si disponible
                user_username: currentUser,
                message: message,
                timestamp: new Date().toISOString()
            };
            
            // Ajouter au feed unifié
            if (ChatManager && ChatManager.unifiedFeeds && ChatManager.unifiedFeeds.activity) {
                ChatManager.unifiedFeeds.activity.push({
                    type: 'chat',
                    timestamp: newMessage.timestamp,
                    data: newMessage
                });
                
                // Trier par ordre croissant
                ChatManager.unifiedFeeds.activity.sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));
                
                // Limiter le feed (garder les plus récents)
                ChatManager.unifiedFeeds.activity = ChatManager.unifiedFeeds.activity.slice(-30);
                
                // Mettre à jour le lastActivityTime pour éviter que le polling récupère ce message
                // IMPORTANT: Utiliser le timestamp réel du serveur, pas local
                if (data.timestamp) {
                    ChatManager.lastActivityTime = data.timestamp;
                    console.log(`🕐 lastActivityTime mis à jour avec timestamp serveur: ${data.timestamp}`);
                } else {
                    // Fallback: utiliser timestamp local mais s'assurer qu'il n'est pas plus ancien
                    const msgTime = new Date();
                    const msgTimeStr = msgTime.toISOString();
                    
                    if (msgTimeStr > ChatManager.lastActivityTime) {
                        ChatManager.lastActivityTime = msgTimeStr;
                        console.log(`🕐 lastActivityTime mis à jour avec timestamp actuel: ${ChatManager.lastActivityTime}`);
                    } else {
                        console.log(`🕐 Timestamp local ignoré car plus ancien`);
                    }
                }
                
                // Redessiner tout le feed pour éviter les doublons
                ChatManager.displayUnifiedActivity();
                
                console.log('✅ Message ajouté au feed en temps réel');
            } else {
                console.error('❌ ChatManager non initialisé, impossible d\'ajouter le message au feed');
            }
        } else {
            alert('Erreur: ' + (data.error || 'Impossible d\'envoyer le message'));
        }
    } catch (error) {
        console.error('Erreur envoi message:', error);
        
        if (error.message.includes('JSON')) {
            alert('Erreur de communication avec le serveur. Veuillez réessayer.');
        } else if (error.message.includes('HTTP')) {
            alert('Erreur serveur: ' + error.message);
        } else {
            alert('Erreur réseau. Vérifiez votre connexion.');
        }
    } finally {
        sendBtn.disabled = false;
        sendBtn.textContent = '➤';
    }
}

async function reportMessage(messageId) {
    const reason = prompt('Raison du signalement:') || 'Contenu inapproprié';
    
    try {
        const response = await fetch('/STRIMR/STRIMR-Web/api.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                action: 'report_chat_message',
                channel_id: CONFIG.channelId,
                message_id: messageId,
                reason: reason
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Message signalé. Merci de contribuer à un chat respectueux.');
        } else {
            alert('Erreur: ' + (data.error || 'Impossible de signaler'));
        }
    } catch (error) {
        console.error('Erreur signalement:', error);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function leaveStream() {
    // Ne décrémenter que si l'utilisateur navigue vraiment vers une autre page
    // Pas lors d'un simple refresh
    if (window.performance && window.performance.navigation.type !== 1) {
        fetch('/STRIMR/STRIMR-Web/decrement.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                channel_id: CONFIG.channelId,
                session_id: sessionId
            })
        }).catch(() => {});
    }
    
    // Nettoyer tous les intervals
    Object.values(STATE.intervals).forEach(interval => {
        if (interval) clearInterval(interval);
    });
    
    // Nettoyer le chat
    ChatManager.cleanup();
}

// === INITIALISATION ===
document.addEventListener('DOMContentLoaded', async function() {
    console.log('🚀 Initialisation de la page watch...');
    
    // Initialiser le stream
    await StreamManager.initialize();
    
    // Initialiser le chat unifié pour TOUS les utilisateurs (avec petit délai)
    setTimeout(() => {
        ChatManager.initialize();
    }, 100);
    
    // Initialiser les donations si connecté (sans conflits avec ChatManager)
    if (CONFIG.isLoggedIn) {
        await DonationManager.initialize();
    }
    
    // Nettoyer d'abord tous les anciens intervalles
    console.log('🧹 Nettoyage de tous les intervalles...');
    if (STATE.intervals.viewerUpdate) {
        clearInterval(STATE.intervals.viewerUpdate);
    }
    if (STATE.intervals.sessionPing) {
        clearInterval(STATE.intervals.sessionPing);
    }
    if (ChatManager.sessionPollingInterval) {
        clearInterval(ChatManager.sessionPollingInterval);
        ChatManager.sessionPollingInterval = null;
    }
    if (ChatManager.chatPollingInterval) {
        clearInterval(ChatManager.chatPollingInterval);
        ChatManager.chatPollingInterval = null;
    }
    
    // Démarrer les mises à jour (seulement les essentiels)
    updateViewerCount();
    STATE.intervals.viewerUpdate = setInterval(updateViewerCount, 15000);
    STATE.intervals.sessionPing = setInterval(pingSession, 30000);
    
    console.log('✅ Initialisation terminée');
});

// === GESTION DE LA FERMETURE DE PAGE ===
let isRefreshing = false;
let isNavigating = false;

// Détecter si c'est un refresh ou une navigation
window.addEventListener('beforeunload', function(event) {
    // Marquer comme refresh si l'utilisateur actualise
    isRefreshing = true;
});

// Gérer la fermeture de page avec différenciation
window.addEventListener('pagehide', function(event) {
    // Si c'est un refresh, ne pas décrémenter
    if (!isRefreshing) {
        leaveStream();
    }
});

// Pour les navigations via liens/boutons
window.addEventListener('unload', function(event) {
    // Seulement si ce n'est pas un refresh
    if (!isRefreshing) {
        navigator.sendBeacon('/STRIMR/STRIMR-Web/decrement.php', JSON.stringify({
            channel_id: CONFIG.channelId,
            session_id: sessionId
        }));
    }
});

// Détecter les vrais changements de page
document.addEventListener('click', function(event) {
    const link = event.target.closest('a');
    if (link && link.href && !link.href.includes(window.location.pathname)) {
        isNavigating = true;
        leaveStream();
    }
});
</script>

</body>
</html>