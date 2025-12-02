<?php
// addEvent.php - Version debug
header('Content-Type: application/json');

// Activer l'affichage des erreurs pour le debug
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

try {
    // Inclure les fichiers nécessaires
    include_once "../config.php";
    include_once "../models/events.php";
    include_once "../controllers/eventsController.php";    
    // Vérifier la méthode HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed');
    }
    
    // Récupérer les données JSON
    $jsonInput = file_get_contents('php://input');
    $input = json_decode($jsonInput, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }
    
    // Validation des données requises
    if (empty($input['title'])) {
        throw new Exception('Title is required');
    }
    if (empty($input['start_date'])) {
        throw new Exception('Start date is required');
    }
    
    // Créer l'événement
    $event = new Events();
    $event->setTitle($input['title']);
    $event->setDescription($input['description'] ?? '');
    $event->setEventType($input['event_type'] ?? 'general');
    $event->setStartDate($input['start_date']);
    $event->setEndDate($input['end_date'] ?? $input['start_date']);
    $event->setStatus($input['status'] ?? 'upcoming');
    $event->setUpdatedAt(date('Y-m-d H:i:s'));
    $event->setCreatedAt(date('Y-m-d H:i:s'));

    // For local testing: force the user id to 1 so inserts don't fail.
    // NOTE: Remove or change this in production — this bypasses authentication.
    $event->setUserId(1);
    // Appeler la fonction d'ajout
    $eventC = new EventsC();
    $result = $eventC->ajouterEvent($event);
    
    if ($result) {
        echo json_encode([
            'success' => true, 
            'message' => 'Event added successfully',
            'id' => $result
        ]);
    } else {
        throw new Exception('Failed to insert event into database');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'error_type' => 'server_error'
    ]);
}
?>