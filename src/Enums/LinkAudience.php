<?php

declare(strict_types=1);

namespace Larena\Link\Enums;

enum LinkAudience: string
{
    case Public = 'public';
    case Authenticated = 'authenticated';
    case SpecificUsers = 'specific_users';
    case InternalSystem = 'internal_system';

    public function requiresAccessScope(): bool
    {
        return $this !== self::Public;
    }
}
