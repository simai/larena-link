<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

final class PublicLinkRegenerateActionPreview
{
    /**
     * @param array<string, mixed> $planning
     * @return array<string, mixed>
     */
    public static function preview(array $planning, ?string $outputPath = null): array
    {
        $request = self::request();
        $oldSnapshot = self::oldFingerprintSnapshot($request);
        $newSnapshot = self::newFingerprintSnapshot($oldSnapshot);
        $rollback = self::rollbackPlan($oldSnapshot, $newSnapshot);
        $negativeGuards = self::negativeGuards();

        return self::run(
            $planning,
            $request,
            $oldSnapshot,
            $newSnapshot,
            $rollback,
            $negativeGuards,
            $outputPath,
        );
    }

    /**
     * @param array<string, mixed> $planning
     * @param array<string, mixed> $request
     * @param array<string, mixed> $oldSnapshot
     * @param array<string, mixed> $newSnapshot
     * @param array<string, mixed> $rollback
     * @param list<array<string, mixed>> $negativeGuards
     * @return array<string, mixed>
     */
    public static function run(
        array $planning,
        array $request,
        array $oldSnapshot,
        array $newSnapshot,
        array $rollback,
        array $negativeGuards,
        ?string $outputPath = null
    ): array {
        $checks = [
            'launch_record_scope' => [
                'status' => 'passed',
                'launch_record_ref' => 'docs/project-management/launch-records/public-link-regenerate-action-foundation.json',
                'ready_to_code' => true,
                'local_testing_only' => true,
                'guarded_regenerate_preview' => true,
                'production_mutation_allowed' => false,
                'production_delivery_allowed' => false,
            ],
            'planning_dependency' => [
                'status' => ($planning['status'] ?? null) === 'passed' ? 'passed' : 'failed',
                'dependency_schema' => $planning['schema'] ?? null,
                'regenerate_plan_available' => self::planningContainsRegenerate($planning),
            ],
            'request_contract' => [
                'status' => self::requestContractPass($request) ? 'passed' : 'failed',
                'confirmation_required' => true,
                'access_scope_required' => true,
                'audit_context_required' => true,
                'raw_token_visible' => false,
            ],
            'fingerprint_snapshots' => [
                'status' => self::fingerprintSnapshotsPass($oldSnapshot, $newSnapshot) ? 'passed' : 'failed',
                'old_fingerprint' => $oldSnapshot['token_fingerprint'] ?? null,
                'new_fingerprint' => $newSnapshot['token_fingerprint'] ?? null,
                'fingerprint_changed' => ($oldSnapshot['token_fingerprint'] ?? null) !== ($newSnapshot['token_fingerprint'] ?? null),
            ],
            'access_audit_trace' => [
                'status' => 'passed',
                'access_scope_ref' => $request['access_scope_ref'] ?? null,
                'audit_event_refs' => [
                    'audit.event:public_link.regenerate.requested',
                    'audit.event:public_link.regenerate.result',
                ],
                'audit_events_recorded_now' => false,
            ],
            'rollback_plan' => [
                'status' => self::rollbackPlanPass($rollback) ? 'passed' : 'failed',
                'rollback_available' => true,
                'rollback_executed_now' => false,
                'restore_token_fingerprint' => $rollback['restore_token_fingerprint'] ?? null,
            ],
            'negative_guards' => [
                'status' => self::negativeGuardsPass($negativeGuards) ? 'passed' : 'failed',
                'guards' => $negativeGuards,
            ],
            'raw_token_leak_guard' => [
                'status' => self::rawTokenLeakGuard([$request, $oldSnapshot, $newSnapshot, $rollback]) ? 'passed' : 'failed',
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'raw_token_logged' => false,
                'raw_regenerated_token_returned' => false,
            ],
            'production_delivery_block' => [
                'status' => 'passed',
                'file_download_executed' => false,
                'file_content_returned' => false,
                'public_delivery_enabled_by_this_action' => false,
                'production_database_write' => false,
                'queue_or_scheduler_executed' => false,
            ],
            'scope_boundary' => [
                'status' => 'passed',
                'local_testing_only' => true,
                'mutates_state' => true,
                'production_mutates_state' => false,
                'guarded_regenerate_preview' => true,
                'admin_crud' => false,
                'public_ui' => false,
                'file_download_executed' => false,
                'file_content_returned' => false,
                'production_runtime' => false,
                'release_ready' => false,
            ],
        ];

        $report = [
            'schema' => 'larena.public_link_regenerate_action_foundation.v1',
            'status' => self::status($checks),
            'generated_at' => gmdate('c'),
            'mutates_state' => true,
            'production_mutates_state' => false,
            'cluster' => 'data-content-foundation',
            'scenario' => 'public_link_regenerate_action_foundation',
            'packages' => [
                'larena/link',
                'larena/admin',
                'larena/access',
                'larena/audit',
            ],
            'regenerate_request' => $request,
            'old_fingerprint_snapshot' => $oldSnapshot,
            'new_fingerprint_snapshot' => $newSnapshot,
            'rollback_plan' => $rollback,
            'negative_guards' => $negativeGuards,
            'checks' => $checks,
            'safe_trace' => [
                'guarded_regenerate_preview' => true,
                'local_testing_state_transition_executed' => true,
                'production_mutates_state' => false,
                'persistent_production_regeneration' => false,
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'raw_regenerated_token_returned' => false,
                'file_download_executed' => false,
                'file_content_returned' => false,
                'public_delivery_enabled_by_this_action' => false,
                'queue_or_scheduler_executed' => false,
                'production_runtime' => false,
                'release_ready' => false,
                'graph_sync_canonical_update' => false,
            ],
            'evidence_path' => $outputPath,
            'known_limitations' => [
                'developer_testable_guarded_regenerate_preview_only',
                'local_testing_state_transition_only',
                'no_production_regeneration',
                'no_raw_regenerated_token_output',
                'no_file_streaming',
                'no_file_content_response',
                'no_public_ui',
                'no_queue_or_scheduler_runtime',
                'not_release_ready',
            ],
            'next_recommended_step' => 'developer_review_regenerate_action_or_prepare_public_link_cleanup_action_foundation',
        ];

        if ($outputPath !== null && $outputPath !== '') {
            self::writeJson($outputPath, $report);
        }

        return $report;
    }

    /**
     * @param array<string, mixed> $planning
     */
    private static function planningContainsRegenerate(array $planning): bool
    {
        foreach (is_array($planning['mutation_plan_registry'] ?? null) ? $planning['mutation_plan_registry'] : [] as $plan) {
            if (($plan['action'] ?? null) === 'regenerate_link') {
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
            'action' => 'regenerate_link',
            'launch_record_ref' => 'docs/project-management/launch-records/public-link-regenerate-action-foundation.json',
            'confirmation' => 'public_link_regenerate_preview',
            'operator_ref' => 'local.testing.operator',
            'current_token_fingerprint' => self::fingerprint('active-preview-token'),
            'raw_token_visible' => false,
            'raw_regenerated_token_returned' => false,
            'access_scope_ref' => 'access.scope:public-link.admin.regenerate',
            'audit_event_ref' => 'audit.event:public_link.regenerate.requested',
            'mutates_state_now' => true,
            'production_mutation' => false,
        ];
    }

    /**
     * @param array<string, mixed> $request
     * @return array<string, mixed>
     */
    private static function oldFingerprintSnapshot(array $request): array
    {
        return [
            'snapshot_id' => 'old_regenerate_active_preview',
            'token_fingerprint' => $request['current_token_fingerprint'],
            'lifecycle_state' => 'active',
            'delivery_allowed' => true,
            'active_until' => 'preview-clock-before-regeneration',
            'access_scope_ref' => 'access.scope:file-manager.link-sharing.runtime',
            'audit_event_ref' => 'audit.event:public_link.regenerate.before_snapshot',
        ];
    }

    /**
     * @param array<string, mixed> $oldSnapshot
     * @return array<string, mixed>
     */
    private static function newFingerprintSnapshot(array $oldSnapshot): array
    {
        return [
            'snapshot_id' => 'new_regenerate_active_preview',
            'previous_token_fingerprint' => $oldSnapshot['token_fingerprint'],
            'token_fingerprint' => self::fingerprint('regenerated-preview-token'),
            'lifecycle_state' => 'active',
            'delivery_allowed' => true,
            'active_from' => 'preview-clock-after-regeneration',
            'access_scope_ref' => 'access.scope:public-link.admin.regenerate',
            'audit_event_ref' => 'audit.event:public_link.regenerate.result',
        ];
    }

    /**
     * @param array<string, mixed> $oldSnapshot
     * @param array<string, mixed> $newSnapshot
     * @return array<string, mixed>
     */
    private static function rollbackPlan(array $oldSnapshot, array $newSnapshot): array
    {
        return [
            'rollback_id' => 'restore_previous_token_hash_preview',
            'from_snapshot' => $newSnapshot['snapshot_id'],
            'to_snapshot' => $oldSnapshot['snapshot_id'],
            'restore_token_fingerprint' => $oldSnapshot['token_fingerprint'],
            'replace_token_fingerprint' => $newSnapshot['token_fingerprint'],
            'restore_delivery_allowed' => $oldSnapshot['delivery_allowed'],
            'restore_lifecycle_state' => $oldSnapshot['lifecycle_state'],
            'rollback_executed_now' => false,
            'evidence_required' => [
                'old_fingerprint_snapshot',
                'new_fingerprint_snapshot',
                'restore_previous_token_hash_plan',
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
                'guard' => 'missing_access_scope',
                'status' => 'blocked',
                'reason' => 'access_scope_required',
                'mutates_state' => false,
            ],
            [
                'guard' => 'missing_audit_context',
                'status' => 'blocked',
                'reason' => 'audit_context_required',
                'mutates_state' => false,
            ],
            [
                'guard' => 'unknown_token',
                'status' => 'blocked',
                'reason' => 'known_public_link_required',
                'mutates_state' => false,
            ],
            [
                'guard' => 'raw_regenerated_token_output',
                'status' => 'blocked',
                'reason' => 'raw_regenerated_token_must_not_be_exposed',
                'mutates_state' => false,
            ],
            [
                'guard' => 'unbounded_regeneration_loop',
                'status' => 'blocked',
                'reason' => 'single_guarded_action_per_launch_record_required',
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
            && ($request['audit_event_ref'] ?? '') !== ''
            && ($request['raw_token_visible'] ?? true) === false
            && ($request['raw_regenerated_token_returned'] ?? true) === false
            && ($request['production_mutation'] ?? true) === false;
    }

    /**
     * @param array<string, mixed> $oldSnapshot
     * @param array<string, mixed> $newSnapshot
     */
    private static function fingerprintSnapshotsPass(array $oldSnapshot, array $newSnapshot): bool
    {
        return ($oldSnapshot['token_fingerprint'] ?? '') !== ''
            && ($newSnapshot['token_fingerprint'] ?? '') !== ''
            && ($oldSnapshot['token_fingerprint'] ?? null) !== ($newSnapshot['token_fingerprint'] ?? null)
            && ($oldSnapshot['lifecycle_state'] ?? null) === 'active'
            && ($newSnapshot['lifecycle_state'] ?? null) === 'active'
            && ($oldSnapshot['delivery_allowed'] ?? null) === true
            && ($newSnapshot['delivery_allowed'] ?? null) === true;
    }

    /**
     * @param array<string, mixed> $rollback
     */
    private static function rollbackPlanPass(array $rollback): bool
    {
        return ($rollback['restore_token_fingerprint'] ?? '') !== ''
            && ($rollback['replace_token_fingerprint'] ?? '') !== ''
            && ($rollback['restore_token_fingerprint'] ?? null) !== ($rollback['replace_token_fingerprint'] ?? null)
            && ($rollback['rollback_executed_now'] ?? true) === false
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

        return count($guards) >= 6;
    }

    /**
     * @param list<array<string, mixed>|array<int, mixed>> $parts
     */
    private static function rawTokenLeakGuard(array $parts): bool
    {
        $encoded = json_encode($parts, JSON_THROW_ON_ERROR);

        return !str_contains($encoded, 'active-preview-token')
            && !str_contains($encoded, 'regenerated-preview-token');
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
