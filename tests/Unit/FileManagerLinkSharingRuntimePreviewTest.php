<?php

declare(strict_types=1);

use Larena\Link\Runtime\FileManagerLinkSharingRuntimePreview;

require_once __DIR__ . '/../../vendor/autoload.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$checks = [
    'launch_record_boundary' => [
        'status' => 'passed',
        'status_cap' => 'developer_testable_foundation',
    ],
    'logical_file_target' => [
        'status' => 'passed',
        'logical_file_id' => 'file-manager-link-sharing-runtime-1',
        'audit_event_recorded' => true,
        'mutates_state' => false,
        'runtime_state' => 'in_memory_only',
    ],
    'file_manager_share_intake' => [
        'status' => 'passed',
        'share_status' => 'allowed',
        'share_explain_code' => 'share_plan_ready',
        'mutates_state' => false,
    ],
    'file_manager_missing_logical_file_guard' => [
        'status' => 'passed',
        'blocked_status' => 'unsafe_operation',
    ],
    'file_manager_missing_access_guard' => [
        'status' => 'passed',
        'blocked_status' => 'missing_access_policy',
    ],
    'temporary_link_policy' => [
        'status' => 'passed',
        'ttl_seconds' => 1800,
        'audience' => 'authenticated',
        'temporary' => true,
        'revocable' => true,
        'mutates_state' => false,
        'production_runtime' => false,
    ],
    'link_missing_access_scope_guard' => [
        'status' => 'passed',
        'reason' => 'missing_access_scope',
    ],
    'expiry_policy_guard' => [
        'status' => 'passed',
        'link_status' => 'expired',
    ],
    'public_exposure_guard' => [
        'status' => 'passed',
        'reason' => 'public_delivery_not_allowed',
        'real_public_url_generated' => false,
    ],
    'confirmation_guard' => [
        'status' => 'passed',
        'reason' => 'missing_confirmation',
    ],
    'revocation_policy_guard' => [
        'status' => 'passed',
        'mutates_state' => false,
        'production_runtime' => false,
    ],
    'access_boundary' => [
        'status' => 'passed',
        'owner' => 'larena/access',
    ],
    'audit_boundary' => [
        'status' => 'passed',
        'owner' => 'larena/audit',
    ],
    'scope_boundary' => [
        'status' => 'passed',
        'mutates_state' => false,
        'public_route' => false,
        'public_ui' => false,
        'real_file_mutation' => false,
        'real_database_mutation' => false,
        'real_public_url_generation' => false,
        'production_runtime' => false,
        'release_ready' => false,
    ],
];

$safeTrace = [
    'logical_file_id' => 'file-manager-link-sharing-runtime-1',
    'link_identity_ref' => 'link:file-manager-link-sharing-runtime-preview',
    'access_scope_ref' => 'access.scope:file-manager.link-sharing.runtime',
    'audit_event_ref' => 'audit.event:file-manager.link-sharing.runtime',
    'ttl_seconds' => 1800,
];

$outputPath = sys_get_temp_dir() . '/larena-file-manager-link-sharing-runtime-' . bin2hex(random_bytes(4)) . '.json';
$report = FileManagerLinkSharingRuntimePreview::run($checks, $safeTrace, $outputPath);

assert_true($report['schema'] === 'larena.file_manager_link_sharing_runtime_preview.v1', 'Unexpected schema.');
assert_true($report['status'] === 'passed', 'File-manager link sharing runtime preview must pass.');
assert_true($report['mutates_state'] === false, 'Preview must not mutate state.');
assert_true($report['scenario'] === 'file_manager_link_sharing_runtime', 'Unexpected scenario.');
assert_true($report['checks']['temporary_link_policy']['ttl_seconds'] === 1800, 'Unexpected TTL.');
assert_true($report['checks']['public_exposure_guard']['real_public_url_generated'] === false, 'Public URL must stay disabled.');
assert_true($report['checks']['scope_boundary']['public_route'] === false, 'Public route must stay disabled.');
assert_true($report['checks']['scope_boundary']['real_database_mutation'] === false, 'Database mutation must stay disabled.');
assert_true(in_array('no_public_route', $report['known_limitations'], true), 'Public route limitation missing.');
assert_true(in_array('no_token_storage_runtime', $report['known_limitations'], true), 'Token storage limitation missing.');
assert_true(in_array('not_release_ready', $report['known_limitations'], true), 'Release limitation missing.');
assert_true($report['safe_trace']['ttl_seconds'] === 1800, 'Safe trace TTL missing.');
assert_true(is_file($outputPath), 'Preview must write JSON evidence when output path is provided.');

echo "FileManagerLinkSharingRuntimePreviewTest passed.\n";
