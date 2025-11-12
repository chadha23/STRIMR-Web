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
let currentPage = 'servers';      // Which page is currently active: 'servers', 'stream', 'marketplace', or 'feed'
let currentServer = 'general';    // Which server is selected (for servers page)
let currentChannel = 'general';   // Which channel is selected (for servers page)
let messages = [];                // Array to store chat messages
let posts = [];                   // Array to store feed posts
let streams = [];                 // Array to store stream data
let marketplaceItems = [];        // Array to store marketplace listings
let marketplaceCategory = 'all';  // Selected marketplace category filter
let marketplaceSearchQuery = '';  // Current marketplace search text
const marketplaceFavorites = new Set(); // Track saved marketplace listings
let marketplaceBalance = 1200;    // Mock user balance for marketplace

// ============================================
// MOCK DATA (For Testing - Replace with PHP Backend)
// Lines 51-100
// This data is used for testing the frontend
// TODO: Replace with actual API calls to your PHP backend
// ============================================
const mockMessages = [
    { id: 1, username: 'Ava Rivers', avatar: 'AR', content: 'Morning crew! What are we building today?', time: '09:05 AM' },
    { id: 2, username: 'Luca Stone', avatar: 'LS', content: 'I just pushed a fresh update to the stream overlay ✨', time: '09:07 AM' },
    { id: 3, username: 'Nova Chen', avatar: 'NC', content: 'Finishing the API docs—need a quick review in #random.', time: '09:09 AM' },
    { id: 4, username: 'Ava Rivers', avatar: 'AR', content: 'On it! I also have a moodboard for the new landing page.', time: '09:12 AM' },
];

const mockStreams = [
    { id: 1, streamer: 'PixelPulse', title: 'Designing a neon sci-fi HUD in Figma', game: 'Creative Studio', viewers: 1834, isLive: true },
    { id: 2, streamer: 'SynthWave', title: 'Live coding a rhythm game in Rust', game: 'Game Dev', viewers: 1242, isLive: true },
    { id: 3, streamer: 'LoopSmith', title: 'Layering ambience & beats – chill set', game: 'Music Lab', viewers: 968, isLive: true },
    { id: 4, streamer: 'CloudAtlas', title: 'Ask me anything about scaling microservices', game: 'Tech Talks', viewers: 657, isLive: true },
];

const mockPosts = [
    { id: 1, username: 'Ava Rivers', handle: '@ava.codes', avatar: 'AR', content: 'Shipping a new feature preview tonight. Gradient generator now supports keyframed stops 🔥', likes: 48, retweets: 12, replies: 6, liked: false },
    { id: 2, username: 'Luca Stone', handle: '@lucamakes', avatar: 'LS', content: 'Experimenting with WebGPU shaders for procedural nebula art. Early results look insane.', likes: 72, retweets: 21, replies: 9, liked: true },
    { id: 3, username: 'Nova Chen', handle: '@novadraws', avatar: 'NC', content: 'Sketching UI concepts for a cross-platform music sequencer. Dark mode feels ethereal ✨', likes: 31, retweets: 8, replies: 4, liked: false },
    { id: 4, username: 'Jules Park', handle: '@jules.dev', avatar: 'JP', content: 'Weekend goal: refactor notifications into composable hooks + animation states. Wish me luck.', likes: 26, retweets: 10, replies: 3, liked: false },
];

const mockMarketplaceItems = [
    {
        id: 1,
        title: 'Custom Mechanical Keyboard (65%)',
        price: 260,
        seller: 'Milo Hart',
        location: 'Vancouver · Ships Worldwide',
        condition: 'Like New',
        category: 'tech',
        tags: ['Keycaps', 'Hot-Swap', 'Custom'],
        description: 'Gasket-mounted, lubed Zealios V2 switches, RGB underglow, includes coiled USB-C cable.',
        cover: 'linear-gradient(135deg, rgba(136,146,255,0.45), rgba(111,242,214,0.35))'
    },
    {
        id: 2,
        title: 'Studio Monitor Speakers (Pair)',
        price: 420,
        seller: 'Jules Park',
        location: 'Los Angeles · Local Pickup',
        condition: 'Excellent',
        category: 'creative',
        tags: ['Audio', 'Music', 'Studio'],
        description: 'KRK Rokit 7 G4 monitors, barely used, includes acoustic pads and balanced TRS cables.',
        cover: 'linear-gradient(135deg, rgba(242,125,222,0.38), rgba(120,125,255,0.32))'
    },
    {
        id: 3,
        title: 'iPad Pro 12.9" + Apple Pencil',
        price: 750,
        seller: 'Nova Chen',
        location: 'Remote · Ships North America',
        condition: 'Pristine',
        category: 'tech',
        tags: ['Tablet', 'Apple', 'Pro'],
        description: 'M1 iPad Pro 256GB (2021) in space grey, Apple Pencil 2, Magic Keyboard included.',
        cover: 'linear-gradient(135deg, rgba(111,242,214,0.38), rgba(136,146,255,0.4))'
    },
    {
        id: 4,
        title: 'Handmade Neon Wall Art',
        price: 180,
        seller: 'PixelPulse',
        location: 'Berlin · Ships EU',
        condition: 'Handmade',
        category: 'creative',
        tags: ['Art', 'Lighting', 'Decor'],
        description: 'Custom neon LED sign, choose your palette. Includes dimmer and wall-mount kit.',
        cover: 'linear-gradient(135deg, rgba(224,30,90,0.42), rgba(88,101,242,0.28))'
    },
    {
        id: 5,
        title: 'Ergonomic Studio Chair',
        price: 340,
        seller: 'LoopSmith',
        location: 'Remote · Ships US & CA',
        condition: 'Like New',
        category: 'lifestyle',
        tags: ['Workspace', 'Comfort', 'Support'],
        description: 'Herman Miller Sayl chair in black, fully adjustable arms, breathable back mesh.',
        cover: 'linear-gradient(135deg, rgba(120,125,255,0.35), rgba(67,181,129,0.26))'
    },
    {
        id: 6,
        title: 'Analog Film Camera Kit',
        price: 295,
        seller: 'Ava Rivers',
        location: 'Portland · Local + Ship',
        condition: 'Good',
        category: 'lifestyle',
        tags: ['Photography', 'Vintage', 'Film'],
        description: 'Canon AE-1 with 50mm f/1.4 lens, light meter, and 5 rolls of Kodak Portra 400.',
        cover: 'linear-gradient(135deg, rgba(255,181,72,0.32), rgba(136,146,255,0.35))'
    }
];

const formatCurrency = (amount) => `$${Number(amount).toLocaleString()}`;

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
                    <div class="viewer-count">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                            <circle cx="12" cy="12" r="3" stroke-width="1.8"/>
                        </svg>
                        <span>${stream.viewers.toLocaleString()}</span>
                    </div>
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
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M9 12h9m-9 5h6M5 17V7a2 2 0 012-2h10a2 2 0 012 2v12l-4-3H7a2 2 0 01-2-2z"/>
                    </svg>
                    <span>${post.replies}</span>
                </div>
                <div class="post-action retweet" onclick="event.stopPropagation()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M17 1l4 4-4 4M21 5h-7a4 4 0 00-4 4v1m-3 9l-4-4 4-4m-4 4h7a4 4 0 004-4v-1"/>
                    </svg>
                    <span>${post.retweets}</span>
                </div>
                <div class="post-action like ${post.liked ? 'liked' : ''}" onclick="toggleLike(${post.id})">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M12 20l-1.45-1.32C6.4 15.36 4 13.28 4 10.5A3.5 3.5 0 0111 8a3.5 3.5 0 017 .5c0 2.78-2.4 4.86-6.55 8.18z"/>
                    </svg>
                    <span>${post.likes}</span>
                </div>
                <div class="post-action share" onclick="sharePost(${post.id})">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8M16 6l-4-4-4 4M12 2v14"/>
                    </svg>
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
 * toggleMarketplaceFavorite(id)
 * Adds/removes marketplace listing from favorites
 */
function toggleMarketplaceFavorite(itemId) {
    if (marketplaceFavorites.has(itemId)) {
        marketplaceFavorites.delete(itemId);
    } else {
        marketplaceFavorites.add(itemId);
    }
    updateMarketplaceView();
}

/**
 * searchMarketplace(event)
 * Updates marketplace search query and re-renders listings
 */
function searchMarketplace(event) {
    marketplaceSearchQuery = event.target.value.toLowerCase();
    updateMarketplaceView();
}

/**
 * filterMarketplace(category, button)
 * Filters marketplace listings by category
 */
function filterMarketplace(category, button) {
    marketplaceCategory = category;
    document.querySelectorAll('.marketplace-filter').forEach(filterBtn => {
        filterBtn.classList.toggle('active', filterBtn === button);
    });
    updateMarketplaceView();
}

/**
 * updateMarketplaceView()
 * Applies search and category filters then renders marketplace listings
 */
function updateMarketplaceView() {
    const filtered = marketplaceItems.filter(item => {
        const matchesCategory = marketplaceCategory === 'all' || item.category === marketplaceCategory;
        if (!matchesCategory) return false;

        if (!marketplaceSearchQuery) return true;

        const haystack = [
            item.title,
            item.seller,
            item.location,
            item.description,
            item.tags.join(' ')
        ].join(' ').toLowerCase();

        return haystack.includes(marketplaceSearchQuery);
    });

    renderMarketplace(filtered);
}

/**
 * renderMarketplace(listings)
 * Renders marketplace listing cards
 */
function renderMarketplace(listings = []) {
    const grid = document.getElementById('marketplace-grid');
    const empty = document.getElementById('marketplace-empty');
    const wallet = document.getElementById('marketplace-wallet');

    if (!grid || !empty || !wallet) return;

    grid.innerHTML = '';
    wallet.innerHTML = `
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 7a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 12h4v2h-4a1 1 0 010-2z"/>
        </svg>
        <span>Current balance: <strong>${formatCurrency(marketplaceBalance)}</strong></span>
    `;

    if (!listings.length) {
        empty.classList.add('show');
        return;
    }

    empty.classList.remove('show');

    listings.forEach((item, index) => {
        const card = document.createElement('div');
        card.className = 'marketplace-card';
        card.style.animationDelay = `${index * 0.05}s`;

        const tagsMarkup = item.tags.map(tag => `<span class="marketplace-card-tag">${tag}</span>`).join('');
        const favoriteClass = marketplaceFavorites.has(item.id) ? 'active' : '';

        card.innerHTML = `
            <div class="marketplace-card-media" style="background-image: ${item.cover};">
                <span class="marketplace-card-pill">${item.condition}</span>
                <button class="marketplace-favorite ${favoriteClass}" type="button" aria-label="Save listing" onclick="toggleMarketplaceFavorite(${item.id}); event.stopPropagation();">
                    <svg viewBox="0 0 24 24">
                        <path d="M12 20l-1.45-1.32C6.4 15.36 4 13.28 4 10.5A3.5 3.5 0 0111 8a3.5 3.5 0 017 .5c0 2.78-2.4 4.86-6.55 8.18z"/>
                    </svg>
                </button>
            </div>
            <div class="marketplace-card-body">
                <div class="marketplace-card-price">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                              d="M12 1v22M7 5h6a4 4 0 110 8H8a4 4 0 000 8h8"/>
                    </svg>
                    ${formatCurrency(item.price)}
                </div>
                <h3 class="marketplace-card-title">${item.title}</h3>
                <p class="marketplace-card-description">${item.description}</p>
                <div class="marketplace-card-meta">
                    <span class="marketplace-card-seller">Seller · ${item.seller}</span>
                    <span class="marketplace-card-location">
                        <svg viewBox="0 0 24 24">
                            <path d="M12 22s7-5.686 7-11A7 7 0 005 11c0 5.314 7 11 7 11z"/>
                            <circle cx="12" cy="11" r="2.5"/>
                        </svg>
                        ${item.location}
                    </span>
                </div>
                <div class="marketplace-card-tags">${tagsMarkup}</div>
                <button class="marketplace-card-action" type="button" onclick="purchaseListing(${item.id})">
                    Buy Now
                </button>
            </div>
        `;

        grid.appendChild(card);
    });
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
    marketplaceItems = [...mockMarketplaceItems];
    
    // Render all initial content
    renderMessages();
    renderStreams();
    renderPosts();
    updateMarketplaceView();
}

/**
 * purchaseListing(id)
 * Mock purchase interaction for marketplace
 */
function purchaseListing(id) {
    const item = marketplaceItems.find(listing => listing.id === id);
    if (!item) return;

    if (item.price > marketplaceBalance) {
        alert('Not enough balance to complete this purchase.');
        return;
    }

    marketplaceBalance -= item.price;
    alert(`Purchased ${item.title} for ${formatCurrency(item.price)}!`);
    updateMarketplaceView();
}

var activePageName = 'servers';
var activeServerId = 'general';
var activeChannelId = 'general';
var messageList = [];
var postList = [];
var streamList = [];
var marketplaceList = [];
var marketplaceCategoryName = 'all';
var marketplaceSearchText = '';
var savedMarketplaceIds = [];
var marketplaceBalanceAmount = 1200;

var serverNameById = {
    home: 'Home',
    general: 'General Server',
    gaming: 'Gaming Server',
    music: 'Music Server',
    coding: 'Coding Server'
};

var mockMessageData = [
    { username: 'Ava Rivers', avatar: 'AR', content: 'Morning crew! What are we building today?', time: '09:05 AM' },
    { username: 'Luca Stone', avatar: 'LS', content: 'I just pushed a fresh update to the stream overlay ✨', time: '09:07 AM' },
    { username: 'Nova Chen', avatar: 'NC', content: 'Finishing the API docs—need a quick review in #random.', time: '09:09 AM' },
    { username: 'Ava Rivers', avatar: 'AR', content: 'On it! I also have a moodboard for the new landing page.', time: '09:12 AM' }
];

var mockStreamData = [
    { streamer: 'PixelPulse', title: 'Designing a neon sci-fi HUD in Figma', game: 'Creative Studio', viewers: 1834, isLive: true },
    { streamer: 'SynthWave', title: 'Live coding a rhythm game in Rust', game: 'Game Dev', viewers: 1242, isLive: true },
    { streamer: 'LoopSmith', title: 'Layering ambience & beats – chill set', game: 'Music Lab', viewers: 968, isLive: true },
    { streamer: 'CloudAtlas', title: 'Ask me anything about scaling microservices', game: 'Tech Talks', viewers: 657, isLive: true }
];

var mockPostData = [
    { username: 'Ava Rivers', handle: '@ava.codes', avatar: 'AR', content: 'Shipping a new feature preview tonight. Gradient generator now supports keyframed stops 🔥', likes: 48, retweets: 12, replies: 6, liked: false },
    { username: 'Luca Stone', handle: '@lucamakes', avatar: 'LS', content: 'Experimenting with WebGPU shaders for procedural nebula art. Early results look insane.', likes: 72, retweets: 21, replies: 9, liked: true },
    { username: 'Nova Chen', handle: '@novadraws', avatar: 'NC', content: 'Sketching UI concepts for a cross-platform music sequencer. Dark mode feels ethereal ✨', likes: 31, retweets: 8, replies: 4, liked: false },
    { username: 'Jules Park', handle: '@jules.dev', avatar: 'JP', content: 'Weekend goal: refactor notifications into composable hooks + animation states. Wish me luck.', likes: 26, retweets: 10, replies: 3, liked: false }
];

var mockMarketplaceData = [
    {
        id: 1,
        title: 'Custom Mechanical Keyboard (65%)',
        price: 260,
        seller: 'Milo Hart',
        location: 'Vancouver · Ships Worldwide',
        condition: 'Like New',
        category: 'tech',
        tags: ['Keycaps', 'Hot-Swap', 'Custom'],
        description: 'Gasket-mounted, lubed Zealios V2 switches, RGB underglow, includes coiled USB-C cable.',
        background: 'linear-gradient(135deg, rgba(136,146,255,0.45), rgba(111,242,214,0.35))'
    },
    {
        id: 2,
        title: 'Studio Monitor Speakers (Pair)',
        price: 420,
        seller: 'Jules Park',
        location: 'Los Angeles · Local Pickup',
        condition: 'Excellent',
        category: 'creative',
        tags: ['Audio', 'Music', 'Studio'],
        description: 'KRK Rokit 7 G4 monitors, barely used, includes acoustic pads and balanced TRS cables.',
        background: 'linear-gradient(135deg, rgba(242,125,222,0.38), rgba(120,125,255,0.32))'
    },
    {
        id: 3,
        title: 'iPad Pro 12.9" + Apple Pencil',
        price: 750,
        seller: 'Nova Chen',
        location: 'Remote · Ships North America',
        condition: 'Pristine',
        category: 'tech',
        tags: ['Tablet', 'Apple', 'Pro'],
        description: 'M1 iPad Pro 256GB (2021) in space grey, Apple Pencil 2, Magic Keyboard included.',
        background: 'linear-gradient(135deg, rgba(111,242,214,0.38), rgba(136,146,255,0.4))'
    },
    {
        id: 4,
        title: 'Handmade Neon Wall Art',
        price: 180,
        seller: 'PixelPulse',
        location: 'Berlin · Ships EU',
        condition: 'Handmade',
        category: 'creative',
        tags: ['Art', 'Lighting', 'Decor'],
        description: 'Custom neon LED sign, choose your palette. Includes dimmer and wall-mount kit.',
        background: 'linear-gradient(135deg, rgba(224,30,90,0.42), rgba(88,101,242,0.28))'
    },
    {
        id: 5,
        title: 'Ergonomic Studio Chair',
        price: 340,
        seller: 'LoopSmith',
        location: 'Remote · Ships US & CA',
        condition: 'Like New',
        category: 'lifestyle',
        tags: ['Workspace', 'Comfort', 'Support'],
        description: 'Herman Miller Sayl chair in black, fully adjustable arms, breathable back mesh.',
        background: 'linear-gradient(135deg, rgba(120,125,255,0.35), rgba(67,181,129,0.26))'
    },
    {
        id: 6,
        title: 'Analog Film Camera Kit',
        price: 295,
        seller: 'Ava Rivers',
        location: 'Portland · Local + Ship',
        condition: 'Good',
        category: 'lifestyle',
        tags: ['Photography', 'Vintage', 'Film'],
        description: 'Canon AE-1 with 50mm f/1.4 lens, light meter, and 5 rolls of Kodak Portra 400.',
        background: 'linear-gradient(135deg, rgba(255,181,72,0.32), rgba(136,146,255,0.35))'
    }
];

function formatMoney(amount) {
    return '$' + Number(amount).toLocaleString();
}

function showPage(pageName, navItem) {
    activePageName = pageName;

    var navItems = document.getElementsByClassName('nav-item');
    for (var i = 0; i < navItems.length; i++) {
        navItems[i].classList.remove('active');
    }
    if (navItem) {
        navItem.classList.add('active');
    }

    var pages = document.getElementsByClassName('page');
    for (var j = 0; j < pages.length; j++) {
        pages[j].classList.remove('active');
    }
    var pageElement = document.getElementById(pageName + '-page');
    if (pageElement) {
        pageElement.classList.add('active');
    }
}

function chooseServer(serverId, serverElement) {
    activeServerId = serverId;

    var serverIcons = document.getElementsByClassName('server-icon');
    for (var i = 0; i < serverIcons.length; i++) {
        serverIcons[i].classList.remove('active');
    }
    if (serverElement) {
        serverElement.classList.add('active');
    }

    var serverNameText = document.getElementById('server-name');
    if (serverNameText) {
        var friendlyName = serverNameById[serverId] || 'Server';
        serverNameText.textContent = friendlyName;
    }

    var firstChannel = document.querySelector('.channel-item');
    chooseChannel('general', firstChannel);
}

function chooseChannel(channelId, channelElement) {
    activeChannelId = channelId;

    var channelItems = document.getElementsByClassName('channel-item');
    for (var i = 0; i < channelItems.length; i++) {
        channelItems[i].classList.remove('active');
    }
    if (channelElement) {
        channelElement.classList.add('active');
    }

    var channelName = document.getElementById('channel-name');
    if (channelName) {
        channelName.textContent = channelId;
    }

    var messageInput = document.getElementById('message-input');
    if (messageInput) {
        messageInput.placeholder = 'Message #' + channelId;
    }

    showMessages();
}

function showMessages() {
    var container = document.getElementById('messages-container');
    if (!container) {
        return;
    }

    var html = '';
    for (var i = 0; i < messageList.length; i++) {
        var message = messageList[i];
        html += '<div class="message">';
        html += '<div class="message-avatar">' + message.avatar + '</div>';
        html += '<div class="message-content">';
        html += '<div class="message-header">';
        html += '<span class="message-username">' + message.username + '</span>';
        html += '<span class="message-time">' + message.time + '</span>';
        html += '</div>';
        html += '<div class="message-text">' + message.content + '</div>';
        html += '</div>';
        html += '</div>';
    }
    container.innerHTML = html;

    setTimeout(function () {
        container.scrollTop = container.scrollHeight;
    }, 100);
}

function submitMessage() {
    var input = document.getElementById('message-input');
    if (!input) {
        return;
    }

    var text = input.value.trim();
    if (text === '') {
        return;
    }

    var now = new Date();
    var timeText = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });

    messageList.push({
        username: 'You',
        avatar: '👤',
        content: text,
        time: timeText
    });

    input.value = '';
    showMessages();
}

function messageFieldKeyPress(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        submitMessage();
    }
}

function showStreams(list) {
    var container = document.getElementById('streams-grid');
    if (!container) {
        return;
    }

    var streamItems = list || streamList;
    var html = '';

    for (var i = 0; i < streamItems.length; i++) {
        var currentStream = streamItems[i];
        html += '<div class="stream-card">';
        html += '<div class="stream-thumbnail">';
        if (currentStream.isLive) {
            html += '<div class="live-badge"><div class="live-dot"></div><span>LIVE</span></div>';
            html += '<div class="viewer-count"><span>' + currentStream.viewers.toLocaleString() + '</span></div>';
        }
        html += '</div>';
        html += '<div class="stream-info">';
        html += '<div class="stream-title">' + currentStream.title + '</div>';
        html += '<div class="streamer-name">' + currentStream.streamer + '</div>';
        html += '<div class="stream-game">' + currentStream.game + '</div>';
        html += '</div>';
        html += '</div>';
    }

    container.innerHTML = html;
}

function filterStreamsByText(text) {
    var searchValue = text.toLowerCase();
    if (searchValue === '') {
        showStreams();
        return;
    }

    var filtered = [];
    for (var i = 0; i < streamList.length; i++) {
        var currentStream = streamList[i];
        var combined = currentStream.streamer + ' ' + currentStream.title + ' ' + currentStream.game;
        if (combined.toLowerCase().indexOf(searchValue) !== -1) {
            filtered.push(currentStream);
        }
    }

    showStreams(filtered);
}

function showPosts() {
    var container = document.getElementById('posts-container');
    if (!container) {
        return;
    }

    var html = '';
    for (var i = 0; i < postList.length; i++) {
        var post = postList[i];
        html += '<div class="post">';
        html += '<div class="post-header">';
        html += '<div class="post-avatar">' + post.avatar + '</div>';
        html += '<div class="post-user-info">';
        html += '<div class="post-username">' + post.username + '</div>';
        html += '<div class="post-handle">' + post.handle + '</div>';
        html += '</div>';
        html += '<div class="post-more">⋯</div>';
        html += '</div>';
        html += '<div class="post-content">' + post.content + '</div>';
        html += '<div class="post-actions">';
        html += '<div class="post-action reply"><span>' + post.replies + '</span></div>';
        html += '<div class="post-action retweet"><span>' + post.retweets + '</span></div>';
        html += '<div class="post-action like' + (post.liked ? ' liked' : '') + '" onclick="changePostLike(' + post.id + ', this)">';
        html += '<span>' + post.likes + '</span></div>';
        html += '<div class="post-action share" onclick="sharePost(' + post.id + ')"></div>';
        html += '</div>';
        html += '</div>';
    }

    container.innerHTML = html;
}

function addPost() {
    var input = document.getElementById('compose-input');
    if (!input) {
        return;
    }

    var text = input.value.trim();
    if (text === '') {
        return;
    }

    var newPost = {
        id: Date.now(),
        username: 'You',
        handle: '@you',
        avatar: '👤',
        content: text,
        likes: 0,
        retweets: 0,
        replies: 0,
        liked: false
    };

    postList.unshift(newPost);
    input.value = '';
    showPosts();
}

function changePostLike(postId) {
    for (var i = 0; i < postList.length; i++) {
        if (postList[i].id === postId) {
            if (postList[i].liked) {
                postList[i].liked = false;
                postList[i].likes = postList[i].likes - 1;
            } else {
                postList[i].liked = true;
                postList[i].likes = postList[i].likes + 1;
            }
            break;
        }
    }
    showPosts();
}

function sharePost(postId) {
    alert('Share feature is not ready yet.');
}

function updateMarketplaceSearch(text) {
    marketplaceSearchText = text.toLowerCase();
    showMarketplace();
}

function changeMarketplaceCategory(category, buttonElement) {
    marketplaceCategoryName = category;

    var buttons = document.getElementsByClassName('marketplace-filter');
    for (var i = 0; i < buttons.length; i++) {
        buttons[i].classList.remove('active');
    }
    if (buttonElement) {
        buttonElement.classList.add('active');
    }

    showMarketplace();
}

function switchSavedMarketplaceItem(itemId) {
    var index = savedMarketplaceIds.indexOf(itemId);
    if (index === -1) {
        savedMarketplaceIds.push(itemId);
    } else {
        savedMarketplaceIds.splice(index, 1);
    }
    showMarketplace();
}

function buyMarketplaceItem(itemId) {
    var item = null;
    for (var i = 0; i < marketplaceList.length; i++) {
        if (marketplaceList[i].id === itemId) {
            item = marketplaceList[i];
            break;
        }
    }

    if (!item) {
        return;
    }

    if (item.price > marketplaceBalanceAmount) {
        alert('Not enough balance to buy this item.');
        return;
    }

    marketplaceBalanceAmount -= item.price;
    alert('You bought ' + item.title + ' for ' + formatMoney(item.price) + '.');
    showMarketplace();
}

function showMarketplace() {
    var grid = document.getElementById('marketplace-grid');
    var emptyState = document.getElementById('marketplace-empty');
    var wallet = document.getElementById('marketplace-wallet');

    if (!grid || !emptyState || !wallet) {
        return;
    }

    wallet.innerHTML = '<span>Current balance: <strong>' + formatMoney(marketplaceBalanceAmount) + '</strong></span>';

    var itemsToShow = [];
    for (var i = 0; i < marketplaceList.length; i++) {
        var currentItem = marketplaceList[i];
        var matchesCategory = (marketplaceCategoryName === 'all') || (currentItem.category === marketplaceCategoryName);
        var matchesSearch = true;

        if (marketplaceSearchText !== '') {
            var combined = currentItem.title + ' ' + currentItem.seller + ' ' + currentItem.location + ' ' + currentItem.description + ' ' + currentItem.tags.join(' ');
            matchesSearch = combined.toLowerCase().indexOf(marketplaceSearchText) !== -1;
        }

        if (matchesCategory && matchesSearch) {
            itemsToShow.push(currentItem);
        }
    }

    if (itemsToShow.length === 0) {
        grid.innerHTML = '';
        emptyState.classList.add('show');
        return;
    }

    emptyState.classList.remove('show');

    var html = '';
    for (var j = 0; j < itemsToShow.length; j++) {
        var item = itemsToShow[j];
        var tagsText = '';
        for (var t = 0; t < item.tags.length; t++) {
            tagsText += '<span class="marketplace-card-tag">' + item.tags[t] + '</span>';
        }
        var savedClass = savedMarketplaceIds.indexOf(item.id) !== -1 ? ' active' : '';

        html += '<div class="marketplace-card">';
        html += '<div class="marketplace-card-media" style="background-image: ' + item.background + ';">';
        html += '<span class="marketplace-card-pill">' + item.condition + '</span>';
        html += '<button class="marketplace-favorite' + savedClass + '" type="button" onclick="switchSavedMarketplaceItem(' + item.id + ')">♥</button>';
        html += '</div>';
        html += '<div class="marketplace-card-body">';
        html += '<div class="marketplace-card-price">' + formatMoney(item.price) + '</div>';
        html += '<h3 class="marketplace-card-title">' + item.title + '</h3>';
        html += '<p class="marketplace-card-description">' + item.description + '</p>';
        html += '<div class="marketplace-card-seller">Seller · ' + item.seller + '</div>';
        html += '<div class="marketplace-card-location">' + item.location + '</div>';
        html += '<div class="marketplace-card-tags">' + tagsText + '</div>';
        html += '<button class="marketplace-card-action" type="button" onclick="buyMarketplaceItem(' + item.id + ')">Buy Now</button>';
        html += '</div>';
        html += '</div>';
    }

    grid.innerHTML = html;
}

function checkUserLogin() {
    var token = localStorage.getItem('userToken');
    if (!token) {
        window.location.href = 'login.html';
        return false;
    }

    var username = localStorage.getItem('username');
    if (username) {
        var nameElement = document.getElementById('user-name');
        var avatarElement = document.getElementById('user-avatar');
        if (nameElement) {
            nameElement.textContent = username;
        }
        if (avatarElement) {
            avatarElement.textContent = username.charAt(0).toUpperCase();
        }
    }

    return true;
}

function logOutUser() {
    var shouldLogOut = confirm('Are you sure you want to logout?');
    if (!shouldLogOut) {
        return;
    }

    localStorage.removeItem('userToken');
    localStorage.removeItem('userId');
    localStorage.removeItem('username');
    localStorage.removeItem('userName');
    window.location.href = 'login.html';
}

function startApp() {
    if (!checkUserLogin()) {
        return;
    }

    messageList = mockMessageData.slice();
    streamList = mockStreamData.slice();

    postList = [];
    for (var i = 0; i < mockPostData.length; i++) {
        var postCopy = JSON.parse(JSON.stringify(mockPostData[i]));
        postCopy.id = i + 1;
        postList.push(postCopy);
    }

    marketplaceList = [];
    for (var j = 0; j < mockMarketplaceData.length; j++) {
        marketplaceList.push(JSON.parse(JSON.stringify(mockMarketplaceData[j])));
    }

    showMessages();
    showStreams();
    showPosts();
    showMarketplace();
}

startApp();

