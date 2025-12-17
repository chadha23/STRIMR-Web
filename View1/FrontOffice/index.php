<?php
require_once __DIR__ . "/../../controllers/ServerController.php";
require_once __DIR__ . "/../../models/Server.php";
require_once __DIR__ . "/../../controllers/MessageController.php";
require_once __DIR__ . "/../../models/Message.php";

$serverC = new ServerController();
$servers = $serverC->listServers();

$messageC = new MessageController();

// Envoi d'un message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_content'], $_POST['server_id'])) {
    $content = trim($_POST['message_content']);
    $server_id = (int)$_POST['server_id'];

    if ($content !== '') {
        $message = new Message(null, $server_id, $content);
        $messageC->addMessage($message);
        header("Location: " . $_SERVER['PHP_SELF'] . "?server=" . $server_id);
        exit();
    }
}

// Récupérer le serveur actif
$active_server_id = isset($_GET['server']) ? (int)$_GET['server'] : ($servers[0]['id'] ?? 0);
$messages = $messageC->getMessagesByServer($active_server_id);

// Trouver le nom du serveur actif
$active_server_name = "Server Name";
foreach ($servers as $srv) {
    if ($srv['id'] == $active_server_id) {
        $active_server_name = htmlspecialchars($srv['name']);
        break;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discord Clone - Simple Template</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="top-nav">
        <div class="nav-container">
            <div class="nav-item active" onclick="showPage('servers', this)">
                <div class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M7 8h10M7 12h6m8-1a9 9 0 10-4.5 7.8l3.6 1.2a1 1 0 001.3-1.1l-.6-3A8.9 8.9 0 0021 11z"/>
                    </svg>
                </div>
                <div class="nav-label">Servers</div>
            </div>
            <div class="nav-item" onclick="showPage('stream', this)">
                <div class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M4 6a2 2 0 012-2h12a2 2 0 012 2v9a2 2 0 01-2 2h-3l-4.5 3a1 1 0 01-1.5-.86V17H6a2 2 0 01-2-2V6z"/>
                    </svg>
                </div>
                <div class="nav-label">Stream</div>
            </div>
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
            <div class="nav-item" onclick="showPage('feed', this)">
                <div class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M21 5.92a6.55 6.55 0 01-1.89.52 3.3 3.3 0 001.45-1.82 6.59 6.59 0 01-2.07.8 3.28 3.28 0 00-5.6 2.24 3.4 3.4 0 00.08.75A9.31 9.31 0 013 5.16a3.29 3.29 0 001.02 4.38 3.23 3.23 0 01-1.48-.41v.04a3.29 3.29 0 002.63 3.22 3.3 3.3 0 01-1.47.06 3.29 3.29 0 003.07 2.28A6.58 6.58 0 013 17.54 9.29 9.29 0 008.05 19c6.29 0 9.73-5.22 9.73-9.75q0-.23-.01-.45A6.97 6.97 0 0021 5.92z"/>
                    </svg>
                </div>
                <div class="nav-label">Feed</div>
            </div>
        </div>
    </nav>

    <div class="main-wrapper">
        <div id="servers-page" class="page active">
            <div class="servers-page">

                <!-- === SIDEBAR SERVEURS (gauche) === -->
                <div class="servers-sidebar">
                    <div class="server-icon home active" title="Home">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M4 10.5L12 4l8 6.5V20a1 1 0 01-1 1h-5v-6h-4v6H5a1 1 0 01-1-1z"/>
                        </svg>
                    </div>
                    <div class="divider"></div>

                    <!-- SERVEURS DYNAMIQUES -->
                    <?php foreach ($servers as $srv): ?>
                        <div class="server-icon <?= $srv['id'] == $active_server_id ? 'active' : '' ?>"
                             title="<?= htmlspecialchars($srv['name']) ?>"
                             onclick="window.location='?server=<?= $srv['id'] ?>'">
                            <span><?= strtoupper(substr($srv['name'], 0, 2)) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- === SIDEBAR SALONS (milieu) === -->
                <div class="channels-sidebar">
                    <div class="channels-header">
                        <h2 id="server-name"><?= $active_server_name ?></h2>
                    </div>

                    <div class="channels-list">
                        <div class="channel-section">
                            <div class="channel-section-title">Text Channels</div>
                            <div class="channel-item active"><span># general</span></div>
                            <div class="channel-item"><span># announcements</span></div>
                            <div class="channel-item"><span># random</span></div>
                        </div>
                    </div>

                    <div class="user-info">
                        <div class="user-avatar">U</div>
                        <div class="user-details">
                            <div class="user-name">Username</div>
                            <div class="user-id">#1234</div>
                        </div>
                    </div>
                </div>

                <!-- === ZONE CHAT (droite) === -->
                <div class="chat-area">
                    <div class="content-header">
                        <h2># general</h2>
                    </div>

                    <div class="content-body">
                        <div class="messages-container" id="messages-container">
                            <?php foreach ($messages as $msg): ?>
                                <div class="message">
                                    <div class="message-avatar">U</div>
                                    <div class="message-info">
                                        <div class="message-user">User<?= $msg['id'] ?></div>
                                        <div class="message-content"><?= htmlspecialchars($msg['content']) ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Formulaire d'envoi -->
                    <div class="input-area">
                        <div class="input-container">
                            <form method="POST" style="display:flex; width:100%; gap:8px;">
                                <input type="hidden" name="server_id" value="<?= $active_server_id ?>">
                                <input type="text" name="message_content" class="input-field" placeholder="Message #general" required autocomplete="off">
                                <button type="submit" class="send-btn">Send</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Les autres pages (stream, marketplace, feed) restent 100% identiques -->
        <!-- ... (tu les gardes exactement comme dans ton template d'origine) ... -->

    </div>

    <script src="../controllers/script.js"></script>
</body>
</html>

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
    </div>

    <!-- ============================================
         JAVASCRIPT
         Lines 201-210
         Link to external JavaScript file
         ============================================ -->
    <script src="../controllers/script.js"></script>
</body>
</html>

