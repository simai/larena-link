<?php

declare(strict_types=1);

use Larena\Link\Runtime\PublicLinkCleanupActionPreview;

require_once __DIR__ . '/../bootstrap.php';

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
    'action' => 'cleanup_links',
    'launch_record_ref' => 'docs/project-management/launch-records/public-link-cleanup-action-foundation.json',
    'confirmation' => 'public_link_cleanup_preview',
    'operator_ref' => 'local.testing.operator',
    'retention_policy_ref' => 'retention.policy:public_links.expired_consumed_revoked.preview',
    'dry_run' => true,
    'access_scope_ref' => 'access.scope:public-link.admin.cleanup',
    'audit_event_ref' => 'audit.event:public_link.cleanup.requested',
    'mutates_state_now' => true,
    'production_mutation' => false,
    'scheduler_or_queue_execution' => false,
];

$candidateSet = [
    'snapshot_id' => 'cleanup_candidate_set_preview',
    'retention_policy_ref' => 'retention.policy:public_links.expired_consumed_revoked.preview',
    'candidate_query_shape' => [
        'include_lifecycle_states' => ['expired', 'consumed', 'revoked'],
        'exclude_lifecycle_states' => ['active'],
        'requires_retention_policy' => true,
        'requires_operator_review' => true,
    ],
    'cleanup_candidates' => [
        ['link_ref' => 'public-link:expired-preview', 'lifecycle_state' => 'expired', 'reason' => 'expired_before_retention_cutoff', 'token_fingerprint' => 'sha256:expired'],
        ['link_ref' => 'public-link:consumed-preview', 'lifecycle_state' => 'consumed', 'reason' => 'one_time_link_consumed_before_retention_cutoff', 'token_fingerprint' => 'sha256:consumed'],
        ['link_ref' => 'public-link:revoked-preview', 'lifecycle_state' => 'revoked', 'reason' => 'revoked_before_retention_cutoff', 'token_fingerprint' => 'sha256:revoked'],
    ],
    'excluded_active_links' => [
        ['link_ref' => 'public-link:active-preview', 'lifecycle_state' => 'active', 'reason' => 'active_links_must_not_be_cleaned', 'token_fingerprint' => 'sha256:active'],
    ],
    'access_scope_ref' => 'access.scope:public-link.admin.cleanup',
    'audit_event_ref' => 'audit.event:public_link.cleanup.candidate_snapshot',
];

$wouldClean = [
    'snapshot_id' => 'cleanup_would_clean_preview',
    'from_snapshot' => 'cleanup_candidate_set_preview',
    'would_clean_refs' => [
        'public-link:expired-preview',
        'public-link:consumed-preview',
        'public-link:revoked-preview',
    ],
    'excluded_refs' => ['public-link:active-preview'],
    'would_delete_records' => 3,
    'would_delete_files' => 0,
    'database_delete_executed' => false,
    'file_delete_executed' => false,
    'scheduler_executed' => false,
    'queue_executed' => false,
    'audit_event_ref' => 'audit.event:public_link.cleanup.result',
];

$rollback = [
    'rollback_id' => 'replay_cleanup_candidate_set_preview',
    'candidate_set_snapshot' => 'cleanup_candidate_set_preview',
    'would_clean_snapshot' => 'cleanup_would_clean_preview',
    'rollback_strategy' => 'replay_candidate_set_and_restore_records_from_snapshot',
    'restore_candidate_refs' => [
        'public-link:expired-preview',
        'public-link:consumed-preview',
        'public-link:revoked-preview',
    ],
    'excluded_active_refs' => ['public-link:active-preview'],
    'rollback_executed_now' => false,
    'evidence_required' => [
        'candidate_set_snapshot',
        'would_clean_snapshot',
        'restore_or_replay_plan',
    ],
];

$negativeGuards = [
    ['guard' => 'missing_launch_record', 'status' => 'blocked', 'reason' => 'launch_record_required', 'mutates_state' => false],
    ['guard' => 'missing_retention_policy', 'status' => 'blocked', 'reason' => 'retention_policy_required', 'mutates_state' => false],
    ['guard' => 'cleanup_active_links', 'status' => 'blocked', 'reason' => 'active_links_must_not_be_cleaned', 'mutates_state' => false],
    ['guard' => 'scheduler_execution_in_preview', 'status' => 'blocked', 'reason' => 'scheduler_must_not_run_in_preview', 'mutates_state' => false],
    ['guard' => 'queue_execution_in_preview', 'status' => 'blocked', 'reason' => 'queue_must_not_run_in_preview', 'mutates_state' => false],
    ['guard' => 'file_deletion', 'status' => 'blocked', 'reason' => 'file_deletion_not_allowed_in_cleanup_preview', 'mutates_state' => false],
    ['guard' => 'production_delete_claim', 'status' => 'blocked', 'reason' => 'production_delete_requires_future_launch_record', 'mutates_state' => false],
];

$outputPath = sys_get_temp_dir() . '/larena-link-cleanup-action-' . bin2hex(random_bytes(4)) . '.json';
$report = PublicLinkCleanupActionPreview::run($planning, $request, $candidateSet, $wouldClean, $rollback, $negativeGuards, $outputPath);
$previewOutputPath = sys_get_temp_dir() . '/larena-link-cleanup-action-preview-' . bin2hex(random_bytes(4)) . '.json';
$previewReport = PublicLinkCleanupActionPreview::preview($planning, $previewOutputPath);

assert_true($report['schema'] === 'larena.public_link_cleanup_action_foundation.v1', 'Unexpected schema.');
assert_true($report['status'] === 'passed', 'Cleanup action preview must pass.');
assert_true($report['scenario'] === 'public_link_cleanup_action_foundation', 'Unexpected scenario.');
assert_true($report['mutates_state'] === true, 'Local testing transition flag must stay visible.');
assert_true($report['production_mutates_state'] === false, 'Preview must not mutate production state.');
assert_true($report['checks']['planning_dependency']['status'] === 'passed', 'Planning dependency check must pass.');
assert_true($report['checks']['request_contract']['status'] === 'passed', 'Request contract must pass.');
assert_true($report['checks']['candidate_set_snapshot']['status'] === 'passed', 'Candidate set must pass.');
assert_true($report['checks']['would_clean_snapshot']['status'] === 'passed', 'Would-clean snapshot must pass.');
assert_true($report['checks']['rollback_replay_plan']['status'] === 'passed', 'Rollback replay plan must pass.');
assert_true($report['checks']['negative_guards']['status'] === 'passed', 'Negative guards must pass.');
assert_true($report['checks']['production_deletion_block']['production_database_delete'] === false, 'Production database delete must stay disabled.');
assert_true($report['checks']['production_deletion_block']['file_delete_executed'] === false, 'File delete must stay disabled.');
assert_true($report['checks']['production_deletion_block']['queue_executed'] === false, 'Queue must stay disabled.');
assert_true($report['checks']['scope_boundary']['admin_crud'] === false, 'Admin CRUD must stay disabled.');
assert_true($report['safe_trace']['guarded_cleanup_preview'] === true, 'Cleanup preview flag missing.');
assert_true($report['safe_trace']['dry_run_only'] === true, 'Dry-run flag missing.');
assert_true($report['safe_trace']['persistent_production_cleanup'] === false, 'Persistent production cleanup must stay disabled.');
assert_true($report['safe_trace']['active_link_cleanup'] === false, 'Active links must stay protected.');
assert_true($report['safe_trace']['file_deletion_executed'] === false, 'File deletion must stay disabled.');
assert_true($report['safe_trace']['release_ready'] === false, 'Release-ready claim must stay disabled.');
assert_true(in_array('no_production_cleanup', $report['known_limitations'], true), 'Production cleanup limitation missing.');
assert_true(in_array('no_file_deletion', $report['known_limitations'], true), 'File deletion limitation missing.');
assert_true(in_array('not_release_ready', $report['known_limitations'], true), 'Release limitation missing.');
assert_true(is_file($outputPath), 'Preview must write JSON evidence when output path is provided.');
assert_true($previewReport['schema'] === 'larena.public_link_cleanup_action_foundation.v1', 'Preview helper schema mismatch.');
assert_true($previewReport['status'] === 'passed', 'Preview helper must pass.');
assert_true($previewReport['checks']['candidate_set_snapshot']['candidate_count'] === 3, 'Preview helper must expose cleanup candidates.');
assert_true($previewReport['checks']['candidate_set_snapshot']['excluded_active_count'] === 1, 'Preview helper must protect active links.');
assert_true($previewReport['safe_trace']['dry_run_only'] === true, 'Preview helper must stay dry-run only.');
assert_true($previewReport['safe_trace']['production_database_delete'] === false, 'Preview helper must not delete production records.');
assert_true(!str_contains(json_encode($previewReport, JSON_THROW_ON_ERROR), 'active-preview-token'), 'Preview helper must not expose active raw token.');
assert_true(is_file($previewOutputPath), 'Preview helper must write JSON evidence when output path is provided.');

echo "PublicLinkCleanupActionPreviewTest passed.\n";
