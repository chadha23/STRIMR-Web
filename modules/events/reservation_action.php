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
        throw new Exception('M├®thode non autoris├®e');
    }
    
    $reservationId = filter_input(INPUT_POST, 'reservation_id', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? '';
    
    if (!$reservationId) {
        throw new Exception('ID de r├®servation invalide');
    }
    
    $reservationC = new ReservationC();
    
    switch ($action) {
        case 'accept':
            // Mettre ├á jour le statut ├á "confirmed"
            $result = $reservationC->updateReservationStatus($reservationId, 'confirmed');
            $response['message'] = 'R├®servation accept├®e avec succ├¿s';
            break;
            
        case 'reject':
            // Mettre ├á jour le statut ├á "cancelled"
            $result = $reservationC->updateReservationStatus($reservationId, 'cancelled');
            $response['message'] = 'R├®servation refus├®e avec succ├¿s';
            break;
            
        case 'cancel':
            // Mettre ├á jour le statut ├á "cancelled"
            $result = $reservationC->updateReservationStatus($reservationId, 'cancelled');
            $response['message'] = 'R├®servation annul├®e avec succ├¿s';
            break;
            
        case 'delete':
            // Supprimer la r├®servation
            $result = $reservationC->supprimerReservation($reservationId);
            $response['message'] = 'R├®servation supprim├®e avec succ├¿s';
            break;
            
        default:
            throw new Exception('Action non reconnue');
    }
    
    if ($result) {
        $response['success'] = true;
    } else {
        throw new Exception('├ëchec de l\'op├®ration');
    }
    
} catch (Exception $e) {
    $response['message'] = 'Erreur : ' . $e->getMessage();
    http_response_code(500);
}

echo json_encode($response);
exit;
?>
