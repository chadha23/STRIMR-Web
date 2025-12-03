<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// Debug: Log the request
file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Login attempt\n", FILE_APPEND);

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Get JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Debug: Log received data
file_put_contents('login_debug.log', "Received data: " . print_r($data, true) . "\n", FILE_APPEND);

// Validate input
if (empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Email/username and password are required']);
    exit();
}

$loginInput = trim($data['email']); // This can be email or username
$loginPassword = isset($data['password']) ? trim($data['password']) : '';

// Debug: Log before DB connection
file_put_contents('login_debug.log', date('Y-m-d H:i:s') . " - Login attempt for: " . $loginInput . "\n", FILE_APPEND);

require_once 'db.php';

// Check connection
if ($conn->connect_error) {
    file_put_contents('login_debug.log', "Connection failed: " . $conn->connect_error . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

// Check if input is email (contains @) or username
// Query to check both email AND username
$stmt = $conn->prepare("SELECT id, username, password, email FROM users WHERE email = ? OR username = ?");
if (!$stmt) {
    file_put_contents('login_debug.log', "Prepare failed: " . $conn->error . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    $conn->close();
    exit();
}

$stmt->bind_param("ss", $loginInput, $loginInput);
$executed = $stmt->execute();

if (!$executed) {
    file_put_contents('login_debug.log', "Execute failed: " . $stmt->error . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database query failed']);
    $stmt->close();
    $conn->close();
    exit();
}

$result = $stmt->get_result();

// Debug: Log user fetch result
file_put_contents('login_debug.log', "Found rows: " . $result->num_rows . "\n", FILE_APPEND);

// CRITICAL: Only allow login if user exists in database
if ($result->num_rows === 0) {
    // User does NOT exist in database - REJECT login
    file_put_contents('login_debug.log', "User not found in database: " . $loginInput . "\n", FILE_APPEND);
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Invalid email/username or password']);
    $stmt->close();
    $conn->close();
    exit();
}

// User exists - now verify password
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // Debug: Log password verification
    file_put_contents(
        'login_debug.log',
        "Verifying password for user: " . $user['email'] .
        " (username: " . $user['username'] . ")" .
        " | provided password: '" . $loginPassword . "'" .
        " | hash snippet: " . substr($user['password'], 0, 20) . "\n",
        FILE_APPEND
    );
    
    // CRITICAL: Verify password - only allow login if password is correct
    if (password_verify($loginPassword, $user['password'])) {
        // Password is correct - user exists AND password matches
        unset($user['password']); // Don't return password hash
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        
        // Check if the logged-in user is 'admin'
        $isAdmin = ($user['username'] === 'admin');
        
        file_put_contents('login_debug.log', "Login successful for user ID: " . $user['id'] . " (" . $user['email'] . ")\n", FILE_APPEND);
        
        echo json_encode([
            'success' => true,
            'user' => $user,
            'token' => session_id(),
            'isAdmin' => $isAdmin
        ]);
    } else {
        // Invalid password - user exists but password is wrong
        file_put_contents('login_debug.log', "Invalid password for user: " . $user['email'] . "\n", FILE_APPEND);
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Invalid email/username or password']);
    }
} else {
    // Multiple users found (shouldn't happen if database is properly set up)
    file_put_contents('login_debug.log', "ERROR: Multiple users found for: " . $loginInput . "\n", FILE_APPEND);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: Multiple users found']);
}

$stmt->close();
$conn->close();
exit();