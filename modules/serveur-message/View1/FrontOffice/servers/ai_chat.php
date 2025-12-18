<?php
/**
 * AI Chat AJAX Endpoint
 * Handles AI chat requests and returns JSON responses
 */

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../../Controller/AIController.php';

$response = [
    'success' => false,
    'message' => '',
    'error' => ''
];

try {
    // Check if it's a POST request
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Get the message from POST data or JSON body
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    $userMessage = '';
    if (isset($data['message'])) {
        $userMessage = trim($data['message']);
    } elseif (isset($_POST['message'])) {
        $userMessage = trim($_POST['message']);
    }
    
    if (empty($userMessage)) {
        throw new Exception('No message provided');
    }
    
    // Get AI response
    $aiController = new AIController();
    $aiResponse = $aiController->chat($userMessage);
    
    $response['success'] = true;
    $response['message'] = $aiResponse;
    
} catch (Exception $e) {
    $response['success'] = false;
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
?>

