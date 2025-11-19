<?php
header("Content-Type: application/json");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !isset($data["id"])) {
    echo json_encode([
        "success" => false,
        "message" => "No post id received."
    ]);
    exit;
}

$id = $data["id"];

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

$stmt = $pdo->prepare("DELETE FROM Posts WHERE id = ?");
$stmt->execute([$id]);

if ($stmt->rowCount() > 0) {
    echo json_encode([
        "success" => true,
        "message" => "Post deleted successfully."
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Post not found."
    ]);
}
?>