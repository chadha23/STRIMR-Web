<?php
// updateEvent.php - accepts JSON and updates an existing event
header('Content-Type: application/json');

try {
    include_once "../config.php";
    include_once "../models/events.php";
    include_once "../controllers/eventsController.php";

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }

    if (empty($input['id_event'])) {
        throw new Exception('Event ID is required');
    }

    $id_event = $input['id_event'];

    $event = new Events();
    // Set fields if provided (otherwise keep null)
    if (isset($input['title'])) $event->setTitle($input['title']);
    if (isset($input['description'])) $event->setDescription($input['description']);
    if (isset($input['event_type'])) $event->setEventType($input['event_type']);
    if (isset($input['start_date'])) $event->setStartDate($input['start_date']);
    if (isset($input['end_date'])) $event->setEndDate($input['end_date']);
    if (isset($input['status'])) $event->setStatus($input['status']);

    $event->setUpdatedAt(date('Y-m-d H:i:s'));

    // For local testing: force user_id = 1 to bypass missing session
    // REMOVE or secure this in production
    $event->setUserId(1);

    $eventC = new EventsC();
    $success = $eventC->modifierEvent($event, $id_event);

    if ($success) {
        echo json_encode([ 'success' => true, 'message' => 'Event updated successfully' ]);
    } else {
        throw new Exception('Failed to update event');
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([ 'success' => false, 'message' => $e->getMessage() ]);
}
?>