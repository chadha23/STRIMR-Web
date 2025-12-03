# Discord Clone - Complete Template

A beautiful, animated frontend template combining Discord, Twitch, and Twitter features.

## 🚀 Quick Start

1. **Start with Login:** Open `login.html` in your browser
2. **Login or Sign Up:** Create an account or login
3. **Main App:** You'll be redirected to `index.html` automatically

## 📁 Project Structure

```
HELL/
├── login.html              # Login/Signup page (START HERE!)
├── login-styles.css         # Login page styling
├── login-script.js          # Login/signup functionality
│
├── index.html              # Main app (Discord/Twitch/Twitter)
├── styles.css              # Main app styling
├── script.js               # Main app functionality
│
├── admin.html              # Admin Dashboard (ADMIN ACCESS)
├── admin-styles.css         # Admin dashboard styling
├── admin-script.js          # Admin dashboard functionality
│
└── Documentation files     # Guides and requirements
```

## 🎯 How to Use

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

## 🔌 Backend Requirements

### Login API
- **Endpoint:** `POST api/login.php`
- **Body:** `{ email, password }`
- **Returns:** `{ success, token, user }`

### Signup API
- **Endpoint:** `POST api/signup.php`
- **Body:** `{ name, email, username, password }`
- **Returns:** `{ success, token, user }`

See `LOGIN_PAGE_GUIDE.md` for detailed API specifications.

## 📚 Documentation

- **`CODE_ORGANIZATION.md`** - Code structure and line numbers
- **`LOGIN_PAGE_GUIDE.md`** - Login/signup page guide
- **`BACKEND_REQUIREMENTS.md`** - PHP backend requirements
- **`ANIMATIONS_GUIDE.md`** - Animation documentation
- **`ADMIN_DASHBOARD_GUIDE.md`** - Admin dashboard guide and backend integration

## 🎨 Features

- ✅ Beautiful login/signup page
- ✅ Smooth animations throughout
- ✅ Three main pages (Servers, Stream, Feed)
- ✅ Form validation
- ✅ Auto-redirect if logged in
- ✅ Logout functionality
- ✅ **Admin Dashboard** with statistics, user management, and analytics

## 💡 Technologies

- Pure HTML, CSS, JavaScript
- No frameworks or dependencies
- Ready for PHP backend

---

**Perfect for PHP + Apache + MySQL developers!**
