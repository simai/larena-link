<?php

declare(strict_types=1);

$migrationPath = __DIR__ . '/../../database/migrations/2026_06_08_000001_create_larena_public_link_lookup_table.php';
$providerPath = __DIR__ . '/../../src/Providers/LinkServiceProvider.php';

assert(is_file($migrationPath));
assert(is_file($providerPath));

$migration = (string) file_get_contents($migrationPath);
$provider = (string) file_get_contents($providerPath);

assert(str_contains($provider, "loadMigrationsFrom(__DIR__ . '/../../database/migrations')"));
assert(str_contains($migration, "Schema::create('larena_public_link_lookup'"));
assert(str_contains($migration, "token_hash_ref', 96"));
assert(str_contains($migration, "Schema::dropIfExists('larena_public_link_lookup')"));

echo "PublicLinkMigrationRegistrationTest passed.\n";
