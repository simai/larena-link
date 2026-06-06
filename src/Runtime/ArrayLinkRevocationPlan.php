<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

final class ArrayLinkRevocationPlan
{
    /**
     * @param array<string, scalar|null> $diagnostics
     */
    public function __construct(
        private readonly string $linkIdentityRef,
        private readonly string $requestedByRef,
        private readonly string $reasonRef,
        private readonly bool $confirmed,
        private readonly array $diagnostics = [],
    ) {
    }

    public function linkIdentityRef(): string
    {
        return $this->linkIdentityRef;
    }

    public function requestedByRef(): string
    {
        return $this->requestedByRef;
    }

    public function reasonRef(): string
    {
        return $this->reasonRef;
    }

    public function confirmed(): bool
    {
        return $this->confirmed;
    }

    /**
     * @return array<string, scalar|null>
     */
    public function safeDiagnostics(): array
    {
        return $this->diagnostics;
    }
}
