<!DOCTYPE html>
<html>
<head>
    <title><?= $channel['username'] ?> - LIVE</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
</head>
<body>
<div class="container">
    <h1>LIVE - <?= htmlspecialchars($channel['username']) ?></h1>
    <video id="video" controls autoplay style="width:100%;max-width:1000px;"></video>
    <p>Viewers: <strong><?= $channel['viewer_count'] ?></strong></p>
</div>

<script>
if (Hls.isSupported()) {
    const hls = new Hls();
    hls.loadSource('http://localhost:8000/live/<?= $channel['stream_key'] ?>/index.m3u8');
    hls.attachMedia(document.getElementById('video'));
} else if (document.getElementById('video').canPlayType('application/vnd.apple.mpegurl')) {
    document.getElementById('video').src = 'http://localhost:8000/live/<?= $channel['stream_key'] ?>/index.m3u8';
}
</script>
</body>
</html>