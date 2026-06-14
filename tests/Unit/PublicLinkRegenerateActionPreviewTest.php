<?php

declare(strict_types=1);

use Larena\Link\Runtime\PublicLinkRegenerateActionPreview;

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
    'action' => 'regenerate_link',
    'launch_record_ref' => 'docs/project-management/launch-records/public-link-regenerate-action-foundation.json',
    'confirmation' => 'public_link_regenerate_preview',
    'operator_ref' => 'local.testing.operator',
    'current_token_fingerprint' => 'sha256:active',
    'raw_token_visible' => false,
    'raw_regenerated_token_returned' => false,
    'access_scope_ref' => 'access.scope:public-link.admin.regenerate',
    'audit_event_ref' => 'audit.event:public_link.regenerate.requested',
    'mutates_state_now' => true,
    'production_mutation' => false,
];

$oldSnapshot = [
    'snapshot_id' => 'old_regenerate_active_preview',
    'token_fingerprint' => 'sha256:active',
    'lifecycle_state' => 'active',
    'delivery_allowed' => true,
    'active_until' => 'preview-clock-before-regeneration',
    'access_scope_ref' => 'access.scope:file-manager.link-sharing.runtime',
    'audit_event_ref' => 'audit.event:public_link.regenerate.before_snapshot',
];

$newSnapshot = [
    'snapshot_id' => 'new_regenerate_active_preview',
    'previous_token_fingerprint' => 'sha256:active',
    'token_fingerprint' => 'sha256:regenerated',
    'lifecycle_state' => 'active',
    'delivery_allowed' => true,
    'active_from' => 'preview-clock-after-regeneration',
    'access_scope_ref' => 'access.scope:public-link.admin.regenerate',
    'audit_event_ref' => 'audit.event:public_link.regenerate.result',
];

$rollback = [
    'rollback_id' => 'restore_previous_token_hash_preview',
    'from_snapshot' => 'new_regenerate_active_preview',
    'to_snapshot' => 'old_regenerate_active_preview',
    'restore_token_fingerprint' => 'sha256:active',
    'replace_token_fingerprint' => 'sha256:regenerated',
    'restore_delivery_allowed' => true,
    'restore_lifecycle_state' => 'active',
    'rollback_executed_now' => false,
    'evidence_required' => [
        'old_fingerprint_snapshot',
        'new_fingerprint_snapshot',
        'restore_previous_token_hash_plan',
    ],
];

$negativeGuards = [
    ['guard' => 'missing_launch_record', 'status' => 'blocked', 'reason' => 'launch_record_required', 'mutates_state' => false],
    ['guard' => 'missing_access_scope', 'status' => 'blocked', 'reason' => 'access_scope_required', 'mutates_state' => false],
    ['guard' => 'missing_audit_context', 'status' => 'blocked', 'reason' => 'audit_context_required', 'mutates_state' => false],
    ['guard' => 'unknown_token', 'status' => 'blocked', 'reason' => 'known_public_link_required', 'mutates_state' => false],
    ['guard' => 'raw_regenerated_token_output', 'status' => 'blocked', 'reason' => 'raw_regenerated_token_must_not_be_exposed', 'mutates_state' => false],
    ['guard' => 'unbounded_regeneration_loop', 'status' => 'blocked', 'reason' => 'single_guarded_action_per_launch_record_required', 'mutates_state' => false],
];

$outputPath = sys_get_temp_dir() . '/larena-link-regenerate-action-' . bin2hex(random_bytes(4)) . '.json';
$report = PublicLinkRegenerateActionPreview::run($planning, $request, $oldSnapshot, $newSnapshot, $rollback, $negativeGuards, $outputPath);

assert_true($report['schema'] === 'larena.public_link_regenerate_action_foundation.v1', 'Unexpected schema.');
assert_true($report['status'] === 'passed', 'Regenerate action preview must pass.');
assert_true($report['scenario'] === 'public_link_regenerate_action_foundation', 'Unexpected scenario.');
assert_true($report['mutates_state'] === true, 'Local testing transition flag must stay visible.');
assert_true($report['production_mutates_state'] === false, 'Preview must not mutate production state.');
assert_true($report['checks']['planning_dependency']['status'] === 'passed', 'Planning dependency check must pass.');
assert_true($report['checks']['request_contract']['status'] === 'passed', 'Request contract must pass.');
assert_true($report['checks']['fingerprint_snapshots']['status'] === 'passed', 'Fingerprint snapshots must pass.');
assert_true($report['checks']['fingerprint_snapshots']['fingerprint_changed'] === true, 'Fingerprint must change in preview evidence.');
assert_true($report['checks']['rollback_plan']['status'] === 'passed', 'Rollback plan must pass.');
assert_true($report['checks']['negative_guards']['status'] === 'passed', 'Negative guards must pass.');
assert_true($report['checks']['raw_token_leak_guard']['status'] === 'passed', 'Raw token guard must pass.');
assert_true($report['checks']['production_delivery_block']['production_database_write'] === false, 'Production database write must stay disabled.');
assert_true($report['checks']['production_delivery_block']['queue_or_scheduler_executed'] === false, 'Queue/scheduler must stay disabled.');
assert_true($report['checks']['scope_boundary']['admin_crud'] === false, 'Admin CRUD must stay disabled.');
assert_true($report['safe_trace']['guarded_regenerate_preview'] === true, 'Regenerate preview flag missing.');
assert_true($report['safe_trace']['persistent_production_regeneration'] === false, 'Persistent production regeneration must stay disabled.');
assert_true($report['safe_trace']['raw_regenerated_token_returned'] === false, 'Raw regenerated token must stay hidden.');
assert_true($report['safe_trace']['file_content_returned'] === false, 'File content must stay disabled.');
assert_true($report['safe_trace']['graph_sync_canonical_update'] === false, 'Graph sync must not be canonical.');
assert_true(in_array('no_production_regeneration', $report['known_limitations'], true), 'Production regeneration limitation missing.');
assert_true(in_array('no_raw_regenerated_token_output', $report['known_limitations'], true), 'Raw regenerated token limitation missing.');
assert_true(in_array('not_release_ready', $report['known_limitations'], true), 'Release limitation missing.');
assert_true(is_file($outputPath), 'Preview must write JSON evidence when output path is provided.');

echo "PublicLinkRegenerateActionPreviewTest passed.\n";
