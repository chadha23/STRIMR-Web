<?php
include_once "../controllers/eventsController.php";    

$eventsC = new EventsC();
$listeEvents = $eventsC->afficherEvents();

// Fonctions utilitaires PHP
function getEventTypeLabel($type) {
    $labels = [
        'meeting' => 'Réunion',
        'workshop' => 'Atelier', 
        'social' => 'Social',
        'other' => 'Autre'
    ];
    return $labels[$type] ?? $type;
}

function getStatusLabel($status) {
    $labels = [
        'active' => 'Actif',
        'cancelled' => 'Annulé', 
        'completed' => 'Terminé',
        'upcoming' => 'À venir'
    ];
    return $labels[$status] ?? $status;
}

function formatDate($dateString) {
    $date = new DateTime($dateString);
    return $date->format('d M Y \à H:i');
}
?>




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ============================================
         HEAD SECTION
         Lines 1-10
         ============================================ -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discord Clone - Simple Template</title>
    
    <!-- Link to external CSS file -->
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- ============================================
         TOP NAVIGATION BAR
         Lines 11-30
         Fixed navigation at the top with 3 buttons:
         - Servers (Discord-style chat)
         - Stream (Twitch-style streaming)
         - Feed (Twitter-style feed)
         ============================================ -->
    <nav class="top-nav">
        <div class="nav-container">
            <!-- Servers Button - Opens Discord-style chat page -->
            <div class="nav-item active" onclick="showPage('servers', this)">
                <div class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M7 8h10M7 12h6m8-1a9 9 0 10-4.5 7.8l3.6 1.2a1 1 0 001.3-1.1l-.6-3A8.9 8.9 0 0021 11z"/>
                    </svg>
                </div>
                <div class="nav-label">Servers</div>
            </div>
            
            <!-- Stream Button - Opens Twitch-style streaming page -->
            <div class="nav-item" onclick="showPage('stream', this)">
                <div class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M4 6a2 2 0 012-2h12a2 2 0 012 2v9a2 2 0 01-2 2h-3l-4.5 3a1 1 0 01-1.5-.86V17H6a2 2 0 01-2-2V6z"/>
                    </svg>
                </div>
                <div class="nav-label">Stream</div>
            </div>

            <!-- Marketplace Button - Opens Facebook-style marketplace page -->
            <div class="nav-item" onclick="showPage('marketplace', this)">
                <div class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M3 9l1.8-3.6A2 2 0 016.6 4h10.8a2 2 0 011.8 1.4L21 9v9a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M3 9h18M8 13h1.5a1.5 1.5 0 011.5 1.5V20M14 20v-5.5A1.5 1.5 0 0115.5 13H17"/>
                    </svg>
                </div>
                <div class="nav-label">Marketplace</div>
            </div>
            
            <!-- Feed Button - Opens Twitter-style feed page -->
            <div class="nav-item" onclick="showPage('feed', this)">
                <div class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M21 5.92a6.55 6.55 0 01-1.89.52 3.3 3.3 0 001.45-1.82 6.59 6.59 0 01-2.07.8 3.28 3.28 0 00-5.6 2.24 3.4 3.4 0 00.08.75A9.31 9.31 0 013 5.16a3.29 3.29 0 001.02 4.38 3.23 3.23 0 01-1.48-.41v.04a3.29 3.29 0 002.63 3.22 3.3 3.3 0 01-1.47.06 3.29 3.29 0 003.07 2.28A6.58 6.58 0 013 17.54 9.29 9.29 0 008.05 19c6.29 0 9.73-5.22 9.73-9.75q0-.23-.01-.45A6.97 6.97 0 0021 5.92z"/>
                    </svg>
                </div>
                <div class="nav-label">Feed</div>
            </div>
            <!-- Events Button - Nouvel onglet pour les événements -->
<div class="nav-item" onclick="showPage('events', this)">
    <div class="nav-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
        </svg>
    </div>
    <div class="nav-label">Événements</div>
</div>
        </div>
    </nav>


    <!-- ============================================
         MAIN CONTENT AREA
         Lines 31-200
         Container for all pages (servers, stream, feed)
         Only one page is visible at a time
         ============================================ -->
    <div class="main-wrapper">
        
        <!-- ============================================
             SERVERS PAGE (Discord-style)
             Lines 35-120
             Three-column layout: servers sidebar, channels sidebar, chat area
             ============================================ -->
        <div id="servers-page" class="page active">
            <div class="servers-page">
                
                <!-- Servers Sidebar (Left) - List of server icons -->
                <div class="servers-sidebar">
                    <!-- Home Server Icon -->
                    <div class="server-icon home active" onclick="chooseServer('home', this)" title="Home">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M4 10.5L12 4l8 6.5V20a1 1 0 01-1 1h-5v-6h-4v6H5a1 1 0 01-1-1z"/>
                        </svg>
                    </div>
                    <div class="divider"></div>
                    
                    <!-- General Server Icon -->
                    <div class="server-icon green" onclick="chooseServer('general', this)" title="General">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M7 8h10M7 12h6m10-1a9 9 0 11-4.5-7.8l3.6-1.2a1 1 0 011.3 1.1l-.6 3A8.9 8.9 0 0123 11z"/>
                        </svg>
                    </div>
                    
                    <!-- Gaming Server Icon -->
                    <div class="server-icon purple" onclick="chooseServer('gaming', this)" title="Gaming">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M6.5 8h11a3.5 3.5 0 013.5 3.5V14a4 4 0 01-4 4h-1.1l-.9 1a2 2 0 01-3 0l-.9-1H8a4 4 0 01-4-4v-2.5A3.5 3.5 0 016.5 8z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M8.5 12h-3m1.5-1.5v3M17.75 12.75h.01M20 10h.01"/>
                        </svg>
                    </div>
                    
                    <!-- Music Server Icon -->
                    <div class="server-icon pink" onclick="chooseServer('music', this)" title="Music">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M9 18a3 3 0 11-2-2.82V6.5a1 1 0 01.79-.98l10-2.2A1 1 0 0119 4.3V14a3 3 0 11-2-2.83V7.2l-8 1.76V18z"/>
                        </svg>
                    </div>
                    
                    <!-- Coding Server Icon -->
                    <div class="server-icon" style="background-color: #faa61a;" onclick="chooseServer('coding', this)" title="Coding">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <rect x="3" y="5" width="18" height="12" rx="2" ry="2" stroke-width="1.8"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M2 17h20M9 21h6"/>
                        </svg>
                    </div>
                </div>

                <!-- Channels Sidebar (Middle) - List of channels for selected server -->
                <div class="channels-sidebar">
                    <!-- Server Name Header -->
                    <div class="channels-header">
                        <h2 id="server-name">Server Name</h2>
                    </div>
                    
                    <!-- Channels List -->
                    <div class="channels-list">
                        <div class="channel-section">
                            <div class="channel-section-title">Text Channels</div>
                            
                            <!-- General Channel -->
                            <div class="channel-item active" onclick="chooseChannel('general', this)"><span>general</span></div>
                            
                            <!-- Announcements Channel -->
                            <div class="channel-item" onclick="chooseChannel('announcements', this)"><span>announcements</span></div>
                            
                            <!-- Random Channel -->
                            <div class="channel-item" onclick="chooseChannel('random', this)"><span>random</span></div>
                        </div>
                    </div>
                    
                    <!-- User Info Footer -->
                    <div class="user-info">
                        <div class="user-avatar" id="user-avatar">U</div>
                        <div class="user-details">
                            <div class="user-name" id="user-name">Username</div>
                            <div class="user-id" id="user-id">#1234</div>
                        </div>
                        <button class="logout-btn" onclick="logOutUser()" title="Logout" style="background: none; border: none; color: #8e9297; cursor: pointer; padding: 4px; border-radius: 4px; transition: all 0.2s;">
                            🚪
                        </button>
                    </div>
                </div>

                <!-- Chat Area (Right) - Messages display and input -->
                <div class="chat-area">
                    <!-- Channel Header -->
                    <div class="content-header">
                        <h2 id="channel-name">general</h2>
                    </div>
                    
                    <!-- Messages Container - Messages are inserted here by JavaScript -->
                    <div class="content-body">
                        <div class="messages-container" id="messages-container">
                            <!-- Messages will be inserted here by JavaScript (see script.js renderMessages function) -->
                        </div>
                    </div>
                    
                    <!-- Message Input Area -->
                    <div class="input-area">
                        <div class="input-container">
                            <!-- Add Attachment Button -->
                            <button class="input-btn" type="button" aria-label="Add attachment">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                          d="M12 5v14m7-7H5"/>
                                </svg>
                            </button>
                            
                            <!-- Message Input Field -->
                            <input 
                                type="text" 
                                class="input-field" 
                                id="message-input"
                                placeholder="Message #general"
                                onkeypress="messageFieldKeyPress(event)"
                            />
                            
                            <!-- Attachment Button -->
                            <button class="input-btn" type="button" aria-label="Upload file">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                          d="M18.364 5.636a4 4 0 010 5.657l-7.071 7.071a4 4 0 01-5.657-5.657l7.07-7.071a2.5 2.5 0 013.536 3.536l-6.364 6.364"/>
                                </svg>
                            </button>
                            
                            <!-- Emoji Button -->
                            <button class="input-btn" type="button" aria-label="Add emoji">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <circle cx="12" cy="12" r="9" stroke-width="1.8"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                          d="M8 15s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01"/>
                                </svg>
                            </button>
                            
                            <!-- Send Button -->
                            <button class="send-btn" onclick="submitMessage()">Send</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================
             STREAM PAGE (Twitch-style)
             Lines 125-150
             Grid layout with search bar and stream cards
             ============================================ -->
        <div id="stream-page" class="page">
            <div class="stream-page">
                
                <!-- Search Bar - Search for streamers -->
                <div class="stream-search-container">
                    <input 
                        type="text" 
                        class="stream-search" 
                        id="stream-search"
                        placeholder="Search for streamers..."
                        onkeyup="filterStreamsByText(this.value)"
                    />
                </div>

                <!-- Streams Grid - Stream cards are inserted here by JavaScript -->
                <div class="streams-grid" id="streams-grid">
                    <!-- Stream cards will be inserted here by JavaScript (see script.js renderStreams function) -->
                </div>
            </div>
        </div>

        <!-- ============================================
             MARKETPLACE PAGE (Facebook-style)
             ============================================ -->
        <div id="marketplace-page" class="page">
            <div class="marketplace-page">
                <div class="marketplace-header">
                    <div>
                        <h1 class="marketplace-title">Marketplace</h1>
                        <p class="marketplace-subtitle">Discover unique listings from the community</p>
                    </div>
                    <div class="marketplace-actions">
                        <div class="marketplace-search">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                      d="M21 21l-4.35-4.35M17 10.5a6.5 6.5 0 11-13 0 6.5 6.5 0 0113 0z"/>
                            </svg>
                            <input 
                                id="marketplace-search"
                                type="text" 
                                placeholder="Search listings, sellers, or categories..."
                                oninput="updateMarketplaceSearch(this.value)"
                                autocomplete="off"
                            />
                        </div>
                        <div class="marketplace-filters">
                            <button type="button" class="marketplace-filter active" data-category="all" onclick="changeMarketplaceCategory('all', this)">
                                All
                            </button>
                            <button type="button" class="marketplace-filter" data-category="tech" onclick="changeMarketplaceCategory('tech', this)">
                                Tech
                            </button>
                            <button type="button" class="marketplace-filter" data-category="creative" onclick="changeMarketplaceCategory('creative', this)">
                                Creative
                            </button>
                            <button type="button" class="marketplace-filter" data-category="lifestyle" onclick="changeMarketplaceCategory('lifestyle', this)">
                                Lifestyle
                            </button>
                        </div>
                    </div>
                </div>

                <div class="marketplace-grid" id="marketplace-grid"></div>
                <div class="marketplace-wallet" id="marketplace-wallet" aria-live="polite"></div>
                <div class="marketplace-empty" id="marketplace-empty">
                    <div class="marketplace-empty-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M3 9l1.8-3.6A2 2 0 016.6 4h10.8a2 2 0 011.8 1.4L21 9v9a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M3 9h18M8 13h1.5a1.5 1.5 0 011.5 1.5V20M14 20v-5.5A1.5 1.5 0 0115.5 13H17"/>
                        </svg>
                    </div>
                    <h2>No listings match your filters</h2>
                    <p>Try adjusting your search or browse another category to discover more items.</p>
                </div>
            </div>
        </div>

        <!-- ============================================
             FEED PAGE (Twitter-style)
             Lines 155-200
             Compose box and scrolling feed
             ============================================ -->
        <div id="feed-page" class="page">
            <div class="feed-page">
                <div class="feed-container">
                    
                    <!-- Compose Box - Create new post -->
                    <div class="compose-box">
                        <div class="compose-header">
                            <div class="compose-avatar">U</div>
                            <div style="flex: 1;">
                                <!-- Post Input Textarea -->
                                <textarea 
                                    class="compose-input" 
                                    id="compose-input"
                                    placeholder="What's happening?"
                                ></textarea>
                            </div>
                        </div>
                        
                        <!-- Compose Actions -->
                        <div class="compose-actions">
                            <!-- Media Icons -->
                            <div class="compose-icons">
                                <button type="button" class="compose-icon" aria-label="Add image">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                              d="M4 6a2 2 0 012-2h12a2 2 0 012 2v12H4V6z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                              d="M4 16l4.5-4.5a2 2 0 012.83 0L18 19M14.5 10.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                                    </svg>
                                </button>
                                <button type="button" class="compose-icon" aria-label="Go live">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <rect x="3" y="5" width="14" height="14" rx="2" ry="2" stroke-width="1.8"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                              d="M17 9l4-2v10l-4-2z"/>
                                    </svg>
                                </button>
                                <button type="button" class="compose-icon" aria-label="Add emoji">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <circle cx="12" cy="12" r="9" stroke-width="1.8"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                              d="M8 15s1.5 2 4 2 4-2 4-2M9 9h.01M15 9h.01"/>
                                    </svg>
                                </button>
                            </div>
                            
                            <!-- Tweet Button -->
                    <button class="compose-btn" onclick="addPost()">Tweet</button>
                        </div>
                    </div>

                    <!-- Posts Container - Posts are inserted here by JavaScript -->
                    <div class="posts-container" id="posts-container">
                        <!-- Posts will be inserted here by JavaScript (see script.js renderPosts function) -->
                    </div>
                </div>
            </div>
        </div>

<!-- ============================================
     EVENTS SECTION (dans la page Feed)
     ============================================ -->
<!-- ============================================
     EVENTS SECTION
     ============================================ -->
<div id="events-page" class="page">
    <div class="events-page">
        <div class="events-container">
            
            <!-- Header des événements -->
            <div class="events-header">
                <h1 class="events-title">Événements</h1>
                <p class="events-subtitle">Découvrez les événements à venir</p>
            </div>

            <!-- Filtres des événements -->
            <div class="events-filters">
                <button type="button" class="event-filter active" data-type="all" onclick="filterEvents('all', this)">
                    Tous
                </button>
                <button type="button" class="event-filter" data-type="meeting" onclick="filterEvents('meeting', this)">
                    Réunions
                </button>
                <button type="button" class="event-filter" data-type="workshop" onclick="filterEvents('workshop', this)">
                    Ateliers
                </button>
                <button type="button" class="event-filter" data-type="social" onclick="filterEvents('social', this)">
                    Social
                </button>
                <button type="button" class="event-filter" data-type="other" onclick="filterEvents('other', this)">
                    Autres
                </button>
            </div>

            <!-- Grille des événements -->
<div class="events-grid" id="events-grid">
    <?php if (!empty($listeEvents)): ?>
        <?php foreach($listeEvents as $event): ?>
            <div class="event-card" data-type="<?php echo htmlspecialchars($event['event_type']); ?>">
                <div class="event-header">
                    <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                    <span class="event-type <?php echo htmlspecialchars($event['event_type']); ?>">
                        <?php echo getEventTypeLabel($event['event_type']); ?>
                    </span>
                </div>
                <p class="event-description"><?php echo htmlspecialchars($event['description']); ?></p>
                <div class="event-dates">
                    <div class="event-date">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <?php echo formatDate($event['start_date']); ?>
                    </div>
                    <?php if ($event['end_date']): ?>
                    <div class="event-date">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Fin: <?php echo formatDate($event['end_date']); ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Affichage du statut -->
                <?php if (isset($event['status'])): ?>
                <div class="event-status-container">
                    <span class="event-status <?php echo htmlspecialchars($event['status']); ?>">
                        <?php echo getStatusLabel($event['status']); ?>
                    </span>
                </div>
                <?php endif; ?>
                
                <!-- BOUTON RÉSERVER -->
                <div class="event-actions">
                    <button class="reserve-btn" 
                            onclick="openReservationModal(
                                '<?php echo $event['id_event']; ?>',
                                '<?php echo htmlspecialchars(addslashes($event['title'])); ?>',
                                '<?php echo formatDate($event['start_date']); ?>',
                                '<?php echo isset($event['available_places']) ? $event['available_places'] : ''; ?>'
                            )">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="18" height="18">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Réserver
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="events-empty">
            <div class="events-empty-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h2>Aucun événement prévu</h2>
            <p>Revenez plus tard pour découvrir de nouveaux événements.</p>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL DE RÉSERVATION -->
<!-- MODAL DE RÉSERVATION -->
<div id="reservationModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Réserver cet événement</h2>
            <button class="close-btn" onclick="closeReservationModal()">&times;</button>
        </div>
        
        <div class="modal-body">
            <!-- Informations sur l'événement -->
            <div class="event-summary">
                <h3 id="modalEventTitle"></h3>
                <div class="event-info">
                    <div class="info-item">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span id="modalEventDate"></span>
                    </div>
                    <div class="info-item" id="placesInfo" style="display: none;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="16" height="16">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span id="modalAvailablePlaces"></span>
                    </div>
                </div>
            </div>
            
            <!-- Formulaire de réservation simplifié -->
            <form id="reservationForm">
                <input type="hidden" id="event_id" name="event_id">
                
                <div class="form-group">
                    <label for="reservation_date">
                        <span class="label-text">Date de réservation</span>
                        <span class="required">*</span>
                    </label>
                    <input type="datetime-local" 
                           id="reservation_date" 
                           name="reservation_date"
                           class="form-control"
                           required>
                    <div class="error-message" id="dateError"></div>
                    <small class="help-text">Sélectionnez la date et l'heure pour votre réservation</small>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="closeReservationModal()">
                        Annuler
                    </button>
                    <button type="button" class="btn btn-primary" onclick="submitReservation()" id="submitBtn">
                        <span class="btn-text">Confirmer la réservation</span>
                        <span class="spinner" style="display: none;"></span>
                    </button>
                </div>
            </form>
            
            <div id="successMessage" class="success-message" style="display: none;"></div>
        </div>
    </div>
</div>

<style>
/* Styles du modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    animation: fadeIn 0.3s;
}

.modal-content {
    background-color: #fff;
    margin: 50px auto;
    padding: 0;
    width: 90%;
    max-width: 500px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    animation: slideIn 0.3s;
}

.modal-header {
    padding: 20px 24px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px 10px 0 0;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
}

.close-btn {
    background: none;
    border: none;
    color: white;
    font-size: 28px;
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background-color 0.3s;
}

.close-btn:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

.modal-body {
    padding: 24px;
}

/* Résumé de l'événement */
.event-summary {
    background: #f8f9fa;
    padding: 16px;
    border-radius: 8px;
    margin-bottom: 24px;
    border-left: 4px solid #667eea;
}

.event-summary h3 {
    margin: 0 0 10px 0;
    color: #333;
    font-size: 1.2rem;
}

.event-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
    font-size: 0.9rem;
}

.info-item svg {
    color: #667eea;
}

/* Formulaire */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #333;
}

.label-text {
    display: inline-block;
    margin-right: 4px;
}

.required {
    color: #e74c3c;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 2px solid #ddd;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.error-message {
    color: #e74c3c;
    font-size: 0.85rem;
    margin-top: 5px;
    display: none;
}

.help-text {
    display: block;
    margin-top: 6px;
    color: #666;
    font-size: 0.85rem;
}

/* Boutons */
.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-secondary {
    background-color: #f8f9fa;
    color: #333;
    border: 1px solid #ddd;
}

.btn-secondary:hover {
    background-color: #e9ecef;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
}

/* Bouton Réserver dans la carte */
.reserve-btn {
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s;
    width: 100%;
    margin-top: 15px;
}

.reserve-btn:hover {
    background: linear-gradient(135deg, #27ae60, #219653);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(46, 204, 113, 0.3);
}

.reserve-btn svg {
    width: 18px;
    height: 18px;
}

/* Message de succès */
.success-message {
    background-color: #d4edda;
    color: #155724;
    padding: 12px;
    border-radius: 6px;
    margin-top: 20px;
    border: 1px solid #c3e6cb;
    animation: fadeIn 0.3s;
}

/* Spinner */
.spinner {
    display: inline-block;
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: white;
    animation: spin 1s ease-in-out infinite;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 600px) {
    .modal-content {
        margin: 20px auto;
        width: 95%;
    }
    
    .modal-header {
        padding: 16px 20px;
    }
    
    .modal-header h2 {
        font-size: 1.3rem;
    }
    
    .modal-body {
        padding: 20px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
}
</style>

<script>
// Variables globales
let currentEventId = null;

// Ouvrir le modal de réservation
function openReservationModal(eventId, eventTitle, eventDate, availablePlaces) {
    currentEventId = eventId;
    
    // Mettre à jour les informations de l'événement
    document.getElementById('modalEventTitle').textContent = eventTitle;
    document.getElementById('modalEventDate').textContent = eventDate;
    
    // Mettre à jour l'ID de l'événement dans le formulaire
    document.getElementById('event_id').value = eventId;
    
    // Afficher les places disponibles si disponibles
    if (availablePlaces && availablePlaces !== '') {
        document.getElementById('placesInfo').style.display = 'flex';
        const placesText = parseInt(availablePlaces) > 0 
            ? `${availablePlaces} places disponibles` 
            : 'Complet';
        const placesColor = parseInt(availablePlaces) > 0 ? '#2ecc71' : '#e74c3c';
        document.getElementById('modalAvailablePlaces').textContent = placesText;
        document.getElementById('modalAvailablePlaces').style.color = placesColor;
    } else {
        document.getElementById('placesInfo').style.display = 'none';
    }
    
    // Configurer la date minimale
    const now = new Date();
    const minDate = now.toISOString().slice(0, 16);
    document.getElementById('reservation_date').min = minDate;
    
    // Réinitialiser le formulaire
    resetForm();
    
    // Afficher le modal
    document.getElementById('reservationModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Focus sur le champ date
    setTimeout(() => {
        document.getElementById('reservation_date').focus();
    }, 100);
}

// Fermer le modal
function closeReservationModal() {
    document.getElementById('reservationModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    currentEventId = null;
}

// Réinitialiser le formulaire
function resetForm() {
    document.getElementById('reservation_date').value = '';
    
    // Cacher les messages d'erreur
    hideErrors();
    
    // Cacher le message de succès
    document.getElementById('successMessage').style.display = 'none';
    
    // Réactiver le bouton de soumission
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = false;
    submitBtn.querySelector('.btn-text').textContent = 'Confirmer la réservation';
    submitBtn.querySelector('.spinner').style.display = 'none';
}

// Cacher tous les messages d'erreur
function hideErrors() {
    document.querySelectorAll('.error-message').forEach(el => {
        el.style.display = 'none';
    });
}

// Afficher une erreur
function showError(fieldId, message) {
    const errorElement = document.getElementById(fieldId + 'Error');
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = 'block';
    }
}

// Valider le formulaire (plus simple maintenant)
function validateForm() {
    let isValid = true;
    
    // Cacher les erreurs précédentes
    hideErrors();
    
    // Validation de la date seulement
    const reservationDate = document.getElementById('reservation_date').value;
    if (!reservationDate) {
        showError('date', 'Veuillez sélectionner une date');
        isValid = false;
    } else {
        const selectedDate = new Date(reservationDate);
        const now = new Date();
        if (selectedDate < now) {
            showError('date', 'La date ne peut pas être dans le passé');
            isValid = false;
        }
    }
    
    return isValid;
}

// Soumettre la réservation
async function submitReservation() {
    // Valider le formulaire
    if (!validateForm()) {
        return;
    }
    
    // Désactiver le bouton et afficher le spinner
    const submitBtn = document.getElementById('submitBtn');
    const btnText = submitBtn.querySelector('.btn-text');
    const spinner = submitBtn.querySelector('.spinner');
    
    submitBtn.disabled = true;
    btnText.textContent = 'Traitement en cours...';
    spinner.style.display = 'inline-block';
    
    try {
        // Préparer les données
        const formData = new FormData(document.getElementById('reservationForm'));
        // ID utilisateur statique = 1
        formData.append('user_id', '1');
        // Statut par défaut = pending
        formData.append('status', 'pending');
        
        // Envoyer la requête AJAX
        const response = await fetch('reservation_add.php', {
            method: 'POST',
            body: formData
        });
        
        // Vérifier la réponse
        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }
        
        const result = await response.text();
        
        // Vérifier si c'est un succès
        if (result.includes('success') || result.trim() === 'success') {
            // Afficher le message de succès
            const successMessage = document.getElementById('successMessage');
            successMessage.textContent = 'Réservation effectuée avec succès !';
            successMessage.style.display = 'block';
            
            // Réinitialiser le formulaire
            setTimeout(() => {
                closeReservationModal();
                // Optionnel: Recharger la page
                window.location.reload();
            }, 1500);
        } else {
            // Essayer de parser comme JSON pour les messages d'erreur
            try {
                const errorData = JSON.parse(result);
                throw new Error(errorData.message || 'Erreur inconnue');
            } catch (e) {
                throw new Error('La réservation a échoué : ' + result);
            }
        }
        
    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la réservation : ' + error.message);
        
        // Réactiver le bouton
        submitBtn.disabled = false;
        btnText.textContent = 'Confirmer la réservation';
        spinner.style.display = 'none';
    }
}

// Fermer le modal en cliquant à l'extérieur
window.onclick = function(event) {
    const modal = document.getElementById('reservationModal');
    if (event.target === modal) {
        closeReservationModal();
    }
};

// Fermer avec la touche Échap
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeReservationModal();
    }
});

// Validation en temps réel de la date
document.getElementById('reservation_date').addEventListener('change', function() {
    if (this.value) {
        const selectedDate = new Date(this.value);
        const now = new Date();
        if (selectedDate < now) {
            showError('date', 'La date ne peut pas être dans le passé');
        } else {
            hideErrors();
        }
    }
});

// Validation au moment de la soumission avec Enter
document.getElementById('reservationForm').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        submitReservation();
    }
});
</script>
        </div>
    </div>
</div>
<style>/* ============================================
   STYLES POUR LA PAGE ÉVÉNEMENTS
   ============================================ */

.events-page {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
    min-height: calc(100vh - 60px);
}

.events-header {
    text-align: center;
    margin-bottom: 30px;
    padding-top: 20px;
}

.events-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 10px;
}

.events-subtitle {
    color: #b9bbbe;
    font-size: 1.1rem;
}

.events-filters {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.event-filter {
    padding: 8px 16px;
    border: 1px solid #4f545c;
    background: #36393f;
    color: #b9bbbe;
    border-radius: 20px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 0.9rem;
}

.event-filter:hover {
    background: #4f545c;
    border-color: #5d6269;
}

.event-filter.active {
    background: #5865f2;
    border-color: #5865f2;
    color: white;
}

.events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.event-card {
    background: #36393f;
    border-radius: 8px;
    padding: 20px;
    border: 1px solid #4f545c;
    transition: all 0.2s;
    cursor: pointer;
    position: relative;
}

.event-card:hover {
    transform: translateY(-2px);
    border-color: #5865f2;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.event-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
    gap: 10px;
}

.event-title {
    font-size: 1.2rem;
    font-weight: 600;
    color: #fff;
    margin: 0;
    flex: 1;
    line-height: 1.3;
}

.event-type {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    white-space: nowrap;
}

.event-type.meeting { background: #5865f2; color: white; }
.event-type.workshop { background: #eb459e; color: white; }
.event-type.social { background: #57f287; color: #000; }
.event-type.other { background: #faa61a; color: #000; }

.event-description {
    color: #b9bbbe;
    margin-bottom: 15px;
    line-height: 1.4;
    font-size: 0.95rem;
}

.event-dates {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 15px;
    padding-top: 15px;
    border-top: 1px solid #4f545c;
}

.event-date {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #b9bbbe;
    font-size: 0.9rem;
}

.event-date svg {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

.event-status-container {
    display: flex;
    justify-content: flex-end;
    margin-top: 10px;
}

.event-status {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.event-status.active { background: #57f287; color: #000; }
.event-status.cancelled { background: #ed4245; color: white; }
.event-status.completed { background: #b9bbbe; color: #000; }
.event-status.upcoming { background: #faa61a; color: #000; }

.events-empty {
    text-align: center;
    padding: 60px 20px;
    color: #72767d;
    grid-column: 1 / -1;
}

.events-empty-icon svg {
    width: 64px;
    height: 64px;
    margin-bottom: 20px;
    color: #72767d;
}

.events-empty h2 {
    font-size: 1.5rem;
    margin-bottom: 10px;
    color: #b9bbbe;
}

.events-empty p {
    font-size: 1rem;
    color: #72767d;
}

/* Responsive */
@media (max-width: 768px) {
    .events-grid {
        grid-template-columns: 1fr;
    }
    
    .events-header {
        text-align: left;
    }
    
    .events-filters {
        justify-content: flex-start;
    }
    
    .event-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    
    .event-type {
        align-self: flex-start;
    }
}</style>

<script>// ============================================
// FONCTIONS POUR LES ÉVÉNEMENTS
// ============================================

function filterEvents(type, element) {
    // Mettre à jour les boutons actifs
    document.querySelectorAll('.event-filter').forEach(btn => {
        btn.classList.remove('active');
    });
    element.classList.add('active');
    
    // Filtrer les événements
    const eventCards = document.querySelectorAll('.event-card');
    let visibleCount = 0;
    
    eventCards.forEach(card => {
        const eventType = card.getAttribute('data-type');
        
        if (type === 'all' || eventType === type) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Afficher/masquer le message "Aucun événement"
    const eventsEmpty = document.getElementById('events-empty');
    if (eventsEmpty) {
        if (visibleCount === 0) {
            eventsEmpty.style.display = 'block';
        } else {
            eventsEmpty.style.display = 'none';
        }
    }
}

// Initialiser les filtres au chargement
document.addEventListener('DOMContentLoaded', function() {
    // S'assurer que tous les événements sont visibles au départ
    filterEvents('all', document.querySelector('.event-filter.active'));
});</script>

    </div>

    <!-- ============================================
         JAVASCRIPT
         Lines 201-210
         Link to external JavaScript file
         ============================================ -->
    <script src="script.js"></script>
</body>
</html>

