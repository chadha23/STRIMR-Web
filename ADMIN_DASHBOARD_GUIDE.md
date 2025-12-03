# Admin Dashboard Guide

## 📁 Files Created

- **`admin.html`** - Admin dashboard page
- **`admin-styles.css`** - Dashboard styling
- **`admin-script.js`** - Dashboard functionality

## 🎯 Features

### Dashboard Section
- **Statistics Cards:**
  - Total Users
  - Active Users (currently online)
  - Total Messages
  - Total Servers
  - Live Streams
  - Total Posts
- **User Growth Chart** - Visual representation
- **Activity Overview** - Recent activities
- **Recent Activity Table** - Latest actions

### Users Section
- **User Management Table:**
  - User ID, Name, Email
  - Join date
  - Status (Active/Inactive)
  - Message count
  - Actions (View, Edit, Delete)
- **Filters:** All, Active, Inactive, New
- **Sorting:** Newest, Oldest, Name, Activity
- **Pagination** - Navigate through users

### Servers Section
- **Server Cards** with details
- **View/Edit/Delete** actions

### Messages Section
- **Messages Table** with all messages
- **View/Delete** actions

### Streams Section
- **Streams Table** with live streams
- **View/End** actions

### Posts Section
- **Posts Table** with all posts
- **View/Delete** actions

### Settings Section
- **General Settings:**
  - Site name
  - Maintenance mode toggle
  - Max users limit

## 🔌 PHP Backend Requirements

### Dashboard Statistics
**Endpoint:** `GET api/admin/stats.php`

**Returns:**
```json
{
  "success": true,
  "stats": {
    "totalUsers": 1250,
    "activeUsers": 342,
    "totalMessages": 15420,
    "totalServers": 45,
    "liveStreams": 8,
    "totalPosts": 8920
  }
}
```

### Get Users
**Endpoint:** `GET api/admin/users.php?page=1&filter=all&sort=newest`

**Returns:**
```json
{
  "success": true,
  "users": [
    {
      "id": 1,
      "name": "John Doe",
      "username": "johndoe",
      "email": "john@example.com",
      "joined": "2024-01-15",
      "status": "active",
      "messages": 245
    }
  ],
  "totalPages": 10,
  "currentPage": 1
}
```

### Get Servers
**Endpoint:** `GET api/admin/servers.php`

**Returns:**
```json
{
  "success": true,
  "servers": [
    {
      "id": 1,
      "name": "General Server",
      "channels": 5,
      "members": 123,
      "created": "2024-01-01"
    }
  ]
}
```

### Get Messages
**Endpoint:** `GET api/admin/messages.php`

**Returns:**
```json
{
  "success": true,
  "messages": [
    {
      "id": 1,
      "user": "John Doe",
      "content": "Hello everyone!",
      "server": "General",
      "channel": "general",
      "time": "2 hours ago"
    }
  ]
}
```

### Get Streams
**Endpoint:** `GET api/admin/streams.php`

### Get Posts
**Endpoint:** `GET api/admin/posts.php`

### Delete User
**Endpoint:** `POST api/admin/deleteUser.php`
**Body:** `{ userId: 1 }`

### Delete Message
**Endpoint:** `POST api/admin/deleteMessage.php`
**Body:** `{ messageId: 1 }`

### Delete Post
**Endpoint:** `POST api/admin/deletePost.php`
**Body:** `{ postId: 1 }`

### End Stream
**Endpoint:** `POST api/admin/endStream.php`
**Body:** `{ streamId: 1 }`

## 📍 Where to Connect Backend

### In `admin-script.js`:

1. **`loadDashboard()`** - Line ~80
   - Replace with: `fetch('your-backend/api/admin/stats.php')`

2. **`loadUsers()`** - Line ~120
   - Replace with: `fetch('your-backend/api/admin/users.php?page=' + currentPage)`

3. **`loadServers()`** - Line ~180
   - Replace with: `fetch('your-backend/api/admin/servers.php')`

4. **`loadMessages()`** - Line ~220
   - Replace with: `fetch('your-backend/api/admin/messages.php')`

5. **`loadStreams()`** - Line ~250
   - Replace with: `fetch('your-backend/api/admin/streams.php')`

6. **`loadPosts()`** - Line ~280
   - Replace with: `fetch('your-backend/api/admin/posts.php')`

7. **Delete functions** - Replace with PHP calls

## 🔐 Admin Authentication

**TODO:** Add admin login check at the top of `admin-script.js`:

```javascript
// Check admin authentication
const adminToken = localStorage.getItem('adminToken');
if (!adminToken) {
    window.location.href = 'login.html';
    return;
}
```

## 📊 Statistics Calculation

Your PHP should calculate:
- **Total Users:** Count from users table
- **Active Users:** Users who logged in within last 15 minutes
- **Total Messages:** Count from messages table
- **Total Servers:** Count from servers table
- **Live Streams:** Streams where is_live = true
- **Total Posts:** Count from posts table

## 🎨 Customization

- **Change colors:** Edit `admin-styles.css`
- **Add sections:** Add new section in HTML and JavaScript
- **Modify tables:** Edit table structure in HTML

---

**Ready to connect to your PHP backend!**



