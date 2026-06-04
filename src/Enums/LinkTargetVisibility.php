<?php

declare(strict_types=1);

namespace Larena\Link\Enums;

enum LinkTargetVisibility: string
{
    case Public = 'public';
    case Protected = 'protected';
    case Private = 'private';
    case Internal = 'internal';

    public function requiresAccessPolicy(): bool
    {
        return $this !== self::Public;
    }
}
