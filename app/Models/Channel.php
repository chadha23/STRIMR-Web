<?php
class Channel {
    public static function getOrCreate($userId) {
        $pdo = Database::get();
        $stmt = $pdo->prepare("SELECT * FROM channels WHERE user_id = ?");
        $stmt->execute([$userId]);
        $ch = $stmt->fetch();

        if (!$ch) {
            $slug = strtolower($_SESSION['username']);
            $key = bin2hex(random_bytes(20));
            $pdo->prepare("INSERT INTO channels (user_id, slug, stream_key, is_live) VALUES (?, ?, ?, 1)")
                ->execute([$userId, $slug, $key]);
            return ['slug' => $slug, 'stream_key' => $key];
        }
        $pdo->prepare("UPDATE channels SET is_live = 1 WHERE id = ?")->execute([$ch['id']]);
        return $ch;
    }

    public static function getBySlug($slug) {
        $pdo = Database::get();
        $stmt = $pdo->prepare("SELECT c.*, u.username FROM channels c JOIN users u ON c.user_id = u.id WHERE c.slug = ?");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }

    public static function getLive() {
        $pdo = Database::get();
        return $pdo->query("SELECT c.*, u.username FROM channels c JOIN users u ON c.user_id = u.id WHERE c.is_live = 1")->fetchAll();
    }

    public static function incrementViewer($slug) {
        $pdo = Database::get();
        $pdo->prepare("UPDATE channels SET viewer_count = viewer_count + 1 WHERE slug = ?")->execute([$slug]);
    }
}