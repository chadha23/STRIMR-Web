<?php
function formatDate($dateString) {
    if (empty($dateString) || $dateString == '0000-00-00 00:00:00') {
        return '-';
    }
    try {
        $date = new DateTime($dateString);
        return $date->format('d/m/Y H:i');
    } catch (Exception $e) {
        return $dateString;
    }
}

function getEventTypeLabel($type) {
    $labels = [
        'concert' => '­ƒÄÁ Concert',
        'conference' => '­ƒôÜ Conf├®rence',
        'workshop' => '­ƒöº Atelier',
        'sport' => 'ÔÜ¢ Sport',
        'other' => '­ƒôà Autre'
    ];
    return $labels[$type] ?? ucfirst($type);
}

function getStatusLabel($status) {
    $labels = [
        'active' => 'Actif',
        'cancelled' => 'Annul├®',
        'completed' => 'Termin├®',
        'pending' => 'En attente'
    ];
    return $labels[$status] ?? ucfirst($status);
}

function getReservationStatusLabel($status) {
    $labels = [
        'pending' => 'ÔÅ│ En attente',
        'confirmed' => 'Ô£à Confirm├®e',
        'cancelled' => 'ÔØî Annul├®e'
    ];
    return $labels[$status] ?? ucfirst($status);
}
?>
