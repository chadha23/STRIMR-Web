<?php
class Reservation {
    private $id_reservation;
    private $event_id;
    private $user_id;
    private $reservation_date;
    private $status;

    public function __construct(
        $id_reservation = null,
        $event_id = null,
        $user_id = null,
        $reservation_date = null,
        $status = 'pending'
    ) {
        $this->id_reservation = $id_reservation;
        $this->event_id = $event_id;
        $this->user_id = $user_id;
        $this->reservation_date = $reservation_date;
        $this->status = $status;
    }

    // ---------- Getters ----------
    public function getIdReservation() {
        return $this->id_reservation;
    }

    public function getEventId() {
        return $this->event_id;
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function getReservationDate() {
        return $this->reservation_date;
    }

    public function getStatus() {
        return $this->status;
    }

    // ---------- Setters ----------
    public function setEventId($event_id) {
        $this->event_id = $event_id;
    }

    public function setUserId($user_id) {
        $this->user_id = $user_id;
    }

    public function setReservationDate($reservation_date) {
        $this->reservation_date = $reservation_date;
    }

    public function setStatus($status) {
        $this->status = $status;
    }
}
