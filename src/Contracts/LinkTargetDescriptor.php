<?php

declare(strict_types=1);

namespace Larena\Link\Contracts;

use Larena\Link\Enums\LinkTargetVisibility;

interface LinkTargetDescriptor
{
    public function type(): string;

    public function ownerPackage(): string;

    public function targetId(): string;

    public function visibility(): LinkTargetVisibility;

    public function accessPolicyRef(): string;
}
