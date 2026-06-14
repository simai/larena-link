<?php

declare(strict_types=1);

use Larena\Link\Runtime\PublicLinkGuardedAdminMutationPlanningPreview;

require_once __DIR__ . '/../../vendor/autoload.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$operator = [
    'schema' => 'larena.public_link_operator_lifecycle_management_foundation.v1',
    'status' => 'passed',
    'operator_registry' => [
        ['case_id' => 'active_link'],
        ['case_id' => 'already_consumed'],
        ['case_id' => 'expired_link'],
        ['case_id' => 'revoked_link'],
        ['case_id' => 'missing_access'],
        ['case_id' => 'unknown_token'],
    ],
];

$plans = [
    [
        'action' => 'revoke_link',
        'owner_package' => 'larena/link',
        'state' => 'blocked_future_launch_required',
        'required_launch_record' => 'public-link-revoke-action-foundation',
        'access_scope_ref' => 'access.scope:public-link.admin.revoke',
        'audit_event_refs' => ['audit.event:requested', 'audit.event:result'],
        'rollback_evidence' => ['before_state_snapshot', 'after_state_snapshot', 'restore_plan'],
        'required_negative_tests' => [
            'cannot_revoke_without_launch_record',
            'cannot_revoke_without_access_scope',
            'cannot_expose_raw_token',
            'cannot_mutate_unknown_token',
        ],
        'mutates_state_now' => false,
        'requires_future_launch_record' => true,
    ],
    [
        'action' => 'regenerate_link',
        'owner_package' => 'larena/link',
        'state' => 'blocked_future_launch_required',
        'required_launch_record' => 'public-link-regenerate-action-foundation',
        'access_scope_ref' => 'access.scope:public-link.admin.regenerate',
        'audit_event_refs' => ['audit.event:requested', 'audit.event:result'],
        'rollback_evidence' => ['old_fingerprint_snapshot', 'new_fingerprint_snapshot', 'restore_plan'],
        'required_negative_tests' => [
            'cannot_regenerate_without_launch_record',
            'cannot_return_raw_token_in_preview',
            'cannot_regenerate_without_access_scope',
            'cannot_overwrite_active_link_without_audit',
        ],
        'mutates_state_now' => false,
        'requires_future_launch_record' => true,
    ],
    [
        'action' => 'cleanup_links',
        'owner_package' => 'larena/link',
        'state' => 'blocked_future_launch_required',
        'required_launch_record' => 'public-link-cleanup-action-foundation',
        'access_scope_ref' => 'access.scope:public-link.admin.cleanup',
        'audit_event_refs' => ['audit.event:requested', 'audit.event:result'],
        'rollback_evidence' => ['candidate_set_snapshot', 'deleted_set_snapshot', 'restore_plan'],
        'required_negative_tests' => [
            'cannot_cleanup_without_launch_record',
            'cannot_cleanup_without_retention_policy',
            'cannot_cleanup_active_links',
            'cannot_run_scheduler_in_preview',
        ],
        'mutates_state_now' => false,
        'requires_future_launch_record' => true,
    ],
];

$outputPath = sys_get_temp_dir() . '/larena-link-admin-mutation-planning-' . bin2hex(random_bytes(4)) . '.json';
$report = PublicLinkGuardedAdminMutationPlanningPreview::run($operator, $plans, $outputPath);

assert_true($report['schema'] === 'larena.public_link_guarded_admin_mutation_planning_foundation.v1', 'Unexpected schema.');
assert_true($report['status'] === 'passed', 'Guarded admin mutation planning preview must pass.');
assert_true($report['scenario'] === 'public_link_guarded_admin_mutation_planning_foundation', 'Unexpected scenario.');
assert_true($report['mutates_state'] === false, 'Preview must not mutate state.');
assert_true($report['production_mutates_state'] === false, 'Preview must not mutate production state.');
assert_true(count($report['mutation_plan_registry']) === 3, 'Mutation plan registry must contain three plans.');
assert_true($report['checks']['operator_lifecycle_dependency']['status'] === 'passed', 'Operator dependency check must pass.');
assert_true($report['checks']['mutation_plan_registry']['status'] === 'passed', 'Mutation plan registry check must pass.');
assert_true($report['checks']['launch_record_requirements']['status'] === 'passed', 'Launch record requirements must pass.');
assert_true($report['checks']['rollback_evidence_requirements']['status'] === 'passed', 'Rollback evidence requirements must pass.');
assert_true($report['checks']['access_audit_requirements']['status'] === 'passed', 'Access/audit requirements must pass.');
assert_true($report['checks']['negative_test_requirements']['status'] === 'passed', 'Negative test requirements must pass.');
assert_true($report['checks']['mutation_execution_block']['status'] === 'passed', 'Mutation execution block must pass.');
assert_true($report['checks']['scope_boundary']['mutation_actions_allowed'] === false, 'Mutation actions must remain disabled.');
assert_true($report['safe_trace']['guarded_admin_mutation_planning_available'] === true, 'Planning flag missing.');
assert_true($report['safe_trace']['mutation_actions_allowed'] === false, 'Mutation actions must remain disabled in safe trace.');
assert_true($report['safe_trace']['database_write_executed'] === false, 'Database writes must stay disabled.');
assert_true($report['safe_trace']['queue_or_scheduler_executed'] === false, 'Queue/scheduler must stay disabled.');
assert_true($report['safe_trace']['file_content_returned'] === false, 'File content must stay disabled.');
assert_true($report['safe_trace']['graph_sync_canonical_update'] === false, 'Graph sync must not be canonical.');
assert_true(in_array('no_mutation_actions', $report['known_limitations'], true), 'Mutation limitation missing.');
assert_true(in_array('not_release_ready', $report['known_limitations'], true), 'Release limitation missing.');
assert_true(is_file($outputPath), 'Preview must write JSON evidence when output path is provided.');

echo "PublicLinkGuardedAdminMutationPlanningPreviewTest passed.\n";
