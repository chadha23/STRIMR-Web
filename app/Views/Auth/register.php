<?php $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Register - Twitch Clone</title>
    <link rel="stylesheet" href="<?= $base_url ?>/assets/css/style.css">
</head>
<body>
<div class="container">
    <div class="box">
        <h1>Create Account</h1>
        <form method="post">
            <input name="username" placeholder="Username" required minlength="3"><br><br>
            <input name="password" type="password" placeholder="Password" required minlength="6"><br><br>
            <input type="password" name="password2" placeholder="Confirm password" required minlength="6"><br><br>
            <button type="submit">Register</button>
        </form>
        <p><a href="<?= $base_url ?>/login">Already have account?</a></p>
    </div>
</div>
</body>
</html>