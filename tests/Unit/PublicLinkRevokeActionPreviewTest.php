<?php

declare(strict_types=1);

use Larena\Link\Runtime\PublicLinkRevokeActionPreview;

require_once __DIR__ . '/../../vendor/autoload.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$planning = [
    'schema' => 'larena.public_link_guarded_admin_mutation_planning_foundation.v1',
    'status' => 'passed',
    'mutation_plan_registry' => [
        ['action' => 'revoke_link'],
        ['action' => 'regenerate_link'],
        ['action' => 'cleanup_links'],
    ],
];

$request = [
    'action' => 'revoke_link',
    'launch_record_ref' => 'docs/project-management/launch-records/public-link-revoke-action-foundation.json',
    'confirmation' => 'public_link_revoke_preview',
    'operator_ref' => 'local.testing.operator',
    'token_fingerprint' => 'sha256:active',
    'raw_token_visible' => false,
    'access_scope_ref' => 'access.scope:public-link.admin.revoke',
    'audit_event_ref' => 'audit.event:public_link.revoke.requested',
    'mutates_state_now' => true,
    'production_mutation' => false,
];

$before = [
    'snapshot_id' => 'before_revoke_active_preview',
    'token_fingerprint' => 'sha256:active',
    'lifecycle_state' => 'active',
    'revoked_at' => null,
    'delivery_allowed' => true,
    'access_scope_ref' => 'access.scope:file-manager.link-sharing.runtime',
    'audit_event_ref' => 'audit.event:public_link.revoke.before_snapshot',
];

$after = [
    'snapshot_id' => 'after_revoke_active_preview',
    'token_fingerprint' => 'sha256:active',
    'lifecycle_state' => 'revoked',
    'revoked_at' => 'preview-clock',
    'delivery_allowed' => false,
    'access_scope_ref' => 'access.scope:public-link.admin.revoke',
    'audit_event_ref' => 'audit.event:public_link.revoke.result',
];

$rollback = [
    'rollback_id' => 'restore_active_preview_state',
    'from_snapshot' => 'after_revoke_active_preview',
    'to_snapshot' => 'before_revoke_active_preview',
    'restore_state' => 'active',
    'restore_revoked_at' => null,
    'restore_delivery_allowed' => true,
    'rollback_executed_now' => false,
    'evidence_required' => [
        'before_state_snapshot',
        'after_state_snapshot',
        'restore_previous_revocation_state_plan',
    ],
];

$negativeGuards = [
    ['guard' => 'missing_launch_record', 'status' => 'blocked', 'reason' => 'launch_record_required', 'mutates_state' => false],
    ['guard' => 'missing_access_scope', 'status' => 'blocked', 'reason' => 'access_scope_required', 'mutates_state' => false],
    ['guard' => 'unknown_token', 'status' => 'blocked', 'reason' => 'known_public_link_required', 'mutates_state' => false],
    ['guard' => 'raw_token_output', 'status' => 'blocked', 'reason' => 'raw_token_must_not_be_exposed', 'mutates_state' => false],
];

$outputPath = sys_get_temp_dir() . '/larena-link-revoke-action-' . bin2hex(random_bytes(4)) . '.json';
$report = PublicLinkRevokeActionPreview::run($planning, $request, $before, $after, $rollback, $negativeGuards, $outputPath);

assert_true($report['schema'] === 'larena.public_link_revoke_action_foundation.v1', 'Unexpected schema.');
assert_true($report['status'] === 'passed', 'Revoke action preview must pass.');
assert_true($report['scenario'] === 'public_link_revoke_action_foundation', 'Unexpected scenario.');
assert_true($report['mutates_state'] === true, 'Local testing transition flag must stay visible.');
assert_true($report['production_mutates_state'] === false, 'Preview must not mutate production state.');
assert_true($report['checks']['planning_dependency']['status'] === 'passed', 'Planning dependency check must pass.');
assert_true($report['checks']['request_contract']['status'] === 'passed', 'Request contract must pass.');
assert_true($report['checks']['before_after_snapshots']['status'] === 'passed', 'Before/after snapshots must pass.');
assert_true($report['checks']['rollback_plan']['status'] === 'passed', 'Rollback plan must pass.');
assert_true($report['checks']['negative_guards']['status'] === 'passed', 'Negative guards must pass.');
assert_true($report['checks']['raw_token_leak_guard']['status'] === 'passed', 'Raw token guard must pass.');
assert_true($report['checks']['production_delivery_block']['production_database_write'] === false, 'Production database write must stay disabled.');
assert_true($report['checks']['production_delivery_block']['queue_or_scheduler_executed'] === false, 'Queue/scheduler must stay disabled.');
assert_true($report['checks']['scope_boundary']['admin_crud'] === false, 'Admin CRUD must stay disabled.');
assert_true($report['safe_trace']['guarded_revoke_preview'] === true, 'Revoke preview flag missing.');
assert_true($report['safe_trace']['persistent_production_revocation'] === false, 'Persistent production revocation must stay disabled.');
assert_true($report['safe_trace']['file_content_returned'] === false, 'File content must stay disabled.');
assert_true($report['safe_trace']['graph_sync_canonical_update'] === false, 'Graph sync must not be canonical.');
assert_true(in_array('no_production_revocation', $report['known_limitations'], true), 'Production revocation limitation missing.');
assert_true(in_array('not_release_ready', $report['known_limitations'], true), 'Release limitation missing.');
assert_true(is_file($outputPath), 'Preview must write JSON evidence when output path is provided.');

echo "PublicLinkRevokeActionPreviewTest passed.\n";
