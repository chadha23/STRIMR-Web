/* ============================================
   ADMIN DASHBOARD JAVASCRIPT
   ============================================
   Handles admin panel functionality
   ============================================ */

// ============================================
// GLOBAL STATE
// ============================================
let currentSection = 'dashboard';
let currentPage = 1;
let usersPerPage = 10;

// ============================================
// MOCK DATA (Replace with PHP Backend)
// ============================================
const mockUsers = [
    { id: 1, name: 'John Doe', username: 'johndoe', email: 'john@example.com', joined: '2024-01-15', status: 'active', messages: 245 },
    { id: 2, name: 'Jane Smith', username: 'janesmith', email: 'jane@example.com', joined: '2024-01-10', status: 'active', messages: 189 },
    { id: 3, name: 'Bob Johnson', username: 'bobj', email: 'bob@example.com', joined: '2024-01-08', status: 'inactive', messages: 67 },
    { id: 4, name: 'Alice Williams', username: 'alicew', email: 'alice@example.com', joined: '2024-01-20', status: 'active', messages: 312 },
    { id: 5, name: 'Charlie Brown', username: 'charlieb', email: 'charlie@example.com', joined: '2024-01-05', status: 'active', messages: 156 },
];

const mockServers = [
    { id: 1, name: 'General Server', channels: 5, members: 123, created: '2024-01-01' },
    { id: 2, name: 'Gaming Server', channels: 8, members: 456, created: '2024-01-05' },
    { id: 3, name: 'Music Server', channels: 3, members: 89, created: '2024-01-10' },
];

const mockMessages = [
    { id: 1, user: 'John Doe', content: 'Hello everyone!', server: 'General', channel: 'general', time: '2 hours ago' },
    { id: 2, user: 'Jane Smith', content: 'Great discussion!', server: 'Gaming', channel: 'chat', time: '3 hours ago' },
    { id: 3, user: 'Bob Johnson', content: 'Thanks for the help!', server: 'General', channel: 'support', time: '5 hours ago' },
];

const mockStreams = [
    { id: 1, streamer: 'ProGamer123', title: 'Epic Gaming Session', viewers: 1234, status: 'live', started: '2 hours ago' },
    { id: 2, streamer: 'MusicMaster', title: 'Music Production', viewers: 856, status: 'live', started: '1 hour ago' },
];

const mockPosts = [
    { id: 1, user: 'John Doe', content: 'Great project!', likes: 45, retweets: 12, time: '1 hour ago' },
    { id: 2, user: 'Jane Smith', content: 'Amazing work!', likes: 89, retweets: 23, time: '2 hours ago' },
];

// ============================================
// INITIALIZATION
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    // Check if admin is logged in
    // TODO: Add admin authentication check
    // const adminToken = localStorage.getItem('adminToken');
    // if (!adminToken) {
    //     window.location.href = 'login.html';
    //     return;
    // }
    
    // Load dashboard data
    loadDashboard();
    loadUsers();
    loadServers();
    loadMessages();
    loadStreams();
    loadPosts();
});
//controle de saisie serveur
function validateAddServer() {
    const name = document.querySelector('form.add-form input[name="name"]').value.trim();

    if (name === "") {
        alert("Le nom du serveur est obligatoire !");
        return false; // annule l'envoi du formulaire
    }

    if (name.length < 3) {
        alert("Le nom doit contenir au moins 3 caractères.");
        return false;
    }

    return true; // OK
}


// ============================================
// SECTION SWITCHING
// ============================================
function switchSection(section) {
    currentSection = section;
    
    // Update active nav link
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
    });
    event.target.closest('.nav-link').classList.add('active');
    
    // Hide all sections
    document.querySelectorAll('.content-section').forEach(sec => {
        sec.classList.remove('active');
    });
    
    // Show selected section
    document.getElementById(section + '-section').classList.add('active');
    
    // Update page title
    const titles = {
        'dashboard': { title: 'Dashboard', subtitle: 'Overview of your platform' },
        'users': { title: 'User Management', subtitle: 'Manage all users' },
        'servers': { title: 'Server Management', subtitle: 'Manage all servers' },
        'messages': { title: 'Message Management', subtitle: 'View all messages' },
        'streams': { title: 'Stream Management', subtitle: 'Manage live streams' },
        'posts': { title: 'Post Management', subtitle: 'Manage all posts' },
        'settings': { title: 'Settings', subtitle: 'Configure your platform' },
    };
    
    const pageInfo = titles[section] || { title: 'Admin Panel', subtitle: '' };
    document.getElementById('page-title').textContent = pageInfo.title;
    document.getElementById('page-subtitle').textContent = pageInfo.subtitle;
}

// ============================================
// DASHBOARD FUNCTIONS
// ============================================
function loadDashboard() {
    // TODO: Load statistics from PHP backend
    // fetch('your-backend/api/admin/stats.php')
    //     .then(response => response.json())
    //     .then(data => {
    //         updateStats(data);
    //     });
    
    // Mock data for now
    updateStats({
        totalUsers: 1250,
        activeUsers: 342,
        totalMessages: 15420,
        totalServers: 45,
        liveStreams: 8,
        totalPosts: 8920
    });
    
    loadRecentActivity();
}

function updateStats(stats) {
    document.getElementById('stat-total-users').textContent = stats.totalUsers.toLocaleString();
    document.getElementById('stat-active-users').textContent = stats.activeUsers.toLocaleString();
    document.getElementById('stat-total-messages').textContent = stats.totalMessages.toLocaleString();
    document.getElementById('stat-total-servers').textContent = stats.totalServers.toLocaleString();
    document.getElementById('stat-live-streams').textContent = stats.liveStreams.toLocaleString();
    document.getElementById('stat-total-posts').textContent = stats.totalPosts.toLocaleString();
}

function loadRecentActivity() {
    const container = document.getElementById('activity-list');
    container.innerHTML = '';
    
    const activities = [
        { icon: '👤', text: 'New user registered: alicew', time: '5 minutes ago' },
        { icon: '💬', text: 'New message in General Server', time: '10 minutes ago' },
        { icon: '📺', text: 'Stream started: ProGamer123', time: '15 minutes ago' },
        { icon: '🐦', text: 'New post created by johndoe', time: '20 minutes ago' },
    ];
    
    activities.forEach(activity => {
        const item = document.createElement('div');
        item.className = 'activity-item';
        item.innerHTML = `
            <div class="activity-icon">${activity.icon}</div>
            <div class="activity-content">
                <div class="activity-text">${activity.text}</div>
                <div class="activity-time">${activity.time}</div>
            </div>
        `;
        container.appendChild(item);
    });
    
    // Load recent activity table
    const tableBody = document.getElementById('recent-activity-table');
    tableBody.innerHTML = '';
    
    mockMessages.slice(0, 5).forEach(msg => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${msg.user}</td>
            <td>Sent message</td>
            <td>${msg.content.substring(0, 50)}...</td>
            <td>${msg.time}</td>
        `;
        tableBody.appendChild(row);
    });
}

// ============================================
// USERS FUNCTIONS
// ============================================
function loadUsers() {
    // TODO: Load users from PHP backend
    // fetch(`your-backend/api/admin/users.php?page=${currentPage}`)
    //     .then(response => response.json())
    //     .then(data => {
    //         renderUsers(data.users);
    //         updatePagination(data.totalPages);
    //     });
    
    renderUsers(mockUsers);
    updatePagination(1);
}

function renderUsers(users) {
    const tableBody = document.getElementById('users-table');
    tableBody.innerHTML = '';
    
    users.forEach(user => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>#${user.id}</td>
            <td>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #5865f2 0%, #4752c4 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 12px;">
                        ${user.name.charAt(0)}
                    </div>
                    <div>
                        <div style="color: white; font-weight: 600;">${user.name}</div>
                        <div style="color: #8e9297; font-size: 12px;">@${user.username}</div>
                    </div>
                </div>
            </td>
            <td>${user.email}</td>
            <td>${formatDate(user.joined)}</td>
            <td><span class="status-badge ${user.status === 'active' ? 'status-active' : 'status-inactive'}">${user.status}</span></td>
            <td>${user.messages}</td>
            <td>
                <button class="action-btn btn-view" onclick="viewUser(${user.id})">View</button>
                <button class="action-btn btn-edit" onclick="editUser(${user.id})">Edit</button>
                <button class="action-btn btn-delete" onclick="deleteUser(${user.id})">Delete</button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

function filterUsers() {
    const filter = document.getElementById('user-filter').value;
    // TODO: Apply filter to users list
    loadUsers();
}

function sortUsers() {
    const sort = document.getElementById('user-sort').value;
    // TODO: Apply sort to users list
    loadUsers();
}

function viewUser(userId) {
    // TODO: Show user details modal
    alert(`View user ${userId} - Connect to your PHP backend`);
}

function editUser(userId) {
    // TODO: Show edit user modal
    alert(`Edit user ${userId} - Connect to your PHP backend`);
}

function deleteUser(userId) {
    if (confirm(`Are you sure you want to delete user #${userId}?`)) {
        // TODO: Delete user via PHP backend
        // fetch(`your-backend/api/admin/deleteUser.php`, {
        //     method: 'POST',
        //     body: JSON.stringify({ userId: userId })
        // })
        alert(`Delete user ${userId} - Connect to your PHP backend`);
        loadUsers();
    }
}

// ============================================
// SERVERS FUNCTIONS
// ============================================
function loadServers() {
    // TODO: Load servers from PHP backend
    const container = document.getElementById('servers');
    container.innerHTML = '';
    
    mockServers.forEach(server => {
        const card = document.createElement('div');
        card.className = 'server-card';
        card.innerHTML = `
            <h3 style="color: white; margin-bottom: 12px;">${server.name}</h3>
            <div style="display: flex; flex-direction: column; gap: 8px; color: #8e9297; font-size: 14px;">
                <div>Channels: <span style="color: white;">${server.channels}</span></div>
                <div>Members: <span style="color: white;">${server.members}</span></div>
                <div>Created: <span style="color: white;">${formatDate(server.created)}</span></div>
            </div>
            <div style="margin-top: 16px; display: flex; gap: 8px;">
                <button class="action-btn btn-view" onclick="viewServer(${server.id})">View</button>
                <button class="action-btn btn-edit" onclick="editServer(${server.id})">Edit</button>
                <button class="action-btn btn-delete" onclick="deleteServer(${server.id})">Delete</button>
            </div>
        `;
        container.appendChild(card);
    });
}

function viewServer(serverId) {
    alert(`View server ${serverId} - Connect to your PHP backend`);
}

function editServer(serverId) {
    alert(`Edit server ${serverId} - Connect to your PHP backend`);
}

function deleteServer(serverId) {
    if (confirm(`Are you sure you want to delete server #${serverId}?`)) {
        alert(`Delete server ${serverId} - Connect to your PHP backend`);
        loadServers();
    }
}

// ============================================
// MESSAGES FUNCTIONS
// ============================================
function loadMessages() {
    const tableBody = document.getElementById('messages-table');
    tableBody.innerHTML = '';
    
    mockMessages.forEach(msg => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>#${msg.id}</td>
            <td>${msg.user}</td>
            <td>${msg.content.substring(0, 60)}...</td>
            <td>${msg.server}</td>
            <td>#${msg.channel}</td>
            <td>${msg.time}</td>
            <td>
                <button class="action-btn btn-view" onclick="viewMessage(${msg.id})">View</button>
                <button class="action-btn btn-delete" onclick="deleteMessage(${msg.id})">Delete</button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

function viewMessage(messageId) {
    alert(`View message ${messageId} - Connect to your PHP backend`);
}

function deleteMessage(messageId) {
    if (confirm(`Delete message #${messageId}?`)) {
        alert(`Delete message ${messageId} - Connect to your PHP backend`);
        loadMessages();
    }
}

// ============================================
// STREAMS FUNCTIONS
// ============================================
function loadStreams() {
    const tableBody = document.getElementById('streams-table');
    tableBody.innerHTML = '';
    
    mockStreams.forEach(stream => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>#${stream.id}</td>
            <td>${stream.streamer}</td>
            <td>${stream.title}</td>
            <td>${stream.viewers.toLocaleString()}</td>
            <td><span class="status-badge ${stream.status === 'live' ? 'status-active' : 'status-inactive'}">${stream.status}</span></td>
            <td>${stream.started}</td>
            <td>
                <button class="action-btn btn-view" onclick="viewStream(${stream.id})">View</button>
                <button class="action-btn btn-delete" onclick="endStream(${stream.id})">End</button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

function viewStream(streamId) {
    alert(`View stream ${streamId} - Connect to your PHP backend`);
}

function endStream(streamId) {
    if (confirm(`End stream #${streamId}?`)) {
        alert(`End stream ${streamId} - Connect to your PHP backend`);
        loadStreams();
    }
}

// ============================================
// POSTS FUNCTIONS
// ============================================
function loadPosts() {
    const tableBody = document.getElementById('posts-table');
    tableBody.innerHTML = '';
    
    mockPosts.forEach(post => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>#${post.id}</td>
            <td>${post.user}</td>
            <td>${post.content.substring(0, 60)}...</td>
            <td>${post.likes}</td>
            <td>${post.retweets}</td>
            <td>${post.time}</td>
            <td>
                <button class="action-btn btn-view" onclick="viewPost(${post.id})">View</button>
                <button class="action-btn btn-delete" onclick="deletePost(${post.id})">Delete</button>
            </td>
        `;
        tableBody.appendChild(row);
    });
}

function viewPost(postId) {
    alert(`View post ${postId} - Connect to your PHP backend`);
}

function deletePost(postId) {
    if (confirm(`Delete post #${postId}?`)) {
        alert(`Delete post ${postId} - Connect to your PHP backend`);
        loadPosts();
    }
}

// ============================================
// MODAL FUNCTIONS
// ============================================
function showAddUserModal() {
    document.getElementById('add-user-modal').classList.add('show');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('show');
}

function handleAddUser(event) {
    event.preventDefault();
    // TODO: Add user via PHP backend
    alert('Add user - Connect to your PHP backend');
    closeModal('add-user-modal');
}

function showAddServerModal() {
    alert('Add server modal - Connect to your PHP backend');
}

// ============================================
// UTILITY FUNCTIONS
// ============================================
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function refreshData() {
    // TODO: Refresh all data from PHP backend
    loadDashboard();
    loadUsers();
    loadServers();
    loadMessages();
    loadStreams();
    loadPosts();
    
    // Show feedback
    const btn = event.target;
    btn.style.transform = 'rotate(360deg)';
    setTimeout(() => {
        btn.style.transform = 'rotate(0deg)';
    }, 500);
}

function changePage(direction) {
    currentPage += direction;
    if (currentPage < 1) currentPage = 1;
    // TODO: Load page from PHP backend
    loadUsers();
    document.getElementById('current-page').textContent = currentPage;
}

function updatePagination(totalPages) {
    document.getElementById('total-pages').textContent = totalPages;
    document.getElementById('current-page').textContent = currentPage;
}

function handleAdminLogout() {
    if (confirm('Are you sure you want to logout?')) {
        // TODO: Clear admin session
        localStorage.removeItem('adminToken');
        window.location.href = 'login.html';
    }
}



