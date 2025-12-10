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
            <div class="nav-item active" onclick="switchPage('servers')">
                <div class="nav-icon">💬</div>
                <div class="nav-label">Servers</div>
            </div>
            
            <!-- Stream Button - Opens Twitch-style streaming page -->
            <div class="nav-item" onclick="switchPage('stream')">
                <div class="nav-icon">📺</div>
                <div class="nav-label">Stream</div>
            </div>
        
            <!-- Feed Button - Opens Twitter-style feed page -->
            <div class="nav-item" onclick="switchPage('feed')">
                <div class="nav-icon">🐦</div>
                <div class="nav-label">Feed</div>
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
                    <div class="server-icon home active" onclick="selectServer('home', this)" title="Home">
                        💬
                    </div>
                    <div class="divider"></div>
                    
                    <!-- General Server Icon -->
                    <div class="server-icon green" onclick="selectServer('general', this)" title="General">
                        💬
                    </div>
                    
                    <!-- Gaming Server Icon -->
                    <div class="server-icon purple" onclick="selectServer('gaming', this)" title="Gaming">
                        🎮
                    </div>
                    
                    <!-- Music Server Icon -->
                    <div class="server-icon pink" onclick="selectServer('music', this)" title="Music">
                        🎵
                    </div>
                    
                    <!-- Coding Server Icon -->
                    <div class="server-icon" style="background-color: #faa61a;" onclick="selectServer('coding', this)" title="Coding">
                        💻
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
                            <div class="channel-item active" onclick="selectChannel('general', this)"><span>general</span></div>
                            
                            <!-- Announcements Channel -->
                            <div class="channel-item" onclick="selectChannel('announcements', this)"><span>announcements</span></div>
                            
                            <!-- Random Channel -->
                            <div class="channel-item" onclick="selectChannel('random', this)"><span>random</span></div>
                        </div>
                    </div>
                    
                    <!-- User Info Footer -->
                    <div class="user-info">
                        <div class="user-avatar" id="user-avatar" onclick="openProfilePage()">U</div>
                        <div class="user-details">
                            <div class="user-name" id="user-name">Username</div>
                            <div class="user-id" id="user-id">#1234</div>
                        </div>
                        <button class="logout-btn" onclick="handleLogout()" title="Logout" style="background: none; border: none; color: #8e9297; cursor: pointer; padding: 4px; border-radius: 4px; transition: all 0.2s;">
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
                            <button class="input-btn">➕</button>
                            
                            <!-- Message Input Field -->
                            <input 
                                type="text" 
                                class="input-field" 
                                id="message-input"
                                placeholder="Message #general"
                                onkeypress="handleKeyPress(event)"
                            />
                            
                            <!-- Attachment Button -->
                            <button class="input-btn">📎</button>
                            
                            <!-- Emoji Button -->
                            <button class="input-btn">😊</button>
                            
                            <!-- Send Button -->
                            <button class="send-btn" onclick="sendMessage()">Send</button>
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
                        onkeyup="searchStreams(event)"
                    />
                </div>

                <!-- Streams Grid - Stream cards are inserted here by JavaScript -->
                <div class="streams-grid" id="streams-grid">
                    <!-- Stream cards will be inserted here by JavaScript (see script.js renderStreams function) -->
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
                                <span class="compose-icon">🖼️</span>
                                <span class="compose-icon">📹</span>
                                <span class="compose-icon">😊</span>
                            </div>
                            
                            <!-- Tweet Button -->
                            <button class="compose-btn" onclick="createPost()">Tweet</button>
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
    <script src="script.js"></script>
</body>
</html>

