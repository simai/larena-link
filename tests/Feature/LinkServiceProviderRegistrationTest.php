<?php

declare(strict_types=1);

$composer = json_decode((string) file_get_contents(__DIR__ . '/../../composer.json'), true, 512, JSON_THROW_ON_ERROR);
$provider = 'Larena\\Link\\Providers\\LinkServiceProvider';

assert(in_array($provider, $composer['extra']['laravel']['providers'] ?? [], true));

$source = (string) file_get_contents(__DIR__ . '/../../src/Providers/LinkServiceProvider.php');
assert(str_contains($source, "loadRoutesFrom(__DIR__ . '/../../routes/internal.php')"));
assert(str_contains($source, "loadMigrationsFrom(__DIR__ . '/../../database/migrations')"));
assert(str_contains($source, "loadViewsFrom(__DIR__ . '/../../resources/views', 'larena-link')"));
assert(str_contains($source, "environment(['local', 'testing'])"));

echo "LinkServiceProviderRegistrationTest passed.\n";
