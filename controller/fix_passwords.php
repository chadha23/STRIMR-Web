<?php
// Fix password hashes for all users - set all to '123'
require_once __DIR__ . '/../model/db.php';

$newPassword = '123';

try {
    $usersStmt = $conn->query("SELECT username FROM users ORDER BY username");
    $users = $usersStmt->fetchAll(PDO::FETCH_COLUMN);
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
    <title>STRIMR | Fix Passwords</title>
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

        .results {
            margin-bottom: 32px;
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

        .actions {
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
            <h1>Updating Passwords</h1>
            <p class="subtitle">Live log of password resets for the STRIMR accounts listed below.</p>

            <div class="results">
                <ul class="status-list">
<?php
if (isset($loadError)) {
    echo "<li class='status-item error'><span class='icon'>✗</span>Unable to load users: {$loadError}</li>";
} elseif (empty($users)) {
    echo "<li class='status-item error'><span class='icon'>!</span>No users found in the database.</li>";
} else {
    try {
        $stmt = $conn->prepare("UPDATE users SET password = :password WHERE username = :username");
        $updated = 0;

        foreach ($users as $username) {
            // Generate fresh hash for each user
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
            $stmt->execute([
                ':password' => $newHash,
                ':username' => $username
            ]);
        
            echo "<li class='status-item success'><span class='icon'>✓</span>Updated password for: <strong>$username</strong></li>";
            $updated++;
            
            // Verify the update worked
            $check = $conn->prepare("SELECT password FROM users WHERE username = :username");
            $check->execute([':username' => $username]);
            $row = $check->fetch();
            if ($row) {
                $verify = password_verify($newPassword, $row['password']);
                $statusClass = $verify ? 'success' : 'error';
                $statusLabel = $verify ? '✓ MATCH' : '✗ NO MATCH';
                echo "<li class='status-item {$statusClass}'><span class='icon'>" . ($verify ? '✓' : '✗') . "</span>Verification for <strong>$username</strong>: {$statusLabel}</li>";
            }
        }
        
        echo "<li class='status-item summary'>Updated $updated users</li>";
        echo "<li class='status-item summary'>All passwords are now: <strong>$newPassword</strong></li>";
    } catch (PDOException $e) {
        echo "<li class='status-item error'><span class='icon'>✗</span>Failed to update passwords: " . $e->getMessage() . "</li>";
    }
}
?>
                </ul>
            </div>

            <div class="actions">
                <a class="btn" href="check_password.php">Verify passwords</a>
                <a class="btn secondary" href="../view/login.html">Go to login page</a>
            </div>
        </div>
    </div>
</body>
</html>
