# Simple Template - NO React, NO Vite!

## Ô£à Good News!

**`index-simple.html` uses ZERO dependencies!**

- ÔØî NO React
- ÔØî NO Vite
- ÔØî NO Node.js needed
- ÔØî NO npm install needed
- Ô£à Pure HTML
- Ô£à Pure CSS
- Ô£à Pure JavaScript

## ­ƒÜÇ How to Use

1. **Just open `index-simple.html` in your browser**
   - Double-click the file
   - Or right-click ÔåÆ Open With ÔåÆ Browser

2. **That's it!** No installation, no setup needed!

## ­ƒôü What Files Do You Need?

**You only need ONE file:**
- Ô£à `index-simple.html` - This is your complete website!

**You DON'T need these (can delete if you want):**
- ÔØî `src/` folder - React code (not needed)
- ÔØî `package.json` - npm dependencies (not needed)
- ÔØî `vite.config.js` - Vite config (not needed)
- ÔØî `index.html` - React entry point (not needed)

## ­ƒÄ» What's Inside index-simple.html?

Everything is in one file:
- Ô£à HTML structure
- Ô£à CSS styling (in `<style>` tag)
- Ô£à JavaScript code (in `<script>` tag)
- Ô£à All three pages: Servers, Stream, Feed
- Ô£à Mock data for testing
- Ô£à TODO comments showing where to connect your PHP backend

## ­ƒöî Connecting to Your PHP Backend

Just find the TODO comments and replace with your PHP calls:

```javascript
// BEFORE (mock data):
messages.push(newMessage);

// AFTER (your PHP):
fetch('http://localhost/your-backend/api/sendMessage.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
        serverId: currentServer,
        channelId: currentChannel,
        content: content
    })
})
.then(response => response.json())
.then(data => {
    messages.push(data.message);
    renderMessages();
});
```

## ­ƒÆí Why This is Better for You

1. **Simple** - Just HTML, CSS, JavaScript (what you know!)
2. **No Build Tools** - No compilation, no bundling
3. **Easy to Modify** - All code visible in one file
4. **Works Immediately** - Just open in browser
5. **Perfect for PHP** - Easy to connect to your backend

## ­ƒùæ´©Å Can I Delete the React Files?

**Yes!** If you're only using `index-simple.html`, you can delete:
- `src/` folder
- `package.json`
- `vite.config.js`
- `tailwind.config.js`
- `postcss.config.js`
- `index.html` (the React one)

**Keep only:**
- Ô£à `index-simple.html` - Your main file
- Ô£à Documentation files (README, guides, etc.)

## ­ƒôØ Technologies Used

- **HTML5** - Structure
- **CSS3** - Styling (all inline in `<style>` tag)
- **Vanilla JavaScript** - No frameworks, no libraries
- **That's it!** Nothing else!

## ­ƒÄ¿ Customization

Everything is in one file, so you can:
- Change colors in the CSS section
- Modify layout in the HTML section
- Add features in the JavaScript section
- Connect to PHP backend where TODO comments are

---

**You're all set! Just use `index-simple.html` - it's completely standalone!** ­ƒÄë



