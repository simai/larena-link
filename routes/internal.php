<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Larena\Link\Http\Controllers\Internal\PublicContentLinkFlowReviewController;
use Larena\Link\Http\Controllers\Internal\PublicLinkDryRunRuntimeReviewController;
use Larena\Link\Http\Controllers\Internal\PublicLinkRuntimeHardeningReviewController;
use Larena\Link\Http\Controllers\Internal\PublicLinkRuntimePlanningReviewController;

Route::middleware('web')
    ->prefix('larena/internal')
    ->name('larena.internal.')
    ->group(static function (): void {
        Route::get('/public-content-link-flow', [PublicContentLinkFlowReviewController::class, 'review'])
            ->name('public-content-link-flow');

        Route::get('/public-link-dry-run-runtime', [PublicLinkDryRunRuntimeReviewController::class, 'review'])
            ->name('public-link-dry-run-runtime');

        Route::get('/public-link-runtime-hardening', [PublicLinkRuntimeHardeningReviewController::class, 'review'])
            ->name('public-link-runtime-hardening');

        Route::get('/public-link-runtime-planning', [PublicLinkRuntimePlanningReviewController::class, 'review'])
            ->name('public-link-runtime-planning');
    });
