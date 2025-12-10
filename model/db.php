<?php
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "STRIMR-Web";

try {
    // Create PDO connection 
    $conn = new PDO(
        "mysql:host=$servername;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    // echo "CONNECTED SUCCESSFULLY";
} catch (PDOException $e) {
    // Same idea as before, but with PDO exception
    die("Connection failed: " . $e->getMessage());
}
?>
