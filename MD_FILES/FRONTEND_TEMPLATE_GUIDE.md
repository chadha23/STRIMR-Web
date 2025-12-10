# Frontend Template Guide - For Backend Developers

This is a **frontend template** ready for you to connect your PHP/JS/SQL backend.

## 📁 Understanding the Structure

```
HELL/
├── src/
│   ├── components/              # UI Components (modify these!)
│   │   ├── ServersSidebar.jsx   # Left sidebar - server list
│   │   ├── ChannelsSidebar.jsx  # Middle sidebar - channels & view switcher
│   │   ├── ChatArea.jsx         # Chat messages display
│   │   ├── LiveStreamArea.jsx   # Live streaming interface
│   │   └── TwitterFeed.jsx      # Twitter-style feed
│   │
│   ├── services/
│   │   └── api.js              # ⭐ CONNECT YOUR BACKEND HERE!
│   │
│   ├── App.jsx                  # Main app - coordinates everything
│   ├── main.jsx                 # Entry point (don't modify)
│   └── index.css                # Global styles (modify for colors/themes)
│
├── package.json                 # Dependencies (add packages here if needed)
└── README.md                    # Overview
```

## 🎯 Where to Make Changes

### 1. Connect Your Backend (MOST IMPORTANT)

**File:** `src/services/api.js`

This is where you connect your PHP backend. Currently it uses mock data.

**Steps:**
1. Update the API URL:
   ```javascript
   const API_BASE_URL = 'http://localhost/your-backend/api';
   ```

2. Replace mock data with real API calls:
   ```javascript
   // BEFORE (mock data):
   export async function getMessages(serverId, channelId) {
     return [/* fake data */];
   }
   
   // AFTER (your PHP backend):
   export async function getMessages(serverId, channelId) {
     const response = await fetch(`${API_BASE_URL}/getMessages.php?server=${serverId}&channel=${channelId}`);
     const data = await response.json();
     return data.messages;
   }
   ```

**See `BACKEND_REQUIREMENTS.md` for exact endpoint specifications!**

### 2. Modify UI Components

**Files:** `src/components/*.jsx`

Each component is independent and can be modified:

- **ChatArea.jsx:** Change how messages look, add features
- **ServersSidebar.jsx:** Modify server list appearance
- **ChannelsSidebar.jsx:** Change channel display
- **LiveStreamArea.jsx:** Customize streaming interface
- **TwitterFeed.jsx:** Modify feed appearance

**Example - Add a feature to ChatArea:**
```javascript
// In ChatArea.jsx, add a delete message button:
const handleDelete = async (messageId) => {
  // Call your API
  await deleteMessage(messageId);
  // Reload messages
  loadMessages();
};
```

### 3. Change Styling/Colors

**File:** `tailwind.config.js`
```javascript
theme: {
  extend: {
    colors: {
      discord: {
        dark: '#202225',  // Change these colors
        primary: '#5865f2',
      }
    }
  }
}
```

**File:** `src/index.css`
- Global styles, scrollbar colors, etc.

### 4. Add New Components

1. Create new file: `src/components/YourComponent.jsx`
2. Import in `App.jsx`:
   ```javascript
   import YourComponent from './components/YourComponent'
   ```
3. Use it:
   ```javascript
   {view === 'newview' && <YourComponent />}
   ```

### 5. Add New Features

**Example: Add user authentication**

1. Create: `src/components/Login.jsx`
2. Create API function: `src/services/api.js`
   ```javascript
   export async function login(username, password) {
     const response = await fetch(`${API_BASE_URL}/login.php`, {
       method: 'POST',
       body: JSON.stringify({ username, password })
     });
     return await response.json();
   }
   ```
3. Use in component or App.jsx

## 🔌 How Frontend Connects to Backend

### The Flow:

```
User Action (clicks button, types message)
    ↓
React Component (ChatArea.jsx, etc.)
    ↓
API Service (src/services/api.js)
    ↓
Your PHP Backend (your PHP files)
    ↓
Your Database (MySQL/SQL)
    ↓
Response comes back through same chain
```

### Example: Sending a Message

**1. User types and clicks send**
```javascript
// In ChatArea.jsx
const handleSend = async (e) => {
  const newMessage = await sendMessage(server, channel, input);
  // sendMessage() is defined in api.js
}
```

**2. API service calls your PHP**
```javascript
// In src/services/api.js
export async function sendMessage(serverId, channelId, content) {
  const response = await fetch(`${API_BASE_URL}/sendMessage.php`, {
    method: 'POST',
    body: JSON.stringify({ serverId, channelId, content })
  });
  return await response.json();
}
```

**3. Your PHP processes it**
```php
// Your PHP file: api/sendMessage.php
$input = json_decode(file_get_contents('php://input'), true);
// Save to database
// Return JSON response
```

## 📝 Key Concepts for Modification

### React State
- `useState`: Stores data that changes (like messages list)
- When state changes, React re-renders the component
- Example: `const [messages, setMessages] = useState([])`

### useEffect Hook
- Runs code when component loads or when data changes
- Example: Load messages when channel changes
```javascript
useEffect(() => {
  loadMessages();
}, [channel]); // Re-run when channel changes
```

### API Calls
- Use `fetch()` to call your PHP backend
- Always use `async/await` for API calls
- Handle errors with try/catch

### Component Props
- Data passed from parent to child
- Example: `<ChatArea server="general" channel="general" />`
- Access via: `const ChatArea = ({ server, channel }) => { ... }`

## 🛠 Common Modifications

### Add a New Button
```javascript
<button onClick={handleClick} className="bg-blue-500 px-4 py-2 rounded">
  Click Me
</button>
```

### Add a New API Endpoint
```javascript
// In src/services/api.js
export async function yourNewFunction(param) {
  const response = await fetch(`${API_BASE_URL}/yourEndpoint.php?param=${param}`);
  const data = await response.json();
  return data;
}
```

### Add a New View/Page
```javascript
// In App.jsx
const [view, setView] = useState('chat'); // Add 'newview' option

// In ChannelsSidebar.jsx, add button:
<button onClick={() => onViewChange('newview')}>New View</button>

// In App.jsx, render it:
{view === 'newview' && <YourNewComponent />}
```

### Modify Message Display
```javascript
// In ChatArea.jsx, find where messages are displayed:
{messages.map((message) => (
  <div>
    {/* Modify this structure */}
    <span>{message.username}</span>
    <p>{message.content}</p>
  </div>
))}
```

## 🎨 Styling Tips

### Tailwind CSS Classes
- `bg-color`: Background color
- `text-color`: Text color
- `px-4`: Padding horizontal
- `py-2`: Padding vertical
- `rounded`: Rounded corners
- `hover:bg-gray`: Hover effect
- `flex`: Flexbox layout

### Color Scheme
- Discord dark theme by default
- Change colors in `tailwind.config.js`
- Or use inline classes: `className="bg-blue-500"`

## 📚 File-by-File Explanation

### `src/App.jsx`
- **What it does:** Main coordinator
- **What you can change:** Add new views, manage global state
- **Key parts:** State management, view switching

### `src/components/ChatArea.jsx`
- **What it does:** Displays chat messages
- **What you can change:** Message layout, add features (delete, edit, reactions)
- **Key parts:** `loadMessages()`, `handleSend()`, message rendering

### `src/components/ServersSidebar.jsx`
- **What it does:** Shows server list
- **What you can change:** Server icons, add/remove servers, styling
- **Key parts:** Server selection, server list rendering

### `src/services/api.js`
- **What it does:** Connects to your backend
- **What you can change:** Add new API functions, modify endpoints
- **Key parts:** All API functions, API_BASE_URL

## 🚀 Development Workflow

1. **Start frontend:** `npm run dev`
2. **Make changes** to components or API
3. **Test in browser** (auto-refreshes)
4. **Check console** (F12) for errors
5. **Connect backend** when ready

## 💡 Tips

- **Read the comments** - Every file has explanations
- **Start small** - Get one feature working, then add more
- **Test incrementally** - Don't change everything at once
- **Use browser console** - `console.log()` to debug
- **Check BACKEND_REQUIREMENTS.md** - Know what your backend needs to return

## 🔍 Debugging

**Browser not updating?**
- Check terminal for errors
- Hard refresh: Ctrl+Shift+R

**API not working?**
- Check `API_BASE_URL` in `api.js`
- Check browser console (F12) for errors
- Test your PHP endpoint directly in browser

**Component not showing?**
- Check if it's imported in App.jsx
- Check if view state matches
- Check browser console for errors

---

**Remember:** This is YOUR template - modify it however you want! The structure is clear and well-commented to help you customize it.



