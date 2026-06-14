<?php

declare(strict_types=1);

use Larena\Link\Runtime\PublicLinkDryRunRuntimePreview;

require_once __DIR__ . '/../../vendor/autoload.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$planning = [
    'schema' => 'larena.public_link_runtime_planning_preview.v1',
    'status' => 'passed',
    'scenario' => 'guarded_public_link_runtime_planning',
    'mutates_state' => false,
    'production_mutates_state' => false,
    'safe_trace' => [
        'logical_file_id' => 'logical-file:dry-run-unit',
        'link_identity_ref' => 'link.identity:dry-run-unit',
        'access_scope_ref' => 'access.query_scope:dry-run-unit',
        'audit_event_ref' => 'audit.event:dry-run-unit',
        'ttl_seconds' => 1800,
    ],
];

$contentLinkFlow = [
    'schema' => 'larena.public_content_link_flow_preview.v1',
    'status' => 'passed',
    'scenario' => 'public_content_link_flow',
    'mutates_state' => false,
    'production_mutates_state' => false,
];

$outputPath = sys_get_temp_dir() . '/larena-link-public-link-dry-run-runtime-' . bin2hex(random_bytes(4)) . '.json';
$report = PublicLinkDryRunRuntimePreview::run($planning, $contentLinkFlow, $outputPath);

assert_true($report['schema'] === 'larena.public_link_dry_run_runtime_preview.v1', 'Unexpected schema.');
assert_true($report['status'] === 'passed', 'Dry-run runtime preview must pass.');
assert_true($report['scenario'] === 'guarded_public_link_dry_run_runtime', 'Unexpected scenario.');
assert_true($report['mutates_state'] === false, 'Dry-run preview must not mutate state.');
assert_true($report['production_mutates_state'] === false, 'Dry-run preview must not mutate production state.');
assert_true($report['checks']['source_planning_contract']['status'] === 'passed', 'Source planning contract must pass.');
assert_true($report['checks']['dry_run_resolution_contract']['status'] === 'passed', 'Dry-run resolution contract must pass.');
assert_true($report['checks']['dry_run_resolution_contract']['case_count'] === 7, 'Dry-run case count must remain stable.');
assert_true($report['checks']['dry_run_resolution_contract']['allowed_case_count'] === 1, 'Only one case should allow.');
assert_true($report['checks']['dry_run_resolution_contract']['denied_case_count'] === 6, 'Six cases should deny.');
assert_true($report['checks']['scope_boundary']['public_route'] === false, 'Scope must keep public route disabled.');
assert_true($report['checks']['scope_boundary']['route_registered_now'] === false, 'Scope must keep route registration disabled.');
assert_true($report['checks']['scope_boundary']['token_storage_runtime'] === false, 'Scope must keep token storage disabled.');
assert_true($report['checks']['scope_boundary']['token_material_generated_now'] === false, 'Scope must keep token generation disabled.');
assert_true($report['checks']['scope_boundary']['file_download_executed'] === false, 'Scope must block file download.');
assert_true($report['checks']['scope_boundary']['real_file_mutation'] === false, 'Scope must block file mutation.');
assert_true($report['checks']['scope_boundary']['real_database_mutation'] === false, 'Scope must block database mutation.');
assert_true($report['checks']['scope_boundary']['release_ready'] === false, 'Preview must not claim release readiness.');
assert_true($report['safe_trace']['graph_sync_canonical_update'] === false, 'Graph sync must not be canonical.');
assert_true(in_array('not_release_ready', $report['known_limitations'], true), 'Preview must stay not release-ready.');
assert_true(is_file($outputPath), 'Preview must write JSON evidence when output path is provided.');

$cases = [];
foreach ($report['dry_run_cases'] as $case) {
    $cases[(string) $case['id']] = $case;
}

assert_true($cases['active_link_with_access']['decision'] === 'would_allow', 'Active link should allow.');
assert_true($cases['expired_link']['decision'] === 'would_deny', 'Expired link should deny.');
assert_true($cases['revoked_link']['decision'] === 'would_deny', 'Revoked link should deny.');
assert_true($cases['missing_access_scope']['decision'] === 'would_deny', 'Missing access scope should deny.');
assert_true($cases['replay_detected']['decision'] === 'would_deny', 'Replay should deny.');
assert_true($cases['nonce_missing']['decision'] === 'would_deny', 'Missing nonce should deny.');
assert_true($cases['rate_limit_exceeded']['decision'] === 'would_deny', 'Rate limit should deny.');

echo "PublicLinkDryRunRuntimePreviewTest passed.\n";
