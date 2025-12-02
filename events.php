<?php
include '../controllers/eventsController.php';
include '../controllers/reservationController.php';

$eventC = new EventsC();
$reservationC = new ReservationC();

// Récupérer les données AVEC fetchAll()
$listeEvents = $eventC->afficherEvents()->fetchAll();

// Vérifiez que la méthode existe dans votre ReservationC
$listeReservations = [];
if (method_exists($reservationC, 'afficherReservationsAvecDetails')) {
    $listeReservations = $reservationC->afficherReservationsAvecDetails()->fetchAll();
} else {
    // Fallback: Récupérer les réservations de base
    $listeReservations = $reservationC->afficherReservations()->fetchAll();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Discord Clone</title>
    <link rel="stylesheet" href="admin-styles.css">
</head>

<body>
    <!-- ============================================
         ADMIN DASHBOARD PAGE
         ============================================ -->
    <div class="admin-container">
        <!-- ============================================
             SIDEBAR NAVIGATION
             ============================================ -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2 class="sidebar-title">Admin Panel</h2>
                <div class="admin-avatar">A</div>
            </div>

            <nav class="sidebar-nav">
                <a href="admin.html" class="nav-link " data-section="dashboard"
                    onclick="showAdminSection('dashboard', this)">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Dashboard</span>
                </a>
                <a href="#users" class="nav-link" data-section="users" onclick="showAdminSection('users', this)">
                    <span class="nav-icon">👥</span>
                    <span class="nav-text">Users</span>
                </a>
                <a href="#servers" class="nav-link" data-section="servers" onclick="showAdminSection('servers', this)">
                    <span class="nav-icon">💬</span>
                    <span class="nav-text">Servers</span>
                </a>
                <a href="#messages" class="nav-link" data-section="messages"
                    onclick="showAdminSection('messages', this)">
                    <span class="nav-icon">💌</span>
                    <span class="nav-text">Messages</span>
                </a>
                <a href="#streams" class="nav-link" data-section="streams" onclick="showAdminSection('streams', this)">
                    <span class="nav-icon">📺</span>
                    <span class="nav-text">Streams</span>
                </a>
                <a href="#posts" class="nav-link" data-section="posts" onclick="showAdminSection('posts', this)">
                    <span class="nav-icon">🐦</span>
                    <span class="nav-text">Posts</span>
                </a>
                <a href="events.php" class="nav-link active" data-section="posts"
                    onclick="showAdminSection('posts', this)">
                    <span class="nav-icon">🐦</span>
                    <span class="nav-text">Events</span>
                </a>
                <a href="#settings" class="nav-link" data-section="settings"
                    onclick="showAdminSection('settings', this)">
                    <span class="nav-icon">⚙️</span>
                    <span class="nav-text">Settings</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <button class="logout-btn" onclick="logOutAdmin()">
                    <span class="nav-icon">🚪</span>
                    <span class="nav-text">Logout</span>
                </button>
            </div>
        </aside>

        <!-- ============================================
             MAIN CONTENT AREA
             ============================================ -->
        <main class="admin-main">
            <!-- Top Header -->
            <header class="admin-header">
                <div class="header-left">
                    <h1 class="page-title" id="page-title">Dashboard</h1>
                    <p class="page-subtitle" id="page-subtitle">Overview of your platform</p>
                </div>
                <div class="header-right">
                    <div class="search-box">
                        <input type="text" placeholder="Search..." id="admin-search" class="search-input" />
                        <span class="search-icon">🔍</span>
                    </div>
                    <button class="refresh-btn" onclick="refreshAdminData(this)" title="Refresh Data">
                        🔄
                    </button>
                </div>
            </header>

            <!-- DASHBOARD SECTION -->
            <section id="dashboard-section" class="content-section active">
                <!-- Statistics Cards -->
                <!-- Statistics Cards - Système Événements/Réservations -->
                <div class="stats-grid">
                    <!-- Événements Actifs -->
                    <div class="stat-card">
                        <div class="stat-icon events-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Événements Actifs</div>
                            <div class="stat-value" id="stat-active-events">
                                <?php
                                $activeEvents = 0;
                                if (is_array($listeEvents)) {
                                    $activeEvents = count(array_filter($listeEvents, fn($e) => isset($e['status']) && $e['status'] == 'active'));
                                }
                                echo $activeEvents;
                                ?>
                            </div>
                            <div class="stat-change positive">À venir</div>
                        </div>
                    </div>

                    <!-- Réservations En Attente -->
                    <div class="stat-card">
                        <div class="stat-icon pending-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Réservations En Attente</div>
                            <div class="stat-value" id="stat-pending-reservations">
                                <?php
                                $pendingReservations = 0;
                                if (is_array($listeReservations)) {
                                    $pendingReservations = count(array_filter(
                                        $listeReservations,
                                        fn($r) => isset($r['status']) && $r['status'] == 'pending'
                                    ));
                                }
                                echo $pendingReservations;
                                ?>
                            </div>
                            <div class="stat-change warning">Nécessitent validation</div>
                        </div>
                    </div>

                    <!-- Taux de Réservation -->
                    <div class="stat-card">
                        <div class="stat-icon rate-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Taux de Réservation</div>
                            <div class="stat-value" id="stat-reservation-rate">
                                <?php
                                $rate = 0;
                                if (is_array($listeEvents) && is_array($listeReservations)) {
                                    $totalEvents = count($listeEvents);
                                    if ($totalEvents > 0) {
                                        $uniqueEventIds = array_unique(array_column($listeReservations, 'event_id'));
                                        $eventsWithReservations = count($uniqueEventIds);
                                        $rate = round(($eventsWithReservations / $totalEvents) * 100);
                                    }
                                }
                                echo $rate . '%';
                                ?>
                            </div>
                            <div class="stat-change positive">Événements réservés</div>
                        </div>
                    </div>

                    <!-- Participations Moyennes -->
                    <div class="stat-card">
                        <div class="stat-icon participation-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Participation Moyenne</div>
                            <div class="stat-value" id="stat-avg-participation">
                                <?php
                                $avgParticipation = 0;
                                if (is_array($listeReservations) && !empty($listeReservations)) {
                                    $eventReservationCounts = [];
                                    foreach ($listeReservations as $reservation) {
                                        if (isset($reservation['event_id'])) {
                                            $eventId = $reservation['event_id'];
                                            if (!isset($eventReservationCounts[$eventId])) {
                                                $eventReservationCounts[$eventId] = 0;
                                            }
                                            $eventReservationCounts[$eventId]++;
                                        }
                                    }
                                    if (!empty($eventReservationCounts)) {
                                        $avgParticipation = round(array_sum($eventReservationCounts) / count($eventReservationCounts), 1);
                                    }
                                }
                                echo $avgParticipation;
                                ?>
                            </div>
                            <div class="stat-change">Par événement</div>
                        </div>
                    </div>

                    <!-- Événements Ce Mois -->
                    <div class="stat-card">
                        <div class="stat-icon month-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 6v6l4 2" />
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Événements Ce Mois</div>
                            <div class="stat-value" id="stat-events-this-month">
                                <?php
                                $eventsThisMonth = 0;
                                if (is_array($listeEvents)) {
                                    $currentMonth = date('m');
                                    $currentYear = date('Y');
                                    $eventsThisMonth = count(array_filter($listeEvents, function ($event) use ($currentMonth, $currentYear) {
                                        if (!isset($event['start_date']))
                                            return false;
                                        $eventMonth = date('m', strtotime($event['start_date']));
                                        $eventYear = date('Y', strtotime($event['start_date']));
                                        return $eventMonth == $currentMonth && $eventYear == $currentYear;
                                    }));
                                }
                                echo $eventsThisMonth;
                                ?>
                            </div>
                            <div class="stat-change positive">En <?= date('F') ?></div>
                        </div>
                    </div>

                    <!-- Taux de Confirmation -->
                    <div class="stat-card">
                        <div class="stat-icon confirmation-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">Taux de Confirmation</div>
                            <div class="stat-value" id="stat-confirmation-rate">
                                <?php
                                $confirmationRate = 0;
                                if (is_array($listeReservations) && !empty($listeReservations)) {
                                    $confirmedReservations = count(array_filter(
                                        $listeReservations,
                                        fn($r) => isset($r['status']) && $r['status'] == 'confirmed'
                                    ));
                                    $totalReservations = count($listeReservations);
                                    if ($totalReservations > 0) {
                                        $confirmationRate = round(($confirmedReservations / $totalReservations) * 100);
                                    }
                                }
                                echo $confirmationRate . '%';
                                ?>
                            </div>
                            <div class="stat-change positive">Réservations confirmées</div>
                        </div>
                    </div>
                </div>

                <!-- Charts/Graphs Area -->
                <div class="charts-grid">
                    <!-- Évolution des Réservations -->
                  
                </div>

                <!-- Recent Activity Table -->
                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">Recent Activity</h3>
                        <button class="view-all-btn" onclick="openAddEvent()">Add Event</button>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>User ID</th>
                                    <th>Title</th>
                                    <th>Start</th>
                                    <th>End</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($listeEvents as $event) { ?>
                                    <tr>
                                        <td><?= $event['id_event'] ?></td>
                                        <td><?= $event['user_id'] ?></td>
                                        <td><?= $event['title'] ?></td>
                                        <td><?= $event['start_date'] ?></td>
                                        <td><?= $event['end_date'] ?></td>
                                        <td><?= $event['status'] ?></td>

                                        <td>
                                            <!-- BTN Modifier -->
                                            <div class="row-actions">
                                                <button class="action-btn btn-edit" title="Edit"
                                                    data-id=<?= json_encode($event['id_event']) ?>
                                                    data-title=<?= json_encode($event['title']) ?>
                                                    data-description=<?= json_encode($event['description']) ?>
                                                    data-event-type=<?= json_encode($event['event_type']) ?>
                                                    data-start=<?= json_encode($event['start_date']) ?>
                                                    data-end=<?= json_encode($event['end_date']) ?>
                                                    data-status=<?= json_encode($event['status']) ?>>
                                                    <span class="btn-icon">✏️</span>
                                                    <span class="btn-label">Edit</span>
                                                </button>

                                                <button class="action-btn btn-delete" data-id="<?= $event['id_event'] ?>"
                                                    data-title="<?= htmlspecialchars($event['title'], ENT_QUOTES) ?>">
                                                    Delete
                                                </button>


                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>

                        </table>
                    </div>
                </div>


                <div class="table-card">
                    <div class="table-header">
                        <h3 class="table-title">Réservations</h3>

                    </div>
                    <div class="table-container">
                        <table class="data-table" id="reservationsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Événement</th>
                                    <th>Utilisateur</th>
                                    <th>Date Réservation</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Supposons que $listeReservations contient les réservations
                                // Requête SQL: SELECT r.*, e.title as event_title FROM reservations r JOIN events e ON r.event_id = e.id_event
                                foreach ($listeReservations as $reservation) {
                                    $statusClass = 'status-' . $reservation['status'];
                                    ?>
                                    <tr class="reservation-row" data-status="<?= $reservation['status'] ?>">
                                        <td><?= $reservation['id_reservation'] ?></td>
                                        <td>
                                            <div class="event-info-cell">
                                                <strong><?= htmlspecialchars($reservation['event_title']) ?></strong>
                                                <small>ID: <?= $reservation['event_id'] ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="user-info-cell">
                                                <span class="user-id">ID: <?= $reservation['user_id'] ?></span>
                                            </div>
                                        </td>
                                        <td><?= formatDate($reservation['reservation_date']) ?></td>
                                        <td>
                                            <span class="status-badge <?= $statusClass ?>">
                                                <?= getReservationStatusLabel($reservation['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="row-actions">
                                                <?php if ($reservation['status'] == 'pending'): ?>
                                                    <button class="action-btn btn-accept"
                                                        data-id="<?= $reservation['id_reservation'] ?>"
                                                        data-event="<?= htmlspecialchars($reservation['event_title']) ?>">
                                                        <span class="btn-icon">✅</span>
                                                        <span class="btn-label">Accepter</span>
                                                    </button>

                                                    <button class="action-btn btn-reject"
                                                        data-id="<?= $reservation['id_reservation'] ?>"
                                                        data-event="<?= htmlspecialchars($reservation['event_title']) ?>">
                                                        <span class="btn-icon">❌</span>
                                                        <span class="btn-label">Refuser</span>
                                                    </button>
                                                <?php elseif ($reservation['status'] == 'confirmed'): ?>
                                                    <button class="action-btn btn-cancel"
                                                        data-id="<?= $reservation['id_reservation'] ?>"
                                                        data-event="<?= htmlspecialchars($reservation['event_title']) ?>">
                                                        <span class="btn-icon">🔄</span>
                                                        <span class="btn-label">Annuler</span>
                                                    </button>
                                                <?php endif; ?>

                                                <button class="action-btn btn-delete-reservation"
                                                    data-id="<?= $reservation['id_reservation'] ?>"
                                                    data-event="<?= htmlspecialchars($reservation['event_title']) ?>">
                                                    <span class="btn-icon">🗑️</span>
                                                    <span class="btn-label">Supprimer</span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
    </div>
    <?php
    function getReservationStatusLabel($status)
    {
        $labels = [
            'pending' => '⏳ En attente',
            'confirmed' => '✅ Confirmée',
            'cancelled' => '❌ Annulée'
        ];
        return $labels[$status] ?? ucfirst($status);
    }

    function formatDate($dateString)
    {
        $date = new DateTime($dateString);
        return $date->format('d/m/Y H:i');
    }
    ?>
    <!-- Modal de confirmation pour les actions -->
    <!-- Modal de confirmation pour les actions - Design Noir -->
    <div id="confirmationModal" class="modal">
        <div class="modal-content confirmation-modal">
            <div class="modal-header">
                <div class="modal-icon">
                    <span id="modalIcon">⚠️</span>
                </div>
                <div class="modal-title-section">
                    <h3 id="modalActionTitle">Confirmation</h3>
                    <p class="modal-subtitle" id="modalActionSubtitle">Action en attente de confirmation</p>
                </div>
                <button class="close-btn" onclick="closeConfirmationModal()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="modal-body">
                <div class="modal-message-container">
                    <p id="modalActionMessage" class="modal-message"></p>
                    <div class="modal-details" id="modalDetails" style="display: none;">
                        <div class="detail-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            <span id="detailEvent">Événement</span>
                        </div>
                        <div class="detail-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span id="detailTime">Juste maintenant</span>
                        </div>
                    </div>
                </div>

                <div class="modal-actions">
                    <button class="btn btn-secondary" onclick="closeConfirmationModal()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <span>Annuler</span>
                    </button>
                    <button class="btn btn-primary" id="confirmActionBtn">
                        <span id="confirmBtnText">Confirmer</span>
                        <span class="spinner" style="display: none;"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Variables pour le thème noir */
        :root {
            --modal-bg: #1e1e1e;
            --modal-header-bg: #252525;
            --modal-border: #333;
            --modal-text: #ffffff;
            --modal-text-secondary: #b0b0b0;
            --danger-color: #ef4444;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --modal-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
        }

        /* Modal général */
        .modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(4px);
            animation: fadeIn 0.3s ease-out;
        }

        /* Contenu spécifique au modal de confirmation */
        .confirmation-modal {
            background: var(--modal-bg);
            border-radius: 16px;
            max-width: 500px;
            border: 1px solid var(--modal-border);
            box-shadow: var(--modal-shadow);
            animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            overflow: hidden;
        }

        /* En-tête du modal */
        .confirmation-modal .modal-header {
            padding: 24px;
            background: var(--modal-header-bg);
            border-bottom: 1px solid var(--modal-border);
            display: flex;
            align-items: flex-start;
            gap: 16px;
            position: relative;
        }

        .modal-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger-color);
            flex-shrink: 0;
        }

        .modal-icon.warning {
            background: rgba(245, 158, 11, 0.15);
            color: var(--warning-color);
        }

        .modal-icon.success {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success-color);
        }

        .modal-icon.info {
            background: rgba(59, 130, 246, 0.15);
            color: var(--info-color);
        }

        .modal-title-section {
            flex: 1;
        }

        .confirmation-modal .modal-header h3 {
            margin: 0 0 4px 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--modal-text);
        }

        .modal-subtitle {
            margin: 0;
            font-size: 0.9rem;
            color: var(--modal-text-secondary);
        }

        .close-btn {
            background: transparent;
            border: 1px solid var(--modal-border);
            color: var(--modal-text-secondary);
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            flex-shrink: 0;
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .close-btn:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--danger-color);
            color: var(--danger-color);
            transform: rotate(90deg);
        }

        /* Corps du modal */
        .confirmation-modal .modal-body {
            padding: 24px;
        }

        .modal-message-container {
            margin-bottom: 24px;
        }

        .modal-message {
            margin: 0 0 20px 0;
            font-size: 1rem;
            line-height: 1.6;
            color: var(--modal-text);
            padding: 16px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 10px;
            border-left: 4px solid var(--danger-color);
        }

        .modal-message.warning {
            border-left-color: var(--warning-color);
        }

        .modal-message.success {
            border-left-color: var(--success-color);
        }

        .modal-message.info {
            border-left-color: var(--info-color);
        }

        .modal-details {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 10px;
            padding: 16px;
            border: 1px solid var(--modal-border);
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--modal-text-secondary);
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .detail-item:last-child {
            margin-bottom: 0;
        }

        .detail-item svg {
            color: var(--modal-text-secondary);
            flex-shrink: 0;
        }

        /* Actions du modal */
        .confirmation-modal .modal-actions {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid var(--modal-border);
        }

        .confirmation-modal .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-width: 120px;
        }

        .confirmation-modal .btn-secondary {
            background: transparent;
            color: var(--modal-text-secondary);
            border: 1px solid var(--modal-border);
        }

        .confirmation-modal .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--modal-text-secondary);
            transform: translateY(-1px);
        }

        .confirmation-modal .btn-primary {
            background: linear-gradient(135deg, var(--danger-color), #dc2626);
            color: white;
            border: none;
        }

        .confirmation-modal .btn-primary.warning {
            background: linear-gradient(135deg, var(--warning-color), #d97706);
        }

        .confirmation-modal .btn-primary.success {
            background: linear-gradient(135deg, var(--success-color), #059669);
        }

        .confirmation-modal .btn-primary.info {
            background: linear-gradient(135deg, var(--info-color), #2563eb);
        }

        .confirmation-modal .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .confirmation-modal .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Spinner */
        .spinner {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
            margin-left: 8px;
        }

        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Responsive */
        @media (max-width: 600px) {
            .confirmation-modal {
                margin: 20px;
                max-width: calc(100% - 40px);
            }

            .confirmation-modal .modal-header {
                padding: 20px;
                flex-direction: column;
                gap: 12px;
                text-align: center;
            }

            .modal-icon {
                align-self: center;
            }

            .close-btn {
                position: absolute;
                top: 16px;
                right: 16px;
            }

            .confirmation-modal .modal-body {
                padding: 20px;
            }

            .confirmation-modal .modal-actions {
                flex-direction: column;
            }

            .confirmation-modal .btn {
                width: 100%;
            }
        }
    </style>

    <script>
        // Variables globales pour les actions
        let currentReservationId = null;
        let currentAction = null;
        let currentEventName = null;

        // Mettre à jour les fonctions pour gérer les icônes et couleurs
        function openConfirmationModal(title, message, actionType = 'warning', details = null) {
            const modal = document.getElementById('confirmationModal');
            const modalIcon = document.getElementById('modalIcon');
            const modalTitle = document.getElementById('modalActionTitle');
            const modalSubtitle = document.getElementById('modalActionSubtitle');
            const modalMessage = document.getElementById('modalActionMessage');
            const confirmBtn = document.getElementById('confirmActionBtn');
            const confirmBtnText = document.getElementById('confirmBtnText');
            const modalDetails = document.getElementById('modalDetails');

            // Définir le style selon le type d'action
            let icon = '⚠️';
            let subtitle = 'Action en attente de confirmation';
            let btnColor = 'danger';

            switch (actionType) {
                case 'accept':
                    icon = '✅';
                    subtitle = 'Confirmer la réservation';
                    btnColor = 'success';
                    break;
                case 'reject':
                case 'delete':
                    icon = '❌';
                    subtitle = 'Action irréversible';
                    btnColor = 'danger';
                    break;
                case 'cancel':
                    icon = '🔄';
                    subtitle = 'Annuler la réservation';
                    btnColor = 'warning';
                    break;
                case 'info':
                    icon = 'ℹ️';
                    subtitle = 'Information';
                    btnColor = 'info';
                    break;
            }

            // Mettre à jour les éléments
            modalIcon.textContent = icon;
            modalIcon.className = 'modal-icon ' + actionType;
            modalTitle.textContent = title;
            modalSubtitle.textContent = subtitle;
            modalMessage.textContent = message;
            modalMessage.className = 'modal-message ' + actionType;

            // Mettre à jour le bouton
            confirmBtn.className = 'btn btn-primary ' + btnColor;
            confirmBtnText.textContent = getConfirmButtonText(actionType);

            // Afficher les détails si fournis
            if (details) {
                modalDetails.style.display = 'block';
                document.getElementById('detailEvent').textContent = details.event || currentEventName;
                document.getElementById('detailTime').textContent = new Date().toLocaleTimeString('fr-FR');
            } else {
                modalDetails.style.display = 'none';
            }

            // Afficher le modal
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';

            // Focus sur le bouton Annuler pour éviter la soumission accidentelle
            setTimeout(() => {
                document.querySelector('.confirmation-modal .btn-secondary').focus();
            }, 100);
        }

        function getConfirmButtonText(actionType) {
            switch (actionType) {
                case 'accept': return 'Accepter';
                case 'reject': return 'Refuser';
                case 'delete': return 'Supprimer';
                case 'cancel': return 'Annuler';
                default: return 'Confirmer';
            }
        }

        function closeConfirmationModal() {
            const modal = document.getElementById('confirmationModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            currentReservationId = null;
            currentAction = null;
            currentEventName = null;

            // Réinitialiser le bouton de confirmation
            const confirmBtn = document.getElementById('confirmActionBtn');
            confirmBtn.disabled = false;
            confirmBtn.querySelector('.spinner').style.display = 'none';
            document.getElementById('confirmBtnText').textContent = 'Confirmer';
        }

        // Mettre à jour les écouteurs d'événements pour inclure les détails
        document.querySelectorAll('.btn-accept').forEach(btn => {
            btn.addEventListener('click', function () {
                currentReservationId = this.dataset.id;
                currentEventName = this.dataset.event;
                currentAction = 'accept';
                openConfirmationModal(
                    'Accepter la réservation',
                    `Voulez-vous accepter la réservation pour cet événement ?`,
                    'accept',
                    { event: currentEventName }
                );
            });
        });

        document.querySelectorAll('.btn-reject').forEach(btn => {
            btn.addEventListener('click', function () {
                currentReservationId = this.dataset.id;
                currentEventName = this.dataset.event;
                currentAction = 'reject';
                openConfirmationModal(
                    'Refuser la réservation',
                    `La réservation sera refusée et l'utilisateur en sera informé.`,
                    'reject',
                    { event: currentEventName }
                );
            });
        });

        document.querySelectorAll('.btn-cancel').forEach(btn => {
            btn.addEventListener('click', function () {
                currentReservationId = this.dataset.id;
                currentEventName = this.dataset.event;
                currentAction = 'cancel';
                openConfirmationModal(
                    'Annuler la réservation',
                    `La réservation sera annulée mais restera visible dans l'historique.`,
                    'cancel',
                    { event: currentEventName }
                );
            });
        });

        document.querySelectorAll('.btn-delete-reservation').forEach(btn => {
            btn.addEventListener('click', function () {
                currentReservationId = this.dataset.id;
                currentEventName = this.dataset.event;
                currentAction = 'delete';
                openConfirmationModal(
                    'Supprimer la réservation',
                    `Cette action supprimera définitivement la réservation de la base de données. Cette action est irréversible.`,
                    'delete',
                    { event: currentEventName }
                );
            });
        });

        // Mettre à jour la fonction de confirmation
        document.getElementById('confirmActionBtn').addEventListener('click', async function () {
            if (!currentReservationId || !currentAction) return;

            const btn = this;
            const btnText = document.getElementById('confirmBtnText');
            const spinner = btn.querySelector('.spinner');

            btn.disabled = true;
            btnText.textContent = 'Traitement...';
            spinner.style.display = 'inline-block';

            try {
                const formData = new FormData();
                formData.append('reservation_id', currentReservationId);
                formData.append('action', currentAction);

                const response = await fetch('reservation_action.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Afficher un message de succès temporaire
                    openConfirmationModal(
                        'Succès',
                        result.message,
                        'success',
                        { event: currentEventName }
                    );

                    // Recharger la page après 1.5 secondes
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    throw new Error(result.message || 'Erreur inconnue');
                }

            } catch (error) {
                // Afficher un message d'erreur
                openConfirmationModal(
                    'Erreur',
                    error.message,
                    'error',
                    { event: currentEventName }
                );

                // Réactiver le bouton
                btn.disabled = false;
                btnText.textContent = getConfirmButtonText(currentAction);
                spinner.style.display = 'none';
            }
        });

        // Gestion des événements clavier
        document.addEventListener('keydown', function (event) {
            const modal = document.getElementById('confirmationModal');
            if (event.key === 'Escape' && modal.style.display === 'block') {
                closeConfirmationModal();
            }

            // Enter sur le bouton Confirmer
            if (event.key === 'Enter' && modal.style.display === 'block' &&
                !event.target.classList.contains('btn-secondary')) {
                event.preventDefault();
                document.getElementById('confirmActionBtn').click();
            }
        });

        // Fermer en cliquant à l'extérieur
        window.onclick = function (event) {
            const modal = document.getElementById('confirmationModal');
            if (event.target === modal) {
                closeConfirmationModal();
            }
        };
    </script>
    </section>

    <!-- USERS SECTION -->
    <section id="users-section" class="content-section">
        <div class="section-header">
            <h2 class="section-title">User Management</h2>
            <button class="add-btn" onclick="openAddUserModal()">+ Add User</button>
        </div>

        <!-- Users Filter/Search -->
        <div class="filter-bar">
            <div class="filter-group">
                <label>Filter:</label>
                <select id="user-filter" class="filter-select" onchange="filterUsers()">
                    <option value="all">All Users</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="new">New (Last 7 days)</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Sort:</label>
                <select id="user-sort" class="filter-select" onchange="sortUsers()">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="name">By Name</option>
                    <option value="activity">Most Active</option>
                </select>
            </div>
        </div>

        <!-- Users Table -->
        <div class="table-card">
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th>Messages</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-table">
                        <!-- User rows will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="pagination">
            <button class="page-btn" onclick="changePage(-1)">← Previous</button>
            <span class="page-info">Page <span id="current-page">1</span> of <span id="total-pages">1</span></span>
            <button class="page-btn" onclick="changePage(1)">Next →</button>
        </div>
    </section>

    <!-- SERVERS SECTION -->
    <section id="servers-section" class="content-section">
        <div class="section-header">
            <h2 class="section-title">Server Management</h2>
            <button class="add-btn" onclick="openAddServerModal()">+ Add Server</button>
        </div>

        <div class="cards-grid" id="servers-grid">
            <!-- Server cards will be inserted here -->
        </div>
    </section>

    <!-- MESSAGES SECTION -->
    <section id="messages-section" class="content-section">
        <div class="section-header">
            <h2 class="section-title">Message Management</h2>
        </div>

        <div class="table-card">
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Content</th>
                            <th>Server</th>
                            <th>Channel</th>
                            <th>Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="messages-table">
                        <!-- Message rows will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- STREAMS SECTION -->
    <section id="streams-section" class="content-section">
        <div class="section-header">
            <h2 class="section-title">Stream Management</h2>
        </div>

        <div class="table-card">
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Streamer</th>
                            <th>Title</th>
                            <th>Viewers</th>
                            <th>Status</th>
                            <th>Started</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="streams-table">
                        <!-- Stream rows will be inserted here -->
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <!-- POSTS SECTION -->
    <section id="posts-section" class="content-section">
        <div class="section-header">
            <h2 class="section-title">Post Management</h2>
        </div>

        <div class="table-card">
            <div class="table-container">

            </div>
        </div>

    </section>

    <!-- SETTINGS SECTION -->
    <section id="settings-section" class="content-section">
        <div class="section-header">
            <h2 class="section-title">Settings</h2>
        </div>

        <div class="settings-card">
            <h3 class="settings-group-title">General Settings</h3>
            <div class="settings-item">
                <label class="settings-label">Site Name</label>
                <input type="text" class="settings-input" value="Discord Clone" />
            </div>
            <div class="settings-item">
                <label class="settings-label">Maintenance Mode</label>
                <label class="toggle-switch">
                    <input type="checkbox" />
                    <span class="toggle-slider"></span>
                </label>
            </div>
            <div class="settings-item">
                <label class="settings-label">Max Users</label>
                <input type="number" class="settings-input" value="10000" />
            </div>
            <button class="save-btn">Save Settings</button>
        </div>
    </section>
    </main>
    </div>

    <!-- ============================================
         MODALS
         ============================================ -->
    <!-- Add User Modal -->
    <div id="add-user-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New User</h3>
                <button class="modal-close" onclick="closeModalById('add-user-modal')">×</button>
            </div>
            <form class="modal-form" onsubmit="submitAddUser(event)">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" class="form-input" required />
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" class="form-input" required />
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="form-input" required />
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" class="form-input" required />
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('add-user-modal')">Cancel</button>
                    <button type="submit" class="btn-submit">Add User</button>
                </div>
            </form>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        //ajouter event popout 

        function openAddEvent() {
            Swal.fire({
                title: "Add New Event",
                html: `
            <input id="swal-title" class="swal2-input" placeholder="Title" required>
            <textarea id="swal-description" class="swal2-textarea" placeholder="Description"></textarea>
            <input id="swal-event-type" class="swal2-input" placeholder="Event type (game, stream...)" value="game">
            <input id="swal-start" type="datetime-local" class="swal2-input" required>
            <input id="swal-end" type="datetime-local" class="swal2-input">
            <select id="swal-status" class="swal2-input">
                <option value="upcoming">Upcoming</option>
                <option value="live">Live</option>
                <option value="ended">Ended</option>
            </select>
        `,
                confirmButtonText: "Add Event",
                showCancelButton: true,
                focusConfirm: false,
                preConfirm: () => {
                    const title = document.getElementById("swal-title").value;
                    const startDate = document.getElementById("swal-start").value;

                    if (!title) {
                        Swal.showValidationMessage("Title is required");
                        return false;
                    }
                    if (!startDate) {
                        Swal.showValidationMessage("Start date is required");
                        return false;
                    }

                    return {
                        title: title,
                        description: document.getElementById("swal-description").value,
                        event_type: document.getElementById("swal-event-type").value,
                        start_date: startDate,
                        end_date: document.getElementById("swal-end").value || startDate,
                        status: document.getElementById("swal-status").value
                    };
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    // Afficher un indicateur de chargement
                    Swal.fire({
                        title: 'Adding event...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch("addEvent.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json"
                        },
                        body: JSON.stringify(result.value)
                    })
                        .then(async response => {
                            let text = await response.text();
                            console.log('Raw response:', text); // debug
                            if (!response.ok) {
                                throw new Error('Network response was not ok: ' + text);
                            }
                            return JSON.parse(text);
                        })
                        .then(response => {
                            Swal.close();
                            if (response.success) {
                                Swal.fire("Success!", response.message, "success")
                                    .then(() => location.reload());
                            } else {
                                Swal.fire("Error", response.message, "error");
                            }
                        })
                        .catch(error => {
                            Swal.close();
                            Swal.fire("Error", "Failed to add event: " + error.message, "error");
                            console.error('Error:', error);
                        });

                }
            });
        }



        //suppression
        // Version avec design personnalisé
        document.addEventListener('DOMContentLoaded', () => {

            // Ajouter un event listener à tous les boutons "delete"
            document.querySelectorAll('.btn-delete').forEach(btn => {
                btn.addEventListener('click', () => {
                    const eventId = btn.dataset.id;
                    const eventTitle = btn.dataset.title;
                    deleteEvent(eventId, eventTitle);
                });
            });

            // Fonction SweetAlert pour confirmer la suppression
            function deleteEvent(eventId, eventTitle = '') {
                Swal.fire({
                    title: 'Delete Event',
                    html: `
                <div style="text-align: center;">
                    <div style="font-size: 4rem; color: #e74c3c; margin-bottom: 1rem;">🗑️</div>
                    <h3>Are you sure?</h3>
                    <p>You are about to delete <strong>${eventTitle || 'this event'}</strong>. This action cannot be undone.</p>
                </div>
            `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete It',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    },
                    buttonsStyling: true,
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        performDelete(eventId);
                    }
                });
            }

            // Fonction AJAX pour supprimer l'événement
            function performDelete(eventId) {
                Swal.fire({
                    title: 'Deleting Event...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                fetch('deleteEvent.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id_event: eventId })
                })
                    .then(response => response.json())
                    .then(data => {
                        Swal.close();
                        if (data.success) {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: '#28a745',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => location.reload());
                        } else {
                            Swal.fire('Error!', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        Swal.close();
                        Swal.fire('Error!', 'Network error occurred', 'error');
                        console.error('Error:', error);
                    });
            }

        });


        // Open edit event modal (defined here so popup works from this file)
        function openEditEvent(id, title, description, event_type, start_date, end_date, status) {
            Swal.fire({
                title: 'Edit Event',
                html: `
            <input id="swal-edit-title" class="swal2-input" placeholder="Title" value="${escapeHtml(title)}">
            <textarea id="swal-edit-description" class="swal2-textarea" placeholder="Description">${escapeHtml(description)}</textarea>
            <input id="swal-edit-event-type" class="swal2-input" placeholder="Event type" value="${escapeHtml(event_type)}">
            <input id="swal-edit-start" type="datetime-local" class="swal2-input" value="${toDatetimeLocal(start_date)}">
            <input id="swal-edit-end" type="datetime-local" class="swal2-input" value="${toDatetimeLocal(end_date)}">
            <select id="swal-edit-status" class="swal2-input">
                <option value="upcoming" ${status === 'upcoming' ? 'selected' : ''}>Upcoming</option>
                <option value="live" ${status === 'live' ? 'selected' : ''}>Live</option>
                <option value="ended" ${status === 'ended' ? 'selected' : ''}>Ended</option>
            </select>
        `,
                confirmButtonText: 'Save Changes',
                showCancelButton: true,
                focusConfirm: false,
                preConfirm: () => {
                    const t = document.getElementById('swal-edit-title').value;
                    const s = document.getElementById('swal-edit-start').value;
                    if (!t) {
                        Swal.showValidationMessage('Title is required');
                        return false;
                    }
                    if (!s) {
                        Swal.showValidationMessage('Start date is required');
                        return false;
                    }
                    return {
                        id_event: id,
                        title: t,
                        description: document.getElementById('swal-edit-description').value,
                        event_type: document.getElementById('swal-edit-event-type').value,
                        start_date: document.getElementById('swal-edit-start').value,
                        end_date: document.getElementById('swal-edit-end').value || document.getElementById('swal-edit-start').value,
                        status: document.getElementById('swal-edit-status').value
                    };
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    Swal.fire({ title: 'Saving changes...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    fetch('updateEvent.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(result.value)
                    })
                        .then(async res => {
                            const text = await res.text();
                            if (!res.ok) throw new Error(text || 'Network error');
                            return JSON.parse(text);
                        })
                        .then(data => {
                            Swal.close();
                            if (data.success) {
                                Swal.fire('Saved', data.message, 'success').then(() => location.reload());
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        })
                        .catch(err => {
                            Swal.close();
                            console.error('Update error:', err);
                            Swal.fire('Error', 'Failed to update event: ' + (err.message || err), 'error');
                        });
                }
            });
        }

        // Small helpers used by the inline edit popup
        function escapeHtml(str) {
            if (str === null || typeof str === 'undefined') return '';
            return String(str).replace(/[&<>\"]/g, function (s) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[s];
            });
        }

        function toDatetimeLocal(value) {
            if (!value) return '';
            if (value.indexOf('T') !== -1) return value;
            const d = new Date(value);
            if (isNaN(d.getTime())) return '';
            const pad = n => String(n).padStart(2, '0');
            const yyyy = d.getFullYear();
            const mm = pad(d.getMonth() + 1);
            const dd = pad(d.getDate());
            const hh = pad(d.getHours());
            const min = pad(d.getMinutes());
            return `${yyyy}-${mm}-${dd}T${hh}:${min}`;
        }

        // Delegated handler to open edit popup from data attributes
        document.addEventListener('click', function (e) {
            const btn = e.target.closest && e.target.closest('.btn-edit');
            if (!btn) return;
            const id = btn.dataset.id;
            const title = btn.dataset.title || '';
            const description = btn.dataset.description || '';
            const event_type = btn.dataset.eventType || btn.dataset['eventType'] || btn.dataset['event-type'] || '';
            const start_date = btn.dataset.start || '';
            const end_date = btn.dataset.end || '';
            const status = btn.dataset.status || '';
            openEditEvent(id, title, description, event_type, start_date, end_date, status);
        });
    </script>




    <script src="admin-script.js"></script>
</body>

</html>