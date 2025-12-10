/* ============================================
   SCRIPT.JS
   ============================================
   This file contains ALL JavaScript functionality.
   
   FILE STRUCTURE:
   - Lines 1-50: Global Variables & State
   - Lines 51-100: Mock Data (for testing)
   - Lines 101-200: Page Navigation Functions
   - Lines 201-400: Servers Page Functions (Discord-style)
   - Lines 401-500: Stream Page Functions (Twitch-style)
   - Lines 501-600: Feed Page Functions (Twitter-style)
   - Lines 601-650: Initialization
   ============================================ */

// ============================================
// GLOBAL STATE VARIABLES
// Lines 1-50
// These variables store the current state of the application
// ============================================
let currentPage = 'servers';      // Which page is currently active: 'servers', 'stream', or 'feed'
let currentServer = 'general';    // Which server is selected (for servers page)
let currentChannel = 'general';   // Which channel is selected (for servers page)
let messages = [];                // Array to store chat messages
let posts = [];                   // Array to store feed posts
let streams = [];                 // Array to store stream data

// ============================================
// MOCK DATA (For Testing - Replace with PHP Backend)
// Lines 51-100
// This data is used for testing the frontend
// TODO: Replace with actual API calls to your PHP backend
// ============================================
const mockMessages = [
    { id: 1, username: 'User1', avatar: '👤', content: 'Hey everyone! How are you doing?', time: '10:30 AM' },
    { id: 2, username: 'User2', avatar: '🎮', content: 'Just finished a great gaming session!', time: '10:32 AM' },
    { id: 3, username: 'User3', avatar: '💻', content: 'Working on some cool projects. Anyone want to collaborate?', time: '10:35 AM' },
    { id: 4, username: 'User1', avatar: '👤', content: 'That sounds awesome! What are you building?', time: '10:37 AM' },
];

const mockStreams = [
    { id: 1, streamer: 'ProGamer123', title: 'Epic Gaming Session - Playing Valorant', game: 'Valorant', viewers: 1234, isLive: true },
    { id: 2, streamer: 'MusicMaster', title: 'Music Production Live - Making a Beat', game: 'Music', viewers: 856, isLive: true },
    { id: 3, streamer: 'CodeWizard', title: 'Coding Tutorial - Building a Discord Clone', game: 'Software Development', viewers: 2341, isLive: true },
    { id: 4, streamer: 'StreamKing', title: 'Just Chatting with Viewers', game: 'Just Chatting', viewers: 567, isLive: true },
];

const mockPosts = [
    { id: 1, username: 'User1', handle: '@user1', avatar: '👤', content: 'This is a tweet! Just sharing some thoughts about the latest tech trends and developments in the industry!', likes: 10, retweets: 5, replies: 2, liked: false },
    { id: 2, username: 'User2', handle: '@user2', avatar: '🎮', content: 'Having a great time coding today! 🚀 Working on some amazing features.', likes: 25, retweets: 12, replies: 3, liked: true },
    { id: 3, username: 'User3', handle: '@user3', avatar: '💻', content: 'Check out this amazing project I just discovered! It\'s really inspiring.', likes: 8, retweets: 4, replies: 1, liked: false },
    { id: 4, username: 'User4', handle: '@user4', avatar: '🎵', content: 'Music production session was a success! Can\'t wait to share what I\'ve been working on.', likes: 15, retweets: 7, replies: 4, liked: false },
];

// ============================================
// PAGE NAVIGATION FUNCTIONS
// Lines 101-200
// Functions to switch between different pages (Servers, Stream, Feed)
// ============================================

/**
 * switchPage(page)
 * Switches between different pages (servers, stream, feed)
 * 
 * @param {string} page - The page to switch to: 'servers', 'stream', or 'feed'
 * 
 * This function:
 * 1. Updates the currentPage variable
 * 2. Updates the active navigation button
 * 3. Hides all pages
 * 4. Shows the selected page
 * 
 * Called when: User clicks a navigation button at the top
 * Location in HTML: Top navigation bar (lines 800-815 in index.html)
 */
function switchPage(page) {
    currentPage = page;
    
    // Update active nav item - Remove 'active' class from all nav items
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    // Add 'active' class to the clicked nav item
    event.target.closest('.nav-item').classList.add('active');
    
    // Hide all pages - Remove 'active' class from all pages
    document.querySelectorAll('.page').forEach(p => {
        p.classList.remove('active');
    });
    
    // Show selected page - Add 'active' class to the selected page
    document.getElementById(page + '-page').classList.add('active');
}

// ============================================
// SERVERS PAGE FUNCTIONS (Discord-style)
// Lines 201-400
// Functions for the servers/chat page
// ============================================

/**
 * selectServer(serverId, element)
 * Selects a server from the servers sidebar
 * 
 * @param {string} serverId - The ID of the server (e.g., 'general', 'gaming')
 * @param {HTMLElement} element - The clicked server icon element
 * 
 * This function:
 * 1. Updates currentServer variable
 * 2. Updates active server icon
 * 3. Updates server name in channels sidebar
 * 4. Resets to first channel
 * 
 * TODO: Add PHP backend call to load channels for this server
 * Location in HTML: Servers sidebar (lines 825-842 in index.html)
 */
function selectServer(serverId, element) {
    currentServer = serverId;
    
    // Update active server icon - Remove 'active' from all server icons
    document.querySelectorAll('.server-icon').forEach(icon => {
        icon.classList.remove('active');
    });
    // Add 'active' to clicked icon
    element.classList.add('active');
    
    // Update server name in channels sidebar header
    const serverNames = {
        'home': 'Home',
        'general': 'General Server',
        'gaming': 'Gaming Server',
        'music': 'Music Server',
        'coding': 'Coding Server'
    };
    document.getElementById('server-name').textContent = serverNames[serverId] || 'Server';
    
    // Reset to first channel
    selectChannel('general', document.querySelector('.channel-item'));
    
    // TODO: Load channels for this server from your PHP backend
    // Example:
    // fetch(`your-backend/api/getChannels.php?server=${serverId}`)
    //     .then(response => response.json())
    //     .then(data => {
    //         // Update channels list with data.channels
    //     });
}

/**
 * selectChannel(channelId, element)
 * Selects a channel from the channels sidebar
 * 
 * @param {string} channelId - The ID of the channel (e.g., 'general', 'announcements')
 * @param {HTMLElement} element - The clicked channel item element
 * 
 * This function:
 * 1. Updates currentChannel variable
 * 2. Updates active channel item
 * 3. Updates channel name in header
 * 4. Updates input placeholder
 * 5. Loads messages for this channel
 * 
 * TODO: Add PHP backend call to load messages for this channel
 * Location in HTML: Channels sidebar (lines 852-855 in index.html)
 */
function selectChannel(channelId, element) {
    currentChannel = channelId;
    
    // Update active channel - Remove 'active' from all channel items
    document.querySelectorAll('.channel-item').forEach(item => {
        item.classList.remove('active');
    });
    // Add 'active' to clicked channel
    if (element) element.classList.add('active');
    
    // Update channel name in chat header
    document.getElementById('channel-name').textContent = channelId;
    // Update input placeholder
    document.getElementById('message-input').placeholder = `Message #${channelId}`;
    
    // TODO: Load messages for this channel from your PHP backend
    // Example:
    // fetch(`your-backend/api/getMessages.php?server=${currentServer}&channel=${channelId}`)
    //     .then(response => response.json())
    //     .then(data => {
    //         messages = data.messages;
    //         renderMessages();
    //     });
    
    // For now, render existing messages
    renderMessages();
}

/**
 * renderMessages()
 * Displays all messages in the chat area
 * 
 * This function:
 * 1. Gets the messages container
 * 2. Clears existing messages
 * 3. Creates HTML for each message
 * 4. Appends messages to container
 * 5. Scrolls to bottom
 * 
 * Called when: Messages are loaded or a new message is sent
 * Location in HTML: Messages container (line 872 in index.html)
 */
function renderMessages() {
    const container = document.getElementById('messages-container');
    container.innerHTML = '';
    
    // Loop through each message and create HTML with animation delay
    messages.forEach((msg, index) => {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message';
        messageDiv.style.animationDelay = `${index * 0.05}s`;
        messageDiv.innerHTML = `
            <div class="message-avatar">${msg.avatar}</div>
            <div class="message-content">
                <div class="message-header">
                    <span class="message-username">${msg.username}</span>
                    <span class="message-time">${msg.time}</span>
                </div>
                <div class="message-text">${msg.content}</div>
            </div>
        `;
        container.appendChild(messageDiv);
    });
    
    // Smooth scroll to bottom to show newest messages
    setTimeout(() => {
        container.scrollTo({
            top: container.scrollHeight,
            behavior: 'smooth'
        });
    }, 100);
}

/**
 * sendMessage()
 * Sends a new message to the current channel
 * 
 * This function:
 * 1. Gets message content from input field
 * 2. Validates that message is not empty
 * 3. Creates new message object
 * 4. Adds to messages array
 * 5. Renders updated messages
 * 6. Clears input field
 * 
 * TODO: Send message to PHP backend instead of just adding locally
 * Called when: User clicks Send button or presses Enter
 * Location in HTML: Send button (line 888 in index.html)
 */
function sendMessage() {
    const input = document.getElementById('message-input');
    const content = input.value.trim();
    
    // Don't send empty messages
    if (!content) return;
    
    // TODO: Send to your PHP backend
    // Example:
    // fetch('your-backend/api/sendMessage.php', {
    //     method: 'POST',
    //     headers: { 'Content-Type': 'application/json' },
    //     body: JSON.stringify({
    //         serverId: currentServer,
    //         channelId: currentChannel,
    //         content: content
    //     })
    // })
    // .then(response => response.json())
    // .then(data => {
    //     messages.push(data.message);
    //     renderMessages();
    //     input.value = '';
    // });
    
    // For now, create message locally
    const newMessage = {
        id: Date.now(),
        username: 'You',
        avatar: '👤',
        content: content,
        time: new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
    };
    
    messages.push(newMessage);
    renderMessages();
    input.value = '';
}

/**
 * handleKeyPress(event)
 * Handles keyboard input in message input field
 * 
 * @param {KeyboardEvent} event - The keyboard event
 * 
 * This function:
 * - Sends message when Enter is pressed (without Shift)
 * - Allows Shift+Enter for new lines
 * 
 * Called when: User types in message input field
 * Location in HTML: Message input (line 884 in index.html)
 */
function handleKeyPress(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
}

// ============================================
// STREAM PAGE FUNCTIONS (Twitch-style)
// Lines 401-500
// Functions for the stream page
// ============================================

/**
 * renderStreams(streamList)
 * Displays stream cards in a grid layout
 * 
 * @param {Array} streamList - Array of stream objects to display (defaults to global streams array)
 * 
 * This function:
 * 1. Gets the streams grid container
 * 2. Clears existing streams
 * 3. Creates HTML for each stream card
 * 4. Appends cards to grid
 * 5. Adds click handler to open stream
 * 
 * Called when: Stream page loads or search is performed
 * Location in HTML: Streams grid (line 910 in index.html)
 */
function renderStreams(streamList = streams) {
    const container = document.getElementById('streams-grid');
    container.innerHTML = '';
    
    // Loop through each stream and create a card with animation
    streamList.forEach((stream, index) => {
        const streamCard = document.createElement('div');
        streamCard.className = 'stream-card';
        streamCard.style.animationDelay = `${index * 0.1}s`;
        streamCard.innerHTML = `
            <div class="stream-thumbnail">
                ${stream.isLive ? `
                    <div class="live-badge">
                        <div class="live-dot"></div>
                        <span>LIVE</span>
                    </div>
                    <div class="viewer-count">👁️ ${stream.viewers.toLocaleString()}</div>
                ` : ''}
            </div>
            <div class="stream-info">
                <div class="stream-title">${stream.title}</div>
                <div class="streamer-name">${stream.streamer}</div>
                <div class="stream-game">${stream.game}</div>
            </div>
        `;
        // Add click handler to open stream
        streamCard.onclick = () => {
            // TODO: Open stream player or redirect to stream page
            alert(`Opening stream: ${stream.streamer}`);
        };
        container.appendChild(streamCard);
    });
}

/**
 * searchStreams(event)
 * Filters streams based on search query
 * 
 * @param {KeyboardEvent} event - The keyboard event from search input
 * 
 * This function:
 * 1. Gets search query from input
 * 2. If query is empty, shows all streams
 * 3. Otherwise, filters streams by streamer name, title, or game
 * 4. Renders filtered results
 * 
 * Called when: User types in search bar
 * Location in HTML: Stream search input (line 905 in index.html)
 */
function searchStreams(event) {
    const query = event.target.value.toLowerCase().trim();
    
    // If search is empty, show all streams
    if (!query) {
        renderStreams(streams);
        return;
    }
    
    // Filter streams that match search query
    const filtered = streams.filter(stream => 
        stream.streamer.toLowerCase().includes(query) ||
        stream.title.toLowerCase().includes(query) ||
        stream.game.toLowerCase().includes(query)
    );
    
    renderStreams(filtered);
}

// ============================================
// FEED PAGE FUNCTIONS (Twitter-style)
// Lines 501-600
// Functions for the feed page
// ============================================

/**
 * renderPosts()
 * Displays all posts in the feed
 * 
 * This function:
 * 1. Gets the posts container
 * 2. Clears existing posts
 * 3. Creates HTML for each post
 * 4. Appends posts to container
 * 
 * Called when: Feed page loads or a new post is created
 * Location in HTML: Posts container (line 943 in index.html)
 */
function renderPosts() {
    const container = document.getElementById('posts-container');
    container.innerHTML = '';
    
    // Loop through each post and create HTML with animation delay
    posts.forEach((post, index) => {
        const postDiv = document.createElement('div');
        postDiv.className = 'post';
        postDiv.style.animationDelay = `${index * 0.1}s`;
        postDiv.innerHTML = `
            <div class="post-header">
                <div class="post-avatar">${post.avatar}</div>
                <div class="post-user-info">
                    <div class="post-username">${post.username}</div>
                    <div class="post-handle">${post.handle}</div>
                </div>
                <div class="post-more">⋯</div>
            </div>
            <div class="post-content">${post.content}</div>
            <div class="post-actions">
                <div class="post-action reply" onclick="event.stopPropagation()">
                    💬 <span>${post.replies}</span>
                </div>
                <div class="post-action retweet" onclick="event.stopPropagation()">
                    🔄 <span>${post.retweets}</span>
                </div>
                <div class="post-action like ${post.liked ? 'liked' : ''}" onclick="toggleLike(${post.id})">
                    ❤️ <span>${post.likes}</span>
                </div>
                <div class="post-action share" onclick="sharePost(${post.id})">
                    📤
                </div>
            </div>
        `;
        container.appendChild(postDiv);
    });
}

/**
 * createPost()
 * Creates a new post/tweet
 * 
 * This function:
 * 1. Gets post content from textarea
 * 2. Validates that content is not empty
 * 3. Creates new post object
 * 4. Adds to beginning of posts array
 * 5. Renders updated posts
 * 6. Clears textarea
 * 
 * TODO: Send post to PHP backend
 * Called when: User clicks Tweet button
 * Location in HTML: Tweet button (line 938 in index.html)
 */
function createPost() {
    const input = document.getElementById('compose-input');
    const content = input.value.trim();
    
    // Don't create empty posts
    if (!content) return;
    
    // TODO: Send to your PHP backend
    // Example:
    // fetch('your-backend/api/createPost.php', {
    //     method: 'POST',
    //     headers: { 'Content-Type': 'application/json' },
    //     body: JSON.stringify({ content: content })
    // })
    // .then(response => response.json())
    // .then(data => {
    //     posts.unshift(data.post);
    //     renderPosts();
    //     input.value = '';
    // });
    
    // For now, create post locally
    const newPost = {
        id: Date.now(),
        username: 'You',
        handle: '@you',
        avatar: '👤',
        content: content,
        likes: 0,
        retweets: 0,
        replies: 0,
        liked: false
    };
    
    posts.unshift(newPost);
    renderPosts();
    input.value = '';
}

/**
 * toggleLike(postId)
 * Toggles like status on a post
 * 
 * @param {number} postId - The ID of the post to like/unlike
 * 
 * This function:
 * 1. Finds the post by ID
 * 2. Toggles liked status
 * 3. Updates like count
 * 4. Renders updated posts
 * 
 * TODO: Send like to PHP backend
 * Called when: User clicks like button on a post
 * Location in HTML: Like button in post actions (line 1193 in rendered HTML)
 */
function toggleLike(postId) {
    const post = posts.find(p => p.id === postId);
    if (post) {
        post.liked = !post.liked;
        post.likes += post.liked ? 1 : -1;
        
        // Add visual feedback
        const likeButton = event.target.closest('.like');
        if (likeButton) {
            likeButton.classList.add('liked');
            setTimeout(() => {
                renderPosts();
            }, 300);
        } else {
            renderPosts();
        }
        
        // TODO: Send like to your PHP backend
        // Example:
        // fetch('your-backend/api/likePost.php', {
        //     method: 'POST',
        //     headers: { 'Content-Type': 'application/json' },
        //     body: JSON.stringify({ postId: postId })
        // });
    }
}

/**
 * sharePost(postId)
 * Handles sharing a post
 * 
 * @param {number} postId - The ID of the post to share
 * 
 * TODO: Implement share functionality with PHP backend
 * Called when: User clicks share button on a post
 */
function sharePost(postId) {
    // TODO: Implement share functionality
    alert('Share functionality - connect to your backend!');
}

// ============================================
// INITIALIZATION
// Lines 601-650
// Functions that run when the page loads
// ============================================

/**
 * checkAuth()
 * Checks if user is logged in
 * Redirects to login page if not authenticated
 */
function checkAuth() {
    const token = localStorage.getItem('userToken');
    const username = localStorage.getItem('username');
    
    if (!token) {
        // User not logged in, redirect to login page
        window.location.href = 'login.html';
        return false;
    }
    
    // Update username display if available
    if (username) {
        const userNameEl = document.getElementById('user-name');
        const userAvatarEl = document.getElementById('user-avatar');
        if (userNameEl) {
            userNameEl.textContent = username;
        }
        if (userAvatarEl) {
            userAvatarEl.textContent = username.charAt(0).toUpperCase();
        }
    }
    
    return true;
}

function openProfilePage() {
    window.location.href = 'profile.html';
}

/**
 * handleLogout()
 * Logs out the user and redirects to login page
 */
function handleLogout() {
    if (confirm('Are you sure you want to logout?')) {
        // Clear all stored data
        localStorage.removeItem('userToken');
        localStorage.removeItem('userId');
        localStorage.removeItem('username');
        localStorage.removeItem('userName');
        
        // Redirect to login page
        window.location.href = 'login.html';
    }
}

/**
 * init()
 * Initializes the application when page loads
 * 
 * This function:
 * 1. Checks if user is logged in
 * 2. Loads mock data into arrays
 * 3. Renders initial messages
 * 4. Renders initial streams
 * 5. Renders initial posts
 * 
 * Called when: Page loads (at the bottom of this file)
 */
function init() {
    // Check authentication first
    if (!checkAuth()) {
        return; // Will redirect to login page
    }
    
    // Load mock data into arrays
    messages = [...mockMessages];
    streams = [...mockStreams];
    posts = [...mockPosts];
    
    // Render all initial content
    renderMessages();
    renderStreams();
    renderPosts();
}

// Start the application when page loads
init();

