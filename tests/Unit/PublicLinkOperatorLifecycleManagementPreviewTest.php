<?php

declare(strict_types=1);

use Larena\Link\Runtime\PublicLinkOperatorLifecycleManagementPreview;

require_once __DIR__ . '/../../vendor/autoload.php';

function assert_true(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . "\n");
        exit(1);
    }
}

$registry = [
    [
        'case_id' => 'active_link',
        'token_fingerprint' => 'sha256:active',
        'raw_token_visible' => false,
        'lifecycle_state' => 'simulated_consumption_planned',
        'adapter_state' => 'adapter_ready_preview',
        'decision' => 'would_allow',
        'reason' => 'lifecycle_gate_passed_adapter_metadata_ready',
        'access_scope_ref' => 'access.scope:active',
        'audit_event_ref' => 'audit.event:active',
        'review_surface' => '/larena/internal/public-link-operator-lifecycle-management',
        'machine_surface' => '/larena/internal/public-link-operator-lifecycle-management?format=json',
        'operator_status' => 'delivery_adapter_ready_preview',
        'allowed_actions' => ['review_decision_trace', 'copy_safe_fingerprint', 'open_machine_report'],
        'blocked_actions' => ['stream_file', 'consume_token', 'write_consumed_at', 'revoke_link', 'regenerate_link', 'delete_link'],
        'requires_future_launch_record' => true,
        'mutates_state' => false,
        'file_content_returned' => false,
        'production_delivery' => false,
    ],
    [
        'case_id' => 'already_consumed',
        'token_fingerprint' => 'sha256:consumed',
        'raw_token_visible' => false,
        'lifecycle_state' => 'blocked_already_consumed',
        'adapter_state' => 'adapter_blocked_already_consumed',
        'decision' => 'would_deny',
        'reason' => 'already_consumed',
        'access_scope_ref' => 'access.scope:consumed',
        'audit_event_ref' => 'audit.event:blocked',
        'review_surface' => '/larena/internal/public-link-operator-lifecycle-management',
        'machine_surface' => '/larena/internal/public-link-operator-lifecycle-management?format=json',
        'operator_status' => 'delivery_blocked_review_required',
        'allowed_actions' => ['review_decision_trace', 'copy_safe_fingerprint', 'open_machine_report'],
        'blocked_actions' => ['stream_file', 'consume_token', 'write_consumed_at', 'revoke_link', 'regenerate_link', 'delete_link'],
        'requires_future_launch_record' => true,
        'mutates_state' => false,
        'file_content_returned' => false,
        'production_delivery' => false,
    ],
    [
        'case_id' => 'expired_link',
        'token_fingerprint' => 'sha256:expired',
        'raw_token_visible' => false,
        'lifecycle_state' => 'blocked_expired',
        'adapter_state' => 'adapter_blocked_expired',
        'decision' => 'would_deny',
        'reason' => 'expired_link',
        'access_scope_ref' => 'access.scope:expired',
        'audit_event_ref' => 'audit.event:blocked',
        'review_surface' => '/larena/internal/public-link-operator-lifecycle-management',
        'machine_surface' => '/larena/internal/public-link-operator-lifecycle-management?format=json',
        'operator_status' => 'delivery_blocked_review_required',
        'allowed_actions' => ['review_decision_trace', 'copy_safe_fingerprint', 'open_machine_report'],
        'blocked_actions' => ['stream_file', 'consume_token', 'write_consumed_at', 'revoke_link', 'regenerate_link', 'delete_link'],
        'requires_future_launch_record' => true,
        'mutates_state' => false,
        'file_content_returned' => false,
        'production_delivery' => false,
    ],
    [
        'case_id' => 'revoked_link',
        'token_fingerprint' => 'sha256:revoked',
        'raw_token_visible' => false,
        'lifecycle_state' => 'blocked_revoked',
        'adapter_state' => 'adapter_blocked_revoked',
        'decision' => 'would_deny',
        'reason' => 'revoked_link',
        'access_scope_ref' => 'access.scope:revoked',
        'audit_event_ref' => 'audit.event:blocked',
        'review_surface' => '/larena/internal/public-link-operator-lifecycle-management',
        'machine_surface' => '/larena/internal/public-link-operator-lifecycle-management?format=json',
        'operator_status' => 'delivery_blocked_review_required',
        'allowed_actions' => ['review_decision_trace', 'copy_safe_fingerprint', 'open_machine_report'],
        'blocked_actions' => ['stream_file', 'consume_token', 'write_consumed_at', 'revoke_link', 'regenerate_link', 'delete_link'],
        'requires_future_launch_record' => true,
        'mutates_state' => false,
        'file_content_returned' => false,
        'production_delivery' => false,
    ],
    [
        'case_id' => 'missing_access',
        'token_fingerprint' => 'sha256:missing',
        'raw_token_visible' => false,
        'lifecycle_state' => 'blocked_missing_access',
        'adapter_state' => 'adapter_blocked_missing_access',
        'decision' => 'would_deny',
        'reason' => 'missing_access_scope',
        'access_scope_ref' => 'access.scope:missing',
        'audit_event_ref' => 'audit.event:blocked',
        'review_surface' => '/larena/internal/public-link-operator-lifecycle-management',
        'machine_surface' => '/larena/internal/public-link-operator-lifecycle-management?format=json',
        'operator_status' => 'delivery_blocked_review_required',
        'allowed_actions' => ['review_decision_trace', 'copy_safe_fingerprint', 'open_machine_report'],
        'blocked_actions' => ['stream_file', 'consume_token', 'write_consumed_at', 'revoke_link', 'regenerate_link', 'delete_link'],
        'requires_future_launch_record' => true,
        'mutates_state' => false,
        'file_content_returned' => false,
        'production_delivery' => false,
    ],
    [
        'case_id' => 'unknown_token',
        'token_fingerprint' => 'sha256:unknown',
        'raw_token_visible' => false,
        'lifecycle_state' => 'blocked_unknown',
        'adapter_state' => 'adapter_blocked_unknown',
        'decision' => 'would_deny',
        'reason' => 'unknown_token',
        'access_scope_ref' => 'access.scope:unknown',
        'audit_event_ref' => 'audit.event:blocked',
        'review_surface' => '/larena/internal/public-link-operator-lifecycle-management',
        'machine_surface' => '/larena/internal/public-link-operator-lifecycle-management?format=json',
        'operator_status' => 'delivery_blocked_review_required',
        'allowed_actions' => ['review_decision_trace', 'copy_safe_fingerprint', 'open_machine_report'],
        'blocked_actions' => ['stream_file', 'consume_token', 'write_consumed_at', 'revoke_link', 'regenerate_link', 'delete_link'],
        'requires_future_launch_record' => true,
        'mutates_state' => false,
        'file_content_returned' => false,
        'production_delivery' => false,
    ],
];

$actionPolicy = [
    ['action' => 'review_decision_trace', 'state' => 'available', 'mutates_state' => false, 'requires_future_launch_record' => false],
    ['action' => 'copy_safe_fingerprint', 'state' => 'available', 'mutates_state' => false, 'requires_future_launch_record' => false],
    ['action' => 'stream_file', 'state' => 'blocked_future_launch_required', 'mutates_state' => true, 'requires_future_launch_record' => true],
    ['action' => 'consume_token', 'state' => 'blocked_future_launch_required', 'mutates_state' => true, 'requires_future_launch_record' => true],
    ['action' => 'revoke_link', 'state' => 'blocked_future_launch_required', 'mutates_state' => true, 'requires_future_launch_record' => true],
    ['action' => 'regenerate_link', 'state' => 'blocked_future_launch_required', 'mutates_state' => true, 'requires_future_launch_record' => true],
];

$outputPath = sys_get_temp_dir() . '/larena-link-operator-lifecycle-' . bin2hex(random_bytes(4)) . '.json';
$report = PublicLinkOperatorLifecycleManagementPreview::run($registry, $actionPolicy, $outputPath);

assert_true($report['schema'] === 'larena.public_link_operator_lifecycle_management_foundation.v1', 'Unexpected schema.');
assert_true($report['status'] === 'passed', 'Operator lifecycle preview must pass.');
assert_true($report['scenario'] === 'public_link_operator_lifecycle_management_foundation', 'Unexpected scenario.');
assert_true($report['mutates_state'] === false, 'Preview must not mutate state.');
assert_true($report['production_mutates_state'] === false, 'Preview must not mutate production state.');
assert_true(count($report['operator_registry']) === 6, 'Registry must contain six cases.');
assert_true($report['checks']['operator_registry']['status'] === 'passed', 'Registry check must pass.');
assert_true($report['checks']['blocked_delivery_explanations']['status'] === 'passed', 'Blocked explanation check must pass.');
assert_true($report['checks']['operator_action_policy']['status'] === 'passed', 'Action policy check must pass.');
assert_true($report['checks']['access_audit_trace']['status'] === 'passed', 'Access/audit trace check must pass.');
assert_true($report['checks']['raw_token_leak_guard']['status'] === 'passed', 'Raw token guard must pass.');
assert_true($report['checks']['file_delivery_block']['file_download_executed'] === false, 'File download must stay disabled.');
assert_true($report['checks']['scope_boundary']['mutation_actions_allowed'] === false, 'Mutation actions must stay disabled.');
assert_true($report['checks']['scope_boundary']['public_ui'] === false, 'Public UI must stay disabled.');
assert_true($report['checks']['scope_boundary']['release_ready'] === false, 'Preview must not claim release readiness.');
assert_true($report['safe_trace']['operator_lifecycle_management_available'] === true, 'Lifecycle preview flag missing.');
assert_true($report['safe_trace']['mutation_actions_allowed'] === false, 'Mutation actions must stay disabled in safe trace.');
assert_true($report['safe_trace']['file_content_returned'] === false, 'File content must stay disabled.');
assert_true($report['safe_trace']['graph_sync_canonical_update'] === false, 'Graph sync must not be canonical.');
assert_true(in_array('no_mutation_actions', $report['known_limitations'], true), 'Mutation limitation missing.');
assert_true(in_array('not_release_ready', $report['known_limitations'], true), 'Release limitation missing.');
assert_true(is_file($outputPath), 'Preview must write JSON evidence when output path is provided.');

echo "PublicLinkOperatorLifecycleManagementPreviewTest passed.\n";
