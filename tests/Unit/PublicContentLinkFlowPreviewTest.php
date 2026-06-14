<?php

declare(strict_types=1);

use Larena\Link\Runtime\PublicContentLinkFlowPreview;

require_once __DIR__ . '/../../vendor/autoload.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$fileOperation = [
    'schema' => 'larena.file_operation_guarded_flow_preview.v1',
    'status' => 'passed',
    'scenario' => 'file_operation_guarded_flow',
    'mutates_state' => false,
    'sandbox_state_mutated' => true,
    'production_mutates_state' => false,
    'checks' => [
        'scope_boundary' => [
            'status' => 'passed',
        ],
    ],
    'safe_trace' => [
        'real_file_mutation' => false,
        'real_database_mutation' => false,
        'public_route' => false,
    ],
];

$fileManagerLink = [
    'schema' => 'larena.file_manager_link_sharing_preview.v1',
    'status' => 'passed',
    'scenario' => 'file_manager_link_sharing',
    'mutates_state' => false,
    'production_mutates_state' => false,
    'checks' => [
        'logical_file_target' => [
            'status' => 'passed',
        ],
        'file_manager_share_intake' => [
            'status' => 'passed',
            'share_status' => 'allowed',
            'share_explain_code' => 'share_plan_ready',
        ],
        'temporary_link_policy' => [
            'status' => 'passed',
            'audience' => 'authenticated',
            'temporary' => true,
            'revocable' => true,
        ],
        'expiry_policy_guard' => [
            'status' => 'passed',
        ],
        'access_boundary' => [
            'status' => 'passed',
        ],
        'audit_boundary' => [
            'status' => 'passed',
        ],
        'revocation_policy_guard' => [
            'status' => 'passed',
            'revocation_status' => 'allowed',
        ],
        'public_exposure_guard' => [
            'status' => 'passed',
        ],
        'confirmation_guard' => [
            'status' => 'passed',
        ],
        'link_missing_access_scope_guard' => [
            'status' => 'passed',
        ],
        'scope_boundary' => [
            'status' => 'passed',
        ],
    ],
    'safe_trace' => [
        'logical_file_id' => 'logical-file:content-link-flow-unit',
        'link_identity_ref' => 'link.identity:content-link-flow-unit',
        'access_scope_ref' => 'access.query_scope:content-link-flow-unit',
        'audit_event_ref' => 'audit.event:content-link-flow-unit',
        'ttl_seconds' => 1800,
    ],
];

$linkSafety = [
    'schema' => 'larena.link_file_sharing_safety_preview.v1',
    'status' => 'passed',
    'scenario' => 'link_file_sharing_safety_workflow',
    'mutates_state' => false,
    'production_mutates_state' => false,
    'checks' => [
        'filesystem_logical_file' => [
            'status' => 'passed',
            'metadata_redacted' => true,
        ],
        'file_manager_share_plan' => [
            'status' => 'passed',
        ],
        'temporary_link_planning' => [
            'status' => 'passed',
        ],
        'access_boundary' => [
            'status' => 'passed',
        ],
        'audit_boundary' => [
            'status' => 'passed',
        ],
        'revocation_planning' => [
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
        'scope_boundary' => [
            'status' => 'passed',
        ],
    ],
];

$outputPath = sys_get_temp_dir() . '/larena-link-public-content-link-flow-' . bin2hex(random_bytes(4)) . '.json';
$report = PublicContentLinkFlowPreview::run($fileOperation, $fileManagerLink, $linkSafety, $outputPath);

assert_true($report['schema'] === 'larena.public_content_link_flow_preview.v1', 'Unexpected schema.');
assert_true($report['status'] === 'passed', 'Public content link flow preview must pass.');
assert_true($report['scenario'] === 'guarded_public_content_file_link_flow', 'Unexpected scenario.');
assert_true($report['mutates_state'] === false, 'Preview must not mutate state.');
assert_true($report['production_mutates_state'] === false, 'Preview must not mutate production state.');
assert_true($report['checks']['content_file_target']['status'] === 'passed', 'Content file target must pass.');
assert_true($report['checks']['file_manager_share_plan']['status'] === 'passed', 'File manager share plan must pass.');
assert_true($report['checks']['temporary_link_contract']['status'] === 'passed', 'Temporary link contract must pass.');
assert_true($report['checks']['expiry_access_audit_contract']['status'] === 'passed', 'Expiry/access/audit contract must pass.');
assert_true($report['checks']['revocation_contract']['status'] === 'passed', 'Revocation contract must pass.');
assert_true($report['checks']['public_runtime_guards']['status'] === 'passed', 'Public runtime guards must pass.');
assert_true($report['checks']['scope_boundary']['status'] === 'passed', 'Scope boundary must pass.');
assert_true($report['safe_trace']['public_route'] === false, 'Public route must stay disabled.');
assert_true($report['safe_trace']['public_ui'] === false, 'Public UI must stay disabled.');
assert_true($report['safe_trace']['real_public_url_generated'] === false, 'Real public URL must not be generated.');
assert_true($report['safe_trace']['token_storage_runtime'] === false, 'Token storage runtime must stay disabled.');
assert_true($report['safe_trace']['one_time_consumption_runtime'] === false, 'One-time runtime must stay disabled.');
assert_true($report['safe_trace']['real_file_mutation'] === false, 'Real file mutation must stay disabled.');
assert_true($report['safe_trace']['real_database_mutation'] === false, 'Real database mutation must stay disabled.');
assert_true($report['safe_trace']['graph_sync_canonical_update'] === false, 'Graph sync must not be canonical.');
assert_true(in_array('stopped_before_public_runtime', $report['flow_steps'], true), 'Flow must stop before public runtime.');
assert_true(in_array('not_release_ready', $report['known_limitations'], true), 'Preview must stay not release-ready.');
assert_true(is_file($outputPath), 'Preview must write JSON evidence when output path is provided.');

echo "PublicContentLinkFlowPreviewTest passed.\n";
