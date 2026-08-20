<?php
/**
 * MongoDB access (profile documents), stored keyed by MySQL user_id.
 *
 * Uses the native "mongodb" PHP extension (ext-mongodb) directly via
 * MongoDB\Driver\Manager, so no Composer / mongodb/mongodb library is
 * required. If you'd rather use the higher-level mongodb/mongodb
 * Composer package, swap these functions for the Collection API —
 * the calling code in profile.php doesn't need to change.
 */
require_once __DIR__ . '/config.php';

function getMongoManager(): MongoDB\Driver\Manager
{
    static $manager = null;

    if ($manager === null) {
        $manager = new MongoDB\Driver\Manager(MONGO_URI);
    }

    return $manager;
}

function mongoNamespace(): string
{
    return MONGO_DB . '.' . MONGO_COLLECTION;
}

/**
 * Fetch a single profile document by user_id. Returns assoc array or null.
 */
function getProfile(int $userId): ?array
{
    $manager = getMongoManager();
    $filter = ['user_id' => $userId];
    $query = new MongoDB\Driver\Query($filter, ['limit' => 1]);
    $cursor = $manager->executeQuery(mongoNamespace(), $query);
    $cursor->setTypeMap(['root' => 'array', 'document' => 'array', 'array' => 'array']);

    $docs = $cursor->toArray();
    return $docs[0] ?? null;
}

/**
 * Create or update (upsert) the profile document for a user_id.
 */
function upsertProfile(int $userId, array $fields): void
{
    $manager = getMongoManager();
    $bulk = new MongoDB\Driver\BulkWrite();

    $fields['user_id'] = $userId;

    $bulk->update(
        ['user_id' => $userId],
        ['$set' => $fields],
        ['upsert' => true, 'multi' => false]
    );

    $manager->executeBulkWrite(mongoNamespace(), $bulk);
}
