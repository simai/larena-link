<?php

declare(strict_types=1);

namespace Larena\Link\Contracts;

interface PublicLinkDeliveryContractHardeningReportSource
{
    /**
     * @return array<string, mixed>
     */
    public function run(): array;
}
