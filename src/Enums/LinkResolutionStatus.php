<?php

declare(strict_types=1);

namespace Larena\Link\Enums;

enum LinkResolutionStatus: string
{
    case Allowed = 'allowed';
    case Denied = 'denied';
    case UnknownTarget = 'unknown_target';
    case MalformedToken = 'malformed_token';
    case Expired = 'expired';
    case Revoked = 'revoked';
    case AccessDenied = 'access_denied';
    case ScopeMismatch = 'scope_mismatch';

    public function permitsResolution(): bool
    {
        return $this === self::Allowed;
    }
}
