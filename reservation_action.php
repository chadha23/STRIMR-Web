<?php
header('Content-Type: application/json');

require_once '../controllers/reservationController.php';
require_once '../models/reservation.php';

$response = [
    'success' => false,
    'message' => ''
];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }
    
    $reservationId = filter_input(INPUT_POST, 'reservation_id', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';
    
    if (!$reservationId) {
        throw new Exception('ID de réservation invalide');
    }
    
    $reservationC = new ReservationC();
    
    switch ($action) {
        case 'accept':
            // Mettre à jour le statut à "confirmed"
            $result = $reservationC->updateReservationStatus($reservationId, 'confirmed');
            $response['message'] = 'Réservation acceptée avec succès';
            break;
            
        case 'reject':
            // Mettre à jour le statut à "cancelled"
            $result = $reservationC->updateReservationStatus($reservationId, 'cancelled');
            $response['message'] = 'Réservation refusée avec succès';
            break;
            
        case 'cancel':
            // Mettre à jour le statut à "cancelled"
            $result = $reservationC->updateReservationStatus($reservationId, 'cancelled');
            $response['message'] = 'Réservation annulée avec succès';
            break;
            
        case 'delete':
            // Supprimer la réservation
            $result = $reservationC->supprimerReservation($reservationId);
            $response['message'] = 'Réservation supprimée avec succès';
            break;
            
        default:
            throw new Exception('Action non reconnue');
    }
    
    if ($result) {
        $response['success'] = true;
    } else {
        throw new Exception('Échec de l\'opération');
    }
    
} catch (Exception $e) {
    $response['message'] = 'Erreur : ' . $e->getMessage();
    http_response_code(500);
}

echo json_encode($response);
exit;
?>