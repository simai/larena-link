<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Larena\Link\Http\Controllers\Public\CmsPublicContentLinkPreviewController;
use Larena\Link\Http\Controllers\Public\PublicLinkRuntimeResolveController;

Route::middleware((array) config('larena-link.public_routes.middleware', ['web']))
    ->group(static function (): void {
        Route::get(
            (string) config('larena-link.public_routes.route', '/larena/link/{token}'),
            PublicLinkRuntimeResolveController::class,
        )->name((string) config(
            'larena-link.public_routes.name',
            'larena.public-link-runtime-hardening.resolve',
        ));

        // The read-only JSON preview of a public content link (local and testing only).
        Route::get(
            (string) config('larena-link.public_routes.content_link_preview_route', '/larena/cms/content-links/{id}/preview'),
            CmsPublicContentLinkPreviewController::class,
        )->name((string) config(
            'larena-link.public_routes.content_link_preview_name',
            'larena.cms-public-content-link.preview',
        ));
    });
