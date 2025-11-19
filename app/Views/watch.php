<?php $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($channel['username']) ?> - LIVE</title>
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
    <style>
        video { width: 100%; max-width: 1100px; background: #000; border-radius: 12px; }
        .live { color: #ff0000; font-weight: bold; font-size: 28px; }
    </style>
</head>
<body>
<div class="container">
    <h1 class="live">LIVE - <?= htmlspecialchars($channel['username']) ?></h1>
    <video id="video" controls autoplay playsinline></video>
    <p style="font-size:20px;">Viewers: <strong><?= $channel['viewer_count'] ?></strong></p>
    <p><a href="<?= $base_url ?>/dashboard">← Back to Dashboard</a></p>
</div>

<script>
// THIS IS THE CORRECT URL FOR Node Media Server v4
const streamKey = "<?= $channel['stream_key'] ?>";
const hlsUrl = `http://localhost:8000/live/${streamKey}/index.m3u8`;

if (Hls.isSupported()) {
    const hls = new Hls({
        debug: false,
        enableWorker: true,
        lowLatencyMode: true
    });
    hls.loadSource(hlsUrl);
    hls.attachMedia(document.getElementById('video'));

    hls.on(Hls.Events.MANIFEST_PARSED, () => {
        document.getElementById('video').play().catch(() => {});
    });

    hls.on(Hls.on(Hls.Events.ERROR, (event, data) => {
        console.error('HLS Error:', data);
        if (data.fatal) {
            setTimeout(() => hls.loadSource(hlsUrl), 2000);
        }
    }));
} else if (document.getElementById('video').canPlayType('application/vnd.apple.mpegurl')) {
    document.getElementById('video').src = hlsUrl;
}
</script>
</body>
</html>