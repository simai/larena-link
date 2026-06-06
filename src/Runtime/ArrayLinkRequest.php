<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

use Larena\Link\Contracts\LinkTargetDescriptor;

final class ArrayLinkRequest
{
    /**
     * @param array<string, scalar|null> $diagnostics
     */
    public function __construct(
        private readonly string $requestId,
        private readonly ?LinkTargetDescriptor $target,
        private readonly ?ArrayLinkPolicy $policy,
        private readonly bool $requiresConfirmation = false,
        private readonly bool $confirmationProvided = false,
        private readonly array $diagnostics = [],
    ) {
    }

    public function requestId(): string
    {
        return $this->requestId;
    }

    public function target(): ?LinkTargetDescriptor
    {
        return $this->target;
    }

    public function policy(): ?ArrayLinkPolicy
    {
        return $this->policy;
    }

    public function requiresConfirmation(): bool
    {
        return $this->requiresConfirmation;
    }

    public function confirmationProvided(): bool
    {
        return $this->confirmationProvided;
    }

    /**
     * @return array<string, scalar|null>
     */
    public function safeDiagnostics(): array
    {
        return $this->diagnostics;
    }
}
