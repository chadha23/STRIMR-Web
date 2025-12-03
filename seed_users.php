<?php
include "db.php";

// Sample user data with password "123" for all users
$users = [
    [
        'username' => 'fawzi',
        'email' => 'john@example.com',
        'password' => password_hash('123', PASSWORD_DEFAULT)
    ],
    [
        'username' => 'chavion',
        'email' => 'jane@example.com',
        'password' => password_hash('123', PASSWORD_DEFAULT)
    ],
    [
        'username' => 'admin',
        'email' => 'admin@strimr.com',
        'password' => password_hash('123', PASSWORD_DEFAULT)
    ],
    [
        'username' => 'harissa',
        'email' => 'alice@example.com',
        'password' => password_hash('123', PASSWORD_DEFAULT)
    ],
    [
        'username' => 'ezdin',
        'email' => 'bob@example.com',
        'password' => password_hash('123', PASSWORD_DEFAULT)
    ]
];

// Insert sample users
$stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $hashed_password);

$inserted_count = 0;
foreach ($users as $user) {
    $username = $user['username'];
    $email = $user['email'];
    $hashed_password = $user['password'];
    
    if ($stmt->execute()) {
        $inserted_count++;
    } else {
        echo "Error inserting user {$username}: " . $conn->error . "<br>";
    }
}

echo "Successfully inserted {$inserted_count} users.";

$stmt->close();
$conn->close();
?>