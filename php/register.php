<?php
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
}

$input = getJsonInput();

$username = trim($input['username'] ?? '');
$email    = trim($input['email'] ?? '');
$password = (string) ($input['password'] ?? '');

// ----- Validation -----
$errors = [];

if ($username === '' || strlen($username) < 3) {
    $errors[] = 'Username must be at least 3 characters.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email is required.';
}
if (strlen($password) < 6) {
    $errors[] = 'Password must be at least 6 characters.';
}

if ($errors) {
    sendJson(['success' => false, 'message' => implode(' ', $errors)], 422);
}

try {
    $pdo = getDbConnection();

    // Check for existing username/email using a prepared statement
    $check = $pdo->prepare('SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1');
    $check->execute(['username' => $username, 'email' => $email]);

    if ($check->fetch()) {
        sendJson(['success' => false, 'message' => 'Username or email already registered.'], 409);
    }

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    $insert = $pdo->prepare(
        'INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)'
    );
    $insert->execute([
        'username'      => $username,
        'email'         => $email,
        'password_hash' => $passwordHash,
    ]);

    sendJson(['success' => true, 'message' => 'Registration successful. You can now log in.']);
} catch (PDOException $e) {
    sendJson(['success' => false, 'message' => 'Server error during registration.'], 500);
}
