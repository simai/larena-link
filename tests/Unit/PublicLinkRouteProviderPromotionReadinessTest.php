<?php

declare(strict_types=1);

use Larena\Link\Runtime\PublicLinkRouteProviderPromotionReadiness;

require_once __DIR__ . '/../bootstrap.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$outputPath = sys_get_temp_dir() . '/larena-link-public-link-route-provider-promotion-readiness-' . bin2hex(random_bytes(4)) . '.json';
$report = PublicLinkRouteProviderPromotionReadiness::run($outputPath);

assert_true($report['schema'] === 'larena.public_link_route_provider_promotion_readiness.v1', 'Unexpected schema.');
assert_true($report['status'] === 'passed', 'Readiness report must pass.');
assert_true($report['mutates_state'] === false, 'Readiness report must not mutate state.');
assert_true($report['summary']['default_mount'] === 'entry_app_public_route', 'Default mount must remain the entry app public route.');
assert_true($report['summary']['env_enabled_mount'] === 'package_service_provider_public_route', 'Env-enabled mount must point to the package provider public route.');
assert_true($report['summary']['provider_promotion_status'] === 'env_gated_package_provider_route_owner_proven', 'Provider promotion status must reflect env-gated proof.');
assert_true($report['checks']['internal_review_routes_owned_by_package_provider']['status'] === 'passed', 'Internal routes must remain package-owned.');
assert_true($report['checks']['public_runtime_route_guarded_env_gated_provider_promotion']['status'] === 'passed', 'Public runtime route must be guarded and env-gated.');
assert_true($report['checks']['public_runtime_route_guarded_env_gated_provider_promotion']['env_gated_provider_promotion_enabled_now'] === true, 'Env-gated provider promotion must be enabled.');
assert_true($report['checks']['public_runtime_route_guarded_env_gated_provider_promotion']['provider_promotion_enabled_by_default'] === false, 'Provider promotion must stay disabled by default.');
assert_true($report['checks']['entry_app_compatibility_adapter_expected']['entry_app_adapter_required_for_default_and_rollback'] === true, 'Entry-app adapter must remain required for default and rollback.');
assert_true($report['checks']['entry_app_compatibility_adapter_expected']['package_local_public_controller_candidate_exists'] === true, 'Package public controller candidate must exist.');
assert_true($report['safe_trace']['env_gated_provider_promotion_executed'] === true, 'Env-gated provider promotion must be executed.');
assert_true($report['safe_trace']['default_provider_promotion_executed'] === false, 'Default provider promotion must remain unexecuted.');
assert_true($report['safe_trace']['public_runtime_enabled'] === false, 'Public runtime must stay disabled.');
assert_true($report['safe_trace']['package_provider_public_route_guarded'] === true, 'Provider public route must be guarded.');
assert_true($report['safe_trace']['release_ready'] === false, 'Release-ready must stay false.');
assert_true(in_array('public route is still mounted by entry app by default', $report['known_limitations'], true), 'Known limitations must mention default entry-app mount.');
assert_true(in_array('package public route is enabled only by LARENA_LINK_PUBLIC_ROUTES=true', $report['known_limitations'], true), 'Known limitations must mention env-gated package route.');
assert_true(is_file($outputPath), 'Readiness report must write JSON evidence.');

echo "PublicLinkRouteProviderPromotionReadinessTest passed.\n";
