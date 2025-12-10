<?php
// Fix password hashes for all users - set all to '123'
require_once 'db.php';

$users = ['admin', 'fawzi', 'chavion', 'harissa', 'ezdin'];
$newPassword = '123';

echo "<h2>Updating Passwords</h2>";

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
    
        echo "<p>✓ Updated password for: $username</p>";
        $updated++;
        
        // Verify the update worked
        $check = $conn->prepare("SELECT password FROM users WHERE username = :username");
        $check->execute([':username' => $username]);
        $row = $check->fetch();
        if ($row) {
            $verify = password_verify($newPassword, $row['password']);
            echo "<p style='margin-left: 20px;'>  Verification: " . ($verify ? '✓ MATCH' : '✗ NO MATCH') . "</p>";
        }
    }
    
    echo "<h3>Updated $updated users</h3>";
    echo "<p>All passwords are now: <strong>$newPassword</strong></p>";
    echo "<p><a href='check_password.php'>Verify passwords</a> | <a href='login.html'>Go to login page</a></p>";
} catch (PDOException $e) {
    echo "<p>✗ Failed to update passwords: " . $e->getMessage() . "</p>";
}
?>

