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

function assert_denied(LinkResolutionStatus $actual, string $message): void
{
    if ($actual->permitsResolution()) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$protectedTarget = new class implements LinkTargetDescriptor {
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

$targetWithoutPolicy = new class implements LinkTargetDescriptor {
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
        return 'logical-file-2';
    }

    public function visibility(): LinkTargetVisibility
    {
        return LinkTargetVisibility::Private;
    }

    public function accessPolicyRef(): string
    {
        return '';
    }
};

$runtime = new InMemoryLinkRuntime();

assert_denied(
    $runtime->planLink(new ArrayLinkRequest('', null, null))->status(),
    'Missing target identity must fail closed.',
);

assert_denied(
    $runtime->planLink(new ArrayLinkRequest('request-2', $protectedTarget, null))->status(),
    'Missing link policy must fail closed.',
);

assert_denied(
    $runtime->planLink(new ArrayLinkRequest(
        'request-3',
        $targetWithoutPolicy,
        new ArrayLinkPolicy(LinkAudience::Authenticated, 3600, 'access.query_scope:authenticated', true, false, true),
    ))->status(),
    'Protected target without access policy must fail closed.',
);

assert_denied(
    $runtime->planLink(new ArrayLinkRequest(
        'request-4',
        $protectedTarget,
        new ArrayLinkPolicy(LinkAudience::Authenticated, 3600, '', true, false, true),
    ))->status(),
    'Authenticated link without access scope must fail closed.',
);

assert_denied(
    $runtime->planLink(new ArrayLinkRequest(
        'request-5',
        $protectedTarget,
        new ArrayLinkPolicy(LinkAudience::Authenticated, 0, 'access.query_scope:authenticated', true, false, true),
    ))->status(),
    'Temporary link without valid TTL must fail closed.',
);

assert_denied(
    $runtime->planLink(new ArrayLinkRequest(
        'request-6',
        $protectedTarget,
        new ArrayLinkPolicy(LinkAudience::Public, 3600, '', true, false, true),
    ))->status(),
    'Public link without public delivery policy must fail closed.',
);

assert_denied(
    $runtime->planLink(new ArrayLinkRequest(
        'request-7',
        $protectedTarget,
        new ArrayLinkPolicy(LinkAudience::Authenticated, 3600, 'access.query_scope:authenticated', true, false, true),
        true,
        false,
    ))->status(),
    'High-risk link without confirmation must fail closed.',
);

assert_denied(
    $runtime->planRevocation(new ArrayLinkRevocationPlan('', 'operator:1', 'policy:expired', true))->status(),
    'Revocation without link identity must fail closed.',
);

assert_denied(
    $runtime->planRevocation(new ArrayLinkRevocationPlan('link:abc', '', 'policy:expired', true))->status(),
    'Revocation without actor must fail closed.',
);

assert_denied(
    $runtime->planRevocation(new ArrayLinkRevocationPlan('link:abc', 'operator:1', '', true))->status(),
    'Revocation without reason must fail closed.',
);

assert_denied(
    $runtime->planRevocation(new ArrayLinkRevocationPlan('link:abc', 'operator:1', 'policy:expired', false))->status(),
    'Revocation without confirmation must fail closed.',
);

echo "InMemoryLinkRuntimeFailsClosedTest passed.\n";
