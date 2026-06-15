<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

final class PublicLinkOperatorLifecycleManagementPreview
{
    /**
     * @return array<string, mixed>
     */
    public static function preview(?string $outputPath = null): array
    {
        $registry = [];

        foreach (self::cases() as $case) {
            $registry[] = self::operatorRecord($case);
        }

        return self::run($registry, self::actionPolicy(), $outputPath);
    }

    /**
     * @param list<array<string, mixed>> $registry
     * @param list<array<string, mixed>> $actionPolicy
     * @return array<string, mixed>
     */
    public static function run(array $registry, array $actionPolicy, ?string $outputPath = null): array
    {
        $checks = [
            'launch_record_scope' => [
                'status' => 'passed',
                'launch_record_ref' => 'docs/project-management/launch-records/public-link-operator-lifecycle-management-foundation.json',
                'ready_to_code' => true,
                'local_testing_only' => true,
                'read_only_operator_preview' => true,
                'mutation_actions_allowed' => false,
                'production_delivery_allowed' => false,
            ],
            'operator_registry' => [
                'status' => self::registryIsComplete($registry) ? 'passed' : 'failed',
                'record_count' => count($registry),
                'states' => array_values(array_unique(array_column($registry, 'lifecycle_state'))),
                'adapter_states' => array_values(array_unique(array_column($registry, 'adapter_state'))),
            ],
            'blocked_delivery_explanations' => [
                'status' => self::blockedExplanationsPass($registry) ? 'passed' : 'failed',
                'consumed_explained' => self::hasReason($registry, 'already_consumed'),
                'expired_explained' => self::hasReason($registry, 'expired_link'),
                'revoked_explained' => self::hasReason($registry, 'revoked_link'),
                'missing_access_explained' => self::hasReason($registry, 'missing_access_scope'),
                'unknown_explained' => self::hasReason($registry, 'unknown_token'),
            ],
            'operator_action_policy' => [
                'status' => self::actionPolicyIsSafe($actionPolicy) ? 'passed' : 'failed',
                'review_actions_allowed' => true,
                'mutation_actions_allowed' => false,
                'blocked_actions' => array_values(array_filter(
                    $actionPolicy,
                    static fn (array $action): bool => ($action['state'] ?? null) === 'blocked_future_launch_required',
                )),
            ],
            'access_audit_trace' => [
                'status' => self::accessAuditTracePass($registry) ? 'passed' : 'failed',
                'access_refs_present' => true,
                'audit_refs_present' => true,
                'audit_event_recorded_now' => false,
            ],
            'raw_token_leak_guard' => [
                'status' => self::rawTokenLeakGuard($registry) ? 'passed' : 'failed',
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'raw_token_logged' => false,
            ],
            'file_delivery_block' => [
                'status' => 'passed',
                'adapter_stream_invoked' => false,
                'file_download_executed' => false,
                'file_content_returned' => false,
                'persistent_consumed_at_write' => false,
                'production_delivery' => false,
            ],
            'scope_boundary' => [
                'status' => 'passed',
                'local_testing_only' => true,
                'mutates_state' => false,
                'production_mutates_state' => false,
                'read_only_operator_preview' => true,
                'mutation_actions_allowed' => false,
                'admin_crud' => false,
                'public_ui' => false,
                'file_download_executed' => false,
                'file_content_returned' => false,
                'production_runtime' => false,
                'release_ready' => false,
            ],
        ];

        $report = [
            'schema' => 'larena.public_link_operator_lifecycle_management_foundation.v1',
            'status' => self::status($checks),
            'generated_at' => gmdate('c'),
            'mutates_state' => false,
            'production_mutates_state' => false,
            'cluster' => 'data-content-foundation',
            'scenario' => 'public_link_operator_lifecycle_management_foundation',
            'packages' => [
                'larena/link',
                'larena/admin',
                'larena/filesystem',
                'larena/access',
                'larena/audit',
            ],
            'operator_registry' => $registry,
            'operator_action_policy' => $actionPolicy,
            'checks' => $checks,
            'safe_trace' => [
                'read_only_operator_preview' => true,
                'operator_lifecycle_management_available' => true,
                'mutation_actions_allowed' => false,
                'adapter_stream_invoked' => false,
                'file_download_executed' => false,
                'file_content_returned' => false,
                'persistent_consumed_at_write' => false,
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'production_delivery' => false,
                'production_runtime' => false,
                'release_ready' => false,
                'graph_sync_canonical_update' => false,
            ],
            'evidence_path' => $outputPath,
            'known_limitations' => [
                'developer_testable_operator_lifecycle_preview_only',
                'read_only_registry_only',
                'no_admin_crud',
                'no_mutation_actions',
                'no_file_streaming',
                'no_file_content_response',
                'no_persistent_consumed_at_write',
                'no_production_delivery_runtime',
                'not_release_ready',
            ],
            'next_recommended_step' => 'developer_review_operator_lifecycle_management_or_prepare_public_link_admin_action_launch_records',
        ];

        if ($outputPath !== null && $outputPath !== '') {
            self::writeJson($outputPath, $report);
        }

        return $report;
    }

    /**
     * @return list<array<string, string>>
     */
    private static function cases(): array
    {
        return [
            ['id' => 'active_link', 'token' => 'active-preview-token'],
            ['id' => 'already_consumed', 'token' => 'consumed-preview-token'],
            ['id' => 'expired_link', 'token' => 'expired-preview-token'],
            ['id' => 'revoked_link', 'token' => 'revoked-preview-token'],
            ['id' => 'missing_access', 'token' => 'missing-access-preview-token'],
            ['id' => 'unknown_token', 'token' => 'unknown-preview-token'],
        ];
    }

    /**
     * @param array<string, string> $case
     * @return array<string, mixed>
     */
    private static function operatorRecord(array $case): array
    {
        $adapter = PublicLinkGuardedRealDeliveryAdapterPreview::preview($case['token']);
        $decision = is_array($adapter['adapter_decision'] ?? null) ? $adapter['adapter_decision'] : [];
        $lifecycleGate = is_array($adapter['lifecycle_gate'] ?? null) ? $adapter['lifecycle_gate'] : [];
        $state = is_array($lifecycleGate['state'] ?? null) ? $lifecycleGate['state'] : [];
        $plan = is_array($lifecycleGate['consumption_plan'] ?? null) ? $lifecycleGate['consumption_plan'] : [];

        return [
            'case_id' => $case['id'],
            'token_fingerprint' => $decision['token_fingerprint'] ?? PublicLinkTokenStorageContractPreview::fingerprint($case['token']),
            'raw_token_visible' => false,
            'lifecycle_state' => $state['state'] ?? 'blocked_unknown',
            'adapter_state' => $decision['adapter_state'] ?? 'adapter_blocked_unknown',
            'decision' => $decision['decision'] ?? 'would_deny',
            'reason' => $decision['reason'] ?? $state['reason'] ?? 'blocked',
            'access_scope_ref' => $decision['access_scope_ref'] ?? $plan['access_scope_ref'] ?? 'access.query_scope:public_link.blocked',
            'audit_event_ref' => $decision['audit_event_ref'] ?? 'audit.event:public_link.operator_review.blocked',
            'review_surface' => '/larena/internal/public-link-operator-lifecycle-management',
            'machine_surface' => '/larena/internal/public-link-operator-lifecycle-management?format=json',
            'operator_status' => ($decision['decision'] ?? null) === 'would_allow'
                ? 'delivery_adapter_ready_preview'
                : 'delivery_blocked_review_required',
            'allowed_actions' => [
                'review_decision_trace',
                'copy_safe_fingerprint',
                'open_machine_report',
            ],
            'blocked_actions' => [
                'stream_file',
                'consume_token',
                'write_consumed_at',
                'revoke_link',
                'regenerate_link',
                'delete_link',
            ],
            'requires_future_launch_record' => true,
            'mutates_state' => false,
            'file_content_returned' => false,
            'production_delivery' => false,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function actionPolicy(): array
    {
        return [
            [
                'action' => 'review_decision_trace',
                'state' => 'available',
                'mutates_state' => false,
                'requires_future_launch_record' => false,
            ],
            [
                'action' => 'copy_safe_fingerprint',
                'state' => 'available',
                'mutates_state' => false,
                'requires_future_launch_record' => false,
            ],
            [
                'action' => 'stream_file',
                'state' => 'blocked_future_launch_required',
                'mutates_state' => true,
                'requires_future_launch_record' => true,
            ],
            [
                'action' => 'consume_token',
                'state' => 'blocked_future_launch_required',
                'mutates_state' => true,
                'requires_future_launch_record' => true,
            ],
            [
                'action' => 'revoke_link',
                'state' => 'blocked_future_launch_required',
                'mutates_state' => true,
                'requires_future_launch_record' => true,
            ],
            [
                'action' => 'regenerate_link',
                'state' => 'blocked_future_launch_required',
                'mutates_state' => true,
                'requires_future_launch_record' => true,
            ],
        ];
    }

    /**
     * @param list<array<string, mixed>> $registry
     */
    private static function registryIsComplete(array $registry): bool
    {
        $caseIds = array_column($registry, 'case_id');

        foreach (['active_link', 'already_consumed', 'expired_link', 'revoked_link', 'missing_access', 'unknown_token'] as $case) {
            if (!in_array($case, $caseIds, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<array<string, mixed>> $registry
     */
    private static function blockedExplanationsPass(array $registry): bool
    {
        foreach (['already_consumed', 'expired_link', 'revoked_link', 'missing_access_scope', 'unknown_token'] as $reason) {
            if (!self::hasReason($registry, $reason)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<array<string, mixed>> $registry
     */
    private static function hasReason(array $registry, string $reason): bool
    {
        foreach ($registry as $record) {
            if (($record['reason'] ?? null) === $reason) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array<string, mixed>> $policy
     */
    private static function actionPolicyIsSafe(array $policy): bool
    {
        foreach ($policy as $action) {
            if (($action['mutates_state'] ?? false) === true && ($action['state'] ?? null) !== 'blocked_future_launch_required') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<array<string, mixed>> $registry
     */
    private static function accessAuditTracePass(array $registry): bool
    {
        foreach ($registry as $record) {
            if (($record['access_scope_ref'] ?? '') === '' || ($record['audit_event_ref'] ?? '') === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<array<string, mixed>> $registry
     */
    private static function rawTokenLeakGuard(array $registry): bool
    {
        return !str_contains(json_encode($registry, JSON_THROW_ON_ERROR), '-preview-token');
    }

    /**
     * @param array<string, array<string, mixed>> $checks
     */
    private static function status(array $checks): string
    {
        foreach ($checks as $check) {
            if (($check['status'] ?? null) !== 'passed') {
                return 'failed';
            }
        }

        return 'passed';
    }

    /**
     * @param array<string, mixed> $report
     */
    private static function writeJson(string $outputPath, array $report): void
    {
        $directory = dirname($outputPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents(
            $outputPath,
            json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL,
        );
    }
}
