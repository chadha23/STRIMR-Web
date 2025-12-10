<?php
// Check if admin password hash is correct
require_once 'db.php';

$stmt = $conn->prepare("SELECT id, username, password, email FROM users WHERE username = 'admin'");
$stmt->execute();
$user = $stmt->fetch();

if ($user) {
    echo "<h2>Admin User Info</h2>";
    echo "<p>ID: " . $user['id'] . "</p>";
    echo "<p>Username: " . $user['username'] . "</p>";
    echo "<p>Email: " . $user['email'] . "</p>";
    echo "<p>Password Hash: " . substr($user['password'], 0, 50) . "...</p>";
    
    // Test password verification
    $testPassword = '123';
    $verify = password_verify($testPassword, $user['password']);
    echo "<p>Password '123' verification: " . ($verify ? '✓ MATCH' : '✗ NO MATCH') . "</p>";
    
    // Generate new hash for testing
    $newHash = password_hash('123', PASSWORD_DEFAULT);
    echo "<p>New hash for '123': " . substr($newHash, 0, 50) . "...</p>";
    
    // Test new hash
    $verifyNew = password_verify('123', $newHash);
    echo "<p>New hash verification: " . ($verifyNew ? '✓ MATCH' : '✗ NO MATCH') . "</p>";
    
    if (!$verify) {
        echo "<h3 style='color: red;'>Password hash doesn't match! Need to update database.</h3>";
        echo "<p><a href='fix_passwords.php'>Click here to fix passwords</a></p>";
    }
} else {
    echo "<p>Admin user not found in database.</p>";
}

?>

