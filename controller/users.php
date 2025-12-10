<?php

require_once __DIR__ . '/../model/db.php';
$sql = "CREATE TABLE users (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

try {
    $conn->exec($sql);
    echo "Users table created successfully!";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage();
}

?>
