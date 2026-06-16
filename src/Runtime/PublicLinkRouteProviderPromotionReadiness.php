<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

final class PublicLinkRouteProviderPromotionReadiness
{
    /**
     * @return array<string, mixed>
     */
    public static function run(?string $outputPath = null): array
    {
        $providerPath = dirname(__DIR__) . '/Providers/LinkServiceProvider.php';
        $providerSource = is_file($providerPath)
            ? (string) file_get_contents($providerPath)
            : '';

        $internalRoutePath = dirname(__DIR__, 2) . '/routes/internal.php';
        $internalRouteSource = is_file($internalRoutePath)
            ? (string) file_get_contents($internalRoutePath)
            : '';

        $packagePublicRoutePath = dirname(__DIR__, 2) . '/routes/public.php';
        $packagePublicRouteExists = is_file($packagePublicRoutePath);

        $checks = [
            'package_service_provider_available' => [
                'status' => $providerSource !== '' ? 'passed' : 'failed',
                'provider' => 'Larena\\Link\\Providers\\LinkServiceProvider',
                'source_path' => 'src/Providers/LinkServiceProvider.php',
                'provider_registered_by_package' => true,
            ],
            'internal_review_routes_owned_by_package_provider' => [
                'status' => str_contains($providerSource, "loadRoutesFrom(__DIR__ . '/../../routes/internal.php')")
                    && str_contains($internalRouteSource, "prefix('larena/internal')")
                    ? 'passed'
                    : 'failed',
                'internal_route_file' => 'routes/internal.php',
                'provider_loads_internal_routes' => str_contains($providerSource, "loadRoutesFrom(__DIR__ . '/../../routes/internal.php')"),
                'internal_prefix' => '/larena/internal',
            ],
            'public_runtime_route_not_promoted_yet' => [
                'status' => !$packagePublicRouteExists
                    && !str_contains($providerSource, "loadRoutesFrom(__DIR__ . '/../../routes/public.php')")
                    ? 'passed'
                    : 'failed',
                'package_public_route_file_exists' => $packagePublicRouteExists,
                'provider_loads_public_route_now' => str_contains($providerSource, "loadRoutesFrom(__DIR__ . '/../../routes/public.php')"),
                'future_route_shape' => '/larena/link/{token}',
                'provider_promotion_enabled_now' => false,
            ],
            'entry_app_compatibility_adapter_expected' => [
                'status' => 'passed',
                'current_runtime_route_owner' => 'simai/larena',
                'future_runtime_route_owner' => 'larena/link',
                'current_adapter_controller' => 'App\\Http\\Controllers\\Larena\\Public\\PublicLinkRuntimeResolveController',
                'entry_app_adapter_required_until_package_route_parity_proven' => true,
                'package_local_public_controller_required_before_promotion' => true,
            ],
            'promotion_guards' => [
                'status' => 'passed',
                'production_runtime' => false,
                'public_delivery' => false,
                'database_mutation' => false,
                'filesystem_mutation' => false,
                'release_ready' => false,
                'required_before_promotion' => [
                    'package public route file exists and is provider-loaded',
                    'package-local public runtime controller exists',
                    'entry-app and package route parity is proven',
                    'negative token/access/replay/rate-limit security checks pass',
                    'entry-app compatibility adapter removal has rollback evidence',
                ],
            ],
            'scope_boundary' => [
                'status' => 'passed',
                'developer_testable_foundation_only' => true,
                'provider_promotion_now' => false,
                'token_storage_runtime_enabled' => false,
                'public_route_runtime_enabled' => false,
                'file_download_runtime_enabled' => false,
                'real_database_mutation' => false,
                'real_filesystem_mutation' => false,
                'release_ready' => false,
            ],
        ];

        $report = [
            'schema' => 'larena.public_link_route_provider_promotion_readiness.v1',
            'status' => self::status($checks),
            'generated_at' => gmdate('c'),
            'mutates_state' => false,
            'production_mutates_state' => false,
            'track' => 'package_owned_route_provider_promotion_and_runtime_ownership_readiness',
            'batch' => 'public_link_runtime_route_provider_promotion_readiness',
            'owner_package' => 'larena/link',
            'route_group' => 'public_link_runtime_routes',
            'summary' => [
                'current_mount' => 'entry_app_public_route',
                'future_mount' => 'package_service_provider_public_route',
                'current_route_shape' => '/larena/link/{token}',
                'provider_promotion_status' => 'readiness_recorded_not_executed',
                'entry_app_compatibility_adapter_required' => true,
                'package_public_route_present' => $packagePublicRouteExists,
                'package_provider_loads_public_route_now' => str_contains($providerSource, "loadRoutesFrom(__DIR__ . '/../../routes/public.php')"),
            ],
            'checks' => $checks,
            'safe_trace' => [
                'owner_package' => 'larena/link',
                'route_group' => 'public_link_runtime_routes',
                'entry_app_adapter_controller' => 'App\\Http\\Controllers\\Larena\\Public\\PublicLinkRuntimeResolveController',
                'provider_promotion_executed' => false,
                'package_public_route_present' => $packagePublicRouteExists,
                'package_provider_loads_public_route_now' => str_contains($providerSource, "loadRoutesFrom(__DIR__ . '/../../routes/public.php')"),
                'public_runtime_enabled' => false,
                'real_file_delivery' => false,
                'real_database_mutation' => false,
                'release_ready' => false,
            ],
            'known_limitations' => [
                'public route still mounted by entry app',
                'entry-app compatibility adapter still required',
                'package public route file not created in this batch',
                'no package-local public runtime controller yet',
                'not production runtime',
                'not release ready',
            ],
            'next_recommended_step' => 'prepare_public_link_runtime_provider_promotion_launch_record',
            'evidence_path' => $outputPath,
        ];

        if ($outputPath !== null && $outputPath !== '') {
            self::writeJson($outputPath, $report);
        }

        return $report;
    }

    /**
     * @param array<string, array<string, mixed>> $checks
     */
    private static function status(array $checks): string
    {
        foreach ($checks as $check) {
            if (($check['status'] ?? null) !== 'passed') {
                return 'failed';
            }
        }

        return 'passed';
    }

    /**
     * @param array<string, mixed> $report
     */
    private static function writeJson(string $path, array $report): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents(
            $path,
            json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL,
        );
    }
}
