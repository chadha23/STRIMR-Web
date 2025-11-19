<!DOCTYPE html>
<html>
<head>
    <title>Login - Twitch Clone</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="container">
    <div class="box">
        <h1>Twitch Clone</h1>
        <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
        <form method="post">
            <input name="username" placeholder="Username" required><br>
            <input name="password" type="password" placeholder="Password" required><br>
            <button type="submit">Login</button>
        </form>
        <p><a href="/register">Create account</a></p>
    </div>
</div>
</body>
</html>