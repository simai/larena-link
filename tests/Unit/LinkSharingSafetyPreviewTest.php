<?php

declare(strict_types=1);

use Larena\Link\Runtime\LinkSharingSafetyPreview;

require_once __DIR__ . '/../../vendor/autoload.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$checks = [
    'filesystem_logical_file' => [
        'status' => 'passed',
        'audit_event_recorded' => true,
        'metadata_redacted' => true,
        'delivery_allowed' => true,
        'mutates_state' => false,
        'runtime_state' => 'in_memory_only',
    ],
    'file_manager_share_plan' => [
        'status' => 'passed',
        'mutates_state' => false,
    ],
    'temporary_link_planning' => [
        'status' => 'passed',
        'ttl_seconds' => 900,
        'temporary' => true,
        'audience' => 'authenticated',
        'mutates_state' => false,
        'production_runtime' => false,
    ],
    'expiration_policy_guard' => [
        'status' => 'passed',
        'link_status' => 'expired',
        'mutates_state' => false,
    ],
    'public_exposure_guard' => [
        'status' => 'passed',
        'reason' => 'public_delivery_not_allowed',
        'mutates_state' => false,
    ],
    'confirmation_guard' => [
        'status' => 'passed',
        'reason' => 'missing_confirmation',
        'mutates_state' => false,
    ],
    'revocation_planning' => [
        'status' => 'passed',
        'mutates_state' => false,
        'production_runtime' => false,
    ],
    'revocation_confirmation_guard' => [
        'status' => 'passed',
        'reason' => 'missing_revocation_confirmation',
        'mutates_state' => false,
    ],
    'link_diagnostics' => [
        'status' => 'passed',
        'warnings' => ['no_public_routes'],
        'mutates_state' => false,
        'production_runtime' => false,
    ],
    'access_boundary' => [
        'status' => 'passed',
        'owner' => 'larena/access',
        'scope_ref' => 'access.scope:link-sharing.safety',
    ],
    'audit_boundary' => [
        'status' => 'passed',
        'owner' => 'larena/audit',
        'event_ref' => 'audit.event:link-sharing.safety',
    ],
    'scope_boundary' => [
        'status' => 'passed',
        'mutates_state' => false,
        'user_facing_behavior' => false,
        'production_runtime' => false,
    ],
];

$safeTrace = [
    'logical_file_id' => 'link-sharing-safety-file-1',
    'link_identity_ref' => 'link:link-sharing-safety-preview',
    'access_scope_ref' => 'access.scope:link-sharing.safety',
    'audit_event_ref' => 'audit.event:link-sharing.safety',
    'temporary_ttl_seconds' => 900,
];

$outputPath = sys_get_temp_dir() . '/larena-link-sharing-safety-' . bin2hex(random_bytes(4)) . '.json';
$report = LinkSharingSafetyPreview::run($checks, $safeTrace, $outputPath);

assert_true($report['schema'] === 'larena.link_file_sharing_safety_preview.v1', 'Unexpected schema.');
assert_true($report['status'] === 'passed', 'Safety preview must pass.');
assert_true($report['mutates_state'] === false, 'Safety preview must not mutate state.');
assert_true($report['scenario'] === 'link_file_sharing_safety_workflow', 'Unexpected scenario.');
assert_true($report['checks']['temporary_link_planning']['ttl_seconds'] === 900, 'Unexpected TTL.');
assert_true($report['checks']['public_exposure_guard']['reason'] === 'public_delivery_not_allowed', 'Public guard missing.');
assert_true($report['checks']['revocation_confirmation_guard']['reason'] === 'missing_revocation_confirmation', 'Revocation guard missing.');
assert_true($report['checks']['scope_boundary']['user_facing_behavior'] === false, 'User-facing behavior must stay disabled.');
assert_true(in_array('no_public_route', $report['known_limitations'], true), 'Public route limitation missing.');
assert_true(in_array('no_token_storage_runtime', $report['known_limitations'], true), 'Token storage limitation missing.');
assert_true(in_array('not_release_ready', $report['known_limitations'], true), 'Release limitation missing.');
assert_true($report['safe_trace']['temporary_ttl_seconds'] === 900, 'Safe trace TTL missing.');
assert_true(is_file($outputPath), 'Preview must write JSON evidence when output path is provided.');

echo "LinkSharingSafetyPreviewTest passed.\n";
