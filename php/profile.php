<?php
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/redis.php';
require_once __DIR__ . '/mongo.php';

$userId = requireAuth();

if ($userId === null) {
    sendJson(['success' => false, 'message' => 'Not authenticated. Please log in again.'], 401);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare('SELECT id, username, email FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $userId]);
        $account = $stmt->fetch();

        if (!$account) {
            sendJson(['success' => false, 'message' => 'User not found.'], 404);
        }

        $profile = getProfile($userId) ?? [];
        unset($profile['user_id'], $profile['_id']);

        sendJson([
            'success' => true,
            'account' => $account,
            'profile' => $profile,
        ]);
    } catch (Exception $e) {
        sendJson(['success' => false, 'message' => 'Server error fetching profile.'], 500);
    }
}

if ($method === 'POST' || $method === 'PUT') {
    $input = getJsonInput();

    // Whitelist of editable profile fields
    $allowed = ['age', 'dob', 'contact', 'address', 'bio'];
    $fields = [];

    foreach ($allowed as $key) {
        if (array_key_exists($key, $input)) {
            $fields[$key] = trim((string) $input[$key]);
        }
    }

    if (!$fields) {
        sendJson(['success' => false, 'message' => 'No valid profile fields supplied.'], 422);
    }

    if (isset($fields['age']) && $fields['age'] !== '' && !ctype_digit($fields['age'])) {
        sendJson(['success' => false, 'message' => 'Age must be a whole number.'], 422);
    }

    try {
        upsertProfile($userId, $fields);
        sendJson(['success' => true, 'message' => 'Profile updated.', 'profile' => $fields]);
    } catch (Exception $e) {
        sendJson(['success' => false, 'message' => 'Server error updating profile.'], 500);
    }
}

sendJson(['success' => false, 'message' => 'Method not allowed'], 405);
