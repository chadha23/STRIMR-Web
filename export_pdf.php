<?php
include_once "../config.php";
include_once "../models/events.php";
include_once "../controllers/eventsController.php";    
require_once '../controllers/reservationController.php';
require_once '../models/reservation.php';
require_once('../vendor/tecnickcom/tcpdf/tcpdf.php');

class PDFExport
{
    private $eventC;
    private $reservationC;
    
    public function __construct()
    {
        $this->eventC = new EventsC();
        $this->reservationC = new ReservationC();
    }
    
    public function generateEventsPDF($title = 'Rapport des Événements')
    {
        // Créer une nouvelle instance TCPDF
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        
        // Information du document
        $pdf->SetCreator('Votre Application');
        $pdf->SetAuthor('Système de Gestion');
        $pdf->SetTitle($title);
        $pdf->SetSubject('Export PDF des Événements');
        
        // En-tête et pied de page
        $pdf->setHeaderData('', 0, $title, 'Généré le: ' . date('d/m/Y H:i:s'));
        $pdf->setHeaderFont(Array('helvetica', '', 12));
        $pdf->setFooterFont(Array('helvetica', '', 10));
        $pdf->SetDefaultMonospacedFont('courier');
        $pdf->SetMargins(15, 25, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);
        $pdf->SetAutoPageBreak(TRUE, 15);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->AddPage();
        
        // Titre principal
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, $title, 0, 1, 'C');
        $pdf->Ln(5);
        
        // Date de génération
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 6, 'Date de génération: ' . date('d/m/Y à H:i:s'), 0, 1, 'R');
        $pdf->Ln(5);
        
        // Récupérer les données
        $events = $this->eventC->afficherEvents();
        $reservations = $this->reservationC->afficherReservationsAvecDetails();

        // Si les méthodes retournent un PDOStatement, convertir en tableau
        if (is_object($events) && method_exists($events, 'fetchAll')) {
            $events = $events->fetchAll(PDO::FETCH_ASSOC);
        }
        if (is_object($reservations) && method_exists($reservations, 'fetchAll')) {
            $reservations = $reservations->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Section des statistiques
        $this->addStatisticsSection($pdf, $events, $reservations);
        
        // Section des événements
        $this->addEventsSection($pdf, $events);
        
        // Section des réservations
        $this->addReservationsSection($pdf, $reservations);
        
        // Générer le PDF
        $filename = 'export_events_' . date('Ymd_His') . '.pdf';
        $pdf->Output($filename, 'D');
    }
    
    private function addStatisticsSection($pdf, $events, $reservations)
    {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(200, 220, 255);
        $pdf->Cell(0, 8, 'STATISTIQUES', 0, 1, 'C', true);
        $pdf->Ln(3);
        
        // Calcul des statistiques
        $stats = $this->calculateStatistics($events, $reservations);
        
        $pdf->SetFont('helvetica', '', 10);
        
        foreach ($stats as $label => $value) {
            $pdf->Cell(90, 7, $label . ':', 0, 0);
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 7, $value, 0, 1);
            $pdf->SetFont('helvetica', '', 10);
        }
        
        $pdf->Ln(10);
    }
    
    private function calculateStatistics($events, $reservations)
    {
        // Initialiser les compteurs
        $totalEvents = 0;
        $activeEvents = 0;
        $pastEvents = 0;
        $today = date('Y-m-d');
        
        $totalReservations = 0;
        $confirmedReservations = 0;
        $pendingReservations = 0;
        $canceledReservations = 0;
        
        // 1. Traitement des événements
        if (is_array($events)) {
            $totalEvents = count($events);
            
            foreach ($events as $event) {
                // Vérifier le statut
                if (isset($event['status'])) {
                    $status = strtolower($event['status']);
                    
                    if ($status == 'active' || $status == 'actif' || $status == 'activé') {
                        $activeEvents++;
                    }
                }
                
                // Vérifier si l'événement est passé
                if (isset($event['end_date']) && !empty($event['end_date'])) {
                    $endDate = $event['end_date'];
                    
                    if ($endDate < $today) {
                        $pastEvents++;
                    }
                }
            }
        } elseif (is_object($events) && method_exists($events, 'fetch')) {
            // Si c'est un PDOStatement
            while ($event = $events->fetch(PDO::FETCH_ASSOC)) {
                $totalEvents++;
                
                if (isset($event['status']) && strtolower($event['status']) == 'active') {
                    $activeEvents++;
                }
                
                if (isset($event['end_date']) && $event['end_date'] < $today) {
                    $pastEvents++;
                }
            }
        }
        
        // 2. Traitement des réservations
        if (is_array($reservations)) {
            $totalReservations = count($reservations);
            
            foreach ($reservations as $reservation) {
                if (isset($reservation['status'])) {
                    $status = strtolower($reservation['status']);
                    
                    switch ($status) {
                        case 'confirmed':
                        case 'confirme':
                        case 'confirmé':
                            $confirmedReservations++;
                            break;
                        case 'pending':
                        case 'en attente':
                            $pendingReservations++;
                            break;
                        case 'canceled':
                        case 'annule':
                        case 'annulé':
                            $canceledReservations++;
                            break;
                        default:
                            // Si le statut n'est pas reconnu, le compter comme "pending"
                            $pendingReservations++;
                            break;
                    }
                }
            }
        } elseif (is_object($reservations) && method_exists($reservations, 'fetch')) {
            // Si c'est un PDOStatement
            while ($reservation = $reservations->fetch(PDO::FETCH_ASSOC)) {
                $totalReservations++;
                
                if (isset($reservation['status'])) {
                    $status = strtolower($reservation['status']);
                    switch ($status) {
                        case 'confirmed':
                            $confirmedReservations++;
                            break;
                        case 'pending':
                            $pendingReservations++;
                            break;
                        case 'canceled':
                            $canceledReservations++;
                            break;
                    }
                }
            }
        }
        
        // Taux de confirmation
        $confirmationRate = $totalReservations > 0 ? 
            round(($confirmedReservations / $totalReservations) * 100, 2) . '%' : '0%';
        
        return [
            'Total des événements' => $totalEvents,
            'Événements actifs' => $activeEvents,
            'Événements passés' => $pastEvents,
            'Total des réservations' => $totalReservations,
            'Réservations confirmées' => $confirmedReservations,
            'Réservations en attente' => $pendingReservations,
            'Réservations annulées' => $canceledReservations,
            'Taux de confirmation' => $confirmationRate,
            'Date du rapport' => date('d/m/Y'),
            'Heure de génération' => date('H:i:s')
        ];
    }
    
    // Cette méthode était manquante - AJOUTEZ-LA !
    private function addEventsSection($pdf, $events)
    {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(220, 240, 220);
        $pdf->Cell(0, 8, 'LISTE DES ÉVÉNEMENTS', 0, 1, 'C', true);
        $pdf->Ln(3);
        
        // Vérifier si $events est vide ou non
        if (empty($events) || (is_array($events) && count($events) == 0)) {
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->Cell(0, 8, 'Aucun événement trouvé.', 0, 1, 'C');
            $pdf->Ln(5);
            return;
        }
        
        // En-tête du tableau
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(240, 240, 240);
        
        $headers = ['ID', 'Titre', 'Type', 'Date début', 'Date fin', 'Statut'];
        $widths = [15, 50, 30, 35, 35, 25];
        
        for ($i = 0; $i < count($headers); $i++) {
            $pdf->Cell($widths[$i], 7, $headers[$i], 1, 0, 'C', true);
        }
        $pdf->Ln();
        
        // Données des événements
        $pdf->SetFont('helvetica', '', 8);
        $fill = false;
        
        // Vérifiez le type de $events
        if (is_array($events)) {
            foreach ($events as $event) {
                $pdf->Cell($widths[0], 6, $event['id_event'] ?? '', 1, 0, 'C', $fill);
                $pdf->Cell($widths[1], 6, substr($event['title'] ?? '', 0, 30), 1, 0, 'L', $fill);
                $pdf->Cell($widths[2], 6, $event['event_type'] ?? '', 1, 0, 'C', $fill);
                $pdf->Cell($widths[3], 6, isset($event['start_date']) ? date('d/m/Y', strtotime($event['start_date'])) : '', 1, 0, 'C', $fill);
                $pdf->Cell($widths[4], 6, isset($event['end_date']) ? date('d/m/Y', strtotime($event['end_date'])) : '', 1, 0, 'C', $fill);
                $pdf->Cell($widths[5], 6, $event['status'] ?? '', 1, 1, 'C', $fill);
                
                $fill = !$fill;
            }
        } elseif (is_object($events) && method_exists($events, 'fetch')) {
            // Si c'est un PDOStatement
            while ($event = $events->fetch(PDO::FETCH_ASSOC)) {
                $pdf->Cell($widths[0], 6, $event['id_event'] ?? '', 1, 0, 'C', $fill);
                $pdf->Cell($widths[1], 6, substr($event['title'] ?? '', 0, 30), 1, 0, 'L', $fill);
                $pdf->Cell($widths[2], 6, $event['event_type'] ?? '', 1, 0, 'C', $fill);
                $pdf->Cell($widths[3], 6, isset($event['start_date']) ? date('d/m/Y', strtotime($event['start_date'])) : '', 1, 0, 'C', $fill);
                $pdf->Cell($widths[4], 6, isset($event['end_date']) ? date('d/m/Y', strtotime($event['end_date'])) : '', 1, 0, 'C', $fill);
                $pdf->Cell($widths[5], 6, $event['status'] ?? '', 1, 1, 'C', $fill);
                
                $fill = !$fill;
            }
        }
        
        $pdf->Ln(10);
    }
    
    // Cette méthode aussi était manquante - AJOUTEZ-LA !
    private function addReservationsSection($pdf, $reservations)
    {
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->SetFillColor(255, 240, 220);
        $pdf->Cell(0, 8, 'LISTE DES RÉSERVATIONS', 0, 1, 'C', true);
        $pdf->Ln(3);
        
        // Vérifier si $reservations est vide ou non
        if (empty($reservations) || (is_array($reservations) && count($reservations) == 0)) {
            $pdf->SetFont('helvetica', 'I', 10);
            $pdf->Cell(0, 8, 'Aucune réservation trouvée.', 0, 1, 'C');
            return;
        }
        
        // En-tête du tableau
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(240, 240, 240);
        
        $headers = ['ID', 'Événement', 'Date réservation', 'Statut'];
        $widths = [15, 80, 50, 45];
        
        for ($i = 0; $i < count($headers); $i++) {
            $pdf->Cell($widths[$i], 7, $headers[$i], 1, 0, 'C', true);
        }
        $pdf->Ln();
        
        // Données des réservations
        $pdf->SetFont('helvetica', '', 8);
        $fill = false;
        
        // Vérifiez le type de $reservations
        if (is_array($reservations)) {
            foreach ($reservations as $reservation) {
                $pdf->Cell($widths[0], 6, $reservation['id_reservation'] ?? '', 1, 0, 'C', $fill);
                $pdf->Cell($widths[1], 6, substr($reservation['event_title'] ?? 'N/A', 0, 40), 1, 0, 'L', $fill);
                $pdf->Cell($widths[2], 6, isset($reservation['reservation_date']) ? date('d/m/Y H:i', strtotime($reservation['reservation_date'])) : '', 1, 0, 'C', $fill);
                
                // Colorier selon le statut
                $statusColor = $this->getStatusColor($reservation['status'] ?? '');
                $pdf->SetTextColor($statusColor[0], $statusColor[1], $statusColor[2]);
                $pdf->Cell($widths[3], 6, $reservation['status'] ?? '', 1, 1, 'C', $fill);
                $pdf->SetTextColor(0, 0, 0); // Réinitialiser la couleur
                
                $fill = !$fill;
            }
        } elseif (is_object($reservations) && method_exists($reservations, 'fetch')) {
            // Si c'est un PDOStatement
            while ($reservation = $reservations->fetch(PDO::FETCH_ASSOC)) {
                $pdf->Cell($widths[0], 6, $reservation['id_reservation'] ?? '', 1, 0, 'C', $fill);
                $pdf->Cell($widths[1], 6, substr($reservation['event_title'] ?? 'N/A', 0, 40), 1, 0, 'L', $fill);
                $pdf->Cell($widths[2], 6, isset($reservation['reservation_date']) ? date('d/m/Y H:i', strtotime($reservation['reservation_date'])) : '', 1, 0, 'C', $fill);
                
                // Colorier selon le statut
                $statusColor = $this->getStatusColor($reservation['status'] ?? '');
                $pdf->SetTextColor($statusColor[0], $statusColor[1], $statusColor[2]);
                $pdf->Cell($widths[3], 6, $reservation['status'] ?? '', 1, 1, 'C', $fill);
                $pdf->SetTextColor(0, 0, 0); // Réinitialiser la couleur
                
                $fill = !$fill;
            }
        }
    }
    
    private function getStatusColor($status)
    {
        $status = strtolower($status);
        switch ($status) {
            case 'confirmed':
            case 'confirme':
            case 'confirmé':
                return [0, 128, 0]; // Vert
            case 'pending':
            case 'en attente':
                return [255, 165, 0]; // Orange
            case 'canceled':
            case 'annule':
            case 'annulé':
                return [255, 0, 0]; // Rouge
            default:
                return [0, 0, 0]; // Noir
        }
    }
    
    // Méthode pour générer un PDF personnalisé
    public function generateCustomReport($startDate = null, $endDate = null)
    {
        $title = 'Rapport Personnalisé';
        if ($startDate && $endDate) {
            $title .= ' du ' . date('d/m/Y', strtotime($startDate)) . 
                     ' au ' . date('d/m/Y', strtotime($endDate));
        }
        
        $this->generateEventsPDF($title);
    }
}

// Utilisation
if (isset($_GET['action']) && $_GET['action'] == 'export') {
    // Activer les erreurs pour le débogage
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    try {
        $pdfExport = new PDFExport();
        
        if (isset($_GET['type'])) {
            switch ($_GET['type']) {
                case 'custom':
                    $startDate = $_GET['start_date'] ?? null;
                    $endDate = $_GET['end_date'] ?? null;
                    $pdfExport->generateCustomReport($startDate, $endDate);
                    break;
                case 'events':
                default:
                    $pdfExport->generateEventsPDF();
                    break;
            }
        } else {
            $pdfExport->generateEventsPDF();
        }
    } catch (Exception $e) {
        // Afficher l'erreur directement
        echo "<h3>Erreur lors de la génération du PDF:</h3>";
        echo "<p><strong>Message:</strong> " . $e->getMessage() . "</p>";
        echo "<p><strong>Fichier:</strong> " . $e->getFile() . "</p>";
        echo "<p><strong>Ligne:</strong> " . $e->getLine() . "</p>";
        echo "<h4>Backtrace:</h4>";
        echo "<pre>" . print_r($e->getTrace(), true) . "</pre>";
    }
    
    exit;
}
?>