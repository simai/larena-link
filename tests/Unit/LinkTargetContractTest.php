<?php

declare(strict_types=1);

use Larena\Link\Contracts\LinkTargetDescriptor;
use Larena\Link\Contracts\TokenPolicy;
use Larena\Link\Enums\LinkAudience;
use Larena\Link\Enums\LinkTargetVisibility;

require_once __DIR__ . '/../bootstrap.php';

$target = new ReflectionClass(LinkTargetDescriptor::class);

foreach (['type', 'ownerPackage', 'targetId', 'visibility', 'accessPolicyRef'] as $method) {
    if (!$target->hasMethod($method)) {
        fwrite(STDERR, "LinkTargetDescriptor is missing {$method}().\n");
        exit(1);
    }
}

$tokenPolicy = new ReflectionClass(TokenPolicy::class);

foreach (['ttlSeconds', 'maxUses', 'scope', 'rawTokenStored', 'safeDiagnostics'] as $method) {
    if (!$tokenPolicy->hasMethod($method)) {
        fwrite(STDERR, "TokenPolicy is missing {$method}().\n");
        exit(1);
    }
}

if (!LinkTargetVisibility::Private->requiresAccessPolicy()) {
    fwrite(STDERR, "Private link targets must require access policy.\n");
    exit(1);
}

if (!LinkAudience::SpecificUsers->requiresAccessScope()) {
    fwrite(STDERR, "Specific-user share links must require access scope.\n");
    exit(1);
}

echo "LinkTargetContractTest passed.\n";
