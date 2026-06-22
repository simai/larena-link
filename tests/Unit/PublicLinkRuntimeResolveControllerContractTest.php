<?php

declare(strict_types=1);

$packageController = (string) file_get_contents(__DIR__ . '/../../src/Http/Controllers/Public/PublicLinkRuntimeResolveController.php');

foreach ([
    'PublicLinkRuntimeHardeningPreview::run(',
    'PublicLinkTokenStorageContractPreview::run($token)',
    'PublicLinkPersistentLookupPreview::run($token)',
    'PublicLinkGuardedDeliveryReadinessPreview::preview($token)',
    'PublicLinkControlledDeliverySimulationPreview::preview($token)',
    'PublicLinkOneTimeConsumptionLifecyclePreview::preview($token)',
    'PublicLinkGuardedRealDeliveryAdapterPreview::preview($token)',
    "'resolution_decision']['http_status_preview'",
] as $requiredNeedle) {
    assert(str_contains($packageController, $requiredNeedle), 'Package controller missing ' . $requiredNeedle);
}

assert(str_contains($packageController, 'PublicLinkDryRunRuntimeReportSource'));
assert(str_contains($packageController, 'raw_token_visible') === false);
assert(!str_contains($packageController, 'file_get_contents('));
assert(!str_contains($packageController, 'DB::'));
assert(!str_contains($packageController, 'Storage::'));

echo "PublicLinkRuntimeResolveControllerContractTest passed.\n";
