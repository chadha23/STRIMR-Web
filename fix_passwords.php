<?php
// Fix password hashes for all users - set all to '123'
require_once 'db.php';

$users = ['admin', 'fawzi', 'chavion', 'harissa', 'ezdin'];
$newPassword = '123';

echo "<h2>Updating Passwords</h2>";

$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
$updated = 0;

foreach ($users as $username) {
    // Generate fresh hash for each user
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Bind parameters for this iteration
    $stmt->bind_param("ss", $newHash, $username);
    
    if ($stmt->execute()) {
        echo "<p>✓ Updated password for: $username</p>";
        $updated++;
        
        // Verify the update worked
        $check = $conn->prepare("SELECT password FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $result = $check->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $verify = password_verify($newPassword, $row['password']);
            echo "<p style='margin-left: 20px;'>  Verification: " . ($verify ? '✓ MATCH' : '✗ NO MATCH') . "</p>";
        }
        $check->close();
    } else {
        echo "<p>✗ Failed to update: $username - " . $stmt->error . "</p>";
    }
}

$stmt->close();

echo "<h3>Updated $updated users</h3>";
echo "<p>All passwords are now: <strong>$newPassword</strong></p>";
echo "<p><a href='check_password.php'>Verify passwords</a> | <a href='login.html'>Go to login page</a></p>";

$conn->close();
?>

