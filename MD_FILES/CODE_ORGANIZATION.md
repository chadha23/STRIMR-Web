# Code Organization Guide

This document explains the structure and organization of all code files.

## 📁 File Structure

```
HELL/
├── index.html          # HTML structure (200 lines)
├── styles.css          # All CSS styling (800 lines)
├── script.js           # All JavaScript functionality (650 lines)
└── CODE_ORGANIZATION.md # This file (documentation)
```

## 📄 File Breakdown

### 1. `index.html` (HTML Structure)

**Purpose:** Contains the HTML structure and layout of the website.

**Lines Breakdown:**
- **Lines 1-10:** HTML head section (meta tags, title, CSS link)
- **Lines 11-30:** Top navigation bar (Servers, Stream, Feed buttons)
- **Lines 31-34:** Main wrapper container
- **Lines 35-120:** Servers page (Discord-style layout)
  - Lines 40-60: Servers sidebar (left)
  - Lines 62-95: Channels sidebar (middle)
  - Lines 97-120: Chat area (right)
- **Lines 125-150:** Stream page (Twitch-style grid)
  - Lines 130-135: Search bar
  - Lines 137-145: Streams grid container
- **Lines 155-200:** Feed page (Twitter-style feed)
  - Lines 160-180: Compose box
  - Lines 182-195: Posts container
- **Lines 201-210:** JavaScript link

**Key Elements:**
- `id="servers-page"` - Servers page container
- `id="stream-page"` - Stream page container
- `id="feed-page"` - Feed page container
- `id="messages-container"` - Where messages are displayed
- `id="streams-grid"` - Where stream cards are displayed
- `id="posts-container"` - Where posts are displayed

---

### 2. `styles.css` (CSS Styling)

**Purpose:** Contains all styling and visual design.

**Lines Breakdown:**
- **Lines 1-50:** Global styles (reset, body, fonts)
- **Lines 51-150:** Top navigation bar styling
- **Lines 151-250:** Main layout and page containers
- **Lines 251-450:** Servers page styling
  - Lines 251-300: Servers sidebar
  - Lines 301-350: Channels sidebar
  - Lines 351-450: Chat area
- **Lines 451-550:** Stream page styling
  - Lines 451-480: Search bar
  - Lines 481-550: Stream cards and grid
- **Lines 551-750:** Feed page styling
  - Lines 551-600: Compose box
  - Lines 601-750: Post cards
- **Lines 751-800:** Scrollbar customization

**Key CSS Classes:**
- `.top-nav` - Top navigation bar
- `.nav-item` - Navigation buttons
- `.servers-page` - Servers page container
- `.stream-page` - Stream page container
- `.feed-page` - Feed page container
- `.message` - Individual message styling
- `.stream-card` - Individual stream card
- `.post` - Individual post styling

---

### 3. `script.js` (JavaScript Functionality)

**Purpose:** Contains all JavaScript functions and logic.

**Lines Breakdown:**
- **Lines 1-50:** Global variables (state management)
- **Lines 51-100:** Mock data (for testing)
- **Lines 101-200:** Page navigation functions
- **Lines 201-400:** Servers page functions
  - Lines 201-250: Server selection
  - Lines 251-300: Channel selection
  - Lines 301-350: Message rendering
  - Lines 351-400: Message sending
- **Lines 401-500:** Stream page functions
  - Lines 401-450: Stream rendering
  - Lines 451-500: Stream search
- **Lines 501-600:** Feed page functions
  - Lines 501-550: Post rendering
  - Lines 551-600: Post creation and interactions
- **Lines 601-650:** Initialization

**Key Functions:**
- `switchPage(page)` - Switches between pages
- `selectServer(serverId, element)` - Selects a server
- `selectChannel(channelId, element)` - Selects a channel
- `renderMessages()` - Displays messages
- `sendMessage()` - Sends a new message
- `renderStreams()` - Displays stream cards
- `searchStreams()` - Filters streams
- `renderPosts()` - Displays posts
- `createPost()` - Creates a new post
- `toggleLike(postId)` - Likes/unlikes a post
- `init()` - Initializes the application

---

## 🔗 How Files Connect

### HTML → CSS
- HTML uses CSS classes defined in `styles.css`
- Example: `<div class="top-nav">` uses `.top-nav` styles from CSS

### HTML → JavaScript
- HTML elements have `id` attributes that JavaScript uses
- Example: `<div id="messages-container">` is accessed by `document.getElementById('messages-container')`
- HTML buttons call JavaScript functions with `onclick`
- Example: `<button onclick="sendMessage()">` calls the `sendMessage()` function

### JavaScript → HTML
- JavaScript finds HTML elements using `getElementById()` or `querySelector()`
- JavaScript modifies HTML content using `.innerHTML` or `.textContent`
- JavaScript creates new HTML elements using `createElement()`

---

## 📍 Where to Find Things

### To Change Colors:
- **File:** `styles.css`
- **Lines:** Look for color values like `#5865f2`, `background-color`, `color`

### To Modify Layout:
- **File:** `index.html`
- **Lines:** HTML structure (servers page: 35-120, stream page: 125-150, feed page: 155-200)

### To Add Features:
- **File:** `script.js`
- **Lines:** Add new functions, modify existing functions

### To Connect Backend:
- **File:** `script.js`
- **Look for:** Comments with `TODO:` - these show where to add PHP backend calls

---

## 🔍 Quick Reference

### Find Functions by Purpose:

**Navigation:**
- `switchPage()` - Line 101 in script.js

**Servers/Chat:**
- `selectServer()` - Line 201 in script.js
- `selectChannel()` - Line 251 in script.js
- `renderMessages()` - Line 301 in script.js
- `sendMessage()` - Line 351 in script.js

**Streams:**
- `renderStreams()` - Line 401 in script.js
- `searchStreams()` - Line 451 in script.js

**Feed:**
- `renderPosts()` - Line 501 in script.js
- `createPost()` - Line 551 in script.js
- `toggleLike()` - Line 580 in script.js

### Find HTML Elements:

**Servers Page:**
- Servers sidebar: Line 40 in index.html
- Channels sidebar: Line 62 in index.html
- Chat area: Line 97 in index.html
- Messages container: Line 105 in index.html
- Message input: Line 113 in index.html

**Stream Page:**
- Search bar: Line 130 in index.html
- Streams grid: Line 137 in index.html

**Feed Page:**
- Compose box: Line 160 in index.html
- Posts container: Line 182 in index.html

---

## 💡 Tips for Navigation

1. **Use line numbers** - Each file has line numbers in comments
2. **Search for TODO** - Find places where backend needs to be connected
3. **Check function names** - Functions are named descriptively
4. **Read comments** - Every section has comments explaining what it does
5. **Follow the structure** - Files are organized by page/feature

---

## 🎯 Common Tasks

### Change a Color:
1. Open `styles.css`
2. Search for the color value (e.g., `#5865f2`)
3. Replace with your color

### Add a New Button:
1. Add HTML in `index.html`
2. Add CSS styling in `styles.css`
3. Add JavaScript function in `script.js`

### Connect to PHP Backend:
1. Find `TODO:` comments in `script.js`
2. Replace mock data with `fetch()` calls
3. Update API URLs to your PHP backend

---

**Everything is organized and documented! Use line numbers to navigate quickly!**



