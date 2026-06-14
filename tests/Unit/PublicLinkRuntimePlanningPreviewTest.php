<?php

declare(strict_types=1);

use Larena\Link\Runtime\PublicLinkRuntimePlanningPreview;

require_once __DIR__ . '/../../vendor/autoload.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$contentLinkFlow = [
    'schema' => 'larena.public_content_link_flow_preview.v1',
    'status' => 'passed',
    'scenario' => 'public_content_link_flow',
    'mutates_state' => false,
    'production_mutates_state' => false,
    'checks' => [
        'expiry_access_audit_contract' => [
            'status' => 'passed',
        ],
        'revocation_contract' => [
            'status' => 'passed',
        ],
        'public_runtime_guards' => [
            'status' => 'passed',
        ],
    ],
    'safe_trace' => [
        'logical_file_id' => 'logical-file:runtime-planning-unit',
        'link_identity_ref' => 'link.identity:runtime-planning-unit',
        'access_scope_ref' => 'access.query_scope:runtime-planning-unit',
        'audit_event_ref' => 'audit.event:runtime-planning-unit',
        'ttl_seconds' => 1800,
    ],
];

$fileManagerLink = [
    'schema' => 'larena.file_manager_link_sharing_preview.v1',
    'status' => 'passed',
    'scenario' => 'file_manager_link_sharing',
    'mutates_state' => false,
    'production_mutates_state' => false,
    'checks' => [
        'expiry_policy_guard' => [
            'status' => 'passed',
        ],
        'revocation_policy_guard' => [
            'status' => 'passed',
        ],
        'public_exposure_guard' => [
            'status' => 'passed',
        ],
    ],
];

$linkSafety = [
    'schema' => 'larena.link_sharing_safety_preview.v1',
    'status' => 'passed',
    'scenario' => 'link_sharing_safety',
    'mutates_state' => false,
    'production_mutates_state' => false,
    'checks' => [
        'temporary_link_planning' => [
            'status' => 'passed',
        ],
        'revocation_confirmation_guard' => [
            'status' => 'passed',
        ],
        'public_exposure_guard' => [
            'status' => 'passed',
        ],
        'confirmation_guard' => [
            'status' => 'passed',
        ],
    ],
];

$outputPath = sys_get_temp_dir() . '/larena-link-public-link-runtime-planning-' . bin2hex(random_bytes(4)) . '.json';
$report = PublicLinkRuntimePlanningPreview::run($contentLinkFlow, $fileManagerLink, $linkSafety, $outputPath);

assert_true($report['schema'] === 'larena.public_link_runtime_planning_preview.v1', 'Unexpected schema.');
assert_true($report['status'] === 'passed', 'Runtime planning preview must pass.');
assert_true($report['scenario'] === 'guarded_public_link_runtime_planning', 'Unexpected scenario.');
assert_true($report['mutates_state'] === false, 'Planning preview must not mutate state.');
assert_true($report['production_mutates_state'] === false, 'Planning preview must not mutate production state.');
assert_true($report['checks']['package_owned_policy_runtime']['status'] === 'passed', 'Package policy runtime must pass.');
assert_true($report['checks']['future_route_gate']['route_registered_now'] === false, 'Public route must not be registered.');
assert_true($report['checks']['token_policy_gate']['token_storage_enabled_now'] === false, 'Token storage must not be enabled.');
assert_true($report['checks']['token_policy_gate']['token_material_generated_now'] === false, 'Token material must not be generated.');
assert_true($report['checks']['negative_runtime_guards']['status'] === 'passed', 'Negative guards must pass.');
assert_true($report['checks']['scope_boundary']['public_route'] === false, 'Scope must keep public route disabled.');
assert_true($report['checks']['scope_boundary']['real_file_mutation'] === false, 'Scope must block file mutation.');
assert_true($report['checks']['scope_boundary']['real_database_mutation'] === false, 'Scope must block database mutation.');
assert_true($report['checks']['scope_boundary']['release_ready'] === false, 'Preview must not claim release readiness.');
assert_true($report['safe_trace']['policy_runtime_owner'] === 'larena/link', 'Policy runtime owner must stay larena/link.');
assert_true($report['safe_trace']['raw_token_output'] === false, 'Raw token output must stay blocked.');
assert_true($report['safe_trace']['token_material_generated_now'] === false, 'Token generation must stay blocked.');
assert_true($report['safe_trace']['graph_sync_canonical_update'] === false, 'Graph sync must not be canonical.');
assert_true(in_array('not_release_ready', $report['known_limitations'], true), 'Preview must stay not release-ready.');
assert_true(is_file($outputPath), 'Preview must write JSON evidence when output path is provided.');

echo "PublicLinkRuntimePlanningPreviewTest passed.\n";
