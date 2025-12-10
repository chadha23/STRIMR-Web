<?php
// Check password hashes for all current users
require_once __DIR__ . '/../model/db.php';

try {
    $stmt = $conn->query("SELECT id, username, password, email FROM users ORDER BY username");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $users = [];
    $loadError = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STRIMR | Check Password</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: dark;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #1e1f22;
            color: #dadada;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .layout {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            width: min(720px, 100%);
            background: #2b2d31;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.45);
        }

        .eyebrow {
            color: #8f94ff;
            font-size: 0.9rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            margin: 0 0 12px;
        }

        h1 {
            margin: 0;
            font-size: 2rem;
            color: #ffffff;
        }

        .subtitle {
            margin: 12px 0 24px;
            color: #b5b6c3;
        }

        .status-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .status-item {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-left: 4px solid #5865f2;
            border-radius: 10px;
            padding: 14px 18px;
            font-size: 0.98rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .status-item.success {
            border-left-color: #43b581;
            color: #d4ffe7;
        }

        .status-item.error {
            border-left-color: #ff6b6b;
            color: #ffdada;
        }

        .status-item.summary {
            border-left-color: #5865f2;
            color: #dee0ff;
            font-weight: 600;
        }

        .status-item strong {
            color: inherit;
        }

        .icon {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .status-item a {
            color: #8f94ff;
            text-decoration: none;
            font-weight: 600;
        }

        .status-item a:hover {
            text-decoration: underline;
        }

        .actions {
            margin-top: 28px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            color: #ffffff;
            background: linear-gradient(120deg, #5865f2, #5f37ff);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 10px 25px rgba(88, 101, 242, 0.35);
        }

        .btn.secondary {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #dadada;
            box-shadow: none;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(88, 101, 242, 0.4);
        }

        .btn.secondary:hover {
            border-color: rgba(255, 255, 255, 0.35);
            box-shadow: none;
        }

        @media (max-width: 600px) {
            .card {
                padding: 24px;
            }

            h1 {
                font-size: 1.6rem;
            }
        }
    </style>
</head>
<body>
    <div class="layout">
        <div class="card">
            <p class="eyebrow">STRIMR Admin</p>
            <h1>Admin User Info</h1>
            <p class="subtitle">Live diagnostics for the stored admin password hash and its verification.</p>

            <ul class="status-list">
<?php
if (isset($loadError)) {
    echo "<li class='status-item error'><span class='icon'>✗</span>Unable to load users: {$loadError}</li>";
} elseif (empty($users)) {
    echo "<li class='status-item error'><span class='icon'>!</span>No users found in the database.</li>";
} else {
    foreach ($users as $user) {
        echo "<li class='status-item summary'><strong>{$user['username']}</strong> (ID: {$user['id']})</li>";
        echo "<li class='status-item summary'>Email: {$user['email']}</li>";
        
        // Test password verification
        $testPassword = '123';
        $verify = password_verify($testPassword, $user['password']);
        $verifyClass = $verify ? 'success' : 'error';
        echo "<li class='status-item {$verifyClass}'><span class='icon'>" . ($verify ? '✓' : '✗') . "</span>Password '123' verification: " . ($verify ? '✓ MATCH' : '✗ NO MATCH') . "</li>";
        
        // Generate new hash for testing
        $newHash = password_hash('123', PASSWORD_DEFAULT);
        
        // Test new hash
        $verifyNew = password_verify('123', $newHash);
        $verifyNewClass = $verifyNew ? 'success' : 'error';
        echo "<li class='status-item {$verifyNewClass}'><span class='icon'>" . ($verifyNew ? '✓' : '✗') . "</span>New hash verification: " . ($verifyNew ? '✓ MATCH' : '✗ NO MATCH') . "</li>";
        
        if (!$verify) {
            echo "<li class='status-item error'><span class='icon'>!</span>Password hash doesn't match! Need to update database.</li>";
            echo "<li class='status-item summary'><a href='fix_passwords.php'>Click here to fix passwords</a></li>";
        }
    }
}
?>
            </ul>

            <div class="actions">
                <a class="btn" href="fix_passwords.php">Fix passwords</a>
                <a class="btn secondary" href="../view/login.html">Go to login page</a>
            </div>
        </div>
    </div>
</body>
</html>
