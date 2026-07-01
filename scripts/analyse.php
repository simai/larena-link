<?php

declare(strict_types=1);

$phpstan = 'vendor/bin/phpstan';
if (!is_file($phpstan)) {
    echo "PHPStan is not installed; skipping static analysis until composer install runs.\n";
    exit(0);
}

$command = escapeshellarg(PHP_BINARY)
    . ' -d memory_limit=512M '
    . escapeshellarg($phpstan)
    . ' analyse --configuration=phpstan.neon.dist --no-progress --memory-limit=512M';

passthru($command, $exitCode);
exit($exitCode);
