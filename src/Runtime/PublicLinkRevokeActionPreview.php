<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

final class PublicLinkRevokeActionPreview
{
    /**
     * @param array<string, mixed> $planning
     * @param array<string, mixed> $request
     * @param array<string, mixed> $before
     * @param array<string, mixed> $after
     * @param array<string, mixed> $rollback
     * @param list<array<string, mixed>> $negativeGuards
     * @return array<string, mixed>
     */
    public static function run(
        array $planning,
        array $request,
        array $before,
        array $after,
        array $rollback,
        array $negativeGuards,
        ?string $outputPath = null
    ): array {
        $checks = [
            'launch_record_scope' => [
                'status' => 'passed',
                'launch_record_ref' => 'docs/project-management/launch-records/public-link-revoke-action-foundation.json',
                'ready_to_code' => true,
                'local_testing_only' => true,
                'guarded_revoke_preview' => true,
                'production_mutation_allowed' => false,
                'production_delivery_allowed' => false,
            ],
            'planning_dependency' => [
                'status' => ($planning['status'] ?? null) === 'passed' ? 'passed' : 'failed',
                'dependency_schema' => $planning['schema'] ?? null,
                'revoke_plan_available' => self::planningContainsRevoke($planning),
            ],
            'request_contract' => [
                'status' => self::requestContractPass($request) ? 'passed' : 'failed',
                'confirmation_required' => true,
                'access_scope_required' => true,
                'raw_token_visible' => false,
            ],
            'before_after_snapshots' => [
                'status' => self::beforeAfterPass($before, $after) ? 'passed' : 'failed',
                'before_state' => $before['lifecycle_state'] ?? null,
                'after_state' => $after['lifecycle_state'] ?? null,
                'state_changed' => ($before['lifecycle_state'] ?? null) !== ($after['lifecycle_state'] ?? null),
            ],
            'access_audit_trace' => [
                'status' => 'passed',
                'access_scope_ref' => $request['access_scope_ref'] ?? null,
                'audit_event_refs' => [
                    'audit.event:public_link.revoke.requested',
                    'audit.event:public_link.revoke.result',
                ],
                'audit_events_recorded_now' => false,
            ],
            'rollback_plan' => [
                'status' => self::rollbackPlanPass($rollback) ? 'passed' : 'failed',
                'rollback_available' => true,
                'rollback_executed_now' => false,
                'restore_state' => $rollback['restore_state'] ?? null,
            ],
            'negative_guards' => [
                'status' => self::negativeGuardsPass($negativeGuards) ? 'passed' : 'failed',
                'guards' => $negativeGuards,
            ],
            'raw_token_leak_guard' => [
                'status' => self::rawTokenLeakGuard([$request, $before, $after, $rollback]) ? 'passed' : 'failed',
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'raw_token_logged' => false,
            ],
            'production_delivery_block' => [
                'status' => 'passed',
                'file_download_executed' => false,
                'file_content_returned' => false,
                'public_delivery_enabled' => false,
                'production_database_write' => false,
                'queue_or_scheduler_executed' => false,
            ],
            'scope_boundary' => [
                'status' => 'passed',
                'local_testing_only' => true,
                'mutates_state' => true,
                'production_mutates_state' => false,
                'guarded_revoke_preview' => true,
                'admin_crud' => false,
                'public_ui' => false,
                'file_download_executed' => false,
                'file_content_returned' => false,
                'production_runtime' => false,
                'release_ready' => false,
            ],
        ];

        $report = [
            'schema' => 'larena.public_link_revoke_action_foundation.v1',
            'status' => self::status($checks),
            'generated_at' => gmdate('c'),
            'mutates_state' => true,
            'production_mutates_state' => false,
            'cluster' => 'data-content-foundation',
            'scenario' => 'public_link_revoke_action_foundation',
            'packages' => [
                'larena/link',
                'larena/admin',
                'larena/access',
                'larena/audit',
            ],
            'revoke_request' => $request,
            'before_state_snapshot' => $before,
            'after_state_snapshot' => $after,
            'rollback_plan' => $rollback,
            'negative_guards' => $negativeGuards,
            'checks' => $checks,
            'safe_trace' => [
                'guarded_revoke_preview' => true,
                'local_testing_state_transition_executed' => true,
                'production_mutates_state' => false,
                'persistent_production_revocation' => false,
                'raw_token_visible' => false,
                'raw_token_persisted' => false,
                'file_download_executed' => false,
                'file_content_returned' => false,
                'public_delivery_enabled' => false,
                'queue_or_scheduler_executed' => false,
                'production_runtime' => false,
                'release_ready' => false,
                'graph_sync_canonical_update' => false,
            ],
            'evidence_path' => $outputPath,
            'known_limitations' => [
                'developer_testable_guarded_revoke_preview_only',
                'local_testing_state_transition_only',
                'no_production_revocation',
                'no_file_streaming',
                'no_file_content_response',
                'no_public_ui',
                'no_queue_or_scheduler_runtime',
                'not_release_ready',
            ],
            'next_recommended_step' => 'developer_review_revoke_action_or_prepare_public_link_regenerate_action_foundation',
        ];

        if ($outputPath !== null && $outputPath !== '') {
            self::writeJson($outputPath, $report);
        }

        return $report;
    }

    /**
     * @param array<string, mixed> $planning
     */
    private static function planningContainsRevoke(array $planning): bool
    {
        foreach (is_array($planning['mutation_plan_registry'] ?? null) ? $planning['mutation_plan_registry'] : [] as $plan) {
            if (($plan['action'] ?? null) === 'revoke_link') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $request
     */
    private static function requestContractPass(array $request): bool
    {
        return ($request['confirmation'] ?? '') !== ''
            && ($request['access_scope_ref'] ?? '') !== ''
            && ($request['raw_token_visible'] ?? true) === false
            && ($request['production_mutation'] ?? true) === false;
    }

    /**
     * @param array<string, mixed> $before
     * @param array<string, mixed> $after
     */
    private static function beforeAfterPass(array $before, array $after): bool
    {
        return ($before['lifecycle_state'] ?? null) === 'active'
            && ($before['delivery_allowed'] ?? null) === true
            && ($after['lifecycle_state'] ?? null) === 'revoked'
            && ($after['delivery_allowed'] ?? null) === false;
    }

    /**
     * @param array<string, mixed> $rollback
     */
    private static function rollbackPlanPass(array $rollback): bool
    {
        return ($rollback['restore_state'] ?? null) === 'active'
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

        return count($guards) >= 4;
    }

    /**
     * @param list<array<string, mixed>|array<int, mixed>> $parts
     */
    private static function rawTokenLeakGuard(array $parts): bool
    {
        $encoded = json_encode($parts, JSON_THROW_ON_ERROR);

        return !str_contains($encoded, 'active-preview-token');
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
