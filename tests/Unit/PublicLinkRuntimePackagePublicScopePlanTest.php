<?php

declare(strict_types=1);

use Larena\Link\Runtime\PublicLinkRuntimePackagePublicScopePlan;

require_once __DIR__ . '/../bootstrap.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$plan = PublicLinkRuntimePackagePublicScopePlan::build();

assert_true($plan['schema'] === 'larena.link.public_link_runtime_package_public_scope_plan.v1', 'Unexpected schema.');
assert_true($plan['status'] === 'candidate_created_not_promoted', 'Plan must reflect candidate-created state.');
assert_true($plan['mutates_state'] === false, 'Plan must not mutate state.');
assert_true($plan['current_state']['entry_app_public_route_retained'] === true, 'Entry-app public route must remain retained.');
assert_true($plan['current_state']['package_public_route_present'] === true, 'Package public route candidate must be present.');
assert_true($plan['current_state']['package_public_route_enabled_by_default'] === false, 'Package public route must stay disabled by default.');
assert_true($plan['current_state']['package_public_controller_present'] === true, 'Package public controller candidate must be present.');
assert_true($plan['required_package_scope']['route_file'] === 'routes/public.php', 'Route file prerequisite must be explicit.');
assert_true($plan['required_package_scope']['controller'] === 'src/Http/Controllers/Public/PublicLinkRuntimeResolveController.php', 'Controller prerequisite must be explicit.');
assert_true(in_array('enable package public route by default', $plan['forbidden_now'], true), 'Default package public route enablement must be forbidden.');
assert_true($plan['next_transition'] === 'prove_public_link_runtime_parity_smoke', 'Next transition must point to parity smoke.');

echo "PublicLinkRuntimePackagePublicScopePlanTest passed.\n";
