
// /**
//  * Central configuration.
//  *
//  * Every value can be overridden by an environment variable (used on Render,
//  * Docker, or any host that injects env vars). If the env var isn't set, the
//  * local-development default after `?:` is used instead — so this file works
//  * unchanged both on XAMPP locally and on Render.
//  */

// function envOr(string $key, string $default): string
// {
//     $value = getenv($key);
//     return ($value === false || $value === '') ? $default : $value;
// }

// // ----- MySQL (registration data) -----
// define('DB_HOST', envOr('DB_HOST', '127.0.0.1'));
// define('DB_PORT', envOr('DB_PORT', '3306'));
// define('DB_NAME', envOr('DB_NAME', 'guvi_internship'));
// define('DB_USER', envOr('DB_USER', 'root'));
// define('DB_PASS', envOr('DB_PASS', ''));

// // ----- MongoDB (profile data) -----
// // If MONGO_URI is set directly (e.g. an Atlas mongodb+srv:// string), it's used as-is.
// // Otherwise a plain URI is built from MONGO_HOST/MONGO_PORT (e.g. a Render private service).
// $mongoUriOverride = getenv('MONGO_URI');
// if ($mongoUriOverride !== false && $mongoUriOverride !== '') {
//     define('MONGO_URI', $mongoUriOverride);
// } else {
//     define('MONGO_URI', 'mongodb://' . envOr('MONGO_HOST', '127.0.0.1') . ':' . envOr('MONGO_PORT', '27017'));
// }
// define('MONGO_DB', envOr('MONGO_DB', 'guvi_internship'));
// define('MONGO_COLLECTION', envOr('MONGO_COLLECTION', 'profiles'));

// // ----- Redis (session storage) -----
// define('REDIS_HOST', envOr('REDIS_HOST', '127.0.0.1'));
// define('REDIS_PORT', (int) envOr('REDIS_PORT', '6379'));
// define('REDIS_PASSWORD', envOr('REDIS_PASSWORD', '')); // blank for local Redis / unauthenticated internal Render Key Value
// define('REDIS_SESSION_PREFIX', 'session:');
// define('REDIS_SESSION_TTL', 3600); // seconds (1 hour)



<?php
/**
 * Central configuration.
 *
 * Supports:
 * - Local XAMPP development
 * - Render deployment
 * - MongoDB Atlas
 * - External MySQL
 * - Redis
 */

/**
 * Get an environment variable.
 */
function envOr(string $key, string $default = ''): string
{
    $value = getenv($key);

    if ($value === false || trim($value) === '') {
        return $default;
    }

    return trim($value);
}


/*
|--------------------------------------------------------------------------
| MySQL
|--------------------------------------------------------------------------
|
| Local:
|   DB_HOST=127.0.0.1
|   DB_PORT=3306
|   DB_NAME=guvi_internship
|   DB_USER=root
|   DB_PASS=
|
| Render:
|   These values are supplied through Render Environment Variables.
|
*/

define('DB_HOST', envOr('DB_HOST', '127.0.0.1'));
define('DB_PORT', (int) envOr('DB_PORT', '3306'));
define('DB_NAME', envOr('DB_NAME', 'sql12835752'));
define('DB_USER', envOr('DB_USER', 'root'));
define('DB_PASS', envOr('DB_PASS', ''));


/*
|--------------------------------------------------------------------------
| MongoDB
|--------------------------------------------------------------------------
|
| Recommended for production:
|
| MONGO_URI=mongodb+srv://username:password@cluster.mongodb.net/
|
| MongoDB Atlas is supported directly.
|
*/

$mongoUri = getenv('MONGO_URI');

if ($mongoUri !== false && trim($mongoUri) !== '') {

    define('MONGO_URI', trim($mongoUri));

} else {

    // Local MongoDB fallback
    $mongoHost = envOr('MONGO_HOST', '127.0.0.1');
    $mongoPort = envOr('MONGO_PORT', '27017');

    define(
        'MONGO_URI',
        'mongodb://' . $mongoHost . ':' . $mongoPort
    );
}


/*
|--------------------------------------------------------------------------
| MongoDB Database / Collection
|--------------------------------------------------------------------------
*/

define(
    'MONGO_DB',
    envOr('MONGO_DB', 'sql12835752')
);

define(
    'MONGO_COLLECTION',
    envOr('MONGO_COLLECTION', 'profiles')
);


/*
|--------------------------------------------------------------------------
| Redis
|--------------------------------------------------------------------------
|
| Local:
|   REDIS_HOST=127.0.0.1
|   REDIS_PORT=6379
|
| Render / Cloud:
|   Set these through Environment Variables.
|
*/

define(
    'REDIS_HOST',
    envOr('REDIS_HOST', '127.0.0.1')
);

define(
    'REDIS_PORT',
    (int) envOr('REDIS_PORT', '6379')
);

define(
    'REDIS_PASSWORD',
    envOr('REDIS_PASSWORD', '')
);


/*
|--------------------------------------------------------------------------
| Redis Session Configuration
|--------------------------------------------------------------------------
*/

define(
    'REDIS_SESSION_PREFIX',
    envOr('REDIS_SESSION_PREFIX', 'session:')
);

define(
    'REDIS_SESSION_TTL',
    (int) envOr('REDIS_SESSION_TTL', '3600')
);
