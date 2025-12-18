<?php
require_once __DIR__ . "/../Model/config.php";
require_once __DIR__ . "/../Model/Notification.php";

class NotificationController {

    public function add(string $content): bool {
        $db = Config::getConnexion();

        try {
            $stmt = $db->prepare("INSERT INTO notifications (content, created_at) VALUES (:content, NOW())");
            $stmt->execute(['content' => $content]);
            return true;
        } catch (Exception $e) {
            error_log("Error in addNotification: " . $e->getMessage());
            return false;
        }
    }

    public function getAll(): array {
        $db = Config::getConnexion();

        try {
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
        } catch (Exception $e) {
            error_log("Error in getAllNotifications: " . $e->getMessage());
            return [];
        }
    }

    public function delete(int $id): bool {
        $db = Config::getConnexion();

        try {
            $stmt = $db->prepare("DELETE FROM notifications WHERE id = :id");
            $stmt->execute(['id' => $id]);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Error in deleteNotification: " . $e->getMessage());
            return false;
        }
    }
}
?>

