<?php

declare(strict_types=1);

namespace Larena\Link\Http\Controllers\Public;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Larena\Link\Contracts\PublicLinkDryRunRuntimeReportSource;
use Larena\Link\Runtime\PublicLinkControlledDeliverySimulationPreview;
use Larena\Link\Runtime\PublicLinkGuardedDeliveryReadinessPreview;
use Larena\Link\Runtime\PublicLinkGuardedRealDeliveryAdapterPreview;
use Larena\Link\Runtime\PublicLinkOneTimeConsumptionLifecyclePreview;
use Larena\Link\Runtime\PublicLinkPersistentLookupPreview;
use Larena\Link\Runtime\PublicLinkRuntimeHardeningPreview;
use Larena\Link\Runtime\PublicLinkTokenStorageContractPreview;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PublicLinkRuntimeResolveController
{
    public function __invoke(string $token): JsonResponse
    {
        if (!App::environment(['local', 'testing'])) {
            throw new NotFoundHttpException();
        }

        /** @var PublicLinkDryRunRuntimeReportSource $source */
        $source = App::make(PublicLinkDryRunRuntimeReportSource::class);

        $report = PublicLinkRuntimeHardeningPreview::run(
            $source->run(),
            PublicLinkTokenStorageContractPreview::run($token),
            PublicLinkPersistentLookupPreview::run($token),
            PublicLinkGuardedDeliveryReadinessPreview::preview($token),
            PublicLinkControlledDeliverySimulationPreview::preview($token),
            PublicLinkOneTimeConsumptionLifecyclePreview::preview($token),
            PublicLinkGuardedRealDeliveryAdapterPreview::preview($token),
            $token,
        );

        return new JsonResponse(
            $report,
            (int) ($report['resolution_decision']['http_status_preview'] ?? 403),
        );
    }
}
