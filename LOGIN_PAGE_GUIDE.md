# Login/Signup Page Guide

## 📁 Files Created

- **`login.html`** - Login and signup page
- **`login-styles.css`** - Styling for login page
- **`login-script.js`** - JavaScript for login/signup functionality

## 🎯 How It Works

### Page Flow:
1. User opens `login.html`
2. User sees login form (default)
3. User can click "Register" to switch to signup form
4. User fills form and submits
5. User is redirected to `index.html` (main app)

### Features:
- ✅ Beautiful animated login form
- ✅ Smooth transition to signup form
- ✅ Form validation (client-side)
- ✅ Password match checking
- ✅ Error/success messages
- ✅ Loading states
- ✅ Auto-redirect if already logged in

## 🔌 Connecting to PHP Backend

### Login Endpoint

**File:** `login-script.js` - `handleLogin()` function

**Your PHP should be:**
- **URL:** `POST api/login.php`
- **Receive:**
```json
{
  "email": "user@example.com",
  "password": "password123"
}
```
- **Return:**
```json
{
  "success": true,
  "token": "session_token_here",
  "user": {
    "id": 1,
    "username": "user123",
    "email": "user@example.com"
  }
}
```

### Signup Endpoint

**File:** `login-script.js` - `handleSignup()` function

**Your PHP should be:**
- **URL:** `POST api/signup.php`
- **Receive:**
```json
{
  "name": "John Doe",
  "email": "user@example.com",
  "username": "user123",
  "password": "password123"
}
```
- **Return:**
```json
{
  "success": true,
  "token": "session_token_here",
  "user": {
    "id": 1,
    "username": "user123",
    "email": "user@example.com",
    "name": "John Doe"
  }
}
```

## 📝 Steps to Connect

1. **Find TODO comments** in `login-script.js`
2. **Replace mock code** with fetch() calls to your PHP
3. **Update API URLs** to your backend
4. **Test login/signup** with your database

## 🔐 Security Notes

- **Password hashing:** Your PHP should hash passwords before storing
- **Token storage:** Store session tokens securely
- **Validation:** Validate all inputs on backend (don't trust client)
- **HTTPS:** Use HTTPS in production

## 🎨 Customization

### Change Colors:
- Edit `login-styles.css`
- Search for color values like `#5865f2`

### Change Form Fields:
- Edit `login.html`
- Add/remove fields as needed

### Modify Validation:
- Edit `login-script.js`
- Update validation functions

## 📍 File Locations

- **Login Form:** `login.html` lines 24-50
- **Signup Form:** `login.html` lines 52-110
- **Login Handler:** `login-script.js` lines 115-180
- **Signup Handler:** `login-script.js` lines 185-280
- **Styling:** `login-styles.css` - all styling

---

**Ready to connect to your PHP backend!**



