<?php

declare(strict_types=1);

namespace Larena\Link\Contracts;

use Larena\Link\Enums\LinkResolutionStatus;

interface LinkRuntime
{
    public function resolve(LinkResolutionRequest $request, TokenPolicy $tokenPolicy): LinkResolutionResult;

    public function validateTarget(LinkTargetDescriptor $target): LinkResolutionStatus;
}
