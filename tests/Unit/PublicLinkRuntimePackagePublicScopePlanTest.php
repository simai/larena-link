<?php

declare(strict_types=1);

use Larena\Link\Runtime\PublicLinkRuntimePackagePublicScopePlan;

require_once __DIR__ . '/../../vendor/autoload.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$plan = PublicLinkRuntimePackagePublicScopePlan::build();

assert_true($plan['schema'] === 'larena.link.public_link_runtime_package_public_scope_plan.v1', 'Unexpected schema.');
assert_true($plan['status'] === 'planned_not_executed', 'Plan must remain planning-only.');
assert_true($plan['mutates_state'] === false, 'Plan must not mutate state.');
assert_true($plan['current_state']['entry_app_public_route_retained'] === true, 'Entry-app public route must remain retained.');
assert_true($plan['current_state']['package_public_route_present'] === false, 'Package public route must still be absent.');
assert_true($plan['required_package_scope']['route_file'] === 'routes/public.php', 'Route file prerequisite must be explicit.');
assert_true($plan['required_package_scope']['controller'] === 'src/Http/Controllers/Public/PublicLinkRuntimeResolveController.php', 'Controller prerequisite must be explicit.');
assert_true(in_array('create routes/public.php in this batch', $plan['forbidden_now'], true), 'Current batch must forbid creating the package public route.');
assert_true($plan['next_transition'] === 'prepare_public_link_runtime_provider_promotion_launch_record', 'Next transition must point to promotion launch-record preparation.');

echo "PublicLinkRuntimePackagePublicScopePlanTest passed.\n";
