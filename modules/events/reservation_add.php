<?php
header('Content-Type: application/json');

require_once '../controllers/reservationController.php';
require_once '../models/reservation.php';

$response = [
    'success' => false,
    'message' => '',
    'errors' => []
];

try {
    // V├®rifier la m├®thode HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('M├®thode non autoris├®e');
    }
    
    // Validation des donn├®es
    $errors = [];
    
    // V├®rifier event_id
    $event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
    if (!$event_id || $event_id < 1) {
        $errors['event_id'] = 'ID ├®v├®nement invalide';
    }
    
    // ID utilisateur statique = 1
    $user_id = 1;
    
    // V├®rifier reservation_date
    $reservation_date = $_POST['reservation_date'] ?? '';
    if (empty($reservation_date)) {
        $errors['reservation_date'] = 'Date de r├®servation requise';
    } else {
        // Valider le format de date
        $date_obj = DateTime::createFromFormat('Y-m-d\TH:i', $reservation_date);
        if (!$date_obj) {
            $errors['reservation_date'] = 'Format de date invalide';
        } elseif ($date_obj < new DateTime()) {
            $errors['reservation_date'] = 'La date ne peut pas ├¬tre dans le pass├®';
        }
    }
    
    // Statut par d├®faut = 'pending'
    $status = 'pending';
    
    // Si erreurs de validation
    if (!empty($errors)) {
        $response['errors'] = $errors;
        $response['message'] = 'Erreurs de validation';
        echo json_encode($response);
        exit;
    }
    
    // V├®rifier si l'├®v├®nement existe et est disponible
    // (├Ç impl├®menter selon votre logique m├®tier)
    // Exemple: v├®rifier dans la base de donn├®es si l'├®v├®nement existe
    
    // V├®rifier si l'utilisateur a d├®j├á r├®serv├® cet ├®v├®nement
    // (├Ç impl├®menter selon votre logique m├®tier)
    // Exemple: v├®rifier dans la table reservations
    
    // Cr├®er l'objet r├®servation
    $reservationC = new ReservationC();
    $reservation = new Reservation(
        null, // id (auto-incr├®ment)
        $event_id,
        $user_id, // Toujours 1
        $reservation_date,
        $status // Toujours 'pending'
    );
    
    // Ajouter la r├®servation
    $result = $reservationC->ajouterReservation($reservation);
    
    if ($result) {
        $response['success'] = true;
        $response['message'] = 'R├®servation effectu├®e avec succ├¿s';
        $response['reservation_id'] = $result;
        
        // Pour compatibilit├® avec le code JavaScript existant
        echo "success";
        exit;
    } else {
        throw new Exception('├ëchec de l\'ajout de la r├®servation');
    }
    
} catch (Exception $e) {
    $response['message'] = 'Erreur : ' . $e->getMessage();
    $response['success'] = false;
    
    // Retourner l'erreur en JSON pour le debug
    echo json_encode($response);
    http_response_code(500);
    exit;
}
?>
