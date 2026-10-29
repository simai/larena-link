<?php

declare(strict_types=1);

$configSource = (string) file_get_contents(__DIR__ . '/../../config/larena-link.php');
$routeSource = (string) file_get_contents(__DIR__ . '/../../routes/public.php');
$providerSource = (string) file_get_contents(__DIR__ . '/../../src/Providers/LinkServiceProvider.php');
$previewSource = (string) file_get_contents(__DIR__ . '/../../src/Http/Controllers/Public/CmsPublicContentLinkPreviewController.php');

assert(str_contains($configSource, "'public_routes'"));
// On by default (Root keeps no copy of the route, developer remark 5, 2026-10-26); LARENA_LINK_PUBLIC_ROUTES=false turns it off.
assert(str_contains($configSource, "getenv('LARENA_LINK_PUBLIC_ROUTES') === false ? true : getenv('LARENA_LINK_PUBLIC_ROUTES')"));
assert(!str_contains($configSource, "getenv('LARENA_LINK_PUBLIC_ROUTES') ?: false"));
assert(str_contains($configSource, "'local_testing_only' => true"));
// The /larena/link/{token} runtime-hardening preview route left with the preview chain it reported on (owner decision 2026-10-29).
assert(!str_contains($configSource, 'public-link-runtime-hardening'));
assert(!str_contains($configSource, '/larena/link/{token}'));
assert(!str_contains($configSource, 'entry_app_compatibility_route'));
assert(str_contains($configSource, "'content_link_preview_route' => '/larena/cms/content-links/{id}/preview'"));
assert(str_contains($configSource, "'content_link_preview_name' => 'larena.cms-public-content-link.preview'"));

assert(str_contains($routeSource, "Route::get("));
assert(!str_contains($routeSource, '/larena/link/{token}'));
assert(!str_contains($routeSource, 'PublicLinkRuntimeResolveController'));
assert(!is_file(__DIR__ . '/../../src/Http/Controllers/Public/PublicLinkRuntimeResolveController.php'));
assert(str_contains($routeSource, "config('larena-link.public_routes.content_link_preview_route', '/larena/cms/content-links/{id}/preview')"));
assert(str_contains($routeSource, 'CmsPublicContentLinkPreviewController::class'));
assert(str_contains($routeSource, "'larena.cms-public-content-link.preview'"));
assert(substr_count($routeSource, 'Route::get(') === 1);
assert(!str_contains($routeSource, 'Route::post('));

assert(str_contains($providerSource, 'shouldLoadPublicRoutes'));
assert(str_contains($providerSource, 'ConfigRepository::class'));
assert(str_contains($providerSource, "get('larena-link.public_routes.enabled', false)"));
assert(str_contains($providerSource, "loadRoutesFrom(__DIR__ . '/../../routes/public.php')"));

assert(str_contains($previewSource, 'namespace Larena\\Link\\Http\\Controllers\\Public;'));
assert(str_contains($previewSource, 'CmsPublicContentLinkReadModel::fromDatabase('));
assert(str_contains($previewSource, "App::environment(['local', 'testing'])"));
assert(str_contains($previewSource, 'NotFoundHttpException'));
assert(str_contains($previewSource, "'current_owner' => 'larena/link'"));
assert(str_contains($previewSource, "'database_write' => false"));
assert(!str_contains($previewSource, 'extends Controller'));
assert(!str_contains($previewSource, 'App\\Http\\Controllers'));
assert(!str_contains($previewSource, 'DB::'));

echo "PublicLinkPublicRouteRegistrationTest passed.\n";
