<?php

declare(strict_types=1);

namespace Larena\Link\Http\Controllers\Internal;

use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Larena\Link\Contracts\PublicLinkDeliveryContractHardeningReportSource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PublicLinkDeliveryContractHardeningReviewController
{
    public function review(Request $request): JsonResponse|ViewContract
    {
        if (!App::environment(['local', 'testing'])) {
            throw new NotFoundHttpException();
        }

        /** @var PublicLinkDeliveryContractHardeningReportSource $source */
        $source = App::make(PublicLinkDeliveryContractHardeningReportSource::class);
        $report = $source->run();

        if ($request->wantsJson() || $request->query('format') === 'json') {
            return new JsonResponse($report);
        }

        return View::make('larena-link::internal.public-link-delivery-contract-hardening-review', [
            'report' => $report,
            'statusLabel' => self::statusLabel((string) ($report['status'] ?? 'unknown')),
        ]);
    }

    private static function statusLabel(string $status): string
    {
        return match ($status) {
            'passed' => 'Passed',
            'degraded' => 'Degraded',
            'failed' => 'Failed',
            default => 'Unknown',
        };
    }
}
