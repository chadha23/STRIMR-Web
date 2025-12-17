<?php
session_start();
require_once __DIR__ . '/Models/Channel.php';
require_once __DIR__ . '/Database.php';

$input = json_decode(file_get_contents('php://input'), true);
$channelId = $input['channel_id'] ?? null;
$sessionId = $input['session_id'] ?? session_id();

if ($channelId && is_numeric($channelId)) {
    Channel::decrementViewer($channelId, $sessionId);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid channel ID']);
}