<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

use Larena\Link\Enums\LinkAudience;

final class ArrayLinkPolicy
{
    /**
     * @param array<string, scalar|null> $diagnostics
     */
    public function __construct(
        private readonly LinkAudience $audience,
        private readonly ?int $ttlSeconds,
        private readonly string $accessScopeRef,
        private readonly bool $temporary,
        private readonly bool $publicDeliveryAllowed,
        private readonly bool $revocable,
        private readonly array $diagnostics = [],
    ) {
    }

    public function audience(): LinkAudience
    {
        return $this->audience;
    }

    public function ttlSeconds(): ?int
    {
        return $this->ttlSeconds;
    }

    public function accessScopeRef(): string
    {
        return $this->accessScopeRef;
    }

    public function temporary(): bool
    {
        return $this->temporary;
    }

    public function publicDeliveryAllowed(): bool
    {
        return $this->publicDeliveryAllowed;
    }

    public function revocable(): bool
    {
        return $this->revocable;
    }

    /**
     * @return array<string, scalar|null>
     */
    public function safeDiagnostics(): array
    {
        return $this->diagnostics;
    }
}
