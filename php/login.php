<?php
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/redis.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
}

$input = getJsonInput();

$identifier = trim($input['username'] ?? $input['email'] ?? '');
$password   = (string) ($input['password'] ?? '');

if ($identifier === '' || $password === '') {
    sendJson(['success' => false, 'message' => 'Username/email and password are required.'], 422);
}

try {
    $pdo = getDbConnection();

    $stmt = $pdo->prepare('SELECT id, username, email, password_hash FROM users WHERE username = :id OR email = :id LIMIT 1');
    $stmt->execute(['id' => $identifier]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        sendJson(['success' => false, 'message' => 'Invalid credentials.'], 401);
    }

    // Create a Redis-backed session token instead of a PHP session
    $token = createSessionToken((int) $user['id']);

    sendJson([
        'success' => true,
        'message' => 'Login successful.',
        'token'   => $token,
        'user'    => [
            'id'       => (int) $user['id'],
            'username' => $user['username'],
            'email'    => $user['email'],
        ],
    ]);
} catch (PDOException $e) {
    sendJson(['success' => false, 'message' => 'Server error during login.'], 500);
}
