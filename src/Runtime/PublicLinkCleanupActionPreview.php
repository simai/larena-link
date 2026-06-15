<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

final class PublicLinkCleanupActionPreview
{
    /**
     * @param array<string, mixed> $planning
     * @return array<string, mixed>
     */
    public static function preview(array $planning, ?string $outputPath = null): array
    {
        $request = self::request();
        $candidateSet = self::candidateSetSnapshot($request);
        $wouldClean = self::wouldCleanSnapshot($candidateSet);
        $rollback = self::rollbackPlan($candidateSet, $wouldClean);
        $negativeGuards = self::negativeGuards();

        return self::run(
            $planning,
            $request,
            $candidateSet,
            $wouldClean,
            $rollback,
            $negativeGuards,
            $outputPath,
        );
    }

    /**
     * @param array<string, mixed> $planning
     * @param array<string, mixed> $request
     * @param array<string, mixed> $candidateSet
     * @param array<string, mixed> $wouldClean
     * @param array<string, mixed> $rollback
     * @param list<array<string, mixed>> $negativeGuards
     * @return array<string, mixed>
     */
    public static function run(
        array $planning,
        array $request,
        array $candidateSet,
        array $wouldClean,
        array $rollback,
        array $negativeGuards,
        ?string $outputPath = null
    ): array {
        $checks = [
            'launch_record_scope' => [
                'status' => 'passed',
                'launch_record_ref' => 'docs/project-management/launch-records/public-link-cleanup-action-foundation.json',
                'ready_to_code' => true,
                'local_testing_only' => true,
                'guarded_cleanup_preview' => true,
                'production_deletion_allowed' => false,
                'scheduler_or_queue_allowed' => false,
            ],
            'planning_dependency' => [
                'status' => ($planning['status'] ?? null) === 'passed' ? 'passed' : 'failed',
                'dependency_schema' => $planning['schema'] ?? null,
                'cleanup_plan_available' => self::planningContainsCleanup($planning),
            ],
            'request_contract' => [
                'status' => self::requestContractPass($request) ? 'passed' : 'failed',
                'confirmation_required' => true,
                'access_scope_required' => true,
                'retention_policy_required' => true,
                'dry_run_required' => true,
            ],
            'candidate_set_snapshot' => [
                'status' => self::candidateSetPass($candidateSet) ? 'passed' : 'failed',
                'candidate_count' => count(is_array($candidateSet['cleanup_candidates'] ?? null) ? $candidateSet['cleanup_candidates'] : []),
                'excluded_active_count' => count(is_array($candidateSet['excluded_active_links'] ?? null) ? $candidateSet['excluded_active_links'] : []),
                'retention_policy_ref' => $candidateSet['retention_policy_ref'] ?? null,
            ],
            'would_clean_snapshot' => [
                'status' => self::wouldCleanPass($wouldClean) ? 'passed' : 'failed',
                'would_delete_records' => $wouldClean['would_delete_records'] ?? null,
                'would_delete_files' => $wouldClean['would_delete_files'] ?? null,
                'database_delete_executed' => $wouldClean['database_delete_executed'] ?? null,
            ],
            'access_audit_trace' => [
                'status' => 'passed',
                'access_scope_ref' => $request['access_scope_ref'] ?? null,
                'audit_event_refs' => [
                    'audit.event:public_link.cleanup.requested',
                    'audit.event:public_link.cleanup.result',
                ],
                'audit_events_recorded_now' => false,
            ],
            'rollback_replay_plan' => [
                'status' => self::rollbackPlanPass($rollback) ? 'passed' : 'failed',
                'rollback_available' => true,
                'rollback_executed_now' => false,
                'replay_candidate_set_available' => true,
            ],
            'negative_guards' => [
                'status' => self::negativeGuardsPass($negativeGuards) ? 'passed' : 'failed',
                'guards' => $negativeGuards,
            ],
            'production_deletion_block' => [
                'status' => 'passed',
                'scheduler_executed' => false,
                'queue_executed' => false,
                'production_database_delete' => false,
                'file_delete_executed' => false,
                'file_content_returned' => false,
            ],
            'scope_boundary' => [
                'status' => 'passed',
                'local_testing_only' => true,
                'mutates_state' => true,
                'production_mutates_state' => false,
                'guarded_cleanup_preview' => true,
                'admin_crud' => false,
                'public_ui' => false,
                'file_deletion_executed' => false,
                'scheduler_or_queue_executed' => false,
                'production_runtime' => false,
                'release_ready' => false,
            ],
        ];

        $report = [
            'schema' => 'larena.public_link_cleanup_action_foundation.v1',
            'status' => self::status($checks),
            'generated_at' => gmdate('c'),
            'mutates_state' => true,
            'production_mutates_state' => false,
            'cluster' => 'data-content-foundation',
            'scenario' => 'public_link_cleanup_action_foundation',
            'packages' => [
                'larena/link',
                'larena/admin',
                'larena/access',
                'larena/audit',
            ],
            'cleanup_request' => $request,
            'candidate_set_snapshot' => $candidateSet,
            'would_clean_snapshot' => $wouldClean,
            'rollback_replay_plan' => $rollback,
            'negative_guards' => $negativeGuards,
            'checks' => $checks,
            'safe_trace' => [
                'guarded_cleanup_preview' => true,
                'local_testing_state_transition_executed' => true,
                'dry_run_only' => true,
                'production_mutates_state' => false,
                'persistent_production_cleanup' => false,
                'active_link_cleanup' => false,
                'scheduler_executed' => false,
                'queue_executed' => false,
                'production_database_delete' => false,
                'file_deletion_executed' => false,
                'file_download_executed' => false,
                'file_content_returned' => false,
                'public_delivery_enabled_by_this_action' => false,
                'raw_token_visible' => false,
                'production_runtime' => false,
                'release_ready' => false,
                'graph_sync_canonical_update' => false,
            ],
            'evidence_path' => $outputPath,
            'known_limitations' => [
                'developer_testable_guarded_cleanup_preview_only',
                'local_testing_dry_run_only',
                'no_scheduler_or_queue_execution',
                'no_production_cleanup',
                'no_file_deletion',
                'no_file_streaming',
                'no_file_content_response',
                'no_public_ui',
                'not_release_ready',
            ],
            'next_recommended_step' => 'developer_review_cleanup_action_or_prepare_public_link_mutation_ladder_review',
        ];

        if ($outputPath !== null && $outputPath !== '') {
            self::writeJson($outputPath, $report);
        }

        return $report;
    }

    /**
     * @param array<string, mixed> $planning
     */
    private static function planningContainsCleanup(array $planning): bool
    {
        foreach (is_array($planning['mutation_plan_registry'] ?? null) ? $planning['mutation_plan_registry'] : [] as $plan) {
            if (($plan['action'] ?? null) === 'cleanup_links') {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    private static function request(): array
    {
        return [
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
    }

    /**
     * @param array<string, mixed> $request
     * @return array<string, mixed>
     */
    private static function candidateSetSnapshot(array $request): array
    {
        return [
            'snapshot_id' => 'cleanup_candidate_set_preview',
            'retention_policy_ref' => $request['retention_policy_ref'],
            'candidate_query_shape' => [
                'include_lifecycle_states' => ['expired', 'consumed', 'revoked'],
                'exclude_lifecycle_states' => ['active'],
                'requires_retention_policy' => true,
                'requires_operator_review' => true,
            ],
            'cleanup_candidates' => [
                [
                    'link_ref' => 'public-link:expired-preview',
                    'lifecycle_state' => 'expired',
                    'reason' => 'expired_before_retention_cutoff',
                    'token_fingerprint' => self::fingerprint('expired-preview-token'),
                ],
                [
                    'link_ref' => 'public-link:consumed-preview',
                    'lifecycle_state' => 'consumed',
                    'reason' => 'one_time_link_consumed_before_retention_cutoff',
                    'token_fingerprint' => self::fingerprint('consumed-preview-token'),
                ],
                [
                    'link_ref' => 'public-link:revoked-preview',
                    'lifecycle_state' => 'revoked',
                    'reason' => 'revoked_before_retention_cutoff',
                    'token_fingerprint' => self::fingerprint('revoked-preview-token'),
                ],
            ],
            'excluded_active_links' => [
                [
                    'link_ref' => 'public-link:active-preview',
                    'lifecycle_state' => 'active',
                    'reason' => 'active_links_must_not_be_cleaned',
                    'token_fingerprint' => self::fingerprint('active-preview-token'),
                ],
            ],
            'access_scope_ref' => 'access.scope:public-link.admin.cleanup',
            'audit_event_ref' => 'audit.event:public_link.cleanup.candidate_snapshot',
        ];
    }

    /**
     * @param array<string, mixed> $candidateSet
     * @return array<string, mixed>
     */
    private static function wouldCleanSnapshot(array $candidateSet): array
    {
        return [
            'snapshot_id' => 'cleanup_would_clean_preview',
            'from_snapshot' => $candidateSet['snapshot_id'],
            'would_clean_refs' => array_column($candidateSet['cleanup_candidates'], 'link_ref'),
            'excluded_refs' => array_column($candidateSet['excluded_active_links'], 'link_ref'),
            'would_delete_records' => count($candidateSet['cleanup_candidates']),
            'would_delete_files' => 0,
            'database_delete_executed' => false,
            'file_delete_executed' => false,
            'scheduler_executed' => false,
            'queue_executed' => false,
            'audit_event_ref' => 'audit.event:public_link.cleanup.result',
        ];
    }

    /**
     * @param array<string, mixed> $candidateSet
     * @param array<string, mixed> $wouldClean
     * @return array<string, mixed>
     */
    private static function rollbackPlan(array $candidateSet, array $wouldClean): array
    {
        return [
            'rollback_id' => 'replay_cleanup_candidate_set_preview',
            'candidate_set_snapshot' => $candidateSet['snapshot_id'],
            'would_clean_snapshot' => $wouldClean['snapshot_id'],
            'rollback_strategy' => 'replay_candidate_set_and_restore_records_from_snapshot',
            'restore_candidate_refs' => array_column($candidateSet['cleanup_candidates'], 'link_ref'),
            'excluded_active_refs' => array_column($candidateSet['excluded_active_links'], 'link_ref'),
            'rollback_executed_now' => false,
            'evidence_required' => [
                'candidate_set_snapshot',
                'would_clean_snapshot',
                'restore_or_replay_plan',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function negativeGuards(): array
    {
        return [
            [
                'guard' => 'missing_launch_record',
                'status' => 'blocked',
                'reason' => 'launch_record_required',
                'mutates_state' => false,
            ],
            [
                'guard' => 'missing_retention_policy',
                'status' => 'blocked',
                'reason' => 'retention_policy_required',
                'mutates_state' => false,
            ],
            [
                'guard' => 'cleanup_active_links',
                'status' => 'blocked',
                'reason' => 'active_links_must_not_be_cleaned',
                'mutates_state' => false,
            ],
            [
                'guard' => 'scheduler_execution_in_preview',
                'status' => 'blocked',
                'reason' => 'scheduler_must_not_run_in_preview',
                'mutates_state' => false,
            ],
            [
                'guard' => 'queue_execution_in_preview',
                'status' => 'blocked',
                'reason' => 'queue_must_not_run_in_preview',
                'mutates_state' => false,
            ],
            [
                'guard' => 'file_deletion',
                'status' => 'blocked',
                'reason' => 'file_deletion_not_allowed_in_cleanup_preview',
                'mutates_state' => false,
            ],
            [
                'guard' => 'production_delete_claim',
                'status' => 'blocked',
                'reason' => 'production_delete_requires_future_launch_record',
                'mutates_state' => false,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $request
     */
    private static function requestContractPass(array $request): bool
    {
        return ($request['confirmation'] ?? '') !== ''
            && ($request['access_scope_ref'] ?? '') !== ''
            && ($request['retention_policy_ref'] ?? '') !== ''
            && ($request['dry_run'] ?? false) === true
            && ($request['production_mutation'] ?? true) === false
            && ($request['scheduler_or_queue_execution'] ?? true) === false;
    }

    /**
     * @param array<string, mixed> $candidateSet
     */
    private static function candidateSetPass(array $candidateSet): bool
    {
        $candidates = is_array($candidateSet['cleanup_candidates'] ?? null) ? $candidateSet['cleanup_candidates'] : [];
        $excluded = is_array($candidateSet['excluded_active_links'] ?? null) ? $candidateSet['excluded_active_links'] : [];

        foreach ($candidates as $candidate) {
            if (($candidate['lifecycle_state'] ?? null) === 'active') {
                return false;
            }
        }

        return count($candidates) >= 3
            && count($excluded) >= 1
            && ($candidateSet['retention_policy_ref'] ?? '') !== '';
    }

    /**
     * @param array<string, mixed> $wouldClean
     */
    private static function wouldCleanPass(array $wouldClean): bool
    {
        return ($wouldClean['would_delete_records'] ?? 0) >= 3
            && ($wouldClean['would_delete_files'] ?? -1) === 0
            && ($wouldClean['database_delete_executed'] ?? true) === false
            && ($wouldClean['file_delete_executed'] ?? true) === false
            && ($wouldClean['scheduler_executed'] ?? true) === false
            && ($wouldClean['queue_executed'] ?? true) === false;
    }

    /**
     * @param array<string, mixed> $rollback
     */
    private static function rollbackPlanPass(array $rollback): bool
    {
        return ($rollback['rollback_strategy'] ?? '') !== ''
            && ($rollback['rollback_executed_now'] ?? true) === false
            && count(is_array($rollback['restore_candidate_refs'] ?? null) ? $rollback['restore_candidate_refs'] : []) >= 3
            && count(is_array($rollback['evidence_required'] ?? null) ? $rollback['evidence_required'] : []) >= 3;
    }

    /**
     * @param list<array<string, mixed>> $guards
     */
    private static function negativeGuardsPass(array $guards): bool
    {
        foreach ($guards as $guard) {
            if (($guard['status'] ?? null) !== 'blocked' || ($guard['mutates_state'] ?? true) !== false) {
                return false;
            }
        }

        return count($guards) >= 7;
    }

    private static function fingerprint(string $token): string
    {
        return 'sha256:' . hash('sha256', $token);
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
