<?php
require_once '../config.php';
require_once '../models/events.php';

class EventsC
{
function ajouterEvent($event)
{
    $sql = "INSERT INTO events 
        (user_id, title, description, event_type, start_date, end_date, status, created_at, updated_at)
        VALUES 
        (:user_id, :title, :description, :event_type, :start_date, :end_date, :status, :created_at, :updated_at)";


    $db = config::getConnexion();
    try {
        $query = $db->prepare($sql);
        $success = $query->execute([
            'title' => $event->getTitle(),
            'description' => $event->getDescription(),
            'event_type' => $event->getEventType(),
            'start_date' => $event->getStartDate(),
            'end_date' => $event->getEndDate(),
            'status' => $event->getStatus(),
            'created_at' => $event->getCreatedAt(),
            'updated_at' => $event->getUpdatedAt(),
            'user_id' => $event->getUserId(),

        ]);
        
        // Retourner l'ID de l'événement inséré
        return $success ? $db->lastInsertId() : false;
        
    } catch (Exception $e) {
        throw new Exception('Database error: ' . $e->getMessage());
    }
}

    function afficherEvents()
    {
        $sql = "SELECT * FROM events ORDER BY start_date DESC";
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            return $list;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    function supprimerEvent($id_event)
    {
        $sql = "DELETE FROM events WHERE id_event = :id_event";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id_event', $id_event);

        try {
            $req->execute();
            return true;
        } catch (Exception $e) {
            die('Erreur:' . $e->getMessage());
        }
    }

    function recupererEvent($id_event)
    {
        $sql = "SELECT * FROM events WHERE id_event = :id_event";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id_event' => $id_event]);
            $event = $query->fetch(PDO::FETCH_ASSOC);
            return $event;
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
        }
    }

    function modifierEvent($event, $id_event)
    {
        $sql = "UPDATE events SET 
                user_id = :user_id,
                title = :title,
                description = :description,
                event_type = :event_type,
                start_date = :start_date,
                end_date = :end_date,
                status = :status,
                updated_at = :updated_at
                WHERE id_event = :id_event";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $success = $query->execute([
                'user_id' => $event->getUserId(),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'event_type' => $event->getEventType(),
                'start_date' => $event->getStartDate(),
                'end_date' => $event->getEndDate(),
                'status' => $event->getStatus(),
                'updated_at' => $event->getUpdatedAt(),
                'id_event' => $id_event
            ]);
            return $success;
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
        }
    }
}
?>
