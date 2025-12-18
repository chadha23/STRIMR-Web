<?php
class StreamPreview {
    
    // Capturer un screenshot du stream
    public static function capturePreview($channelId, $streamKey) {
        // Créer le dossier previews si nécessaire
        $previewDir = __DIR__ . '/../assets/previews/';
        if (!is_dir($previewDir)) {
            mkdir($previewDir, 0755, true);
        }
        
        $filename = "preview_" . $channelId . "_" . time() . ".jpg";
        $filepath = $previewDir . $filename;
        
        // Utiliser ffmpeg pour capturer une frame du stream HLS
        $hlsUrl = "http://localhost:8888/live/{$streamKey}/index.m3u8";
        
        // Commande ffmpeg optimisée pour rapidité
        $command = "ffmpeg -i \"{$hlsUrl}\" -vf \"scale=320:180\" -vframes 1 -q:v 2 -y \"{$filepath}\" 2>NUL";
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($filepath)) {
            // Enregistrer en base
            $pdo = Database::get();
            $stmt = $pdo->prepare("
                INSERT INTO stream_previews (channel_id, preview_path, file_size) 
                VALUES (?, ?, ?)
            ");
            
            $fileSize = filesize($filepath);
            $stmt->execute([$channelId, "assets/previews/" . $filename, $fileSize]);
            
            // Mettre à jour le channel avec la nouvelle preview si possible
            if (self::columnExists('current_preview')) {
                $stmt = $pdo->prepare("
                    UPDATE channels 
                    SET current_preview = ?, preview_updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->execute(["assets/previews/" . $filename, $channelId]);
            }
            
            return "assets/previews/" . $filename;
        }
        
        return false;
    }
    
    // Obtenir une preview aléatoire pour un channel
    public static function getRandomPreview($channelId) {
        $pdo = Database::get();
        
        // Essayer de récupérer une preview depuis la table stream_previews
        $stmt = $pdo->prepare("
            SELECT preview_path 
            FROM stream_previews 
            WHERE channel_id = ? AND is_active = 1 
            ORDER BY created_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$channelId]);
        $preview = $stmt->fetch();
        
        if ($preview) {
            $fullPath = __DIR__ . '/../' . $preview['preview_path'];
            if (file_exists($fullPath)) {
                return $preview['preview_path'];
            }
        }
        
        // Si aucune preview n'existe, retourner l'image par défaut
        return 'assets/img/default-stream.svg';
    }
    
    // Vérifier si une colonne existe
    private static function columnExists($columnName) {
        try {
            $pdo = Database::get();
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as cnt 
                FROM information_schema.COLUMNS 
                WHERE TABLE_SCHEMA = DATABASE() 
                AND TABLE_NAME = 'channels' 
                AND COLUMN_NAME = ?
            ");
            $stmt->execute([$columnName]);
            $result = $stmt->fetch();
            return ($result && $result['cnt'] > 0);
        } catch (Exception $e) {
            return false;
        }
    }
    
    // Nettoyer les vieilles previews
    public static function cleanupOldPreviews() {
        $pdo = Database::get();
        
        // Récupérer les fichiers à supprimer (> 24h et plus que 20 par channel)
        $stmt = $pdo->query("
            SELECT sp1.id, sp1.preview_path, sp1.channel_id
            FROM stream_previews sp1
            WHERE sp1.created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)
            OR (
                SELECT COUNT(*) 
                FROM stream_previews sp2 
                WHERE sp2.channel_id = sp1.channel_id 
                AND sp2.created_at >= sp1.created_at
            ) > 20
        ");
        
        $toDelete = $stmt->fetchAll();
        
        foreach ($toDelete as $preview) {
            // Supprimer le fichier
            $fullPath = __DIR__ . '/../' . $preview['preview_path'];
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            
            // Supprimer de la base
            $pdo->prepare("DELETE FROM stream_previews WHERE id = ?")
                ->execute([$preview['id']]);
        }
        
        return count($toDelete);
    }
    
    // Vérifier si ffmpeg est disponible
    public static function isFFmpegAvailable() {
        exec('ffmpeg -version 2>NUL', $output, $returnCode);
        return $returnCode === 0;
    }
    
    // Capturer automatiquement pour tous les streams actifs
    public static function captureAllActiveStreams() {
        if (!self::isFFmpegAvailable()) {
            error_log("[STRIMR] FFmpeg non disponible pour les previews");
            return;
        }
        
        $pdo = Database::get();
        $stmt = $pdo->query("
            SELECT id, stream_key 
            FROM channels 
            WHERE is_live = 1
        ");
        
        $activeChannels = $stmt->fetchAll();
        $captured = 0;
        
        foreach ($activeChannels as $channel) {
            if (self::capturePreview($channel['id'], $channel['stream_key'])) {
                $captured++;
                // Petite pause pour éviter la surcharge
                usleep(500000); // 0.5 seconde
            }
        }
        
        error_log("[STRIMR] Capturé {$captured} previews sur " . count($activeChannels) . " streams");
        return $captured;
    }
}
?>