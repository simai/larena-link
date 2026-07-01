<?php

declare(strict_types=1);

use Larena\Link\Enums\LinkResolutionStatus;
use Larena\Link\Enums\LinkTargetVisibility;

require_once __DIR__ . '/../bootstrap.php';

foreach ([
    LinkResolutionStatus::Denied,
    LinkResolutionStatus::UnknownTarget,
    LinkResolutionStatus::MalformedToken,
    LinkResolutionStatus::Expired,
    LinkResolutionStatus::Revoked,
    LinkResolutionStatus::AccessDenied,
    LinkResolutionStatus::ScopeMismatch,
] as $status) {
    if ($status->permitsResolution()) {
        fwrite(STDERR, "Link resolution {$status->value} must fail closed.\n");
        exit(1);
    }
}

if (!LinkResolutionStatus::Allowed->permitsResolution()) {
    fwrite(STDERR, "Allowed link resolution must permit resolution.\n");
    exit(1);
}

if (LinkTargetVisibility::Public->requiresAccessPolicy()) {
    fwrite(STDERR, "Public link target visibility must remain the only access-policy-free visibility.\n");
    exit(1);
}

echo "LinkFailsClosedTest passed.\n";
