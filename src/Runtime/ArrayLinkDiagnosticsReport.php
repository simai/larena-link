<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

final class ArrayLinkDiagnosticsReport
{
    /**
     * @param list<string> $warnings
     * @param array<string, scalar|null> $diagnostics
     */
    public function __construct(
        private readonly string $status,
        private readonly array $warnings,
        private readonly bool $mutatesState,
        private readonly bool $productionRuntime,
        private readonly array $diagnostics = [],
    ) {
    }

    public function status(): string
    {
        return $this->status;
    }

    /**
     * @return list<string>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }

    public function mutatesState(): bool
    {
        return $this->mutatesState;
    }

    public function productionRuntime(): bool
    {
        return $this->productionRuntime;
    }

    /**
     * @return array<string, scalar|null>
     */
    public function safeDiagnostics(): array
    {
        return $this->diagnostics + [
            'status' => $this->status,
            'warning_count' => count($this->warnings),
            'mutates_state' => $this->mutatesState,
            'production_runtime' => $this->productionRuntime,
        ];
    }
}
