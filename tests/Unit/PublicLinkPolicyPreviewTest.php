<?php

declare(strict_types=1);

use Larena\Link\Runtime\PublicLinkPolicyPreview;

require_once __DIR__ . '/../../vendor/autoload.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$report = PublicLinkPolicyPreview::run();

assert_true($report['schema'] === 'larena.link_public_link_policy_preview.v1', 'Unexpected schema.');
assert_true($report['status'] === 'passed', 'Public link policy preview must pass.');
assert_true($report['package'] === 'larena/link', 'Policy preview must be package-owned.');
assert_true($report['checks']['package_owned_policy_runtime']['status'] === 'passed', 'Policy runtime must pass.');
assert_true($report['checks']['access_scope_guard']['status'] === 'passed', 'Missing access scope must be blocked.');
assert_true($report['checks']['expiry_guard']['status'] === 'passed', 'Expired link must be blocked.');
assert_true($report['checks']['public_exposure_guard']['status'] === 'passed', 'Public exposure must be blocked.');
assert_true($report['checks']['confirmation_guard']['status'] === 'passed', 'Missing confirmation must be blocked.');
assert_true($report['checks']['token_material_guard']['raw_token_output'] === false, 'Raw token output must stay blocked.');
assert_true($report['checks']['token_material_guard']['token_material_generated_now'] === false, 'Token generation must stay blocked.');
assert_true($report['checks']['token_material_guard']['token_persisted_now'] === false, 'Token persistence must stay blocked.');
assert_true($report['checks']['delivery_runtime_guard']['public_route_registered_now'] === false, 'Public route must stay blocked.');
assert_true($report['checks']['delivery_runtime_guard']['real_delivery_adapter_now'] === false, 'Real delivery must stay blocked.');
assert_true($report['checks']['scope_boundary']['entry_app_dependency'] === false, 'Package preview must not depend on entry app.');
assert_true($report['safe_trace']['policy_runtime_owner'] === 'larena/link', 'Policy runtime owner must be larena/link.');
assert_true($report['safe_trace']['graph_sync_canonical_update'] === false, 'Graph sync must not be canonical update.');
assert_true(in_array('not_release_ready', $report['known_limitations'], true), 'Preview must stay not release-ready.');

echo "PublicLinkPolicyPreviewTest passed.\n";
