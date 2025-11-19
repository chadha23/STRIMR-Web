<?php $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Go Live - <?= htmlspecialchars($_SESSION['username']) ?></title>
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
    <style>
        .copy-btn{background:#9147ff;color:white;border:none;padding:10px 16px;border-radius:8px;cursor:pointer;margin-left:10px;}
        .copy-btn:hover{background:#772ce8;}
        code{background:#000;padding:12px;border-radius:8px;display:block;margin:10px 0;word-break:break-all;}
    </style>
</head>
<body>
<div class="container">
    <h1 style="color:#9147ff;text-align:center;margin-bottom:30px;">You're Going Live!</h1>

    <div class="obs">
        <h2>OBS Settings</h2>
        <p><strong>Server:</strong> <code>rtmp://localhost:1935/live</code></p>
        <p><strong>Stream Key:</strong>
            <div style="display:flex;align-items:center;gap:10px;">
                <code id="key"><?= htmlspecialchars($channel['stream_key']) ?></code>
                <button class="copy-btn" onclick="copy('key')">Copy</button>
            </div>
        </p>
    </div>

    <div style="background:#111;padding:35px;border-radius:16px;text-align:center;margin:40px 0;">
        <h2>Your Stream URL</h2>
        <div style="display:flex;align-items:center;justify-content:center;gap:15px;flex-wrap:wrap;">
            <a href="<?= $base_url ?>/watch/<?= $channel['slug'] ?>" 
               id="link" target="_blank" 
               style="font-size:22px;color:#9147ff;">
                <?= $base_url ?>/watch/<?= $channel['slug'] ?>
            </a>
            <button class="copy-btn" onclick="copy('link')">Copy Link</button>
        </div>
        <br><br>
        <strong style="color:#51cf66;font-size:18px;">Unlimited viewers ready!</strong>
    </div>

    <p style="text-align:center;">
        <a href="<?= $base_url ?>/dashboard">← Back to Dashboard</a>
    </p>
</div>

<script>
function copy(id) {
    navigator.clipboard.writeText(document.getElementById(id).innerText);
    alert('Copied to clipboard!');
}
</script>
</body>
</html>