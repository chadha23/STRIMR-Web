<?php
require_once __DIR__ . '/../Model/config.php';
require_once __DIR__ . '/../Model/Reservation.php';

class ReservationController
{
    /**
     * Add a new reservation
     */
    public function addReservation($reservation)
    {
        $sql = "INSERT INTO reservations 
                (event_id, user_id, reservation_date, status)
                VALUES 
                (:event_id, :user_id, :reservation_date, :status)";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $success = $query->execute([
                'event_id' => $reservation->getEventId(),
                'user_id' => $reservation->getUserId(),
                'reservation_date' => $reservation->getReservationDate(),
                'status' => $reservation->getStatus()
            ]);

            if ($success) {
                $insertId = $db->lastInsertId();
                error_log("Reservation created successfully with ID: " . $insertId);
                return $insertId;
            } else {
                error_log("Failed to create reservation - query execution returned false");
                return false;
            }
        } catch (Exception $e) {
            error_log("ReservationController::addReservation Error: " . $e->getMessage());
            throw new Exception('Database error: ' . $e->getMessage());
        }
    }

    /**
     * Get all reservations
     */
    public function listReservations()
    {
        $sql = "SELECT * FROM reservations ORDER BY reservation_date DESC";
        $db = config::getConnexion();
        
        try {
            $list = $db->query($sql);
            return $list->fetchAll();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    /**
     * Get reservations with event details
     */
    public function listReservationsWithDetails()
    {
        $sql = "SELECT 
                    r.id_reservation,
                    r.event_id,
                    r.user_id,
                    r.reservation_date,
                    r.status,
                    e.title as event_title,
                    e.start_date as event_start_date,
                    e.event_type
                FROM reservations r
                LEFT JOIN events e ON r.event_id = e.id_event
                ORDER BY r.reservation_date DESC";
                
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            return $list->fetchAll();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    /**
     * Get a single reservation by ID
     */
    public function getReservation($id_reservation)
    {
        $sql = "SELECT * FROM reservations WHERE id_reservation = :id_reservation";
        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);
            $query->execute(['id_reservation' => $id_reservation]);
            return $query->fetch(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Update a reservation
     */
    public function updateReservation($reservation, $id_reservation)
    {
        $sql = "UPDATE reservations SET
                    event_id = :event_id,
                    user_id = :user_id,
                    reservation_date = :reservation_date,
                    status = :status
                WHERE id_reservation = :id_reservation";

        $db = config::getConnexion();

        try {
            $query = $db->prepare($sql);

            $success = $query->execute([
                'event_id' => $reservation->getEventId(),
                'user_id' => $reservation->getUserId(),
                'reservation_date' => $reservation->getReservationDate(),
                'status' => $reservation->getStatus(),
                'id_reservation' => $id_reservation
            ]);

            return $success;

        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    /**
     * Update reservation status only
     */
    public function updateReservationStatus($id, $status) 
    {
        $sql = "UPDATE reservations SET status = :status WHERE id_reservation = :id";
        $db = config::getConnexion();
        
        try {
            $query = $db->prepare($sql);
            return $query->execute([
                'id' => $id,
                'status' => $status
            ]);
        } catch (Exception $e) {
            throw new Exception('Database error: ' . $e->getMessage());
        }
    }
    
    /**
     * Delete a reservation
     */
    public function deleteReservation($id) 
    {
        $sql = "DELETE FROM reservations WHERE id_reservation = :id";
        $db = config::getConnexion();
        
        try {
            $query = $db->prepare($sql);
            return $query->execute(['id' => $id]);
        } catch (Exception $e) {
            throw new Exception('Database error: ' . $e->getMessage());
        }
    }

    /**
     * Count total reservations
     */
    public function countReservations()
    {
        $sql = "SELECT COUNT(*) AS total FROM reservations";
        $db = config::getConnexion();
        $query = $db->query($sql);
        return $query->fetch(PDO::FETCH_ASSOC)['total'];
    }

    /**
     * Get paginated reservations with details
     */
    public function listReservationsWithDetailsPaginated($limit, $offset)
    {
        $sql = "SELECT 
                    r.id_reservation,
                    r.event_id,
                    r.user_id,
                    r.reservation_date,
                    r.status,
                    e.title AS event_title,
                    e.start_date AS event_start_date,
                    e.event_type
                FROM reservations r
                LEFT JOIN events e ON r.event_id = e.id_event
                ORDER BY r.reservation_date DESC
                LIMIT :limit OFFSET :offset";

        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $query->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $query->execute();

        return $query->fetchAll();
    }

    /**
     * Get reservations by event ID
     */
    public function getReservationsByEvent($event_id)
    {
        $sql = "SELECT * FROM reservations WHERE event_id = :event_id ORDER BY reservation_date DESC";
        $db = config::getConnexion();
        
        try {
            $query = $db->prepare($sql);
            $query->execute(['event_id' => $event_id]);
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }

    /**
     * Get reservations by user ID
     */
    public function getReservationsByUser($user_id)
    {
        $sql = "SELECT r.*, e.title as event_title, e.start_date as event_start_date 
                FROM reservations r
                LEFT JOIN events e ON r.event_id = e.id_event
                WHERE r.user_id = :user_id 
                ORDER BY r.reservation_date DESC";
        $db = config::getConnexion();
        
        try {
            $query = $db->prepare($sql);
            $query->execute(['user_id' => $user_id]);
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Error: ' . $e->getMessage());
        }
    }
}
?>

