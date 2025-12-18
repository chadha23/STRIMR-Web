<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../../Controller/ServerController.php';
require_once __DIR__ . '/../../../Controller/MessageController.php';
require_once __DIR__ . '/../../../Controller/AIController.php';
require_once __DIR__ . '/../../../Model/Message.php';

$serverC  = new ServerController();
$messageC = new MessageController();
$aiC      = new AIController();

$servers = $serverC->listServers();

// Check if AI Helper server is selected
$isAIServer = isset($_GET['server']) && $_GET['server'] === 'ai';
$aiResponse = '';
$userMessage = '';

// Handle AI chat submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAIServer && isset($_POST['ai_message'])) {
    $userMessage = trim($_POST['ai_message']);
    if ($userMessage !== '') {
        $aiResponse = $aiC->chat($userMessage);
    }
    // Don't redirect for AI chat - we want to show the response
}

// Handle regular message submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isAIServer && isset($_POST['message_content'], $_POST['server_id'])) {
    $content   = trim($_POST['message_content']);
    $server_id = (int)$_POST['server_id'];

    if ($content !== '' && $server_id > 0) {
        $message = new Message(null, $server_id, $content);
        if ($messageC->addMessage($message)) {
            header("Location: index.php?server=" . $server_id);
            exit();
        }
    }
}

// Determine active server
$active_server_id = 0;
if (!$isAIServer) {
    $active_server_id = isset($_GET['server'])
        ? (int)$_GET['server']
        : (!empty($servers) ? (int)$servers[0]['id'] : 0);
}

$messages = $active_server_id > 0 ? $messageC->getMessagesByServer($active_server_id) : [];

// Find active server name
$active_server_name = $isAIServer ? "AI Helper" : "Server Name";
if (!$isAIServer && !empty($servers)) {
    foreach ($servers as $srv) {
        if ((int)$srv['id'] === $active_server_id) {
            $active_server_name = htmlspecialchars($srv['name']);
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isAIServer ? 'AI Helper' : 'Servers'; ?> - STRIMR</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        /* AI Server Specific Styles */
        .server-icon.ai-server {
            background: linear-gradient(135deg, #9c27b0, #e91e63) !important;
            position: relative;
            overflow: hidden;
        }
        .server-icon.ai-server::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            animation: aiShimmer 3s infinite;
        }
        @keyframes aiShimmer {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }
        .server-icon.ai-server span {
            font-size: 20px;
        }
        
        /* AI Chat Styles */
        .ai-chat-container {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        .ai-welcome {
            text-align: center;
            padding: 40px 20px;
            color: #a0a0a0;
        }
        .ai-welcome h3 {
            color: #ffffff;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .ai-welcome p {
            font-size: 14px;
            line-height: 1.6;
        }
        .ai-welcome .ai-icon {
            font-size: 60px;
            margin-bottom: 20px;
            animation: aiPulse 2s infinite;
        }
        @keyframes aiPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }
        
        .ai-message {
            background: linear-gradient(135deg, rgba(156, 39, 176, 0.15), rgba(233, 30, 99, 0.1)) !important;
            border-left: 3px solid #9c27b0;
        }
        .ai-message .message-avatar {
            background: linear-gradient(135deg, #9c27b0, #e91e63) !important;
        }
        .ai-message .message-username {
            color: #e91e63 !important;
        }
        
        .user-message {
            background: linear-gradient(135deg, rgba(88, 101, 242, 0.15), rgba(67, 181, 129, 0.1)) !important;
            border-left: 3px solid #5865f2;
        }
        
        .ai-typing {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 15px 20px;
            color: #a0a0a0;
            font-style: italic;
        }
        .ai-typing .dots {
            display: flex;
            gap: 4px;
        }
        .ai-typing .dot {
            width: 8px;
            height: 8px;
            background: #9c27b0;
            border-radius: 50%;
            animation: typingDot 1.4s infinite;
        }
        .ai-typing .dot:nth-child(2) { animation-delay: 0.2s; }
        .ai-typing .dot:nth-child(3) { animation-delay: 0.4s; }
        @keyframes typingDot {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
            30% { transform: translateY(-10px); opacity: 1; }
        }
        
        .ai-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 15px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .ai-suggestion {
            padding: 8px 16px;
            background: rgba(156, 39, 176, 0.2);
            border: 1px solid rgba(156, 39, 176, 0.3);
            border-radius: 20px;
            color: #e0e0e0;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .ai-suggestion:hover {
            background: rgba(156, 39, 176, 0.4);
            transform: translateY(-2px);
        }
        
        .ai-input-container {
            display: flex;
            gap: 10px;
            width: 100%;
        }
        .ai-input-container input {
            flex: 1;
        }
        .ai-send-btn {
            background: linear-gradient(135deg, #9c27b0, #e91e63) !important;
        }
        .ai-send-btn:hover {
            background: linear-gradient(135deg, #ab47bc, #ec407a) !important;
        }
    </style>
</head>
<body>
    <nav class="top-nav" aria-label="Primary navigation">
        <div class="nav-container">
            <a class="nav-item active" id="servers-nav" href="../servers/index.php" data-page-target="servers-page">
                <div class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M7 8h10M7 12h6m8-1a9 9 0 10-4.5 7.8l3.6 1.2a1 1 0 001.3-1.1l-.6-3A8.9 8.9 0 0021 11z"/>
                    </svg>
                </div>
                <span class="nav-label">Servers</span>
            </a>
            <a class="nav-item" href="../stream/index.html" data-page-target="stream-page">
                <div class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M4 6a2 2 0 012-2h12a2 2 0 012 2v9a2 2 0 01-2 2h-3l-4.5 3a1 1 0 01-1.5-.86V17H6a2 2 0 01-2-2V6z"/>
                    </svg>
                </div>
                <span class="nav-label">Stream</span>
            </a>
            <a class="nav-item" href="../marketplace/index.html" data-page-target="marketplace-page">
                <div class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M3 9l1.8-3.6A2 2 0 016.6 4h10.8a2 2 0 011.8 1.4L21 9v9a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M3 9h18M8 13h1.5a1.5 1.5 0 011.5 1.5V20M14 20v-5.5A1.5 1.5 0 0115.5 13H17"/>
                    </svg>
                </div>
                <span class="nav-label">Marketplace</span>
            </a>
            <a class="nav-item" href="../feed/index.php" data-page-target="feed-page">
                <div class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M21 5.92a6.55 6.55 0 01-1.89.52 3.3 3.3 0 001.45-1.82 6.59 6.59 0 01-2.07.8 3.28 3.28 0 00-5.6 2.24 3.4 3.4 0 00.08.75A9.31 9.31 0 013 5.16a3.29 3.29 0 001.02 4.38 3.23 3.23 0 01-1.48-.41v.04a3.29 3.29 0 002.63 3.22 3.3 3.3 0 01-1.47.06 3.29 3.29 0 003.07 2.28A6.58 6.58 0 013 17.54 9.29 9.29 0 008.05 19c6.29 0 9.73-5.22 9.73-9.75q0-.23-.01-.45A6.97 6.97 0 0021 5.92z"/>
                    </svg>
                </div>
                <span class="nav-label">Feed</span>
            </a>
            <a class="nav-item" href="../events/index.html" data-page-target="events-page">
                <div class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <span class="nav-label">Events</span>
            </a>
        </div>
    </nav>

    <main class="main-wrapper">
        <section id="servers-page" class="page active" data-template-section="servers">
            <div class="servers-page">
                <!-- Servers Sidebar (Left) -->
                <aside class="servers-sidebar" aria-label="Servers list">
                    <button class="server-icon home <?php echo (!$isAIServer && $active_server_id === 0) ? 'active' : ''; ?>" type="button" aria-pressed="true" onclick="window.location='index.php'">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M4 10.5L12 4l8 6.5V20a1 1 0 01-1 1h-5v-6h-4v6H5a1 1 0 01-1-1z"/>
                        </svg>
                    </button>
                    
                    <!-- AI Helper Server -->
                    <button
                        class="server-icon ai-server <?php echo $isAIServer ? 'active' : ''; ?>"
                        type="button"
                        title="AI Helper - Chat with AI"
                        onclick="window.location='index.php?server=ai'">
                        <span>­ƒñû</span>
                    </button>
                    
                    <div class="divider" aria-hidden="true"></div>

                    <!-- Dynamic servers from database -->
                    <?php if (!empty($servers)): ?>
                        <?php foreach ($servers as $srv): ?>
                            <button
                                class="server-icon <?php echo (!$isAIServer && (int)$srv['id'] === $active_server_id) ? 'active' : ''; ?>"
                                type="button"
                                title="<?php echo htmlspecialchars($srv['name']); ?>"
                                onclick="window.location='index.php?server=<?php echo $srv['id']; ?>'">
                                <span><?php echo strtoupper(substr($srv['name'], 0, 2)); ?></span>
                            </button>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </aside>

                <!-- Channels Sidebar (Middle) -->
                <div class="channels-sidebar">
                    <div class="channels-header">
                        <h2 id="server-name"><?php echo $active_server_name; ?></h2>
                    </div>
                    <div class="channels-list">
                        <div class="channel-section">
                            <div class="channel-section-title"><?php echo $isAIServer ? 'AI Chat' : 'Text Channels'; ?></div>
                            <button class="channel-item active" type="button" aria-pressed="true">
                                <span><?php echo $isAIServer ? 'chat-with-ai' : 'general'; ?></span>
                            </button>
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

                <!-- Chat Area (Right) -->
                <section class="chat-area" aria-live="polite">
                    <div class="content-header">
                        <h2 id="channel-name"># <?php echo $isAIServer ? 'chat-with-ai' : 'general'; ?></h2>
                    </div>
                    <div class="content-body">
                        <div class="messages-container" id="messages-container">
                            <?php if ($isAIServer): ?>
                                <!-- AI Chat Interface -->
                                <?php if (empty($userMessage)): ?>
                                    <div class="ai-welcome">
                                        <div class="ai-icon">­ƒñû</div>
                                        <h3>Hello! I'm your AI Assistant</h3>
                                        <p>I'm powered by GPT-2 and I'm here to help! Ask me anything or just chat.<br>
                                        Type a message below to get started.</p>
                                    </div>
                                <?php else: ?>
                                    <!-- User's message -->
                                    <article class="message user-message">
                                        <div class="message-avatar">U</div>
                                        <div class="message-content">
                                            <header class="message-header">
                                                <span class="message-username">You</span>
                                                <span class="message-time">Just now</span>
                                            </header>
                                            <p class="message-text"><?php echo htmlspecialchars($userMessage); ?></p>
                                        </div>
                                    </article>
                                    
                                    <!-- AI's response -->
                                    <article class="message ai-message">
                                        <div class="message-avatar">­ƒñû</div>
                                        <div class="message-content">
                                            <header class="message-header">
                                                <span class="message-username">AI Helper</span>
                                                <span class="message-time">Just now</span>
                                            </header>
                                            <p class="message-text"><?php echo htmlspecialchars($aiResponse); ?></p>
                                        </div>
                                    </article>
                                <?php endif; ?>
                                
                            <?php elseif (!empty($messages)): ?>
                                <?php foreach ($messages as $msg): ?>
                                    <article class="message">
                                        <div class="message-avatar">U</div>
                                        <div class="message-content">
                                            <header class="message-header">
                                                <span class="message-username">User<?php echo $msg['id']; ?></span>
                                                <?php if (isset($msg['created_at'])): ?>
                                                    <span class="message-time"><?php echo date('H:i', strtotime($msg['created_at'])); ?></span>
                                                <?php endif; ?>
                                            </header>
                                            <p class="message-text">
                                                <?php echo htmlspecialchars($msg['content']); ?>
                                            </p>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <article class="message">
                                    <div class="message-content">
                                        <p class="message-text">No messages yet. Be the first to say something!</p>
                                    </div>
                                </article>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($isAIServer): ?>
                        <!-- AI Suggestions -->
                        <div class="ai-suggestions">
                            <button type="button" class="ai-suggestion" onclick="setAIMessage('Hello! How are you?')">­ƒæï Say Hello</button>
                            <button type="button" class="ai-suggestion" onclick="setAIMessage('What can you help me with?')">ÔØô What can you do?</button>
                            <button type="button" class="ai-suggestion" onclick="setAIMessage('Tell me something interesting')">­ƒÆí Tell me something</button>
                            <button type="button" class="ai-suggestion" onclick="setAIMessage('Help me with coding')">­ƒÆ╗ Coding help</button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="input-area">
                        <div class="input-container">
                            <?php if ($isAIServer): ?>
                                <form method="POST" class="ai-input-container" id="ai-form">
                                    <input
                                        type="text"
                                        name="ai_message"
                                        id="ai-input"
                                        class="input-field"
                                        placeholder="Ask the AI anything..."
                                        required
                                        autocomplete="off"
                                    >
                                    <button type="submit" class="send-btn ai-send-btn">Send</button>
                                </form>
                            <?php else: ?>
                                <form method="POST" style="display:flex; width:100%; gap:8px;">
                                    <input type="hidden" name="server_id" value="<?php echo $active_server_id; ?>">
                                    <input
                                        type="text"
                                        name="message_content"
                                        class="input-field"
                                        placeholder="Message #general"
                                        required
                                        autocomplete="off"
                                    >
                                    <button type="submit" class="send-btn">Send</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </div>
        </section>
    </main>

    <script>
        // Set AI message from suggestion buttons
        function setAIMessage(message) {
            document.getElementById('ai-input').value = message;
            document.getElementById('ai-input').focus();
        }
        
        // Scroll to bottom of messages
        function scrollToBottom() {
            const messagesContainer = document.getElementById('messages-container');
            if (messagesContainer) {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        }
        scrollToBottom();
        
        <?php if ($isAIServer): ?>
        // AI Chat with AJAX
        const aiForm = document.getElementById('ai-form');
        const aiInput = document.getElementById('ai-input');
        const messagesContainer = document.getElementById('messages-container');
        
        // Store conversation history
        let conversationHistory = [];
        
        // Check if there's already a conversation from PHP
        <?php if (!empty($userMessage)): ?>
        conversationHistory.push({
            type: 'user',
            message: <?php echo json_encode($userMessage); ?>
        });
        conversationHistory.push({
            type: 'ai',
            message: <?php echo json_encode($aiResponse); ?>
        });
        <?php endif; ?>
        
        aiForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const message = aiInput.value.trim();
            if (!message) return;
            
            // Clear input
            aiInput.value = '';
            
            // Remove welcome message if present
            const welcomeDiv = messagesContainer.querySelector('.ai-welcome');
            if (welcomeDiv) {
                welcomeDiv.remove();
            }
            
            // Add user message to UI
            addMessageToUI('user', message);
            conversationHistory.push({ type: 'user', message: message });
            
            // Show typing indicator
            const typingIndicator = showTypingIndicator();
            
            try {
                // Send to AI endpoint
                const response = await fetch('ai_chat.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ message: message })
                });
                
                const data = await response.json();
                
                // Remove typing indicator
                typingIndicator.remove();
                
                if (data.success && data.message) {
                    addMessageToUI('ai', data.message);
                    conversationHistory.push({ type: 'ai', message: data.message });
                } else {
                    addMessageToUI('ai', 'Sorry, I encountered an error. Please try again!');
                }
            } catch (error) {
                typingIndicator.remove();
                addMessageToUI('ai', 'Sorry, I\'m having trouble connecting. Please try again!');
                console.error('AI Chat Error:', error);
            }
            
            scrollToBottom();
            aiInput.focus();
        });
        
        function addMessageToUI(type, message) {
            const article = document.createElement('article');
            article.className = type === 'ai' ? 'message ai-message' : 'message user-message';
            
            const now = new Date();
            const time = now.getHours().toString().padStart(2, '0') + ':' + 
                        now.getMinutes().toString().padStart(2, '0');
            
            article.innerHTML = `
                <div class="message-avatar">${type === 'ai' ? '­ƒñû' : 'U'}</div>
                <div class="message-content">
                    <header class="message-header">
                        <span class="message-username">${type === 'ai' ? 'AI Helper' : 'You'}</span>
                        <span class="message-time">${time}</span>
                    </header>
                    <p class="message-text">${escapeHtml(message)}</p>
                </div>
            `;
            
            messagesContainer.appendChild(article);
            scrollToBottom();
        }
        
        function showTypingIndicator() {
            const typing = document.createElement('div');
            typing.className = 'ai-typing';
            typing.innerHTML = `
                <div class="message-avatar" style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#9c27b0,#e91e63);display:flex;align-items:center;justify-content:center;">­ƒñû</div>
                <span>AI is thinking</span>
                <div class="dots">
                    <div class="dot"></div>
                    <div class="dot"></div>
                    <div class="dot"></div>
                </div>
            `;
            messagesContainer.appendChild(typing);
            scrollToBottom();
            return typing;
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Auto-focus input
        aiInput?.focus();
        <?php endif; ?>
    </script>
</body>
</html>
