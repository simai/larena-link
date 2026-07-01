<?php

declare(strict_types=1);

use Larena\Link\Contracts\LinkTargetDescriptor;
use Larena\Link\Enums\LinkAudience;
use Larena\Link\Enums\LinkResolutionStatus;
use Larena\Link\Enums\LinkTargetVisibility;
use Larena\Link\Runtime\ArrayLinkPolicy;
use Larena\Link\Runtime\ArrayLinkRequest;
use Larena\Link\Runtime\ArrayLinkRevocationPlan;
use Larena\Link\Runtime\InMemoryLinkRuntime;

require_once __DIR__ . '/../bootstrap.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$target = new class implements LinkTargetDescriptor {
    public function type(): string
    {
        return 'document';
    }

    public function ownerPackage(): string
    {
        return 'larena/file-manager';
    }

    public function targetId(): string
    {
        return 'logical-file-1';
    }

    public function visibility(): LinkTargetVisibility
    {
        return LinkTargetVisibility::Protected;
    }

    public function accessPolicyRef(): string
    {
        return 'access.query_scope:file-manager.read';
    }
};

$runtime = new InMemoryLinkRuntime();
$plan = $runtime->planLink(new ArrayLinkRequest(
    'request-1',
    $target,
    new ArrayLinkPolicy(
        LinkAudience::Authenticated,
        3600,
        'access.query_scope:authenticated',
        true,
        false,
        true,
    ),
));

assert_true($plan->status() === LinkResolutionStatus::Allowed, 'Expected link planning to be allowed.');
assert_true($plan->allowed(), 'Allowed link plan must permit resolution.');
assert_true(!$plan->mutatesState(), 'Planning must not mutate state.');
assert_true(!$plan->productionRuntime(), 'Planning must not claim production runtime.');

$revoke = $runtime->planRevocation(new ArrayLinkRevocationPlan(
    'link:abc',
    'operator:1',
    'policy:expired',
    true,
));

assert_true($revoke->status() === LinkResolutionStatus::Allowed, 'Expected revocation planning to be allowed.');
assert_true(!$revoke->mutatesState(), 'Revocation planning must not mutate state.');

$diagnostics = $runtime->diagnosticsReport();

assert_true($diagnostics->status() === 'developer_testable_foundation', 'Unexpected diagnostics status.');
assert_true(in_array('no_public_routes', $diagnostics->warnings(), true), 'Diagnostics must expose no-public-routes warning.');
assert_true(!$diagnostics->mutatesState(), 'Diagnostics must not mutate state.');
assert_true(!$diagnostics->productionRuntime(), 'Diagnostics must not claim production runtime.');

echo "InMemoryLinkRuntimeTest passed.\n";
