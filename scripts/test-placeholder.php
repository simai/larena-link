<?php

declare(strict_types=1);

$tests = [
    __DIR__ . '/../tests/Unit/LinkTargetContractTest.php',
    __DIR__ . '/../tests/Unit/LinkFailsClosedTest.php',
];

foreach ($tests as $test) {
    require $test;
}

echo "Larena Link contract tests passed.\n";
