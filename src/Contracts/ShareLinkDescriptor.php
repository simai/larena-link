<?php

declare(strict_types=1);

namespace Larena\Link\Contracts;

use Larena\Link\Enums\LinkAudience;

interface ShareLinkDescriptor
{
    public function target(): LinkTargetDescriptor;

    public function audience(): LinkAudience;

    public function scope(): string;

    public function expiresAt(): ?string;

    public function revocationPolicyRef(): string;
}
