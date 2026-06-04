<?php

declare(strict_types=1);

namespace Larena\Link\Contracts;

use Larena\Link\Enums\LinkResolutionStatus;

interface LinkResolutionResult
{
    public function status(): LinkResolutionStatus;

    public function target(): ?LinkTargetDescriptor;

    public function redirectOrDeliveryRef(): ?string;

    /**
     * @return array<string, scalar|null>
     */
    public function safeDiagnostics(): array;
}
