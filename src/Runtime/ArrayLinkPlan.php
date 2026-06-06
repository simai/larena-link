<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

use Larena\Link\Enums\LinkResolutionStatus;

final class ArrayLinkPlan
{
    /**
     * @param array<string, scalar|null> $diagnostics
     */
    public function __construct(
        private readonly LinkResolutionStatus $status,
        private readonly string $reason,
        private readonly bool $mutatesState,
        private readonly bool $productionRuntime,
        private readonly array $diagnostics = [],
    ) {
    }

    public function status(): LinkResolutionStatus
    {
        return $this->status;
    }

    public function reason(): string
    {
        return $this->reason;
    }

    public function mutatesState(): bool
    {
        return $this->mutatesState;
    }

    public function productionRuntime(): bool
    {
        return $this->productionRuntime;
    }

    public function allowed(): bool
    {
        return $this->status->permitsResolution();
    }

    /**
     * @return array<string, scalar|null>
     */
    public function safeDiagnostics(): array
    {
        return $this->diagnostics + [
            'status' => $this->status->value,
            'reason' => $this->reason,
            'mutates_state' => $this->mutatesState,
            'production_runtime' => $this->productionRuntime,
        ];
    }
}
