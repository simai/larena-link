<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

final class PublicLinkGuardedAdminMutationPlanningPreview
{
    /**
     * @return array<string, mixed>
     */
    public static function preview(?string $outputPath = null): array
    {
        return self::run(
            PublicLinkOperatorLifecycleManagementPreview::preview(),
            self::mutationPlans(),
            $outputPath,
        );
    }

    /**
     * @param array<string, mixed> $operator
     * @param list<array<string, mixed>> $plans
     * @return array<string, mixed>
     */
    public static function run(array $operator, array $plans, ?string $outputPath = null): array
    {
        $checks = [
            'launch_record_scope' => [
                'status' => 'passed',
                'launch_record_ref' => 'docs/project-management/launch-records/public-link-guarded-admin-mutation-planning-foundation.json',
                'ready_to_code' => true,
                'local_testing_only' => true,
                'read_only_planning_preview' => true,
                'mutation_actions_allowed' => false,
                'production_delivery_allowed' => false,
            ],
            'operator_lifecycle_dependency' => [
                'status' => ($operator['status'] ?? null) === 'passed' ? 'passed' : 'failed',
                'dependency_schema' => $operator['schema'] ?? null,
                'operator_registry_count' => count(is_array($operator['operator_registry'] ?? null) ? $operator['operator_registry'] : []),
            ],
            'mutation_plan_registry' => [
                'status' => self::planRegistryIsComplete($plans) ? 'passed' : 'failed',
                'planned_actions' => array_column($plans, 'action'),
                'record_count' => count($plans),
            ],
            'launch_record_requirements' => [
                'status' => self::allPlansRequireFutureLaunchRecords($plans) ? 'passed' : 'failed',
                'all_actions_blocked_until_future_launch_record' => true,
            ],
            'rollback_evidence_requirements' => [
                'status' => self::rollbackEvidenceIsComplete($plans) ? 'passed' : 'failed',
                'requires_rollback_plan' => true,
                'requires_before_after_evidence' => true,
            ],
            'access_audit_requirements' => [
                'status' => self::accessAuditRequirementsPass($plans) ? 'passed' : 'failed',
                'access_scope_required' => true,
                'audit_event_required' => true,
            ],
            'negative_test_requirements' => [
                'status' => self::negativeTestsAreComplete($plans) ? 'passed' : 'failed',
                'negative_tests_required_before_mutation' => true,
            ],
            'mutation_execution_block' => [
                'status' => self::allMutationExecutionBlocked($plans) ? 'passed' : 'failed',
                'revocation_executed' => false,
                'regeneration_executed' => false,
                'cleanup_executed' => false,
                'database_write_executed' => false,
                'queue_or_scheduler_executed' => false,
            ],
            'scope_boundary' => [
                'status' => 'passed',
                'local_testing_only' => true,
                'mutates_state' => false,
                'production_mutates_state' => false,
                'read_only_planning_preview' => true,
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
            'schema' => 'larena.public_link_guarded_admin_mutation_planning_foundation.v1',
            'status' => self::status($checks),
            'generated_at' => gmdate('c'),
            'mutates_state' => false,
            'production_mutates_state' => false,
            'cluster' => 'data-content-foundation',
            'scenario' => 'public_link_guarded_admin_mutation_planning_foundation',
            'packages' => [
                'larena/link',
                'larena/admin',
                'larena/filesystem',
                'larena/access',
                'larena/audit',
            ],
            'mutation_plan_registry' => $plans,
            'checks' => $checks,
            'safe_trace' => [
                'read_only_planning_preview' => true,
                'guarded_admin_mutation_planning_available' => true,
                'mutation_actions_allowed' => false,
                'revocation_executed' => false,
                'regeneration_executed' => false,
                'cleanup_executed' => false,
                'database_write_executed' => false,
                'queue_or_scheduler_executed' => false,
                'file_download_executed' => false,
                'file_content_returned' => false,
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'production_delivery' => false,
                'production_runtime' => false,
                'release_ready' => false,
                'graph_sync_canonical_update' => false,
            ],
            'future_launch_records' => [
                'public-link-revoke-action-foundation',
                'public-link-regenerate-action-foundation',
                'public-link-cleanup-action-foundation',
            ],
            'evidence_path' => $outputPath,
            'known_limitations' => [
                'developer_testable_guarded_mutation_planning_only',
                'read_only_planning_registry_only',
                'no_admin_crud',
                'no_mutation_actions',
                'no_file_streaming',
                'no_file_content_response',
                'no_persistent_revocation',
                'no_persistent_regeneration',
                'no_persistent_cleanup',
                'no_production_delivery_runtime',
                'not_release_ready',
            ],
            'next_recommended_step' => 'prepare_first_guarded_admin_mutation_launch_record_or_request_developer_review',
        ];

        if ($outputPath !== null && $outputPath !== '') {
            self::writeJson($outputPath, $report);
        }

        return $report;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function mutationPlans(): array
    {
        return [
            [
                'action' => 'revoke_link',
                'owner_package' => 'larena/link',
                'state' => 'blocked_future_launch_required',
                'human_label' => 'Revoke public link',
                'purpose' => 'Disable future public delivery for a selected public link after operator review.',
                'required_launch_record' => 'public-link-revoke-action-foundation',
                'allowed_first_batch' => [
                    'request shape',
                    'access check',
                    'audit intent/result events',
                    'rollback descriptor',
                    'local/testing state transition proof',
                ],
                'forbidden_first_batch' => [
                    'public file delivery',
                    'raw token exposure',
                    'bulk revocation',
                    'production route mutation',
                ],
                'access_scope_ref' => 'access.scope:public-link.admin.revoke',
                'audit_event_refs' => [
                    'audit.event:public_link.revoke.requested',
                    'audit.event:public_link.revoke.result',
                ],
                'rollback_evidence' => [
                    'before_state_snapshot',
                    'after_state_snapshot',
                    'restore_previous_revocation_state_plan',
                ],
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
                'human_label' => 'Regenerate public link',
                'purpose' => 'Replace public link credentials while preserving safe audit and rollback context.',
                'required_launch_record' => 'public-link-regenerate-action-foundation',
                'allowed_first_batch' => [
                    'request shape',
                    'old/new fingerprint planning',
                    'access check',
                    'audit intent/result events',
                    'rollback descriptor',
                ],
                'forbidden_first_batch' => [
                    'raw token storage',
                    'raw token logging',
                    'production delivery enablement',
                    'unbounded regeneration loop',
                ],
                'access_scope_ref' => 'access.scope:public-link.admin.regenerate',
                'audit_event_refs' => [
                    'audit.event:public_link.regenerate.requested',
                    'audit.event:public_link.regenerate.result',
                ],
                'rollback_evidence' => [
                    'old_fingerprint_snapshot',
                    'new_fingerprint_snapshot',
                    'restore_previous_token_hash_plan',
                ],
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
                'human_label' => 'Cleanup expired or consumed public links',
                'purpose' => 'Plan safe cleanup for expired, consumed or revoked public link records without enabling scheduler/runtime deletion.',
                'required_launch_record' => 'public-link-cleanup-action-foundation',
                'allowed_first_batch' => [
                    'candidate query shape',
                    'dry-run count report',
                    'retention policy reference',
                    'audit summary event plan',
                    'rollback descriptor',
                ],
                'forbidden_first_batch' => [
                    'scheduler execution',
                    'queue execution',
                    'production deletion',
                    'file deletion',
                    'bulk mutation without dry-run evidence',
                ],
                'access_scope_ref' => 'access.scope:public-link.admin.cleanup',
                'audit_event_refs' => [
                    'audit.event:public_link.cleanup.requested',
                    'audit.event:public_link.cleanup.result',
                ],
                'rollback_evidence' => [
                    'candidate_set_snapshot',
                    'deleted_set_snapshot',
                    'restore_or_replay_plan',
                ],
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
    }

    /**
     * @param list<array<string, mixed>> $plans
     */
    private static function planRegistryIsComplete(array $plans): bool
    {
        $actions = array_column($plans, 'action');

        foreach (['revoke_link', 'regenerate_link', 'cleanup_links'] as $action) {
            if (!in_array($action, $actions, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<array<string, mixed>> $plans
     */
    private static function allPlansRequireFutureLaunchRecords(array $plans): bool
    {
        foreach ($plans as $plan) {
            if (($plan['requires_future_launch_record'] ?? false) !== true) {
                return false;
            }

            if (($plan['state'] ?? null) !== 'blocked_future_launch_required') {
                return false;
            }

            if (($plan['required_launch_record'] ?? '') === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<array<string, mixed>> $plans
     */
    private static function rollbackEvidenceIsComplete(array $plans): bool
    {
        foreach ($plans as $plan) {
            if (count(is_array($plan['rollback_evidence'] ?? null) ? $plan['rollback_evidence'] : []) < 3) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<array<string, mixed>> $plans
     */
    private static function accessAuditRequirementsPass(array $plans): bool
    {
        foreach ($plans as $plan) {
            if (($plan['access_scope_ref'] ?? '') === '') {
                return false;
            }

            if (count(is_array($plan['audit_event_refs'] ?? null) ? $plan['audit_event_refs'] : []) < 2) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<array<string, mixed>> $plans
     */
    private static function negativeTestsAreComplete(array $plans): bool
    {
        foreach ($plans as $plan) {
            if (count(is_array($plan['required_negative_tests'] ?? null) ? $plan['required_negative_tests'] : []) < 4) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<array<string, mixed>> $plans
     */
    private static function allMutationExecutionBlocked(array $plans): bool
    {
        foreach ($plans as $plan) {
            if (($plan['mutates_state_now'] ?? true) !== false) {
                return false;
            }
        }

        return true;
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
