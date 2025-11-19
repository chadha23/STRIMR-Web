<?php
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data["Content"])) {
    echo json_encode([
        "success" => false,
        "message" => "No content received."
    ]);
    exit;
}

$content = trim($data["Content"]);

if ($content === "") {
    echo json_encode([
        "success" => false,
        "message" => "Post cannot be empty."
    ]);
    exit;
}

try {
    $pdo = new PDO("mysql:host=localhost;dbname=STRIMR;charset=utf8", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Database connection error."
    ]);
    exit;
}


$postId = uniqid("post_");   
$currentUserId = "user1";


$stmt = $pdo->prepare("INSERT IGNORE INTO Users (id, username, email, password_hash, created_at) VALUES (?, ?, ?, ?, NOW())");
$stmt->execute([$currentUserId, "testuser", "test@example.com", "dummyhash"]);

$stmt = $pdo->prepare("
    INSERT INTO Posts (id, author_id, content, created_at)
    VALUES (?, ?, ?, NOW())
");

$stmt->execute([$postId, $currentUserId, $content]);


echo json_encode([
    "success" => true,
    "message" => "Post created successfully.",
    "username" => "TestUser",          
    "userInitials" => "T",             
]);

?>

