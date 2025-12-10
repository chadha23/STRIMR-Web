# How to View the Frontend Template

## ⚠️ Important: You Can't Just Open index.html

This is a **React application**, not a simple HTML file. You need to:
1. Install Node.js (if you don't have it)
2. Install dependencies
3. Run the development server

## 🚀 Step-by-Step Instructions

### Step 1: Install Node.js (If Needed)

1. **Check if you have Node.js:**
   - Open Command Prompt or PowerShell
   - Type: `node --version`
   - If you see a version number (like `v18.0.0`), you're good!
   - If you see "not recognized", you need to install it

2. **Download Node.js:**
   - Go to: https://nodejs.org/
   - Download the **LTS version** (recommended)
   - Install it (just click Next, Next, Next...)
   - **Restart your computer** after installation

3. **Verify installation:**
   - Open a NEW Command Prompt/PowerShell
   - Type: `node --version` and `npm --version`
   - Both should show version numbers

### Step 2: Install Project Dependencies

1. **Open Command Prompt or PowerShell**
2. **Navigate to your project folder:**
   ```bash
   cd C:\Users\PC\Desktop\HELL
   ```

3. **Install dependencies:**
   ```bash
   npm install
   ```
   This will download React and all other packages (takes 1-2 minutes)

### Step 3: Start the Development Server

1. **Run this command:**
   ```bash
   npm run dev
   ```

2. **You should see:**
   ```
   VITE v5.x.x  ready in xxx ms
   
   ➜  Local:   http://localhost:3000/
   ➜  Network: use --host to expose
   ```

3. **Open your browser:**
   - Go to: `http://localhost:3000`
   - Or click the link if it appears in terminal

### Step 4: View the Website

You should now see:
- ✅ Discord-style interface
- ✅ Servers sidebar on the left
- ✅ Channels sidebar in the middle
- ✅ Chat area (or stream/feed view)
- ✅ Mock data showing example messages

## 🎯 What You'll See

The website has **3 views** you can switch between:

1. **Chat View** (default) - Discord-style chat
2. **Live Stream View** - Twitch-style streaming
3. **Feed View** - Twitter-style feed

**Switch views:** Click the buttons in the channels sidebar (Chat, Live Stream, Feed)

## 📝 Quick Commands Reference

```bash
# Install dependencies (do this once)
npm install

# Start development server
npm run dev

# Stop server
Press Ctrl+C in the terminal
```

## 🐛 Troubleshooting

### "npm is not recognized"
- Node.js is not installed
- Install Node.js from https://nodejs.org/
- Restart your computer
- Open a NEW terminal window

### "Cannot find module"
- Run `npm install` again
- Make sure you're in the project folder

### Port 3000 already in use
- Another program is using port 3000
- Close that program, or
- Vite will automatically use port 3001, 3002, etc.

### Browser shows blank page
- Check terminal for errors
- Make sure server is running
- Try refreshing the page (Ctrl+F5)

## 💡 Important Notes

- **Keep the terminal open** while viewing the website
- The server must be running for the website to work
- When you close the terminal, the website stops
- Changes you make to code will **auto-refresh** in the browser

## 🎨 Next Steps

Once you can see the website:
1. Explore the interface
2. Try switching between views
3. Read `FRONTEND_TEMPLATE_GUIDE.md` to understand the code
4. Start building your backend!

---

**You need the development server running to see the website!**



