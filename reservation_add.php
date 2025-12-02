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
    // Vérifier la méthode HTTP
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }
    
    // Validation des données
    $errors = [];
    
    // Vérifier event_id
    $event_id = filter_input(INPUT_POST, 'event_id', FILTER_VALIDATE_INT);
    if (!$event_id || $event_id < 1) {
        $errors['event_id'] = 'ID événement invalide';
    }
    
    // ID utilisateur statique = 1
    $user_id = 1;
    
    // Vérifier reservation_date
    $reservation_date = $_POST['reservation_date'] ?? '';
    if (empty($reservation_date)) {
        $errors['reservation_date'] = 'Date de réservation requise';
    } else {
        // Valider le format de date
        $date_obj = DateTime::createFromFormat('Y-m-d\TH:i', $reservation_date);
        if (!$date_obj) {
            $errors['reservation_date'] = 'Format de date invalide';
        } elseif ($date_obj < new DateTime()) {
            $errors['reservation_date'] = 'La date ne peut pas être dans le passé';
        }
    }
    
    // Statut par défaut = 'pending'
    $status = 'pending';
    
    // Si erreurs de validation
    if (!empty($errors)) {
        $response['errors'] = $errors;
        $response['message'] = 'Erreurs de validation';
        echo json_encode($response);
        exit;
    }
    
    // Vérifier si l'événement existe et est disponible
    // (À implémenter selon votre logique métier)
    // Exemple: vérifier dans la base de données si l'événement existe
    
    // Vérifier si l'utilisateur a déjà réservé cet événement
    // (À implémenter selon votre logique métier)
    // Exemple: vérifier dans la table reservations
    
    // Créer l'objet réservation
    $reservationC = new ReservationC();
    $reservation = new Reservation(
        null, // id (auto-incrément)
        $event_id,
        $user_id, // Toujours 1
        $reservation_date,
        $status // Toujours 'pending'
    );
    
    // Ajouter la réservation
    $result = $reservationC->ajouterReservation($reservation);
    
    if ($result) {
        $response['success'] = true;
        $response['message'] = 'Réservation effectuée avec succès';
        $response['reservation_id'] = $result;
        
        // Pour compatibilité avec le code JavaScript existant
        echo "success";
        exit;
    } else {
        throw new Exception('Échec de l\'ajout de la réservation');
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