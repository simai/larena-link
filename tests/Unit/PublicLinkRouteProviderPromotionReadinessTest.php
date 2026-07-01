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
assert_true($report['summary']['current_mount'] === 'entry_app_public_route', 'Current mount must remain the entry app public route.');
assert_true($report['summary']['future_mount'] === 'package_service_provider_public_route', 'Future mount must point to the package provider public route.');
assert_true($report['checks']['internal_review_routes_owned_by_package_provider']['status'] === 'passed', 'Internal routes must remain package-owned.');
assert_true($report['checks']['public_runtime_route_guarded_candidate_not_promoted_yet']['status'] === 'passed', 'Public runtime candidate must remain guarded and unpromoted.');
assert_true($report['checks']['public_runtime_route_guarded_candidate_not_promoted_yet']['provider_promotion_enabled_now'] === false, 'Provider promotion must remain disabled.');
assert_true($report['checks']['public_runtime_route_guarded_candidate_not_promoted_yet']['public_route_enabled_by_default'] === false, 'Public route must stay disabled by default.');
assert_true($report['checks']['entry_app_compatibility_adapter_expected']['entry_app_adapter_required_until_package_route_parity_proven'] === true, 'Entry-app adapter must remain required.');
assert_true($report['checks']['entry_app_compatibility_adapter_expected']['package_local_public_controller_candidate_exists'] === true, 'Package public controller candidate must exist.');
assert_true($report['safe_trace']['provider_promotion_executed'] === false, 'Provider promotion must remain unexecuted.');
assert_true($report['safe_trace']['public_runtime_enabled'] === false, 'Public runtime must stay disabled.');
assert_true($report['safe_trace']['package_provider_public_route_guarded'] === true, 'Provider public route must be guarded.');
assert_true($report['safe_trace']['release_ready'] === false, 'Release-ready must stay false.');
assert_true(in_array('public route still mounted by entry app', $report['known_limitations'], true), 'Known limitations must mention entry-app mount.');
assert_true(in_array('package public route candidate disabled by default', $report['known_limitations'], true), 'Known limitations must mention disabled candidate.');
assert_true(is_file($outputPath), 'Readiness report must write JSON evidence.');

echo "PublicLinkRouteProviderPromotionReadinessTest passed.\n";
