<?php
require_once __DIR__ . '/../../model/User.php';

$token = $_GET['token'] ?? null;
if (!$token) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Invalid link</title></head><body><p>Invalid verification link</p></body></html>';
    exit;
}

$userModel = new User($conn);
$verified = $userModel->verifyEmailByToken($token);

if (!$verified) {
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Invalid token</title></head><body><p>Invalid or expired token</p></body></html>';
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Email verified</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
        }
        .container {
            max-width: 420px;
            margin: 60px auto;
            background: #ffffff;
            border-radius: 10px;
            padding: 32px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08);
            text-align: center;
        }
        h1 {
            font-size: 24px;
            margin-bottom: 16px;
        }
        p {
            margin-bottom: 24px;
            font-size: 15px;
            line-height: 1.6;
        }
        a.button {
            display: inline-block;
            padding: 12px 26px;
            border-radius: 6px;
            background-color: #2563eb;
            color: #fff;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Email verified</h1>
        <p>Your account has been verified. You can now log in.</p>
        <a class="button" href="../view/login.php">Go to login</a>
    </div>
</body>
</html>
