<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

final class PublicLinkRuntimePackagePublicScopePlan
{
    /**
     * @return array<string, mixed>
     */
    public static function build(): array
    {
        return [
            'schema' => 'larena.link.public_link_runtime_package_public_scope_plan.v1',
            'status' => 'planned_not_executed',
            'mutates_state' => false,
            'track' => 'package_owned_route_provider_promotion_and_runtime_ownership_readiness',
            'batch' => 'public-link-runtime-package-public-scope-planning',
            'owner_package' => 'larena/link',
            'route_group' => 'public_link_runtime_routes',
            'current_state' => [
                'entry_app_public_route_retained' => true,
                'package_public_route_present' => false,
                'package_public_controller_present' => false,
                'provider_loads_public_route_now' => false,
            ],
            'required_package_scope' => [
                'route_file' => 'routes/public.php',
                'controller' => 'src/Http/Controllers/Public/PublicLinkRuntimeResolveController.php',
                'provider_change' => "loadRoutesFrom(__DIR__ . '/../../routes/public.php')",
                'must_preserve_route_shape' => '/larena/link/{token}',
                'must_preserve_route_name' => 'larena.public-link-runtime-hardening.resolve',
            ],
            'required_guards_before_promotion' => [
                'package public route and controller parity is implemented and tested',
                'negative token, access, replay and rate-limit checks are explicit',
                'entry-app compatibility adapter removal has rollback evidence',
                'public delivery, token storage and runtime mutation remain disabled until a dedicated launch record allows them',
            ],
            'forbidden_now' => [
                'create routes/public.php in this batch',
                'add src/Http/Controllers/Public/* in this batch',
                'load a package public route from LinkServiceProvider in this batch',
                'remove App\\Http\\Controllers\\Larena\\Public\\PublicLinkRuntimeResolveController',
                'change the /larena/link/{token} contract',
            ],
            'next_transition' => 'prepare_public_link_runtime_provider_promotion_launch_record',
        ];
    }
}
