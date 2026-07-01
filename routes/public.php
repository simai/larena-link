<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
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
    });
