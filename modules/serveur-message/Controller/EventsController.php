<?php
require_once __DIR__ . '/../Model/config.php';
require_once __DIR__ . '/../Model/Events.php';

class EventsController
{
    /**
     * Add a new event
     */
    public function addEvent($event)
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
            
            if ($success) {
                $insertId = $db->lastInsertId();
                error_log("Event created successfully with ID: " . $insertId);
                return $insertId;
            } else {
                error_log("Failed to create event - query execution returned false");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("EventsController::addEvent Error: " . $e->getMessage());
            throw new Exception('Database error: ' . $e->getMessage());
        }
    }

    /**
     * Get all events ordered by start date
     */
    public function listEvents()
    {
        $sql = "SELECT * FROM events ORDER BY start_date DESC";
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            $results = $list->fetchAll();
            return $results ? $results : [];
        } catch (Exception $e) {
            error_log("EventsController::listEvents Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete an event by ID
     */
    public function deleteEvent($id_event)
    {
        $sql = "DELETE FROM events WHERE id_event = :id_event";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id_event', $id_event);

        try {
            $req->execute();
            return true;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    /**
     * Get a single event by ID
     */
    public function getEvent($id_event)
    {
        $sql = "SELECT * FROM events WHERE id_event = :id_event";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id_event' => $id_event]);
            $event = $query->fetch(PDO::FETCH_ASSOC);
            return $event;
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Update an existing event
     */
    public function updateEvent($event, $id_event)
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
            echo 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Get paginated events
     */
    public function listEventsPaginated($limit, $offset)
    {
        $sql = "SELECT * FROM events ORDER BY start_date DESC LIMIT :limit OFFSET :offset";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $query->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $query->execute();
        return $query->fetchAll();
    }

    /**
     * Count total events
     */
    public function countEvents()
    {
        $sql = "SELECT COUNT(*) AS total FROM events";
        $db = config::getConnexion();

        try {
            $query = $db->query($sql);
            return $query->fetch(PDO::FETCH_ASSOC)['total'];
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    /**
     * Get events by status
     */
    public function getEventsByStatus($status)
    {
        $sql = "SELECT * FROM events WHERE status = :status ORDER BY start_date DESC";
        $db = config::getConnexion();
        
        try {
            $query = $db->prepare($sql);
            $query->execute(['status' => $status]);
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    /**
     * Get upcoming events
     */
    public function getUpcomingEvents()
    {
        $sql = "SELECT * FROM events WHERE start_date >= NOW() ORDER BY start_date ASC";
        $db = config::getConnexion();
        
        try {
            return $db->query($sql)->fetchAll();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>

