<?php

declare(strict_types=1);

namespace Larena\Link\Contracts;

interface TokenPolicy
{
    public function ttlSeconds(): int;

    public function maxUses(): int;

    public function scope(): string;

    public function rawTokenStored(): bool;

    /**
     * @return array<string, scalar|null>
     */
    public function safeDiagnostics(): array;
}
