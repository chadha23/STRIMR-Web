# Discord Clone - Complete Template

A beautiful, animated frontend template combining Discord, Twitch, and Twitter features.

## ­ƒÜÇ Quick Start

1. **Start with Login:** Open `login.html` in your browser
2. **Login or Sign Up:** Create an account or login
3. **Main App:** You'll be redirected to `index.html` automatically

## ­ƒôü Project Structure

```
HELL/
Ôö£ÔöÇÔöÇ login.html              # Login/Signup page (START HERE!)
Ôö£ÔöÇÔöÇ login-styles.css         # Login page styling
Ôö£ÔöÇÔöÇ login-script.js          # Login/signup functionality
Ôöé
Ôö£ÔöÇÔöÇ index.html              # Main app (Discord/Twitch/Twitter)
Ôö£ÔöÇÔöÇ styles.css              # Main app styling
Ôö£ÔöÇÔöÇ script.js               # Main app functionality
Ôöé
Ôö£ÔöÇÔöÇ admin.html              # Admin Dashboard (ADMIN ACCESS)
Ôö£ÔöÇÔöÇ admin-styles.css         # Admin dashboard styling
Ôö£ÔöÇÔöÇ admin-script.js          # Admin dashboard functionality
Ôöé
ÔööÔöÇÔöÇ Documentation files     # Guides and requirements
```

## ­ƒÄ» How to Use

### Step 1: Login/Signup
1. Open `login.html` in your browser
2. If you don't have an account, click "Register"
3. Fill in the signup form
4. Submit to create account
5. You'll be redirected to the main app

### Step 2: Main App
1. You'll see the main app with three pages:
   - **Servers** - Discord-style chat
   - **Stream** - Twitch-style streaming
   - **Feed** - Twitter-style feed

### Step 3: Admin Dashboard
1. Open `admin.html` in your browser (or add admin login)
2. View statistics, manage users, servers, messages, streams, and posts
3. See `ADMIN_DASHBOARD_GUIDE.md` for backend integration details

## ­ƒöî Backend Requirements

### Login API
- **Endpoint:** `POST api/login.php`
- **Body:** `{ email, password }`
- **Returns:** `{ success, token, user }`

### Signup API
- **Endpoint:** `POST api/signup.php`
- **Body:** `{ name, email, username, password }`
- **Returns:** `{ success, token, user }`

See `LOGIN_PAGE_GUIDE.md` for detailed API specifications.

## ­ƒôÜ Documentation

- **`CODE_ORGANIZATION.md`** - Code structure and line numbers
- **`LOGIN_PAGE_GUIDE.md`** - Login/signup page guide
- **`BACKEND_REQUIREMENTS.md`** - PHP backend requirements
- **`ANIMATIONS_GUIDE.md`** - Animation documentation
- **`ADMIN_DASHBOARD_GUIDE.md`** - Admin dashboard guide and backend integration

## ­ƒÄ¿ Features

- Ô£à Beautiful login/signup page
- Ô£à Smooth animations throughout
- Ô£à Three main pages (Servers, Stream, Feed)
- Ô£à Form validation
- Ô£à Auto-redirect if logged in
- Ô£à Logout functionality
- Ô£à **Admin Dashboard** with statistics, user management, and analytics

## ­ƒÆí Technologies

- Pure HTML, CSS, JavaScript
- No frameworks or dependencies
- Ready for PHP backend

---

**Perfect for PHP + Apache + MySQL developers!**
