<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

final class PublicLinkMutationLadderReviewPreview
{
    /**
     * @param array<string, mixed> $planning
     * @param array<string, mixed> $revoke
     * @param array<string, mixed> $regenerate
     * @param array<string, mixed> $cleanup
     * @return array<string, mixed>
     */
    public static function run(
        array $planning,
        array $revoke,
        array $regenerate,
        array $cleanup,
        ?string $outputPath = null
    ): array {
        $matrix = self::actionMatrix($planning, $revoke, $regenerate, $cleanup);
        $statusSemantics = self::statusSemantics($matrix);
        $boundaries = self::scopeBoundaries($planning, $revoke, $regenerate, $cleanup);
        $checks = [
            'launch_record_scope' => [
                'status' => 'passed',
                'launch_record_ref' => 'docs/project-management/launch-records/public-link-mutation-ladder-review-foundation.json',
                'ready_to_code' => true,
                'local_testing_only' => true,
                'consolidated_review_only' => true,
                'new_mutation_behavior_allowed' => false,
                'production_runtime_allowed' => false,
            ],
            'source_slice_composition' => [
                'status' => self::allReportsPassed([$planning, $revoke, $regenerate, $cleanup]) ? 'passed' : 'failed',
                'source_schemas' => [
                    $planning['schema'] ?? null,
                    $revoke['schema'] ?? null,
                    $regenerate['schema'] ?? null,
                    $cleanup['schema'] ?? null,
                ],
            ],
            'operator_action_matrix' => [
                'status' => self::matrixPass($matrix) ? 'passed' : 'failed',
                'action_count' => count($matrix),
                'actions' => array_column($matrix, 'action'),
            ],
            'human_status_semantics' => [
                'status' => count($statusSemantics) >= 4 ? 'passed' : 'failed',
                'status_count' => count($statusSemantics),
                'codes' => array_column($statusSemantics, 'code'),
            ],
            'machine_detail_links' => [
                'status' => self::machineLinksPass($matrix) ? 'passed' : 'failed',
                'all_actions_have_review_and_machine_links' => true,
            ],
            'safe_boundary_aggregation' => [
                'status' => self::boundariesPass($boundaries) ? 'passed' : 'failed',
                'production_runtime' => $boundaries['production_runtime'],
                'public_delivery' => $boundaries['public_delivery'],
                'file_deletion' => $boundaries['file_deletion'],
                'queue_or_scheduler' => $boundaries['queue_or_scheduler'],
                'release_ready' => $boundaries['release_ready'],
            ],
        ];

        $report = [
            'schema' => 'larena.public_link_mutation_ladder_review_foundation.v1',
            'status' => self::status($checks),
            'generated_at' => gmdate('c'),
            'mutates_state' => false,
            'production_mutates_state' => false,
            'cluster' => 'data-content-foundation',
            'scenario' => 'public_link_mutation_ladder_review_foundation',
            'packages' => [
                'larena/link',
                'larena/admin',
                'larena/access',
                'larena/audit',
            ],
            'source_reports' => [
                'planning' => self::sourceSummary($planning),
                'revoke' => self::sourceSummary($revoke),
                'regenerate' => self::sourceSummary($regenerate),
                'cleanup' => self::sourceSummary($cleanup),
            ],
            'operator_action_matrix' => $matrix,
            'status_semantics' => $statusSemantics,
            'scope_boundaries' => $boundaries,
            'checks' => $checks,
            'safe_trace' => [
                'consolidated_review_only' => true,
                'local_testing_only' => true,
                'mutates_state' => false,
                'new_mutation_behavior_added' => false,
                'production_mutates_state' => false,
                'file_deletion_executed' => false,
                'file_download_executed' => false,
                'file_content_returned' => false,
                'queue_or_scheduler_executed' => false,
                'public_delivery_enabled_by_this_action' => false,
                'raw_token_visible' => false,
                'production_runtime' => false,
                'release_ready' => false,
                'graph_sync_canonical_update' => false,
            ],
            'evidence_path' => $outputPath,
            'known_limitations' => [
                'developer_testable_operator_review_only',
                'no_new_public_link_mutation_behavior',
                'no_production_revocation',
                'no_production_regeneration',
                'no_production_cleanup',
                'no_file_streaming',
                'no_file_content_response',
                'no_public_ui',
                'not_release_ready',
            ],
            'next_recommended_step' => 'developer_review_mutation_ladder_or_prepare_public_link_delivery_lifecycle_decision',
        ];

        if ($outputPath !== null && $outputPath !== '') {
            self::writeJson($outputPath, $report);
        }

        return $report;
    }

    /**
     * @param array<string, mixed> $planning
     * @param array<string, mixed> $revoke
     * @param array<string, mixed> $regenerate
     * @param array<string, mixed> $cleanup
     * @return list<array<string, mixed>>
     */
    private static function actionMatrix(array $planning, array $revoke, array $regenerate, array $cleanup): array
    {
        return [
            self::matrixRow(
                action: 'planning',
                label: 'Review mutation plan',
                state: 'review_surface_ready',
                purpose: 'Check the guarded action ladder before any individual action is reviewed.',
                source: $planning,
                reviewHref: '/larena/internal/public-link-guarded-admin-mutation-planning',
                machineHref: '/larena/internal/public-link-guarded-admin-mutation-planning?format=json',
                smokeCommand: 'php artisan larena:public-link-guarded-admin-mutation-planning-smoke --full',
                launchRecord: 'docs/project-management/launch-records/public-link-guarded-admin-mutation-planning-foundation.json',
                nextAction: 'Review individual revoke, regenerate or cleanup action surfaces.'
            ),
            self::matrixRow(
                action: 'revoke_link',
                label: 'Revoke public link',
                state: 'developer_preview_accepted',
                purpose: 'Review a local/testing revoke transition and rollback plan.',
                source: $revoke,
                reviewHref: '/larena/internal/public-link-revoke-action',
                machineHref: '/larena/internal/public-link-revoke-action?format=json',
                smokeCommand: 'php artisan larena:public-link-revoke-action-smoke --full',
                launchRecord: 'docs/project-management/launch-records/public-link-revoke-action-foundation.json',
                nextAction: 'Keep as preview evidence or prepare production revocation planning later.'
            ),
            self::matrixRow(
                action: 'regenerate_link',
                label: 'Regenerate public link',
                state: 'developer_preview_accepted',
                purpose: 'Review hash-only old/new fingerprint replacement and rollback plan.',
                source: $regenerate,
                reviewHref: '/larena/internal/public-link-regenerate-action',
                machineHref: '/larena/internal/public-link-regenerate-action?format=json',
                smokeCommand: 'php artisan larena:public-link-regenerate-action-smoke --full',
                launchRecord: 'docs/project-management/launch-records/public-link-regenerate-action-foundation.json',
                nextAction: 'Keep as preview evidence or prepare production regeneration planning later.'
            ),
            self::matrixRow(
                action: 'cleanup_links',
                label: 'Cleanup public links',
                state: 'dry_run_ready',
                purpose: 'Review expired, consumed and revoked candidate cleanup without deleting records or files.',
                source: $cleanup,
                reviewHref: '/larena/internal/public-link-cleanup-action',
                machineHref: '/larena/internal/public-link-cleanup-action?format=json',
                smokeCommand: 'php artisan larena:public-link-cleanup-action-smoke --full',
                launchRecord: 'docs/project-management/launch-records/public-link-cleanup-action-foundation.json',
                nextAction: 'Define retention policy and production cleanup planning before any real deletion.'
            ),
        ];
    }

    /**
     * @param array<string, mixed> $source
     * @return array<string, mixed>
     */
    private static function matrixRow(
        string $action,
        string $label,
        string $state,
        string $purpose,
        array $source,
        string $reviewHref,
        string $machineHref,
        string $smokeCommand,
        string $launchRecord,
        string $nextAction
    ): array {
        $status = self::describeStatus($state);

        return [
            'action' => $action,
            'label' => $label,
            'state' => $state,
            'state_label' => $status['label'],
            'state_hint' => $status['hint'],
            'source_status' => $source['status'] ?? 'unknown',
            'source_schema' => $source['schema'] ?? 'unknown',
            'purpose' => $purpose,
            'review_href' => $reviewHref,
            'machine_href' => $machineHref,
            'smoke_command' => $smokeCommand,
            'launch_record_ref' => $launchRecord,
            'next_action' => $nextAction,
            'machine_detail_preserved' => true,
            'safe_boundary' => [
                'mutates_state_in_preview' => (bool) ($source['mutates_state'] ?? false),
                'production_mutates_state' => (bool) ($source['production_mutates_state'] ?? true),
                'production_runtime' => (bool) ($source['safe_trace']['production_runtime'] ?? false),
                'release_ready' => (bool) ($source['safe_trace']['release_ready'] ?? false),
                'file_content_returned' => (bool) ($source['safe_trace']['file_content_returned'] ?? false),
                'queue_or_scheduler_executed' => (bool) (($source['safe_trace']['queue_or_scheduler_executed'] ?? false)
                    || ($source['safe_trace']['queue_executed'] ?? false)
                    || ($source['safe_trace']['scheduler_executed'] ?? false)),
            ],
        ];
    }

    /**
     * @param list<array<string, mixed>> $matrix
     * @return list<array{code: string, label: string, hint: string}>
     */
    private static function statusSemantics(array $matrix): array
    {
        $codes = array_values(array_unique(array_merge(
            array_column($matrix, 'state'),
            ['passed', 'blocked_future_launch_required', 'requires_explicit_launch_record', 'not_applicable_or_future_required']
        )));

        return array_map(
            static fn (string $code): array => self::describeStatus($code),
            $codes,
        );
    }

    /**
     * @return array{code: string, label: string, hint: string}
     */
    private static function describeStatus(string $code): array
    {
        return [
            'code' => $code,
            'label' => match ($code) {
                'dry_run_ready' => 'Dry-run ready',
                'review_surface_ready' => 'Review surface ready',
                'blocked_future_launch_required' => 'Future launch required',
                'not_applicable_or_future_required' => 'Not applicable yet',
                'requires_explicit_launch_record' => 'Explicit launch record required',
                'developer_preview_accepted' => 'Developer preview accepted',
                'passed' => 'Passed',
                default => str_replace(' ', ' ', ucwords(str_replace('_', ' ', $code))),
            },
            'hint' => match ($code) {
                'dry_run_ready' => 'The request was checked without mutating local state.',
                'review_surface_ready' => 'The workflow can be reviewed, but does not execute production runtime.',
                'blocked_future_launch_required' => 'A future launch record is required before this action can run.',
                'not_applicable_or_future_required' => 'This is not required for the current preview stage.',
                'requires_explicit_launch_record' => 'Execution is forbidden without the exact launch record.',
                'developer_preview_accepted' => 'Accepted only as developer-preview evidence, not as production readiness.',
                'passed' => 'The preview gate or check passed.',
                default => 'Machine code shown for traceability; the human label is generated from the code.',
            },
        ];
    }

    /**
     * @param array<string, mixed> ...$reports
     * @return array<string, bool>
     */
    private static function scopeBoundaries(array ...$reports): array
    {
        return [
            'local_testing_only' => true,
            'consolidated_review_only' => true,
            'new_mutation_behavior_added' => false,
            'production_mutates_state' => self::any($reports, 'production_mutates_state'),
            'production_runtime' => self::anySafeTrace($reports, 'production_runtime'),
            'public_delivery' => self::anySafeTrace($reports, 'public_delivery_enabled_by_this_action')
                || self::anySafeTrace($reports, 'public_delivery_enabled'),
            'file_deletion' => self::anySafeTrace($reports, 'file_deletion_executed'),
            'file_content_returned' => self::anySafeTrace($reports, 'file_content_returned'),
            'queue_or_scheduler' => self::anySafeTrace($reports, 'queue_or_scheduler_executed')
                || self::anySafeTrace($reports, 'queue_executed')
                || self::anySafeTrace($reports, 'scheduler_executed'),
            'raw_token_visible' => self::anySafeTrace($reports, 'raw_token_visible'),
            'release_ready' => self::anySafeTrace($reports, 'release_ready'),
        ];
    }

    /**
     * @param list<array<string, mixed>> $reports
     */
    private static function any(array $reports, string $field): bool
    {
        foreach ($reports as $report) {
            if (($report[$field] ?? false) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array<string, mixed>> $reports
     */
    private static function anySafeTrace(array $reports, string $field): bool
    {
        foreach ($reports as $report) {
            if (($report['safe_trace'][$field] ?? false) === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param list<array<string, mixed>> $reports
     */
    private static function allReportsPassed(array $reports): bool
    {
        foreach ($reports as $report) {
            if (($report['status'] ?? null) !== 'passed') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<array<string, mixed>> $matrix
     */
    private static function matrixPass(array $matrix): bool
    {
        if (count($matrix) !== 4) {
            return false;
        }

        foreach ($matrix as $row) {
            if (($row['label'] ?? '') === '' || ($row['state_label'] ?? '') === '' || ($row['state_hint'] ?? '') === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<array<string, mixed>> $matrix
     */
    private static function machineLinksPass(array $matrix): bool
    {
        foreach ($matrix as $row) {
            if (($row['review_href'] ?? '') === '' || ($row['machine_href'] ?? '') === '' || ($row['smoke_command'] ?? '') === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, bool> $boundaries
     */
    private static function boundariesPass(array $boundaries): bool
    {
        return $boundaries['consolidated_review_only'] === true
            && $boundaries['new_mutation_behavior_added'] === false
            && $boundaries['production_mutates_state'] === false
            && $boundaries['production_runtime'] === false
            && $boundaries['public_delivery'] === false
            && $boundaries['file_deletion'] === false
            && $boundaries['file_content_returned'] === false
            && $boundaries['queue_or_scheduler'] === false
            && $boundaries['raw_token_visible'] === false
            && $boundaries['release_ready'] === false;
    }

    /**
     * @param array<string, mixed> $report
     * @return array<string, mixed>
     */
    private static function sourceSummary(array $report): array
    {
        return [
            'schema' => $report['schema'] ?? 'unknown',
            'status' => $report['status'] ?? 'unknown',
            'scenario' => $report['scenario'] ?? 'unknown',
            'mutates_state' => $report['mutates_state'] ?? null,
            'production_mutates_state' => $report['production_mutates_state'] ?? null,
            'release_ready' => $report['safe_trace']['release_ready'] ?? null,
        ];
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
     * @param array<string, mixed> $payload
     */
    private static function writeJson(string $outputPath, array $payload): void
    {
        $directory = dirname($outputPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents(
            $outputPath,
            json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL,
        );
    }
}
