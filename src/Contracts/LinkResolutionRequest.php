<?php

declare(strict_types=1);

namespace Larena\Link\Contracts;

interface LinkResolutionRequest
{
    public function linkCode(): string;

    public function accessScopeRef(): string;

    public function requestContextRef(): string;
}
