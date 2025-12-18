<?php
// Nettoyer tout output préexistant
if (ob_get_level()) {
    ob_clean();
}

session_start();
require_once __DIR__ . '/Models/Channel.php';
require_once __DIR__ . '/Models/UserPoints.php';
require_once __DIR__ . '/Models/ChatMessage.php';
require_once __DIR__ . '/Models/StreamPreview.php';
require_once __DIR__ . '/Database.php';

// Créer la table chat_messages si elle n'existe pas
try {
    $pdo = Database::get();
    $pdo->exec("CREATE TABLE IF NOT EXISTS chat_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        channel_id INT NOT NULL,
        user_id INT NOT NULL,
        username VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_channel_time (channel_id, timestamp)
    )");
    
    // Vérifier aussi que la table users existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) UNIQUE NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Vérifier que la table transactions existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        from_user_id INT NOT NULL,
        to_user_id INT NOT NULL,
        amount INT NOT NULL,
        message TEXT,
        type VARCHAR(50) NOT NULL,
        channel_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_channel_type (channel_id, type),
        INDEX idx_user_type (to_user_id, type)
    )");
    
    // Ajouter la colonne channel_id si elle n'existe pas (pour les anciennes tables)
    try {
        $pdo->exec("ALTER TABLE transactions ADD COLUMN channel_id INT AFTER type");
    } catch(Exception $e) {
        // La colonne existe déjà ou autre erreur, ignorer
    }
    
    // Ajouter la colonne stream_start_time à la table channels si elle n'existe pas
    try {
        $pdo->exec("ALTER TABLE channels ADD COLUMN stream_start_time TIMESTAMP NULL DEFAULT NULL");
    } catch(Exception $e) {
        // La colonne existe déjà ou autre erreur, ignorer
    }
    
    // Corriger les anciens timestamps de chat qui pourraient ne pas avoir la date complète
    try {
        // Mettre à jour les messages avec des timestamps invalides ou incomplets
        $pdo->exec("UPDATE chat_messages SET timestamp = CURRENT_TIMESTAMP WHERE timestamp < '2020-01-01' OR timestamp IS NULL");
    } catch(Exception $e) {
        // Ignorer si erreur
    }
    
    // Vérifier que la table user_points existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_points (
        user_id INT PRIMARY KEY,
        points INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Vérifier que la table stream_previews existe
    $pdo->exec("CREATE TABLE IF NOT EXISTS stream_previews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        channel_id INT NOT NULL,
        preview_path VARCHAR(255) NOT NULL,
        file_size INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_channel_active (channel_id, is_active)
    )");
    $pdo->exec("CREATE TABLE IF NOT EXISTS transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        from_user_id INT NOT NULL,
        to_user_id INT NOT NULL,
        amount INT NOT NULL,
        message TEXT,
        type VARCHAR(50) NOT NULL,
        channel_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_channel_type (channel_id, type),
        INDEX idx_user_type (to_user_id, type)
    )");
    
} catch(Exception $e) {
    error_log('Erreur création tables: ' . $e->getMessage());
    // Ne pas arrêter l'API si les tables existent déjà
}

// Désactiver l'affichage des erreurs pour éviter de corrompre le JSON
error_reporting(E_ERROR | E_PARSE);
ini_set('display_errors', 0);

// S'assurer qu'aucun output n'a été envoyé
if (ob_get_level()) {
    ob_clean();
}

header('Content-Type: application/json; charset=utf-8');

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $action = $input['action'] ?? '';
        $channelId = $input['channel_id'] ?? '';
        
        switch ($action) {
            case 'send_chat_message':
                try {
                    // Vérifier l'authentification
                    if (!isset($_SESSION['user_id'])) {
                        echo json_encode(['success' => false, 'error' => 'Non connecté']);
                        break;
                    }
                    
                    $channelId = $input['channel_id'] ?? '';
                    $message = trim($input['message'] ?? '');
                    $userId = $_SESSION['user_id'];
                    $username = $_SESSION['username'] ?? 'Utilisateur';
                    
                    if (empty($channelId) || empty($message)) {
                        echo json_encode(['success' => false, 'error' => 'Données manquantes']);
                        break;
                    }
                    
                    // Vérifier que la table chat_messages existe
                    $pdo = Database::get();
                    $tableCheck = $pdo->query("SHOW TABLES LIKE 'chat_messages'");
                    if ($tableCheck->rowCount() === 0) {
                        echo json_encode(['success' => false, 'error' => 'Table chat non trouvée']);
                        break;
                    }
                    
                    $result = ChatMessage::sendMessage($channelId, $userId, $username, $message);
                    if ($result) {
                        error_log("✅ Message sauvegardé: Channel $channelId, User $userId, Message: $message");
                        echo json_encode(['success' => true, 'message_id' => $result['id'] ?? 'unknown']);
                    } else {
                        error_log("❌ Échec sauvegarde message: Channel $channelId, User $userId");
                        echo json_encode(['success' => false, 'error' => 'Erreur envoi message']);
                    }
                } catch(Exception $e) {
                    error_log('Erreur chat API: ' . $e->getMessage());
                    echo json_encode(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()]);
                }
                break;
                
            case 'send_donation':
                try {
                    // Vérifier l'authentification
                    if (!isset($_SESSION['user_id'])) {
                        echo json_encode(['success' => false, 'error' => 'Non connecté']);
                        break;
                    }
                    
                    $fromUserId = $_SESSION['user_id'];
                    $toUserId = $input['to_user_id'] ?? '';
                    $amount = intval($input['amount'] ?? 0);
                    $message = trim($input['message'] ?? '');
                    $channelId = $input['channel_id'] ?? '';
                    
                    if (!$toUserId || !$amount || !$channelId) {
                        echo json_encode(['success' => false, 'error' => 'Paramètres manquants']);
                        break;
                    }
                    
                    if ($amount < 10) {
                        echo json_encode(['success' => false, 'error' => 'Montant minimum : 10 points']);
                        break;
                    }
                    
                    // Vérifier les points de l'utilisateur
                    require_once 'Models/UserPoints.php';
                    $userPoints = UserPoints::getPoints($fromUserId);
                    
                    if ($userPoints < $amount) {
                        echo json_encode(['success' => false, 'error' => 'Points insuffisants']);
                        break;
                    }
                    
                    // Effectuer la transaction
                    $pdo = Database::get();
                    $pdo->beginTransaction();
                    
                    try {
                        // Débiter les points de l'expéditeur
                        UserPoints::addPoints($fromUserId, -$amount);
                        
                        // Créditer les points au destinataire
                        UserPoints::addPoints($toUserId, $amount);
                        
                        // Enregistrer la transaction
                        $stmt = $pdo->prepare("
                            INSERT INTO transactions (from_user_id, to_user_id, amount, message, type, channel_id) 
                            VALUES (?, ?, ?, ?, 'donation', ?)
                        ");
                        $stmt->execute([$fromUserId, $toUserId, $amount, $message, $channelId]);
                        
                        $donationId = $pdo->lastInsertId();
                        error_log("✅ Donation sauvegardée: ID $donationId, From $fromUserId to $toUserId, Amount: $amount, Channel: $channelId");
                        
                        $pdo->commit();
                        
                        echo json_encode([
                            'success' => true, 
                            'message' => 'Donation envoyée avec succès',
                            'new_balance' => UserPoints::getPoints($fromUserId),
                            'donation_id' => $donationId
                        ]);
                        
                    } catch(Exception $e) {
                        $pdo->rollback();
                        throw $e;
                    }
                    
                } catch(Exception $e) {
                    error_log('Erreur donation API: ' . $e->getMessage());
                    echo json_encode(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()]);
                }
                break;
                
            case 'heartbeat':
                // Signal que le stream est toujours actif
                Channel::updateHeartbeat($channelId);
                echo json_encode(['success' => true]);
                break;
                
            case 'stop_stream_old':
                // Arrêter le stream manuellement (ancienne méthode)
                Channel::setOffline($channelId);
                echo json_encode(['success' => true]);
                break;
                
            case 'ping_session':
                // Maintenir la session viewer active
                $sessionId = $input['session_id'] ?? session_id();
                Channel::pingViewerSession($channelId, $sessionId);
                echo json_encode(['success' => true]);
                break;
                
            case 'regenerate_key':
                // Régénérer un nouveau stream key
                if (!isset($_SESSION['user_id'])) {
                    echo json_encode(['success' => false, 'error' => 'Non connecté']);
                    break;
                }
                
                $pdo = Database::get();
                $newKey = bin2hex(random_bytes(20));
                
                $stmt = $pdo->prepare("UPDATE channels SET stream_key = ?, is_live = 0 WHERE user_id = ?");
                $stmt->execute([$newKey, $_SESSION['user_id']]);
                
                echo json_encode(['success' => true, 'new_key' => $newKey]);
                break;
                
            case 'cleanup_streams':
                // Nettoyer les streams inactifs
                Channel::cleanupInactiveStreams();
                echo json_encode(['success' => true, 'message' => 'Streams inactifs nettoyés']);
                break;
                
            case 'verify_all_streams':
                // Vérifier et nettoyer tous les streams
                $pdo = Database::get();
                $stmt = $pdo->query("SELECT id, stream_key, is_live FROM channels WHERE is_live = 1");
                $liveChannels = $stmt->fetchAll();
                
                $results = [];
                foreach ($liveChannels as $channel) {
                    $isActuallyLive = Channel::checkStreamStatus($channel['stream_key']);
                    $results[] = [
                        'id' => $channel['id'],
                        'stream_key' => $channel['stream_key'],
                        'was_marked_live' => true,
                        'actually_live' => $isActuallyLive
                    ];
                }
                
                echo json_encode(['success' => true, 'results' => $results]);
                break;
                
            case 'report_chat_message':
                // Signaler un message
                if (!isset($_SESSION['user_id'])) {
                    echo json_encode(['success' => false, 'error' => 'Non connecté']);
                    break;
                }
                
                $messageId = $input['message_id'] ?? 0;
                $reason = $input['reason'] ?? 'Contenu inapproprié';
                
                if (ChatMessage::reportMessage($messageId, $_SESSION['user_id'], $reason)) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Échec du signalement']);
                }
                break;
                
            case 'capture_preview':
                // Capturer une preview du stream
                $streamKey = $input['stream_key'] ?? '';
                
                if ($streamKey) {
                    $preview = StreamPreview::capturePreview($channelId, $streamKey);
                    if ($preview) {
                        echo json_encode(['success' => true, 'preview_path' => $preview]);
                    } else {
                        echo json_encode(['success' => false, 'error' => 'Échec capture']);
                    }
                } else {
                    echo json_encode(['success' => false, 'error' => 'Stream key manquant']);
                }
                break;
                
            case 'start_stream_session':
                // Démarrer une session de stream avec timestamp
                if (!isset($_SESSION['user_id'])) {
                    echo json_encode(['success' => false, 'error' => 'Non connecté']);
                    break;
                }
                
                $streamKey = $input['stream_key'] ?? '';
                $startTime = $input['start_time'] ?? date('Y-m-d H:i:s');
                
                if (!$streamKey) {
                    echo json_encode(['success' => false, 'error' => 'Stream key manquant']);
                    break;
                }
                
                $pdo = Database::get();
                
                // Mettre à jour le canal avec le timestamp de début
                $stmt = $pdo->prepare("UPDATE channels SET is_live = 1, stream_start_time = ? WHERE user_id = ? AND stream_key = ?");
                $result = $stmt->execute([$startTime, $_SESSION['user_id'], $streamKey]);
                
                if ($result) {
                    echo json_encode(['success' => true, 'start_time' => $startTime]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Erreur mise à jour canal']);
                }
                break;
                
            case 'end_stream_session':
                // Fermer la session de stream active (version POST pour le JavaScript)
                $channelId = $input['channel_id'] ?? '';
                
                if ($channelId) {
                    try {
                        require_once 'Models/StreamSession.php';
                        
                        $result = StreamSession::endActiveStream($channelId);
                        
                        if ($result) {
                            error_log("✅ [SESSION] Session fermée avec succès pour channel $channelId");
                            echo json_encode([
                                'success' => true,
                                'message' => 'Session fermée avec succès',
                                'channel_id' => $channelId,
                                'timestamp' => date('Y-m-d H:i:s')
                            ]);
                        } else {
                            echo json_encode([
                                'success' => false,
                                'message' => 'Aucune session active trouvée',
                                'channel_id' => $channelId
                            ]);
                        }
                        
                    } catch(Exception $e) {
                        error_log("❌ Erreur end_stream_session POST: " . $e->getMessage());
                        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                    }
                } else {
                    echo json_encode(['success' => false, 'error' => 'channel_id manquant']);
                }
                break;
                
            case 'end_stream_session_legacy':
                // Terminer une session de stream (ancienne version avec stream_key)
                if (!isset($_SESSION['user_id'])) {
                    echo json_encode(['success' => false, 'error' => 'Non connecté']);
                    break;
                }
                
                $streamKey = $input['stream_key'] ?? '';
                
                if (!$streamKey) {
                    echo json_encode(['success' => false, 'error' => 'Stream key manquant']);
                    break;
                }
                
                $pdo = Database::get();
                
                // Mettre à jour le canal pour arrêter le stream
                $stmt = $pdo->prepare("UPDATE channels SET is_live = 0, stream_start_time = NULL WHERE user_id = ? AND stream_key = ?");
                $result = $stmt->execute([$_SESSION['user_id'], $streamKey]);
                
                if ($result) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Erreur mise à jour canal']);
                }
                break;
                
            case 'stop_stream':
                // Action manuelle d'arrêt de stream depuis l'interface
                if (!isset($_SESSION['user_id'])) {
                    echo json_encode(['success' => false, 'error' => 'Non connecté']);
                    break;
                }
                
                $pdo = Database::get();
                
                // SÉCURITÉ : Générer automatiquement une nouvelle stream key
                $newStreamKey = bin2hex(random_bytes(20));
                error_log("🔐 [SÉCURITÉ] Génération nouvelle stream key pour user {$_SESSION['user_id']}: $newStreamKey");
                
                // Arrêter le stream ET changer la clé pour sécurité
                $stmt = $pdo->prepare("UPDATE channels SET is_live = 0, stream_start_time = NULL, stream_key = ? WHERE user_id = ?");
                $result = $stmt->execute([$newStreamKey, $_SESSION['user_id']]);
                
                if ($result) {
                    // Terminer aussi les sessions actives
                    require_once 'Models/StreamSession.php';
                    StreamSession::endActiveStream($_SESSION['user_id']);
                    
                    error_log("✅ [SÉCURITÉ] Stream arrêté et sécurisé pour user {$_SESSION['user_id']}");
                    
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Stream arrêté et sécurisé',
                        'security_info' => [
                            'key_changed' => true,
                            'session_closed' => true,
                            'new_key_preview' => substr($newStreamKey, 0, 8) . '...' // Aperçu seulement
                        ]
                    ]);
                } else {
                    echo json_encode(['success' => false, 'error' => 'Erreur arrêt stream']);
                }
                break;

            case 'upload_thumbnail':
                error_log("📸 [THUMBNAIL] Début upload_thumbnail POST");
                error_log("📸 [THUMBNAIL] Method: " . $_SERVER['REQUEST_METHOD']);
                error_log("📸 [THUMBNAIL] POST data: " . print_r($_POST, true));
                error_log("📸 [THUMBNAIL] FILES data: " . print_r($_FILES, true));
                
                // Vérifier les paramètres requis
                if (!isset($_POST['channel_id'])) {
                    error_log("❌ [THUMBNAIL] channel_id manquant");
                    http_response_code(400);
                    echo json_encode(['error' => 'Missing channel_id parameter']);
                    break;
                }
                
                if (!isset($_FILES['thumbnail'])) {
                    error_log("❌ [THUMBNAIL] Fichier thumbnail manquant");
                    http_response_code(400);
                    echo json_encode(['error' => 'Missing thumbnail file']);
                    break;
                }
                
                $channelId = (int)$_POST['channel_id'];
                $uploadedFile = $_FILES['thumbnail'];
                
                error_log("📸 [THUMBNAIL] Channel ID: $channelId");
                error_log("📸 [THUMBNAIL] File info: " . print_r($uploadedFile, true));
                
                // Vérifications de sécurité
                if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
                    error_log("❌ [THUMBNAIL] Upload error: " . $uploadedFile['error']);
                    http_response_code(400);
                    echo json_encode(['error' => 'File upload error: ' . $uploadedFile['error']]);
                    break;
                }
                
                // Vérifier le type de fichier (JPEG uniquement pour les thumbnails)
                $allowedTypes = ['image/jpeg', 'image/jpg'];
                $fileType = $uploadedFile['type'];
                
                if (!in_array($fileType, $allowedTypes)) {
                    error_log("❌ [THUMBNAIL] Type invalide: $fileType");
                    http_response_code(400);
                    echo json_encode(['error' => 'Invalid file type. Only JPEG allowed.']);
                    break;
                }
                
                // Vérifier la taille (max 2MB)
                if ($uploadedFile['size'] > 2 * 1024 * 1024) {
                    error_log("❌ [THUMBNAIL] Fichier trop gros: " . $uploadedFile['size']);
                    http_response_code(400);
                    echo json_encode(['error' => 'File too large. Max 2MB allowed.']);
                    break;
                }
                
                // Créer le dossier de destination
                $uploadDir = __DIR__ . '/uploads/thumbnails/';
                if (!is_dir($uploadDir)) {
                    if (!mkdir($uploadDir, 0755, true)) {
                        error_log("❌ Impossible de créer le dossier: $uploadDir");
                        http_response_code(500);
                        echo json_encode(['error' => 'Failed to create upload directory']);
                        break;
                    }
                }
                
                // Générer un nom de fichier sécurisé
                $filename = 'stream_' . $channelId . '_' . time() . '.jpg';
                $filePath = $uploadDir . $filename;
                $relativePath = 'uploads/thumbnails/' . $filename;
                
                // Déplacer le fichier uploadé
                if (move_uploaded_file($uploadedFile['tmp_name'], $filePath)) {
                    try {
                        $pdo = Database::get();
                        
                        // Désactiver les anciens thumbnails pour ce channel
                        $updateStmt = $pdo->prepare("UPDATE stream_previews SET is_active = 0 WHERE channel_id = ?");
                        $updateStmt->execute([$channelId]);
                        
                        // Insérer le nouveau thumbnail
                        $insertStmt = $pdo->prepare("
                            INSERT INTO stream_previews (channel_id, preview_path, file_size, is_active)
                            VALUES (?, ?, ?, 1)
                        ");
                        $insertStmt->execute([$channelId, $relativePath, $uploadedFile['size']]);
                        
                        error_log("✅ [THUMBNAIL] Thumbnail uploadé pour channel $channelId: $relativePath");
                        
                        echo json_encode([
                            'success' => true,
                            'message' => 'Thumbnail uploaded successfully',
                            'thumbnail_url' => '/STRIMR/STRIMR-Web/' . $relativePath,
                            'channel_id' => $channelId,
                            'file_size' => $uploadedFile['size']
                        ]);
                        
                    } catch (Exception $e) {
                        error_log("❌ Erreur database thumbnail: " . $e->getMessage());
                        
                        // Supprimer le fichier en cas d'erreur database
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                        
                        http_response_code(500);
                        echo json_encode(['error' => 'Database error']);
                    }
                } else {
                    error_log("❌ Échec déplacement fichier thumbnail: $filePath");
                    http_response_code(500);
                    echo json_encode(['error' => 'Failed to save file']);
                }
                break;

            case 'cleanup_thumbnails':
                // Nettoyer tous les thumbnails d'un channel
                $channelId = $input['channel_id'] ?? '';
                
                if (!$channelId) {
                    echo json_encode(['success' => false, 'error' => 'channel_id manquant']);
                    break;
                }
                
                try {
                    $pdo = Database::get();
                    
                    // Récupérer tous les thumbnails de ce channel
                    $stmt = $pdo->prepare("SELECT preview_path FROM stream_previews WHERE channel_id = ?");
                    $stmt->execute([$channelId]);
                    $thumbnails = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $deletedCount = 0;
                    
                    // Supprimer les fichiers physiques
                    foreach ($thumbnails as $thumbnail) {
                        $filePath = __DIR__ . '/' . $thumbnail['preview_path'];
                        if (file_exists($filePath)) {
                            if (unlink($filePath)) {
                                $deletedCount++;
                                error_log("🗑️ Thumbnail supprimé: {$thumbnail['preview_path']}");
                            } else {
                                error_log("❌ Échec suppression: {$thumbnail['preview_path']}");
                            }
                        }
                    }
                    
                    // Supprimer les entrées de la base de données
                    $deleteStmt = $pdo->prepare("DELETE FROM stream_previews WHERE channel_id = ?");
                    $deleteStmt->execute([$channelId]);
                    
                    error_log("✅ Nettoyage thumbnails channel $channelId: $deletedCount fichiers supprimés");
                    
                    echo json_encode([
                        'success' => true,
                        'deleted_count' => $deletedCount,
                        'channel_id' => $channelId
                    ]);
                    
                } catch (Exception $e) {
                    error_log("❌ Erreur cleanup thumbnails: " . $e->getMessage());
                    echo json_encode(['success' => false, 'error' => 'Database error']);
                }
                break;

            case 'cleanup_old_thumbnails':
                // Nettoyer uniquement les anciens thumbnails d'un channel (basé sur l'âge)
                $channelId = $input['channel_id'] ?? '';
                $maxAgeMinutes = $input['max_age_minutes'] ?? 10; // Par défaut 10 minutes
                
                if (!$channelId) {
                    echo json_encode(['success' => false, 'error' => 'channel_id manquant']);
                    break;
                }
                
                try {
                    $pdo = Database::get();
                    
                    // Récupérer les thumbnails anciens de ce channel
                    $stmt = $pdo->prepare("
                        SELECT preview_path, created_at 
                        FROM stream_previews 
                        WHERE channel_id = ? 
                        AND created_at < DATE_SUB(NOW(), INTERVAL ? MINUTE)
                        ORDER BY created_at ASC
                    ");
                    $stmt->execute([$channelId, $maxAgeMinutes]);
                    $oldThumbnails = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    $deletedCount = 0;
                    
                    // Supprimer les fichiers physiques anciens
                    foreach ($oldThumbnails as $thumbnail) {
                        $filePath = __DIR__ . '/' . $thumbnail['preview_path'];
                        if (file_exists($filePath)) {
                            if (unlink($filePath)) {
                                $deletedCount++;
                                error_log("🧹 Ancien thumbnail supprimé: {$thumbnail['preview_path']} (créé à {$thumbnail['created_at']})");
                            } else {
                                error_log("❌ Échec suppression ancien: {$thumbnail['preview_path']}");
                            }
                        }
                    }
                    
                    // Supprimer les entrées anciennes de la base de données
                    $deleteStmt = $pdo->prepare("
                        DELETE FROM stream_previews 
                        WHERE channel_id = ? 
                        AND created_at < DATE_SUB(NOW(), INTERVAL ? MINUTE)
                    ");
                    $deleteStmt->execute([$channelId, $maxAgeMinutes]);
                    
                    error_log("✅ Nettoyage anciens thumbnails channel $channelId: $deletedCount fichiers anciens supprimés (> {$maxAgeMinutes} min)");
                    
                    echo json_encode([
                        'success' => true,
                        'deleted_count' => $deletedCount,
                        'channel_id' => $channelId,
                        'max_age_minutes' => $maxAgeMinutes
                    ]);
                    
                } catch (Exception $e) {
                    error_log("❌ Erreur cleanup anciens thumbnails: " . $e->getMessage());
                    echo json_encode(['success' => false, 'error' => 'Database error']);
                }
                break;
                
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Unknown action']);
        }
        break;
        
    case 'GET':
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'check_rtmp_connections':
                // Vérifier s'il y a des connexions RTMP actives via MediaMTX API (plus fiable)
                $hasConnections = false;
                $debugInfo = [];
                $activeClients = 0;
                $activeStreams = [];
                
                // Méthode principale: MediaMTX API pour les streams actifs
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'http://localhost:9997/v3/paths/list');
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
                $apiResponse = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                $debugInfo['mediamtx_api'] = [
                    'http_code' => $httpCode,
                    'response_length' => strlen($apiResponse ?? '')
                ];
                
                if ($httpCode == 200 && !empty($apiResponse)) {
                    $apiData = json_decode($apiResponse, true);
                    if (isset($apiData['items']) && is_array($apiData['items'])) {
                        foreach ($apiData['items'] as $item) {
                            if (isset($item['ready']) && $item['ready'] === true && 
                                isset($item['source']) && isset($item['source']['type']) && 
                                $item['source']['type'] === 'rtmpConn') {
                                $activeClients++;
                                $activeStreams[] = [
                                    'name' => $item['name'] ?? 'unknown',
                                    'tracks' => $item['tracks'] ?? [],
                                    'ready_time' => $item['readyTime'] ?? 'unknown'
                                ];
                            }
                        }
                        $debugInfo['active_streams'] = $activeStreams;
                        $debugInfo['total_paths'] = count($apiData['items']);
                    }
                }
                
                // Méthode de fallback: netstat pour vérifier les connexions
                if (function_exists('shell_exec')) {
                    $output = shell_exec('netstat -an 2>nul | findstr ":1935" 2>nul');
                    $debugInfo['netstat_raw'] = trim($output);
                    
                    if (!empty(trim($output))) {
                        $established = shell_exec('netstat -an 2>nul | findstr ":1935" | findstr "ESTABLISHED" 2>nul');
                        $debugInfo['established_connections'] = trim($established);
                        
                        if (!empty(trim($established))) {
                            $lines = explode("\n", trim($established));
                            $netstatConnections = 0;
                            foreach ($lines as $line) {
                                if (empty(trim($line))) continue;
                                if (strpos($line, 'ESTABLISHED') !== false) {
                                    $netstatConnections++;
                                }
                            }
                            $debugInfo['netstat_connections'] = $netstatConnections;
                            // Si pas de streams via API mais des connexions netstat, considérer comme actif
                            if ($activeClients == 0 && $netstatConnections > 0) {
                                $activeClients = $netstatConnections;
                            }
                        }
                    }
                }
                
                $hasConnections = $activeClients > 0;
                $debugInfo['active_clients_count'] = $activeClients;
                
                echo json_encode([
                    'has_connections' => $hasConnections,
                    'active_clients' => $activeClients,
                    'debug_info' => $debugInfo
                ]);
                break;
                
            case 'check_hls_status':
                // Vérifier le statut HLS d'un stream
                $streamKey = $_GET['stream_key'] ?? '';
                
                if (!$streamKey) {
                    echo json_encode(['success' => false, 'error' => 'stream_key manquant']);
                    break;
                }
                
                $hlsUrl = "http://localhost:8888/live/{$streamKey}/index.m3u8";
                $hlsWorking = false;
                $debugInfo = [];
                $attempts = 0;
                
                // Méthode 1: Test avec cURL (vérification du manifest)
                if (function_exists('curl_init')) {
                    for ($i = 0; $i < 3; $i++) {
                        $attempts++;
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $hlsUrl);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
                        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
                        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) MediaPlayer/1.0');
                        
                        $result = curl_exec($ch);
                        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                        $error = curl_error($ch);
                        curl_close($ch);
                        
                        $debugInfo['curl_attempts'][] = [
                            'attempt' => $i + 1,
                            'http_code' => $httpCode,
                            'error' => $error,
                            'content_length' => $result ? strlen($result) : 0,
                            'has_m3u8_content' => $result && (strpos($result, '#EXTM3U') !== false || strpos($result, '#EXT-X-VERSION') !== false)
                        ];
                        
                        if ($httpCode == 200 && $result && strpos($result, '#EXTM3U') !== false) {
                            $hlsWorking = true;
                            break;
                        }
                        
                        if ($i < 2) sleep(1); // Attendre 1 seconde entre tentatives
                    }
                }
                
                // Méthode 2: Vérifier via MediaMTX API
                if (!$hlsWorking) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, 'http://localhost:9997/v3/paths/list');
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
                    
                    $apiResponse = curl_exec($ch);
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                    
                    $debugInfo['mediamtx_api'] = [
                        'http_code' => $httpCode,
                        'response' => $apiResponse
                    ];
                    
                    if ($httpCode == 200 && !empty($apiResponse)) {
                        $apiData = json_decode($apiResponse, true);
                        if (isset($apiData['items']) && is_array($apiData['items'])) {
                            foreach ($apiData['items'] as $item) {
                                if (isset($item['name']) && $item['name'] === $streamKey && 
                                    isset($item['ready']) && $item['ready'] === true) {
                                    $hlsWorking = true;
                                    $debugInfo['found_in_api'] = $item;
                                    break;
                                }
                            }
                        }
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'hls_working' => $hlsWorking,
                    'stream_key' => $streamKey,
                    'attempts' => $attempts,
                    'debug_info' => $debugInfo
                ]);
                break;
                
            case 'debug_stream_status':
                // Debug complet du statut de stream
                $streamKey = $_GET['stream_key'] ?? '';
                
                if (!$streamKey) {
                    echo json_encode(['success' => false, 'error' => 'stream_key manquant']);
                    break;
                }
                
                $debug = [
                    'stream_key' => $streamKey,
                    'timestamp' => date('Y-m-d H:i:s'),
                ];
                
                // Test RTMP
                $debug['rtmp'] = [];
                if (function_exists('shell_exec')) {
                    $debug['rtmp']['netstat_all'] = trim(shell_exec('netstat -an 2>nul | findstr ":1935" 2>nul') ?? '');
                    $debug['rtmp']['established'] = trim(shell_exec('netstat -an 2>nul | findstr ":1935" | findstr "ESTABLISHED" 2>nul') ?? '');
                }
                
                // Test HLS
                $debug['hls'] = [];
                $hlsUrl = "http://localhost:8888/live/{$streamKey}/index.m3u8";
                
                if (function_exists('curl_init')) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $hlsUrl);
                    curl_setopt($ch, CURLOPT_NOBODY, true);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
                    
                    $result = curl_exec($ch);
                    $debug['hls']['curl_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $debug['hls']['curl_error'] = curl_error($ch);
                    curl_close($ch);
                }
                
                echo json_encode([
                    'success' => true,
                    'debug' => $debug
                ]);
                break;
                
            case 'viewers':
                // Obtenir le nombre de viewers en temps réel
                $channelId = $_GET['channel_id'] ?? '';
                $viewers = Channel::getViewerCount($channelId);
                echo json_encode(['viewers' => $viewers]);
                break;
                
            case 'stream_status':
                // Obtenir le statut d'un stream spécifique
                $channelId = $_GET['channel_id'] ?? '';
                $pdo = Database::get();
                $stmt = $pdo->prepare("SELECT is_live, viewer_count FROM channels WHERE id = ?");
                $stmt->execute([$channelId]);
                $channel = $stmt->fetch();
                
                if ($channel) {
                    // Mettre à jour le nombre de viewers réel
                    Channel::updateViewerCount($channelId);
                    $viewers = Channel::getViewerCount($channelId);
                    echo json_encode([
                        'is_live' => (bool)$channel['is_live'],
                        'viewer_count' => $viewers
                    ]);
                } else {
                    echo json_encode(['is_live' => false, 'viewer_count' => 0]);
                }
                break;
                
            case 'live_streams':
                // Obtenir la liste des streams en direct
                Channel::cleanupInactiveStreams(); // Nettoyer d'abord
                $streams = Channel::getLive();
                echo json_encode($streams);
                break;
                
            case 'user_points':
                // Obtenir les points de l'utilisateur connecté
                if (!isset($_SESSION['user_id'])) {
                    echo json_encode(['points' => 0]);
                } else {
                    $points = UserPoints::getPoints($_SESSION['user_id']);
                    echo json_encode(['points' => $points]);
                }
                break;
                
            case 'recent_donations':
                // Obtenir les donations récentes pour un channel spécifique
                $channelId = $_GET['channel_id'] ?? '';
                $limit = (int)($_GET['limit'] ?? 10);
                
                if ($channelId) {
                    $pdo = Database::get();
                    $stmt = $pdo->prepare("
                        SELECT t.*, u.username as from_username, c.id as target_channel_id
                        FROM transactions t 
                        LEFT JOIN users u ON t.from_user_id = u.id 
                        LEFT JOIN channels c ON t.to_user_id = c.user_id 
                        WHERE c.id = ? AND t.type = 'donation'
                        AND DATE(t.created_at) = CURDATE()
                        ORDER BY t.created_at DESC 
                        LIMIT $limit
                    ");
                    $stmt->execute([$channelId]);
                    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo json_encode($donations);
                } else {
                    echo json_encode([]);
                }
                break;
                
            case 'latest_donation':
                // Obtenir la dernière donation pour un channel spécifique
                $channelId = $_GET['channel_id'] ?? '';
                $afterId = $_GET['after_id'] ?? 0;
                
                if ($channelId) {
                    $pdo = Database::get();
                    
                    // Obtenir les nouvelles donations après l'ID spécifié pour ce channel uniquement
                    $stmt = $pdo->prepare("
                        SELECT t.*, u.username as from_username, c.id as target_channel_id
                        FROM transactions t 
                        LEFT JOIN users u ON t.from_user_id = u.id 
                        LEFT JOIN channels c ON t.to_user_id = c.user_id 
                        WHERE c.id = ? AND t.type = 'donation' AND t.id > ?
                        AND DATE(t.created_at) = CURDATE()
                        ORDER BY t.created_at DESC 
                        LIMIT 1
                    ");
                    $stmt->execute([$channelId, $afterId]);
                    $donation = $stmt->fetch();
                    
                    if ($donation) {
                        echo json_encode([
                            'success' => true, 
                            'donation' => $donation,
                            'timestamp' => time(),
                            'has_new' => true
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false, 
                            'donation' => null,
                            'timestamp' => time(),
                            'has_new' => false
                        ]);
                    }
                } else {
                    echo json_encode(['success' => false, 'donation' => null]);
                }
                break;
                
            case 'get_chat_messages':
                // Récupérer les messages de chat
                $channelId = $_GET['channel_id'] ?? '';
                $since = $_GET['since'] ?? null;
                $limit = (int)($_GET['limit'] ?? 50);
                
                error_log("DEBUG: get_chat_messages called with: channel_id=$channelId, since=$since, limit=$limit [" . date('H:i:s') . "]");
                
                if ($channelId) {
                    try {
                        $pdo = Database::get();
                        
                        // Debug: vérifier le channel et son propriétaire
                        $stmt = $pdo->prepare("SELECT c.id, c.user_id, u.username FROM channels c JOIN users u ON c.user_id = u.id WHERE c.id = ?");
                        $stmt->execute([$channelId]);
                        $channelInfo = $stmt->fetch();
                        
                        if ($channelInfo) {
                            error_log("DEBUG: Channel $channelId belongs to user {$channelInfo['user_id']} ({$channelInfo['username']})");
                        } else {
                            error_log("DEBUG: Channel $channelId NOT FOUND in database");
                        }
                        
                        // Vérifier si la table chat_messages existe
                        $checkTable = $pdo->prepare("SHOW TABLES LIKE 'chat_messages'");
                        $checkTable->execute();
                        $tableExists = $checkTable->rowCount() > 0;
                        
                        if (!$tableExists) {
                            // Table n'existe pas, retourner un tableau vide
                            error_log("DEBUG: Table chat_messages n'existe pas");
                            echo json_encode(['success' => true, 'messages' => [], 'debug' => 'table_not_exists']);
                            break;
                        }
                        
                        // FORCER getRecentMessages pour le chargement initial
                        if ($since && $since !== 'null' && $since !== '' && $since !== 'undefined' && trim($since) !== '') {
                            $messages = ChatMessage::getNewMessages($channelId, $since);
                            error_log("DEBUG: getNewMessages called with since=$since");
                        } else {
                            $messages = ChatMessage::getRecentMessages($channelId, $limit);
                            error_log("DEBUG: getRecentMessages called with limit=$limit (since was empty: '$since')");
                        }
                        
                        error_log("DEBUG: Raw query result: " . json_encode($messages));
                        
                        // Debug: compter les messages
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM chat_messages WHERE channel_id = ?");
                        $stmt->execute([$channelId]);
                        $totalCount = $stmt->fetchColumn();
                        
                        error_log("DEBUG: Channel $channelId - Total messages in DB: $totalCount, Returned: " . count($messages));
                        
                        echo json_encode([
                            'success' => true, 
                            'messages' => $messages,
                            'debug' => ['channel_id' => $channelId, 'total_in_db' => $totalCount, 'returned' => count($messages)]
                        ]);
                    } catch(Exception $e) {
                        // En cas d'erreur, retourner un tableau vide plutôt qu'une erreur 500
                        error_log("DEBUG: Erreur get_chat_messages: " . $e->getMessage());
                        echo json_encode(['success' => true, 'messages' => [], 'debug' => 'error', 'error' => $e->getMessage()]);
                    }
                } else {
                    echo json_encode(['success' => false, 'messages' => []]);
                }
                break;
                
            case 'get_donations':
                // Récupérer les donations pour le feed unifié
                $channelId = $_GET['channel_id'] ?? '';
                $since = $_GET['since'] ?? null;
                $limit = (int)($_GET['limit'] ?? 20);
                
                error_log("DEBUG: get_donations called with: channel_id=$channelId, since=$since, limit=$limit");
                
                if ($channelId) {
                    try {
                        $pdo = Database::get();
                        
                        // Debug: compter toutes les donations pour ce canal
                        // IMPORTANT: to_user_id devrait être l'ID du user propriétaire, pas l'ID du channel
                        $stmt = $pdo->prepare("SELECT c.user_id FROM channels WHERE id = ?");
                        $stmt->execute([$channelId]);
                        $channelData = $stmt->fetch();
                        
                        if (!$channelData) {
                            echo json_encode(['success' => false, 'error' => 'Channel non trouvé', 'donations' => []]);
                            break;
                        }
                        
                        $targetUserId = $channelData['user_id'];
                        
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE to_user_id = ? AND type = 'donation'");
                        $stmt->execute([$targetUserId]);
                        $totalCount = $stmt->fetchColumn();
                        
                        if ($since && $since !== 'null' && $since !== '' && $since !== 'undefined' && trim($since) !== '') {
                            // Nouvelles donations depuis un timestamp
                            $query = "
                                SELECT t.id, t.from_user_id as user_id, t.amount, t.message, t.created_at, 
                                       u.username
                                FROM transactions t
                                JOIN users u ON t.from_user_id = u.id 
                                WHERE t.to_user_id = ? AND t.type = 'donation' AND t.created_at > ?
                                ORDER BY t.created_at DESC 
                                LIMIT " . (int)$limit;
                            $stmt = $pdo->prepare($query);
                            $stmt->execute([$targetUserId, $since]);
                        } else {
                            // Toutes les donations récentes (pas seulement aujourd'hui)
                            $query = "
                                SELECT t.id, t.from_user_id as user_id, t.amount, t.message, t.created_at, 
                                       u.username
                                FROM transactions t
                                JOIN users u ON t.from_user_id = u.id 
                                WHERE t.to_user_id = ? AND t.type = 'donation'
                                ORDER BY t.created_at DESC 
                                LIMIT " . (int)$limit;
                            $stmt = $pdo->prepare($query);
                            $stmt->execute([$targetUserId]);
                        }
                        
                        $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        error_log("DEBUG: Channel $channelId - Total donations in DB: $totalCount, Today: " . count($donations));
                        
                        echo json_encode([
                            'success' => true, 
                            'donations' => $donations,
                            'debug' => ['channel_id' => $channelId, 'total_in_db' => $totalCount, 'today' => count($donations)]
                        ]);
                        
                    } catch(Exception $e) {
                        // En cas d'erreur SQL, retourner un tableau vide plutôt qu'une erreur 500
                        error_log("DEBUG: Erreur get_donations: " . $e->getMessage());
                        echo json_encode(['success' => true, 'donations' => [], 'debug' => 'error', 'error' => $e->getMessage()]);
                    }
                } else {
                    echo json_encode(['success' => false, 'donations' => []]);
                }
                break;
                
            case 'get_stream_preview':
                // Récupérer la preview d'un stream
                $channelId = $_GET['channel_id'] ?? '';
                
                if ($channelId) {
                    $preview = StreamPreview::getRandomPreview($channelId);
                    if ($preview) {
                        echo json_encode(['success' => true, 'preview_path' => $preview]);
                    } else {
                        // Preview par défaut
                        echo json_encode(['success' => true, 'preview_path' => 'assets/img/default-stream.svg']);
                    }
                } else {
                    echo json_encode(['success' => false, 'preview_path' => null]);
                }
                break;
                
            case 'get_session_activity':
                // Récupérer l'activité de la session de stream active
                $channelId = $_GET['channel_id'] ?? '';
                $limit = (int)($_GET['limit'] ?? 50);
                $since = $_GET['since'] ?? null;
                $forceFresh = $_GET['force_fresh'] ?? null; // NOUVEAU: Force une session fraîche
                
                if ($channelId) {
                    try {
                        require_once 'Models/StreamSession.php';
                        
                        // Si force_fresh est demandé, s'assurer qu'on a une session récente
                        if ($forceFresh) {
                            error_log("🆕 Force fresh session demandée pour channel $channelId");
                            
                            // Vérifier s'il y a une session active récente (moins de 30 minutes)
                            $session = StreamSession::getActiveSession($channelId);
                            
                            if (!$session) {
                                error_log("❌ Aucune session active trouvée, création forcée...");
                                $newSessionId = StreamSession::startStream($channelId, "Force Fresh Session");
                                if ($newSessionId) {
                                    error_log("✅ Session fraîche créée: ID $newSessionId");
                                }
                            } else {
                                // Vérifier si la session est trop ancienne
                                $sessionStart = new DateTime($session['stream_start']);
                                $now = new DateTime();
                                $diffMinutes = ($now->getTimestamp() - $sessionStart->getTimestamp()) / 60;
                                
                                if ($diffMinutes > 30) {
                                    error_log("⏰ Session trop ancienne ({$diffMinutes} min), création nouvelle session...");
                                    $newSessionId = StreamSession::startStream($channelId, "Fresh Session - Replace Old");
                                    if ($newSessionId) {
                                        error_log("✅ Session fraîche de remplacement créée: ID $newSessionId");
                                    }
                                } else {
                                    error_log("✅ Session récente trouvée: ID {$session['id']}, âge: {$diffMinutes} min");
                                }
                            }
                        }
                        
                        if ($since) {
                            // Polling mode : récupérer seulement les nouvelles activités depuis $since
                            $activity = StreamSession::getNewSessionActivity($channelId, $since, $limit);
                        } else {
                            // Mode initial : récupérer toute l'activité de la session
                            $activity = StreamSession::getSessionActivity($channelId, $limit);
                        }
                        
                        echo json_encode([
                            'success' => true,
                            'messages' => $activity['messages'],
                            'donations' => $activity['donations'],
                            'session' => $activity['session'],
                            'debug' => [
                                'session_active' => !empty($activity['session']),
                                'session_start' => $activity['session']['stream_start'] ?? null,
                                'message_count' => count($activity['messages']),
                                'donation_count' => count($activity['donations']),
                                'since_param' => $since
                            ]
                        ]);
                        
                    } catch(Exception $e) {
                        error_log("Erreur get_session_activity: " . $e->getMessage());
                        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                    }
                } else {
                    echo json_encode(['success' => false, 'error' => 'channel_id manquant']);
                }
                break;
                
            case 'create_stream_session':
                // Créer une session de stream
                $channelId = $_GET['channel_id'] ?? $_POST['channel_id'] ?? '';
                
                if ($channelId) {
                    try {
                        require_once 'Models/StreamSession.php';
                        
                        $sessionId = StreamSession::startStream($channelId, "Auto Session");
                        
                        if ($sessionId) {
                            echo json_encode([
                                'success' => true,
                                'message' => 'Session créée avec succès',
                                'session_id' => $sessionId,
                                'channel_id' => $channelId,
                                'timestamp' => date('Y-m-d H:i:s')
                            ]);
                        } else {
                            echo json_encode([
                                'success' => false,
                                'message' => 'Erreur lors de la création de session'
                            ]);
                        }
                        
                    } catch(Exception $e) {
                        error_log("Erreur create_stream_session: " . $e->getMessage());
                        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                    }
                } else {
                    echo json_encode(['success' => false, 'error' => 'channel_id manquant']);
                }
                break;
                
            case 'end_stream_session':
                // Fermer la session de stream active
                $channelId = $_GET['channel_id'] ?? $_POST['channel_id'] ?? '';
                
                if ($channelId) {
                    try {
                        require_once 'Models/StreamSession.php';
                        
                        $result = StreamSession::endActiveStream($channelId);
                        
                        if ($result) {
                            echo json_encode([
                                'success' => true,
                                'message' => 'Session fermée avec succès',
                                'channel_id' => $channelId,
                                'timestamp' => date('Y-m-d H:i:s')
                            ]);
                        } else {
                            echo json_encode([
                                'success' => false,
                                'message' => 'Aucune session active trouvée',
                                'channel_id' => $channelId
                            ]);
                        }
                        
                    } catch(Exception $e) {
                        error_log("Erreur end_stream_session: " . $e->getMessage());
                        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                    }
                } else {
                    echo json_encode(['success' => false, 'error' => 'channel_id manquant']);
                }
                break;

            default:
                Channel::cleanupInactiveStreams();
                $streams = Channel::getLive();
                echo json_encode($streams);
        }
        break;
    
    case 'upload_thumbnail':
        error_log("📸 [THUMBNAIL] Début upload_thumbnail");
        error_log("📸 [THUMBNAIL] Method: " . $_SERVER['REQUEST_METHOD']);
        error_log("📸 [THUMBNAIL] POST data: " . print_r($_POST, true));
        error_log("📸 [THUMBNAIL] FILES data: " . print_r($_FILES, true));
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("❌ [THUMBNAIL] Method not allowed: " . $_SERVER['REQUEST_METHOD']);
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
        }
        
        // Vérifier les paramètres requis
        if (!isset($_POST['channel_id'])) {
            error_log("❌ [THUMBNAIL] channel_id manquant");
            http_response_code(400);
            echo json_encode(['error' => 'Missing channel_id parameter']);
            break;
        }
        
        if (!isset($_FILES['thumbnail'])) {
            error_log("❌ [THUMBNAIL] Fichier thumbnail manquant");
            http_response_code(400);
            echo json_encode(['error' => 'Missing thumbnail file']);
            break;
        }
        
        $channelId = (int)$_POST['channel_id'];
        $uploadedFile = $_FILES['thumbnail'];
        
        error_log("📸 [THUMBNAIL] Channel ID: $channelId");
        error_log("📸 [THUMBNAIL] File info: " . print_r($uploadedFile, true));
        
        // Vérifications de sécurité
        if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['error' => 'File upload error: ' . $uploadedFile['error']]);
            break;
        }
        
        // Vérifier le type de fichier (JPEG uniquement pour les thumbnails)
        $allowedTypes = ['image/jpeg', 'image/jpg'];
        $fileType = $uploadedFile['type'];
        
        if (!in_array($fileType, $allowedTypes)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid file type. Only JPEG allowed.']);
            break;
        }
        
        // Vérifier la taille (max 2MB)
        if ($uploadedFile['size'] > 2 * 1024 * 1024) {
            http_response_code(400);
            echo json_encode(['error' => 'File too large. Max 2MB allowed.']);
            break;
        }
        
        // Créer le dossier de destination
        $uploadDir = __DIR__ . '/uploads/thumbnails/';
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("❌ Impossible de créer le dossier: $uploadDir");
                http_response_code(500);
                echo json_encode(['error' => 'Failed to create upload directory']);
                break;
            }
        }
        
        // Générer un nom de fichier sécurisé
        $filename = 'stream_' . $channelId . '_' . time() . '.jpg';
        $filePath = $uploadDir . $filename;
        $relativePath = 'uploads/thumbnails/' . $filename;
        
        // Déplacer le fichier uploadé
        if (move_uploaded_file($uploadedFile['tmp_name'], $filePath)) {
            try {
                $pdo = Database::get();
                
                // Désactiver les anciens thumbnails pour ce channel
                $updateStmt = $pdo->prepare("UPDATE stream_previews SET is_active = 0 WHERE channel_id = ?");
                $updateStmt->execute([$channelId]);
                
                // Insérer le nouveau thumbnail
                $insertStmt = $pdo->prepare("
                    INSERT INTO stream_previews (channel_id, preview_path, file_size, is_active)
                    VALUES (?, ?, ?, 1)
                ");
                $insertStmt->execute([$channelId, $relativePath, $uploadedFile['size']]);
                
                error_log("📸 Thumbnail uploadé pour channel $channelId: $relativePath");
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Thumbnail uploaded successfully',
                    'thumbnail_url' => '/STRIMR/STRIMR-Web/' . $relativePath,
                    'channel_id' => $channelId,
                    'file_size' => $uploadedFile['size']
                ]);
                
            } catch (Exception $e) {
                error_log("❌ Erreur database thumbnail: " . $e->getMessage());
                
                // Supprimer le fichier en cas d'erreur database
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                http_response_code(500);
                echo json_encode(['error' => 'Database error']);
            }
        } else {
            error_log("❌ Échec déplacement fichier thumbnail: $filePath");
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save file']);
        }
        break;
    
    case 'get_thumbnail':
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            break;
        }
        
        if (!isset($_GET['channel_id'])) {
            http_response_code(400);
            echo json_encode(['error' => 'channel_id required']);
            break;
        }
        
        $channelId = (int)$_GET['channel_id'];
        
        try {
            $pdo = Database::get();
            $stmt = $pdo->prepare("
                SELECT preview_path, created_at, file_size 
                FROM stream_previews 
                WHERE channel_id = ? AND is_active = 1 
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$channelId]);
            $thumbnail = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($thumbnail) {
                echo json_encode([
                    'success' => true,
                    'thumbnail_url' => '/STRIMR/STRIMR-Web/' . $thumbnail['preview_path'],
                    'created_at' => $thumbnail['created_at'],
                    'file_size' => $thumbnail['file_size']
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'No thumbnail found'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("❌ Erreur get thumbnail: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Database error']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}
?>
