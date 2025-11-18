<?php
include '../controllers/eventsController.php';
$eventsC = new EventsC();
$listeEvents = $eventsC->afficherEvents();


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
                <a href="admin.html" class="nav-link " data-section="dashboard" onclick="showAdminSection('dashboard', this)">
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
                <a href="#messages" class="nav-link" data-section="messages" onclick="showAdminSection('messages', this)">
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
                <a href="events.php" class="nav-link active" data-section="posts" onclick="showAdminSection('posts', this)">
                    <span class="nav-icon">🐦</span>
                    <span class="nav-text">Events</span>
                </a>
                <a href="#settings" class="nav-link" data-section="settings" onclick="showAdminSection('settings', this)">
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
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon users-icon">👥</div>
                        <div class="stat-content">
                            <div class="stat-label">Total Users</div>
                            <div class="stat-value" id="stat-total-users">0</div>
                            <div class="stat-change positive">+12% from last month</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon active-icon">🟢</div>
                        <div class="stat-content">
                            <div class="stat-label">Active Users</div>
                            <div class="stat-value" id="stat-active-users">0</div>
                            <div class="stat-change positive">Online now</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon messages-icon">💌</div>
                        <div class="stat-content">
                            <div class="stat-label">Total Messages</div>
                            <div class="stat-value" id="stat-total-messages">0</div>
                            <div class="stat-change positive">+5.2% from last week</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon servers-icon">💬</div>
                        <div class="stat-content">
                            <div class="stat-label">Total Servers</div>
                            <div class="stat-value" id="stat-total-servers">0</div>
                            <div class="stat-change">Active servers</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon streams-icon">📺</div>
                        <div class="stat-content">
                            <div class="stat-label">Live Streams</div>
                            <div class="stat-value" id="stat-live-streams">0</div>
                            <div class="stat-change positive">Currently streaming</div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon posts-icon">🐦</div>
                        <div class="stat-content">
                            <div class="stat-label">Total Posts</div>
                            <div class="stat-value" id="stat-total-posts">0</div>
                            <div class="stat-change positive">+8.1% from last week</div>
                        </div>
                    </div>
                </div>

                <!-- Charts/Graphs Area -->
                <div class="charts-grid">
                    <div class="chart-card">
                        <h3 class="chart-title">User Growth</h3>
                        <div class="chart-placeholder" id="user-growth-chart">
                            <div class="chart-bars">
                                <div class="chart-bar" style="height: 60%;"></div>
                                <div class="chart-bar" style="height: 75%;"></div>
                                <div class="chart-bar" style="height: 85%;"></div>
                                <div class="chart-bar" style="height: 70%;"></div>
                                <div class="chart-bar" style="height: 90%;"></div>
                                <div class="chart-bar" style="height: 100%;"></div>
                                <div class="chart-bar" style="height: 95%;"></div>
                            </div>
                            <div class="chart-labels">
                                <span>Mon</span>
                                <span>Tue</span>
                                <span>Wed</span>
                                <span>Thu</span>
                                <span>Fri</span>
                                <span>Sat</span>
                                <span>Sun</span>
                            </div>
                        </div>
                    </div>

                    <div class="chart-card">
                        <h3 class="chart-title">Activity Overview</h3>
                        <div class="activity-list" id="activity-list">
                            <!-- Activity items will be inserted here -->
                        </div>
                    </div>
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

<button class="action-btn btn-delete" data-id="<?= $event['id_event'] ?>" data-title="<?= htmlspecialchars($event['title'], ENT_QUOTES) ?>">
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
            return String(str).replace(/[&<>\"]/g, function(s) {
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
        document.addEventListener('click', function(e) {
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