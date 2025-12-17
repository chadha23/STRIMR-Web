<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/Notification.php';

class NotificationController {

    public function add($content) {
        $db = DatabaseConfig::getConnexion();

        $stmt = $db->prepare("INSERT INTO notifications (content) VALUES (?)");
        $stmt->execute([$content]);
    }

    public function getAll() {
        $db = DatabaseConfig::getConnexion();

        $stmt = $db->query("SELECT * FROM notifications ORDER BY id DESC LIMIT 20");
        $rows = $stmt->fetchAll();

        $notifications = [];
        foreach ($rows as $row) {
            $notifications[] = new Notification(
                $row['id'],
                $row['content'],
                $row['created_at']
            );
        }
        return $notifications;
    }
}

?>
