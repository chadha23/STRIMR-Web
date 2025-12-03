# Development Guide - Step by Step

This guide shows you exactly how to work with this frontend template and connect your backend.

## 🎯 Step 1: Get Frontend Running

### Install Dependencies
```bash
npm install
```

### Start Development Server
```bash
npm run dev
```

### Open Browser
- Go to: `http://localhost:3000`
- You should see the Discord clone interface with mock data

✅ **At this point, frontend works but uses fake data**

---

## 🎯 Step 2: Understand the Code Structure

### Main Entry Point
- **`src/main.jsx`** - Starts React app (don't modify)

### Main App Component
- **`src/App.jsx`** - Coordinates everything
  - Manages which server/channel/view is selected
  - Renders different components based on state
  - **You can modify:** Add new views, change layout

### UI Components
- **`src/components/`** - All UI pieces
  - Each component is independent
  - **You can modify:** Change appearance, add features

### API Service
- **`src/services/api.js`** - **THIS IS WHERE YOU CONNECT BACKEND**
  - Currently uses mock data
  - **You will modify:** Replace mock data with real API calls

---

## 🎯 Step 3: Plan Your Backend

### What Your Backend Needs to Provide

Look at `src/services/api.js` - each function shows:
- What endpoint it expects
- What data format to send
- What data format to return

### Example: Getting Messages

**Frontend expects:**
```javascript
// In api.js
export async function getMessages(serverId, channelId) {
  // Calls: GET api/getMessages.php?server=ID&channel=ID
  // Expects: { success: true, messages: [...] }
}
```

**Your PHP should:**
```php
// api/getMessages.php
// 1. Get server and channel from URL
$serverId = $_GET['server'];
$channelId = $_GET['channel'];

// 2. Query database
$messages = /* SELECT from messages table */;

// 3. Return JSON
echo json_encode([
    'success' => true,
    'messages' => $messages
]);
```

**See `BACKEND_REQUIREMENTS.md` for complete specifications!**

---

## 🎯 Step 4: Build Your Backend (PHP + SQL)

### Database Structure

You'll need tables like:
- `servers` - Store server information
- `channels` - Store channels for each server
- `messages` - Store chat messages
- `users` - Store user accounts
- `streams` - Store live streams
- `posts` - Store Twitter feed posts

### PHP Endpoints

Create PHP files matching the endpoints in `api.js`:
- `api/getMessages.php`
- `api/sendMessage.php`
- `api/getServers.php`
- `api/getChannels.php`
- etc.

**Important:** Each PHP file must:
1. Accept the request (GET or POST)
2. Query your database
3. Return JSON in the exact format shown in `BACKEND_REQUIREMENTS.md`
4. Handle CORS (allow requests from `http://localhost:3000`)

---

## 🎯 Step 5: Connect Frontend to Backend

### Update API Base URL

**File:** `src/services/api.js`

```javascript
// Change this:
const API_BASE_URL = 'http://localhost/your-php-backend/api';

// To your actual backend URL:
const API_BASE_URL = 'http://localhost/my-backend/api';
```

### Replace Mock Data with Real API Calls

**Example - Get Messages:**

**Before (mock data):**
```javascript
export async function getMessages(serverId, channelId) {
  // Mock data
  return [
    { id: 1, username: 'User1', content: 'Hello!' }
  ];
}
```

**After (real API):**
```javascript
export async function getMessages(serverId, channelId) {
  try {
    const response = await fetch(
      `${API_BASE_URL}/getMessages.php?server=${serverId}&channel=${channelId}`
    );
    
    if (!response.ok) {
      throw new Error('Failed to fetch messages');
    }
    
    const data = await response.json();
    
    if (!data.success) {
      throw new Error(data.error || 'Failed to get messages');
    }
    
    return data.messages;
  } catch (error) {
    console.error('Error loading messages:', error);
    // Return empty array or show error to user
    return [];
  }
}
```

### Do This for All API Functions

Go through each function in `api.js`:
1. Remove mock data
2. Add fetch() call to your PHP endpoint
3. Handle response
4. Handle errors

---

## 🎯 Step 6: Test Connection

### Test Each Endpoint

1. **Test PHP directly:**
   - Open: `http://localhost/your-backend/api/getServers.php`
   - Should see JSON response

2. **Test from frontend:**
   - Open browser console (F12)
   - Check Network tab for requests
   - Verify requests go to your PHP
   - Check for errors

3. **Test full flow:**
   - Send a message from frontend
   - Check if it appears
   - Check database to see if it was saved

---

## 🎯 Step 7: Customize & Add Features

### Modify UI Components

**Example: Add delete message button**

**File:** `src/components/ChatArea.jsx`

```javascript
// 1. Add API function call (in api.js first)
import { deleteMessage } from '../services/api';

// 2. Add handler function
const handleDelete = async (messageId) => {
  if (confirm('Delete this message?')) {
    try {
      await deleteMessage(messageId);
      loadMessages(); // Reload messages
    } catch (error) {
      console.error('Failed to delete:', error);
    }
  }
};

// 3. Add button in message display
{messages.map((message) => (
  <div key={message.id}>
    <p>{message.content}</p>
    <button onClick={() => handleDelete(message.id)}>Delete</button>
  </div>
))}
```

### Add New API Function

**File:** `src/services/api.js`

```javascript
export async function deleteMessage(messageId) {
  const response = await fetch(`${API_BASE_URL}/deleteMessage.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ messageId })
  });
  
  const data = await response.json();
  
  if (!data.success) {
    throw new Error(data.error || 'Failed to delete message');
  }
  
  return data;
}
```

**Then create PHP endpoint:** `api/deleteMessage.php`

---

## 🎯 Step 8: Handle Errors & Edge Cases

### Frontend Error Handling

```javascript
try {
  const data = await getMessages();
  setMessages(data);
} catch (error) {
  // Show error message to user
  setError('Failed to load messages');
  console.error(error);
}
```

### Backend Error Handling

Your PHP should always return consistent format:
```php
// Success
echo json_encode(['success' => true, 'data' => $data]);

// Error
echo json_encode(['success' => false, 'error' => 'Error message']);
```

---

## 🎯 Step 9: Add Authentication (Example)

### Frontend

**File:** `src/services/api.js`
```javascript
export async function login(username, password) {
  const response = await fetch(`${API_BASE_URL}/login.php`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ username, password })
  });
  const data = await response.json();
  
  if (data.success) {
    // Store token/session
    localStorage.setItem('token', data.token);
  }
  
  return data;
}
```

**File:** `src/components/Login.jsx` (create new)
```javascript
const [username, setUsername] = useState('');
const [password, setPassword] = useState('');

const handleLogin = async () => {
  const result = await login(username, password);
  if (result.success) {
    // Redirect or update state
  }
};
```

### Backend

**File:** `api/login.php`
```php
$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'];
$password = $input['password'];

// Verify credentials
// Generate token
// Return token
```

---

## 📋 Checklist

- [ ] Frontend runs with `npm run dev`
- [ ] Understand code structure
- [ ] Database tables created
- [ ] PHP endpoints created
- [ ] PHP returns correct JSON format
- [ ] CORS configured in PHP
- [ ] API_BASE_URL updated in frontend
- [ ] Mock data replaced with real API calls
- [ ] Tested each endpoint
- [ ] Error handling added
- [ ] Features customized

---

## 🐛 Common Issues & Solutions

### CORS Error
**Problem:** Browser blocks requests to PHP
**Solution:** Add CORS headers in PHP:
```php
header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
```

### 404 Not Found
**Problem:** PHP endpoint not found
**Solution:** 
- Check API_BASE_URL is correct
- Check PHP file exists
- Check file path is correct

### Wrong JSON Format
**Problem:** Frontend can't parse response
**Solution:**
- Check PHP returns valid JSON
- Check format matches BACKEND_REQUIREMENTS.md
- Use `json_encode()` in PHP

### Data Not Showing
**Problem:** Messages/posts not appearing
**Solution:**
- Check browser console for errors
- Check Network tab for API calls
- Verify PHP returns data
- Check database has data

---

## 💡 Pro Tips

1. **Start with one endpoint** - Get `getMessages` working first
2. **Test PHP directly** - Open PHP file in browser to see JSON
3. **Use browser console** - Check for errors and network requests
4. **Read the comments** - Every file explains what it does
5. **Iterate incrementally** - Don't change everything at once

---

**You're ready to build! Start with Step 1 and work through each step. Good luck! 🚀**



