<?php

declare(strict_types=1);

$configSource = (string) file_get_contents(__DIR__ . '/../../config/larena-link.php');
$routeSource = (string) file_get_contents(__DIR__ . '/../../routes/public.php');
$providerSource = (string) file_get_contents(__DIR__ . '/../../src/Providers/LinkServiceProvider.php');
$controllerSource = (string) file_get_contents(__DIR__ . '/../../src/Http/Controllers/Public/PublicLinkRuntimeResolveController.php');

assert(str_contains($configSource, "'public_routes'"));
assert(str_contains($configSource, "'enabled' => filter_var(getenv('LARENA_LINK_PUBLIC_ROUTES') ?: false"));
assert(str_contains($configSource, "'local_testing_only' => true"));
assert(str_contains($configSource, "'entry_app_compatibility_route' => 'larena.public-link-runtime-hardening.resolve'"));

assert(str_contains($routeSource, "Route::get("));
assert(str_contains($routeSource, "config('larena-link.public_routes.route', '/larena/link/{token}')"));
assert(str_contains($routeSource, 'PublicLinkRuntimeResolveController::class'));
assert(!str_contains($routeSource, 'Route::post('));

assert(str_contains($providerSource, 'shouldLoadPublicRoutes'));
assert(str_contains($providerSource, 'ConfigRepository::class'));
assert(str_contains($providerSource, "get('larena-link.public_routes.enabled', false)"));
assert(str_contains($providerSource, "loadRoutesFrom(__DIR__ . '/../../routes/public.php')"));

assert(str_contains($controllerSource, 'namespace Larena\\Link\\Http\\Controllers\\Public;'));
assert(str_contains($controllerSource, 'PublicLinkRuntimeHardeningPreview::run('));
assert(str_contains($controllerSource, 'PublicLinkDryRunRuntimeReportSource'));
assert(str_contains($controllerSource, 'new JsonResponse('));
assert(str_contains($controllerSource, "App::environment(['local', 'testing'])"));
assert(str_contains($controllerSource, 'NotFoundHttpException'));
assert(!str_contains($controllerSource, 'extends Controller'));
assert(!str_contains($controllerSource, 'App\\Http\\Controllers'));

echo "PublicLinkPublicRouteRegistrationTest passed.\n";
