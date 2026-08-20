<?php
/**
 * Redis connection + session-token helpers.
 * Requires the phpredis extension (ext-redis) to be installed & enabled.
 *
 * The client never gets a PHP session cookie. Instead:
 *   - login.php creates a random opaque token, stores "session:<token>" -> user_id in Redis
 *     with an expiry, and returns the token to the browser.
 *   - The browser stores that token in localStorage.
 *   - profile.php (and any other protected endpoint) requires the token to be sent back
 *     via the "Authorization: Bearer <token>" header, and looks it up in Redis.
 */
require_once __DIR__ . '/config.php';

function getRedisConnection(): Redis
{
    static $redis = null;

    if ($redis === null) {
        $redis = new Redis();
        $redis->connect(REDIS_HOST, REDIS_PORT);

        if (REDIS_PASSWORD !== '') {
            $redis->auth(REDIS_PASSWORD);
        }
    }

    return $redis;
}

function createSessionToken(int $userId): string
{
    $redis = getRedisConnection();
    $token = bin2hex(random_bytes(32));
    $redis->setex(REDIS_SESSION_PREFIX . $token, REDIS_SESSION_TTL, (string) $userId);
    return $token;
}

/**
 * Returns the user_id tied to the token, or null if missing/expired.
 * Also refreshes ("slides") the TTL on successful lookup.
 */
function getUserIdFromToken(?string $token): ?int
{
    if (!$token) {
        return null;
    }

    $redis = getRedisConnection();
    $key = REDIS_SESSION_PREFIX . $token;
    $userId = $redis->get($key);

    if ($userId === false) {
        return null;
    }

    $redis->expire($key, REDIS_SESSION_TTL); // sliding expiry
    return (int) $userId;
}

function destroySessionToken(string $token): void
{
    $redis = getRedisConnection();
    $redis->del(REDIS_SESSION_PREFIX . $token);
}

/**
 * Reads the token out of the Authorization: Bearer <token> header
 * and returns the associated user_id, or null if not authenticated.
 */
function requireAuth(): ?int
{
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

    if (!preg_match('/Bearer\s+(\S+)/i', $authHeader, $matches)) {
        return null;
    }

    return getUserIdFromToken($matches[1]);
}
