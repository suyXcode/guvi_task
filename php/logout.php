<?php
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/redis.php';

$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
    destroySessionToken($matches[1]);
}

sendJson(['success' => true, 'message' => 'Logged out.']);
