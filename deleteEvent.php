<?php
header('Content-Type: application/json');
include_once "../config.php";
include_once "../controllers/eventsController.php";

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Method not allowed');

    // Lire le JSON envoyé depuis fetch
    $input = json_decode(file_get_contents('php://input'), true);
    $id_event = isset($input['id_event']) ? intval($input['id_event']) : 0;

    if ($id_event <= 0) throw new Exception('Invalid Event ID');

    $eventC = new EventsC();

    if ($eventC->supprimerEvent($id_event)) {
        echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Event not found or could not be deleted']);
    }

} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
