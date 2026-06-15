<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

final class PublicLinkDeliveryContractHardeningPreview
{
    /**
     * @param array<string, mixed> $runtime
     * @param array<string, mixed> $adapter
     * @param array<string, mixed> $planning
     * @return array<string, mixed>
     */
    public static function preview(
        array $runtime,
        array $adapter,
        array $planning,
        ?string $outputPath = null
    ): array {
        return self::run(
            $runtime,
            $adapter,
            PublicLinkMutationLadderReviewPreview::preview($planning),
            $outputPath,
        );
    }

    /**
     * @param array<string, mixed> $runtime
     * @param array<string, mixed> $adapter
     * @param array<string, mixed> $mutation
     * @return array<string, mixed>
     */
    public static function run(
        array $runtime,
        array $adapter,
        array $mutation,
        ?string $outputPath = null
    ): array {
        $matrix = self::deliveryMatrix();
        $headers = self::headerPolicy();
        $bodyPolicy = self::bodyPolicy($matrix);
        $boundary = self::scopeBoundary($runtime, $adapter, $mutation);
        $checks = [
            'launch_record_scope' => [
                'status' => 'passed',
                'launch_record_ref' => 'docs/project-management/launch-records/public-link-delivery-contract-hardening-foundation.json',
                'ready_to_code' => true,
                'local_testing_only' => true,
                'delivery_contract_only' => true,
                'production_public_delivery_allowed' => false,
                'file_streaming_allowed' => false,
                'release_ready_claim_allowed' => false,
            ],
            'source_slice_composition' => [
                'status' => self::allReportsPassed([$runtime, $adapter, $mutation]) ? 'passed' : 'failed',
                'source_schemas' => [
                    $runtime['schema'] ?? null,
                    $adapter['schema'] ?? null,
                    $mutation['schema'] ?? null,
                ],
            ],
            'delivery_decision_matrix' => [
                'status' => self::matrixPass($matrix) ? 'passed' : 'failed',
                'state_count' => count($matrix),
                'states' => array_column($matrix, 'state'),
            ],
            'http_status_policy' => [
                'status' => self::httpPolicyPass($matrix) ? 'passed' : 'failed',
                'allowed_active_status' => self::statusFor($matrix, 'active_allowed'),
                'blocked_statuses' => self::blockedStatuses($matrix),
            ],
            'safe_header_policy' => [
                'status' => self::headersPass($headers) ? 'passed' : 'failed',
                'headers' => $headers,
            ],
            'body_policy' => [
                'status' => self::bodyPolicyPass($bodyPolicy) ? 'passed' : 'failed',
                'file_body_included' => false,
                'body_policy' => $bodyPolicy,
            ],
            'access_audit_recheck_points' => [
                'status' => self::accessAuditPass($matrix) ? 'passed' : 'failed',
                'all_states_have_access_scope_ref' => true,
                'all_states_have_audit_event_ref' => true,
            ],
            'negative_delivery_guards' => [
                'status' => self::negativeGuardsPass($matrix) ? 'passed' : 'failed',
                'unknown_token_blocked' => self::decisionFor($matrix, 'unknown_token') === 'deny',
                'expired_link_blocked' => self::decisionFor($matrix, 'expired') === 'deny',
                'revoked_link_blocked' => self::decisionFor($matrix, 'revoked') === 'deny',
                'consumed_link_blocked' => self::decisionFor($matrix, 'consumed') === 'deny',
                'missing_access_blocked' => self::decisionFor($matrix, 'missing_access') === 'deny',
                'adapter_refused_blocked' => self::decisionFor($matrix, 'adapter_refused') === 'deny',
                'missing_file_blocked' => self::decisionFor($matrix, 'missing_file') === 'deny',
            ],
            'scope_boundary' => [
                'status' => self::boundaryPass($boundary) ? 'passed' : 'failed',
                ...$boundary,
            ],
        ];

        $report = [
            'schema' => 'larena.public_link_delivery_contract_hardening_foundation.v1',
            'status' => self::status($checks),
            'generated_at' => gmdate('c'),
            'mutates_state' => false,
            'production_mutates_state' => false,
            'cluster' => 'data-content-foundation',
            'scenario' => 'public_link_delivery_contract_hardening_foundation',
            'packages' => [
                'larena/link',
                'larena/filesystem',
                'larena/access',
                'larena/audit',
            ],
            'delivery_decision_matrix' => $matrix,
            'safe_header_policy' => $headers,
            'body_policy' => $bodyPolicy,
            'status_semantics' => self::statusSemantics($matrix),
            'source_reports' => [
                'runtime_hardening' => self::sourceSummary($runtime),
                'guarded_real_delivery_adapter' => self::sourceSummary($adapter),
                'mutation_ladder_review' => self::sourceSummary($mutation),
            ],
            'checks' => $checks,
            'scope_boundary' => $boundary,
            'safe_trace' => [
                'delivery_contract_hardening_available' => true,
                'local_testing_only' => true,
                'mutates_state' => false,
                'production_mutates_state' => false,
                'public_delivery_contract_only' => true,
                'production_public_delivery' => false,
                'adapter_stream_invoked' => false,
                'stream_now' => false,
                'file_body_included' => false,
                'file_content_returned' => false,
                'file_download_executed' => false,
                'one_time_consumption_runtime' => false,
                'destructive_cleanup_executed' => false,
                'queue_or_scheduler_executed' => false,
                'public_ui' => false,
                'production_runtime' => false,
                'release_ready' => false,
                'graph_sync_canonical_update' => false,
            ],
            'evidence_path' => $outputPath,
            'known_limitations' => [
                'developer_testable_delivery_contract_hardening_only',
                'no_real_file_body_streaming',
                'no_production_public_delivery',
                'no_public_ui',
                'no_destructive_cleanup',
                'no_scheduler_or_queue_cleanup',
                'not_release_ready',
            ],
            'next_recommended_step' => 'exit_public_link_focus_and_prepare_settings_property_admin_integration_goal',
        ];

        if ($outputPath !== null && $outputPath !== '') {
            self::writeJson($outputPath, $report);
        }

        return $report;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function deliveryMatrix(): array
    {
        return [
            self::matrixRow('active_allowed', 'Active link with access', 'allow_preview_only', 202, 'delivery_contract_ready_but_file_body_blocked', 'access.query_scope:public_link.active_delivery_preview', 'audit.event:public_link.delivery.contract.allowed_preview', 'decision_trace_only', 'adapter_ready_preview'),
            self::matrixRow('expired', 'Expired link', 'deny', 410, 'link_expired', 'access.query_scope:public_link.expired', 'audit.event:public_link.delivery.contract.expired_blocked', 'safe_problem_trace', 'adapter_blocked_expired'),
            self::matrixRow('revoked', 'Revoked link', 'deny', 410, 'link_revoked', 'access.query_scope:public_link.revoked', 'audit.event:public_link.delivery.contract.revoked_blocked', 'safe_problem_trace', 'adapter_blocked_revoked'),
            self::matrixRow('consumed', 'Already consumed one-time link', 'deny', 410, 'one_time_link_consumed', 'access.query_scope:public_link.consumed', 'audit.event:public_link.delivery.contract.consumed_blocked', 'safe_problem_trace', 'adapter_blocked_already_consumed'),
            self::matrixRow('missing_access', 'Missing access scope', 'deny', 403, 'access_scope_missing_or_denied', 'access.query_scope:public_link.missing_access', 'audit.event:public_link.delivery.contract.access_blocked', 'safe_problem_trace', 'adapter_blocked_missing_access'),
            self::matrixRow('unknown_token', 'Unknown token', 'deny', 404, 'token_not_found_or_not_disclosable', 'access.query_scope:public_link.unknown', 'audit.event:public_link.delivery.contract.unknown_blocked', 'safe_problem_trace', 'adapter_blocked_unknown'),
            self::matrixRow('adapter_refused', 'Delivery adapter refused', 'deny', 503, 'delivery_adapter_refused', 'access.query_scope:public_link.adapter_refused', 'audit.event:public_link.delivery.contract.adapter_refused', 'safe_problem_trace', 'adapter_blocked_refused'),
            self::matrixRow('missing_file', 'Target file missing', 'deny', 404, 'target_file_missing', 'access.query_scope:public_link.target_missing', 'audit.event:public_link.delivery.contract.target_missing', 'safe_problem_trace', 'adapter_blocked_target_missing'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function matrixRow(
        string $state,
        string $label,
        string $decision,
        int $httpStatus,
        string $reason,
        string $accessScopeRef,
        string $auditEventRef,
        string $bodyKind,
        string $adapterState
    ): array {
        $status = self::describeStatus($state);

        return [
            'state' => $state,
            'label' => $label,
            'state_label' => $status['label'],
            'state_hint' => $status['hint'],
            'decision' => $decision,
            'http_status' => $httpStatus,
            'reason' => $reason,
            'access_scope_ref' => $accessScopeRef,
            'audit_event_ref' => $auditEventRef,
            'adapter_state' => $adapterState,
            'body_kind' => $bodyKind,
            'body_policy' => [
                'file_body_included' => false,
                'safe_problem_or_decision_trace_only' => true,
                'raw_token_visible' => false,
            ],
            'headers' => [
                'X-Larena-Public-Link-State' => $state,
                'X-Larena-Delivery-Contract' => 'developer-preview',
                'X-Larena-File-Body' => 'blocked',
                'X-Larena-Production-Delivery' => 'false',
            ],
            'requires_future_launch_record_for_real_delivery' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function headerPolicy(): array
    {
        return [
            'X-Larena-Delivery-Contract' => 'developer-preview',
            'X-Larena-File-Body' => 'blocked',
            'X-Larena-Production-Delivery' => 'false',
            'Cache-Control' => 'no-store',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'",
        ];
    }

    /**
     * @param list<array<string, mixed>> $matrix
     * @return array<string, mixed>
     */
    private static function bodyPolicy(array $matrix): array
    {
        return [
            'default_body_kind' => 'safe_problem_or_decision_trace_only',
            'file_body_included' => false,
            'raw_token_visible' => false,
            'body_kinds' => array_values(array_unique(array_column($matrix, 'body_kind'))),
        ];
    }

    /**
     * @param array<string, mixed> ...$reports
     * @return array<string, bool>
     */
    private static function scopeBoundary(array ...$reports): array
    {
        return [
            'local_testing_only' => true,
            'delivery_contract_only' => true,
            'mutates_state' => false,
            'production_mutates_state' => self::any($reports, 'production_mutates_state'),
            'public_delivery' => false,
            'adapter_stream_invoked' => self::anySafeTrace($reports, 'adapter_stream_invoked'),
            'file_download_executed' => self::anySafeTrace($reports, 'file_download_executed'),
            'file_content_returned' => self::anySafeTrace($reports, 'file_content_returned'),
            'destructive_cleanup_executed' => false,
            'queue_or_scheduler_executed' => self::anySafeTrace($reports, 'queue_or_scheduler_executed')
                || self::anySafeTrace($reports, 'queue_executed')
                || self::anySafeTrace($reports, 'scheduler_executed'),
            'public_ui' => false,
            'production_runtime' => self::anySafeTrace($reports, 'production_runtime'),
            'release_ready' => self::anySafeTrace($reports, 'release_ready'),
        ];
    }

    /**
     * @param list<array<string, mixed>> $matrix
     */
    private static function matrixPass(array $matrix): bool
    {
        if (count($matrix) < 8) {
            return false;
        }

        foreach ($matrix as $row) {
            foreach (['state', 'decision', 'http_status', 'access_scope_ref', 'audit_event_ref', 'body_policy', 'headers'] as $field) {
                if (!array_key_exists($field, $row)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param list<array<string, mixed>> $matrix
     */
    private static function httpPolicyPass(array $matrix): bool
    {
        return self::statusFor($matrix, 'active_allowed') === 202
            && self::statusFor($matrix, 'expired') === 410
            && self::statusFor($matrix, 'revoked') === 410
            && self::statusFor($matrix, 'consumed') === 410
            && self::statusFor($matrix, 'missing_access') === 403
            && self::statusFor($matrix, 'unknown_token') === 404
            && self::statusFor($matrix, 'adapter_refused') === 503
            && self::statusFor($matrix, 'missing_file') === 404;
    }

    /**
     * @param array<string, mixed> $headers
     */
    private static function headersPass(array $headers): bool
    {
        return ($headers['X-Larena-File-Body'] ?? null) === 'blocked'
            && ($headers['X-Larena-Production-Delivery'] ?? null) === 'false'
            && ($headers['Cache-Control'] ?? null) === 'no-store'
            && ($headers['X-Content-Type-Options'] ?? null) === 'nosniff';
    }

    /**
     * @param array<string, mixed> $bodyPolicy
     */
    private static function bodyPolicyPass(array $bodyPolicy): bool
    {
        return ($bodyPolicy['file_body_included'] ?? true) === false
            && ($bodyPolicy['raw_token_visible'] ?? true) === false;
    }

    /**
     * @param list<array<string, mixed>> $matrix
     */
    private static function accessAuditPass(array $matrix): bool
    {
        foreach ($matrix as $row) {
            if (($row['access_scope_ref'] ?? '') === '' || ($row['audit_event_ref'] ?? '') === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param list<array<string, mixed>> $matrix
     */
    private static function negativeGuardsPass(array $matrix): bool
    {
        foreach (['expired', 'revoked', 'consumed', 'missing_access', 'unknown_token', 'adapter_refused', 'missing_file'] as $state) {
            if (self::decisionFor($matrix, $state) !== 'deny') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, bool> $boundary
     */
    private static function boundaryPass(array $boundary): bool
    {
        return $boundary['local_testing_only'] === true
            && $boundary['delivery_contract_only'] === true
            && $boundary['mutates_state'] === false
            && $boundary['production_mutates_state'] === false
            && $boundary['public_delivery'] === false
            && $boundary['adapter_stream_invoked'] === false
            && $boundary['file_download_executed'] === false
            && $boundary['file_content_returned'] === false
            && $boundary['destructive_cleanup_executed'] === false
            && $boundary['queue_or_scheduler_executed'] === false
            && $boundary['public_ui'] === false
            && $boundary['production_runtime'] === false
            && $boundary['release_ready'] === false;
    }

    /**
     * @param list<array<string, mixed>> $matrix
     */
    private static function statusFor(array $matrix, string $state): ?int
    {
        foreach ($matrix as $row) {
            if (($row['state'] ?? null) === $state) {
                return is_int($row['http_status'] ?? null) ? $row['http_status'] : null;
            }
        }

        return null;
    }

    /**
     * @param list<array<string, mixed>> $matrix
     * @return array<string, int>
     */
    private static function blockedStatuses(array $matrix): array
    {
        $statuses = [];
        foreach ($matrix as $row) {
            if (($row['decision'] ?? null) === 'deny' && is_string($row['state'] ?? null) && is_int($row['http_status'] ?? null)) {
                $statuses[$row['state']] = $row['http_status'];
            }
        }

        return $statuses;
    }

    /**
     * @param list<array<string, mixed>> $matrix
     */
    private static function decisionFor(array $matrix, string $state): ?string
    {
        foreach ($matrix as $row) {
            if (($row['state'] ?? null) === $state) {
                return is_string($row['decision'] ?? null) ? $row['decision'] : null;
            }
        }

        return null;
    }

    /**
     * @param list<array<string, mixed>> $matrix
     * @return list<array{code: string, label: string, hint: string}>
     */
    private static function statusSemantics(array $matrix): array
    {
        return array_map(
            static fn (string $code): array => self::describeStatus($code),
            array_column($matrix, 'state'),
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
                'active_allowed' => 'Active link with access',
                'expired' => 'Expired link',
                'revoked' => 'Revoked link',
                'consumed' => 'Already consumed one-time link',
                'missing_access' => 'Missing access scope',
                'unknown_token' => 'Unknown token',
                'adapter_refused' => 'Delivery adapter refused',
                'missing_file' => 'Target file missing',
                default => ucwords(str_replace('_', ' ', $code)),
            },
            'hint' => match ($code) {
                'active_allowed' => 'The contract allows only a decision trace; real file delivery needs a future launch record.',
                'expired' => 'Expired links fail closed with a gone response.',
                'revoked' => 'Revoked links fail closed with a gone response.',
                'consumed' => 'Consumed one-time links fail closed with a gone response.',
                'missing_access' => 'Missing access scope fails closed before adapter delivery.',
                'unknown_token' => 'Unknown tokens stay non-disclosable.',
                'adapter_refused' => 'Adapter refusal is represented as unavailable without streaming a file body.',
                'missing_file' => 'Missing targets fail closed without exposing storage details.',
                default => 'Machine code shown for traceability; the human label is generated from the code.',
            },
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
     * @param array<string, mixed> $report
     * @return array<string, mixed>
     */
    private static function sourceSummary(array $report): array
    {
        return [
            'schema' => $report['schema'] ?? null,
            'status' => $report['status'] ?? null,
            'scenario' => $report['scenario'] ?? null,
            'mutates_state' => $report['mutates_state'] ?? false,
            'production_mutates_state' => $report['production_mutates_state'] ?? false,
            'production_runtime' => $report['safe_trace']['production_runtime'] ?? false,
            'release_ready' => $report['safe_trace']['release_ready'] ?? false,
        ];
    }

    /**
     * @param array<string, mixed> $report
     */
    private static function writeJson(string $path, array $report): void
    {
        $directory = dirname($path);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents($path, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }
}
