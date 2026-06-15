<?php

declare(strict_types=1);

namespace Larena\Link\Contracts;

interface PublicLinkPersistentLookupReportSource
{
    /**
     * @return array<string, mixed>
     */
    public function run(): array;
}
