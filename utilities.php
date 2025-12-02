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
        'concert' => '🎵 Concert',
        'conference' => '📚 Conférence',
        'workshop' => '🔧 Atelier',
        'sport' => '⚽ Sport',
        'other' => '📅 Autre'
    ];
    return $labels[$type] ?? ucfirst($type);
}

function getStatusLabel($status) {
    $labels = [
        'active' => 'Actif',
        'cancelled' => 'Annulé',
        'completed' => 'Terminé',
        'pending' => 'En attente'
    ];
    return $labels[$status] ?? ucfirst($status);
}

function getReservationStatusLabel($status) {
    $labels = [
        'pending' => '⏳ En attente',
        'confirmed' => '✅ Confirmée',
        'cancelled' => '❌ Annulée'
    ];
    return $labels[$status] ?? ucfirst($status);
}
?>