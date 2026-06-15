<?php

declare(strict_types=1);

$routeSource = (string) file_get_contents(__DIR__ . '/../../routes/internal.php');
$controllerSource = (string) file_get_contents(__DIR__ . '/../../src/Http/Controllers/Internal/PublicLinkRegenerateActionReviewController.php');
$viewPath = __DIR__ . '/../../resources/views/internal/public-link-regenerate-action-review.blade.php';

assert(str_contains($routeSource, "prefix('larena/internal')"));
assert(str_contains($routeSource, "'/public-link-regenerate-action'"));
assert(str_contains($routeSource, "->name('public-link-regenerate-action')"));
assert(str_contains($routeSource, 'PublicLinkRegenerateActionReviewController'));
assert(!str_contains($routeSource, "prefix('larena/link')"));
assert(!str_contains($routeSource, "Route::post("));

assert(str_contains($controllerSource, 'PublicLinkRegenerateActionReportSource'));
assert(str_contains($controllerSource, "App::environment(['local', 'testing'])"));
assert(str_contains($controllerSource, 'larena-link::internal.public-link-regenerate-action-review'));
assert(str_contains($controllerSource, 'new JsonResponse($report)'));
assert(is_file($viewPath));

echo "PublicLinkRegenerateActionRouteBoundaryTest passed.\n";
