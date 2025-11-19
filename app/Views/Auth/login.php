<?php $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login - Twitch Clone</title>
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
</head>
<body>
<div class="container">
    <div class="box">
        <h1>Twitch Clone</h1>
        <form method="post">
            <input name="username" placeholder="Username" required><br><br>
            <input name="password" type="password" placeholder="Password" required><br><br>
            <button type="submit">Login</button>
        </form>
        <p><a href="<?= $base_url ?>/register">Create account</a></p>
    </div>
</div>
</body>
</html>